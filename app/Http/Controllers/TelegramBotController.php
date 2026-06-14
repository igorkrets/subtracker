<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    public function handle(Request $request, TelegramService $telegram)
    {
        $secret = config('services.telegram.webhook_secret');
        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response('Forbidden', 403);
        }

        $update = $request->all();
        $message = $update['message'] ?? null;

        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');

        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text, 2);
            $code = $parts[1] ?? '';

            if ($code) {
                $user = User::where('tg_code', $code)->first();
                if ($user) {
                    $user->update(['tg_chat_id' => $chatId, 'tg_connected_at' => now()]);
                    $telegram->sendMessage($chatId, "✅ Telegram подключён к аккаунту *{$user->name}*! Вы будете получать уведомления о сроках.");
                } else {
                    $telegram->sendMessage($chatId, '❌ Код не найден. Проверьте код в настройках SubTracker.');
                }
            } else {
                $telegram->sendMessage($chatId, "👋 Привет! Я бот SubTracker.\nДля подключения уведомлений войдите в настройки SubTracker и используйте команду `/start ВАШ_КОД`.");
            }
        } elseif ($text === '/list') {
            $user = User::where('tg_chat_id', $chatId)->first();
            if (!$user) {
                $telegram->sendMessage($chatId, '❌ Аккаунт не подключён. Используйте /start КОД');
                return response()->json(['ok' => true]);
            }
            $services = $user->services()
                ->whereNotNull('expires_at')
                ->orderBy('expires_at')
                ->limit(5)
                ->get();
            $msg = "📋 *Ближайшие истечения:*\n";
            foreach ($services as $s) {
                $msg .= "• {$s->name} — {$s->expires_at->format('Y-m-d')} ({$s->days_left} дн.)\n";
            }
            $telegram->sendMessage($chatId, $msg ?: 'Нет предстоящих истечений.');
        } elseif ($text === '/unlink') {
            $user = User::where('tg_chat_id', $chatId)->first();
            if ($user) {
                $user->update(['tg_chat_id' => null, 'tg_connected_at' => null]);
                $telegram->sendMessage($chatId, '🔓 Аккаунт отвязан. Уведомления отключены.');
            }
        }

        return response()->json(['ok' => true]);
    }
}
