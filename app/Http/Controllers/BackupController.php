<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Service;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function download(Request $request, BackupService $backup)
    {
        $format = $request->get('format', 'json');
        $user = Auth::user();
        $filename = 'subtracker-backup-' . now()->format('Y-m-d');

        if ($format === 'json') {
            $data = json_encode($backup->exportJson($user), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return response($data, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
            ]);
        }

        // SQL format
        $sql = $this->generateSql($user);
        return response($sql, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}.sql\"",
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json,csv,txt,sql'],
            'mode' => ['nullable', 'in:merge,clear'],
        ]);

        $user = Auth::user();
        $mode = $request->get('mode', 'merge');
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if ($mode === 'clear') {
            $user->services()->forceDelete();
            $user->groups()->forceDelete();
        }

        $created = ['groups' => 0, 'services' => 0];

        if ($ext === 'json') {
            $data = json_decode(file_get_contents($file->getRealPath()), true);
            if (!$data || !isset($data['version'])) {
                return back()->withErrors(['file' => 'Неверный формат файла']);
            }

            $groupMap = [];
            foreach ($data['groups'] ?? [] as $g) {
                $group = $user->groups()->create($g);
                $created['groups']++;
                $groupMap[$g['name']] = $group->id;
            }

            foreach ($data['services'] ?? [] as $s) {
                unset($s['api_token'], $s['tg_chat_id']);
                $s['group_id'] = $groupMap[$s['group_name'] ?? ''] ?? null;
                unset($s['group_name']);
                $user->services()->create($s);
                $created['services']++;
            }
        } elseif ($ext === 'csv') {
            $lines = array_map('str_getcsv', file($file->getRealPath()));
            $headers = array_shift($lines);
            foreach ($lines as $row) {
                $data = array_combine($headers, $row);
                $user->services()->create([
                    'name' => $data['name'] ?? '',
                    'ip' => $data['ip'] ?? null,
                    'url' => $data['url'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'cost' => $data['cost'] ?? null,
                    'currency' => $data['currency'] ?? 'RUB',
                ]);
                $created['services']++;
            }
        }

        return back()->with('success', "Импортировано: {$created['groups']} групп, {$created['services']} сервисов");
    }

    private function generateSql($user): string
    {
        $sql = "-- SubTracker SQL Backup\n-- User: {$user->email}\n-- Date: " . now()->toIso8601String() . "\n\n";

        foreach ($user->services as $s) {
            $name = addslashes($s->name);
            $sql .= "INSERT INTO services (user_id, name, ip, url, expires_at, cost, currency) VALUES ({$user->id}, '{$name}', '" . addslashes($s->ip ?? '') . "', '" . addslashes($s->url ?? '') . "', '" . ($s->expires_at?->toDateString() ?? 'NULL') . "', " . ($s->cost ?? 'NULL') . ", '{$s->currency}');\n";
        }

        return $sql;
    }
}
