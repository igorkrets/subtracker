<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    private string $token;
    private string $baseUrl;

    public function __construct()
    {
        $this->token   = config('services.telegram.token', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function sendNotification(Service $service, User $user): bool
    {
        if (!$user->tg_chat_id || !$this->token) {
            return false;
        }

        $daysLeft = $service->days_left;
        $daysText = $daysLeft < 0
            ? "просрочено на " . abs($daysLeft) . " дн."
            : "осталось {$daysLeft} дн.";

        $text = "🔔 *SubTracker*\n";
        $text .= "Истекает: *{$service->name}*\n";
        $text .= "Дата: " . $service->expires_at->format('d.m.Y') . " ({$daysText})\n";
        if ($service->ip) $text .= "IP: `{$service->ip}`\n";
        if ($service->provider_name) $text .= "Провайдер: {$service->provider_name}\n";
        if ($service->cost) $text .= "💰 {$service->cost} {$service->currency}\n";
        if ($service->provider_url) $text .= "🔗 {$service->provider_url}";

        $keyboard = ['inline_keyboard' => [[
            ['text' => '🔄 Продлить', 'callback_data' => "select:{$service->id}"],
        ]]];

        return $this->sendMessage($user->tg_chat_id, $text, $keyboard);
    }

    public function sendMessage(string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        if (!$this->token) return false;

        $params = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ];

        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return Http::post("{$this->baseUrl}/sendMessage", $params)->successful();
    }

    public function editMessageText(string $chatId, int $messageId, string $text, ?array $replyMarkup = null): bool
    {
        if (!$this->token) return false;

        $params = [
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ];

        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return Http::post("{$this->baseUrl}/editMessageText", $params)->successful();
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool
    {
        if (!$this->token) return false;

        return Http::post("{$this->baseUrl}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
            'show_alert'        => $showAlert,
        ])->successful();
    }

    public function buildServiceListMessage(User $user): array
    {
        $services = $user->services()
            ->whereNotNull('expires_at')
            ->where('deleted_at', null)
            ->orderBy('expires_at')
            ->limit(10)
            ->get();

        if ($services->isEmpty()) {
            return ['text' => '✅ Нет предстоящих истечений.', 'keyboard' => null];
        }

        $text = "📋 *Ближайшие истечения:*\n\n";
        $buttons = [];
        $row = [];

        foreach ($services as $i => $s) {
            $daysLeft = $s->days_left;
            if ($daysLeft === null) continue;
            $icon = $daysLeft < 0 ? '🔴' : ($daysLeft <= 7 ? '🟠' : ($daysLeft <= 30 ? '🟡' : '🟢'));
            $daysTxt = $daysLeft < 0 ? abs($daysLeft).' дн. назад' : ($daysLeft === 0 ? 'сегодня' : "{$daysLeft} дн.");
            $text .= "{$icon} *{$s->name}* — {$daysTxt} ({$s->expires_at->format('d.m.Y')})\n";

            $row[] = ['text' => "🔄 {$s->name}", 'callback_data' => "select:{$s->id}"];
            if (count($row) === 2) {
                $buttons[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) $buttons[] = $row;

        $keyboard = empty($buttons) ? null : ['inline_keyboard' => $buttons];

        return ['text' => $text, 'keyboard' => $keyboard];
    }
}
