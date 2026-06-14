<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    private string $token;

    public function __construct()
    {
        $this->token = config('services.telegram.token', '');
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
        $text .= "Дата: " . $service->expires_at->format('Y-m-d') . " ({$daysText})\n";
        if ($service->ip) $text .= "IP: `{$service->ip}`\n";
        if ($service->provider_name) $text .= "Провайдер: {$service->provider_name}\n";
        if ($service->cost) $text .= "💰 {$service->cost} {$service->currency}\n";
        if ($service->provider_url) $text .= "🔗 {$service->provider_url}";

        $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $user->tg_chat_id,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

        return $response->successful();
    }

    public function sendMessage(string $chatId, string $text): bool
    {
        if (!$this->token) return false;

        return Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ])->successful();
    }
}
