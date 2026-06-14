<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TelegramBotController extends Controller
{
    public function handle(Request $request, TelegramService $telegram): JsonResponse
    {
        $secret = config('services.telegram.webhook_secret');
        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response()->json(['ok' => false], 403);
        }

        $update = $request->all();

        // Handle inline keyboard button taps
        if (!empty($update['callback_query'])) {
            $this->handleCallback($update['callback_query'], $telegram);
            return response()->json(['ok' => true]);
        }

        $message = $update['message'] ?? null;
        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $message['chat']['id'];
        $text   = trim($message['text'] ?? '');

        match (true) {
            str_starts_with($text, '/start') => $this->handleStart($chatId, $text, $telegram),
            $text === '/list'                => $this->handleList($chatId, $telegram),
            $text === '/menu'                => $this->handleMenu($chatId, $telegram),
            $text === '/status'              => $this->handleStatus($chatId, $telegram),
            $text === '/help'                => $this->handleHelp($chatId, $telegram),
            $text === '/unlink'              => $this->handleUnlink($chatId, $telegram),
            default                          => $this->handleUnknown($chatId, $text, $telegram),
        };

        return response()->json(['ok' => true]);
    }

    private function handleCallback(array $cb, TelegramService $telegram): void
    {
        $cbId      = $cb['id'];
        $chatId    = (string) $cb['message']['chat']['id'];
        $messageId = (int) $cb['message']['message_id'];
        $data      = $cb['data'] ?? '';

        $user = User::where('tg_chat_id', $chatId)->first();
        if (!$user) {
            $telegram->answerCallbackQuery($cbId, '❌ Аккаунт не подключён', true);
            return;
        }

        if ($data === 'list') {
            $telegram->answerCallbackQuery($cbId);
            $list = $telegram->buildServiceListMessage($user);
            $telegram->editMessageText($chatId, $messageId, $list['text'], $list['keyboard']);
            return;
        }

        if (str_starts_with($data, 'select:')) {
            $serviceId = (int) substr($data, 7);
            $service   = $user->services()->find($serviceId);
            if (!$service) {
                $telegram->answerCallbackQuery($cbId, '❌ Сервис не найден', true);
                return;
            }

            $text = "🔄 *{$service->name}*\n";
            if ($service->expires_at) {
                $daysLeft = $service->days_left;
                $daysTxt  = $daysLeft < 0 ? abs($daysLeft).' дн. назад' : ($daysLeft === 0 ? 'сегодня' : "{$daysLeft} дн.");
                $text .= "📅 {$service->expires_at->format('d.m.Y')} · {$daysTxt}\n";
            }
            if ($service->cost) $text .= "💰 {$service->cost} {$service->currency}\n";
            $text .= "\nВыберите период продления:";

            $keyboard = ['inline_keyboard' => [
                [
                    ['text' => '+1 мес',  'callback_data' => "renew:{$serviceId}:1"],
                    ['text' => '+3 мес',  'callback_data' => "renew:{$serviceId}:3"],
                    ['text' => '+6 мес',  'callback_data' => "renew:{$serviceId}:6"],
                    ['text' => '+12 мес', 'callback_data' => "renew:{$serviceId}:12"],
                ],
                [
                    ['text' => '← Назад к списку', 'callback_data' => 'list'],
                ],
            ]];

            $telegram->answerCallbackQuery($cbId);
            $telegram->editMessageText($chatId, $messageId, $text, $keyboard);
            return;
        }

        if (str_starts_with($data, 'renew:')) {
            [$_, $serviceId, $months] = explode(':', $data, 3);
            $service = $user->services()->find((int) $serviceId);
            if (!$service) {
                $telegram->answerCallbackQuery($cbId, '❌ Сервис не найден', true);
                return;
            }

            $from    = $service->expires_at ?? now();
            $newDate = $from->addMonths((int) $months);
            $service->update(['expires_at' => $newDate]);

            $text = "✅ *{$service->name}* продлено\n";
            $text .= "📅 Новая дата: *{$newDate->format('d.m.Y')}*";

            $keyboard = ['inline_keyboard' => [
                [
                    ['text' => '🔄 Продлить ещё', 'callback_data' => "select:{$service->id}"],
                    ['text' => '← К списку',      'callback_data' => 'list'],
                ],
            ]];

            $telegram->answerCallbackQuery($cbId, "Продлено на {$months} мес. ✅");
            $telegram->editMessageText($chatId, $messageId, $text, $keyboard);
            return;
        }

        $telegram->answerCallbackQuery($cbId);
    }

    private function handleStart(string $chatId, string $text, TelegramService $telegram): void
    {
        $parts = explode(' ', $text, 2);
        $code  = $parts[1] ?? '';

        if ($code) {
            $user = User::where('tg_code', $code)->first();
            if ($user) {
                $user->update(['tg_chat_id' => $chatId, 'tg_connected_at' => now()]);
                $telegram->sendMessage($chatId,
                    "✅ Telegram подключён к аккаунту *{$user->name}*!\n\n" .
                    "Вы будете получать уведомления о сроках истечения сервисов.\n\n" .
                    "Команды:\n" .
                    "/list — ближайшие истечения\n" .
                    "/status — статистика\n" .
                    "/help — помощь"
                );
            } else {
                $telegram->sendMessage($chatId, '❌ Код не найден. Проверьте код в настройках SubTracker.');
            }
        } else {
            $appUrl = config('app.url');
            $telegram->sendMessage($chatId,
                "👋 Привет! Я бот *SubTracker* — трекер подписок и серверов.\n\n" .
                "🔔 Отслеживайте VPS, домены, облачные сервисы и подписки. Получайте уведомления до истечения срока оплаты.\n\n" .
                "✅ *Основные возможности:*\n" .
                "• 85+ популярных сервисов в каталоге\n" .
                "• Уведомления за 1, 3, 7, 14, 30 дней\n" .
                "• REST API, экспорт XLSX/PDF/JSON\n" .
                "• Продление сервисов прямо из Telegram\n\n" .
                "🆓 *Регистрация бесплатна:*\n" .
                "{$appUrl}/register\n\n" .
                "Уже есть аккаунт? Подключите Telegram:\n" .
                "Настройки → Уведомления → скопируйте код → /start КОД"
            );
        }
    }

    private function handleList(string $chatId, TelegramService $telegram): void
    {
        $user = User::where('tg_chat_id', $chatId)->first();
        if (!$user) {
            $telegram->sendMessage($chatId, '❌ Аккаунт не подключён. Используйте /start КОД');
            return;
        }

        $list = $telegram->buildServiceListMessage($user);
        $telegram->sendMessage($chatId, $list['text'], $list['keyboard']);
    }

    private function handleStatus(string $chatId, TelegramService $telegram): void
    {
        $user = User::where('tg_chat_id', $chatId)->first();
        if (!$user) {
            $telegram->sendMessage($chatId, '❌ Аккаунт не подключён. Используйте /start КОД');
            return;
        }

        $total    = $user->services()->count();
        $overdue  = $user->services()->whereNotNull('expires_at')->whereDate('expires_at', '<', now())->count();
        $week     = $user->services()->whereNotNull('expires_at')->whereBetween('expires_at', [now(), now()->addDays(7)])->count();
        $month    = $user->services()->whereNotNull('expires_at')->whereBetween('expires_at', [now(), now()->addDays(30)])->count();

        $text = "📊 *Статистика SubTracker*\n\n";
        $text .= "Всего сервисов: *{$total}*\n";
        if ($overdue)  $text .= "🔴 Просрочено: *{$overdue}*\n";
        if ($week)     $text .= "🟠 В 7 дней: *{$week}*\n";
        if ($month)    $text .= "🟡 В 30 дней: *{$month}*\n";
        if (!$overdue && !$week && !$month) $text .= "✅ Всё в порядке!\n";

        $keyboard = ['inline_keyboard' => [
            [['text' => '📋 Ближайшие истечения', 'callback_data' => 'list']],
        ]];

        $telegram->sendMessage($chatId, $text, $keyboard);
    }

    private function handleMenu(string $chatId, TelegramService $telegram): void
    {
        $user = User::where('tg_chat_id', $chatId)->first();
        if (!$user) {
            $telegram->sendMessage($chatId, '❌ Аккаунт не подключён. Используйте /start КОД');
            return;
        }

        $telegram->sendMessage($chatId, "Выберите действие:", ['inline_keyboard' => [
            [
                ['text' => '📋 Список истечений', 'callback_data' => 'list'],
                ['text' => '📊 Статистика',        'callback_data' => 'status'],
            ],
        ]]);
    }

    private function handleHelp(string $chatId, TelegramService $telegram): void
    {
        $telegram->sendMessage($chatId,
            "*Команды SubTracker бота:*\n\n" .
            "/list — ближайшие 10 истечений с кнопками продления\n" .
            "/status — общая статистика аккаунта\n" .
            "/menu — интерактивное меню\n" .
            "/unlink — отвязать Telegram от аккаунта\n" .
            "/help — эта справка\n\n" .
            "💡 Нажмите *Продлить* рядом с сервисом, чтобы продлить его прямо из Telegram."
        );
    }

    private function handleUnlink(string $chatId, TelegramService $telegram): void
    {
        $user = User::where('tg_chat_id', $chatId)->first();
        if ($user) {
            $user->update(['tg_chat_id' => null, 'tg_connected_at' => null]);
            $telegram->sendMessage($chatId, '🔓 Аккаунт отвязан. Уведомления отключены.');
        } else {
            $telegram->sendMessage($chatId, 'Аккаунт уже не подключён.');
        }
    }

    private function handleUnknown(string $chatId, string $text, TelegramService $telegram): void
    {
        if (!str_starts_with($text, '/')) {
            return;
        }
        $telegram->sendMessage($chatId, "Неизвестная команда. Используйте /help для списка команд.");
    }
}
