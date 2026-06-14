<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ExportService
{
    public function getServicesQuery(User $user, array $filters = []): Builder
    {
        $query = Service::with(['group', 'serviceType'])->where('user_id', $user->id);

        if (!empty($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }
        if (!empty($filters['status'])) {
            $today = now()->toDateString();
            match ($filters['status']) {
                'today' => $query->whereDate('expires_at', $today),
                'week' => $query->whereBetween('expires_at', [$today, now()->addDays(7)->toDateString()]),
                'month' => $query->whereBetween('expires_at', [$today, now()->addDays(30)->toDateString()]),
                'overdue' => $query->where('expires_at', '<', $today),
                default => null,
            };
        }
        if (!empty($filters['search'])) {
            $q = '%' . $filters['search'] . '%';
            $query->where(fn($b) => $b
                ->where('name', 'like', $q)
                ->orWhere('ip', 'like', $q)
                ->orWhere('url', 'like', $q)
                ->orWhere('notes', 'like', $q)
                ->orWhere('provider_name', 'like', $q)
            );
        }

        return $query->orderBy('expires_at');
    }

    public function toHtml(User $user, array $filters = []): string
    {
        $services = $this->getServicesQuery($user, $filters)->get();
        $date = now()->format('Y-m-d H:i');

        $rows = '';
        foreach ($services as $s) {
            $rows .= "<tr>
                <td style='padding:8px;border:1px solid #ddd'>{$s->name}</td>
                <td style='padding:8px;border:1px solid #ddd'>" . ($s->ip ?? '') . "</td>
                <td style='padding:8px;border:1px solid #ddd'>" . ($s->provider_name ?? '') . "</td>
                <td style='padding:8px;border:1px solid #ddd'>" . ($s->expires_at?->format('Y-m-d') ?? 'Без даты') . "</td>
                <td style='padding:8px;border:1px solid #ddd'>" . ($s->cost ? "{$s->cost} {$s->currency}" : '') . "</td>
                <td style='padding:8px;border:1px solid #ddd'>" . ($s->group?->name ?? 'Без группы') . "</td>
            </tr>";
        }

        return "<!DOCTYPE html><html lang='ru'><head><meta charset='UTF-8'>
            <title>SubTracker Export — {$date}</title></head><body>
            <h1 style='font-family:sans-serif'>SubTracker — Экспорт сервисов</h1>
            <p style='font-family:sans-serif;color:#666'>Сгенерировано: {$date}</p>
            <table style='border-collapse:collapse;width:100%;font-family:sans-serif;font-size:14px'>
            <thead><tr style='background:#f0f0f0'>
                <th style='padding:8px;border:1px solid #ddd;text-align:left'>Название</th>
                <th style='padding:8px;border:1px solid #ddd;text-align:left'>IP</th>
                <th style='padding:8px;border:1px solid #ddd;text-align:left'>Провайдер</th>
                <th style='padding:8px;border:1px solid #ddd;text-align:left'>Истекает</th>
                <th style='padding:8px;border:1px solid #ddd;text-align:left'>Стоимость</th>
                <th style='padding:8px;border:1px solid #ddd;text-align:left'>Группа</th>
            </tr></thead><tbody>{$rows}</tbody></table></body></html>";
    }
}
