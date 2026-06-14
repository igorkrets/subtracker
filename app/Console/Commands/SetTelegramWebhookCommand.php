<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhookCommand extends Command
{
    protected $signature = 'telegram:webhook:set';
    protected $description = 'Register Telegram bot webhook';

    public function handle(): void
    {
        $token = config('services.telegram.token');
        $secret = config('services.telegram.webhook_secret');
        $url = config('app.url') . '/api/telegram/webhook';

        if (!$token) {
            $this->error('TELEGRAM_BOT_TOKEN not set.');
            return;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => ['message'],
        ]);

        $this->info($response->json('description') ?? 'Done');
    }
}
