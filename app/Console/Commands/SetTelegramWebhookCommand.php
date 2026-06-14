<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhookCommand extends Command
{
    protected $signature = 'telegram:webhook {action=set : set или delete}';
    protected $description = 'Register or remove Telegram bot webhook';

    public function handle(): void
    {
        $token  = config('services.telegram.token');
        $secret = config('services.telegram.webhook_secret');

        if (!$token) {
            $this->error('TELEGRAM_BOT_TOKEN not set in .env');
            return;
        }

        $action = $this->argument('action');

        if ($action === 'delete') {
            $response = Http::post("https://api.telegram.org/bot{$token}/deleteWebhook");
            $this->info($response->json('description') ?? 'Webhook deleted');
            return;
        }

        $url = config('app.url') . '/api/telegram/webhook';

        $this->info("Registering webhook: {$url}");

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url'             => $url,
            'secret_token'    => $secret ?: null,
            'allowed_updates' => ['message', 'callback_query'],
            'drop_pending_updates' => true,
        ]);

        $result = $response->json();

        if ($result['ok'] ?? false) {
            $this->info('✅ ' . ($result['description'] ?? 'Webhook set successfully'));
        } else {
            $this->error('❌ ' . ($result['description'] ?? 'Failed'));
        }

        // Show current webhook info
        $info = Http::get("https://api.telegram.org/bot{$token}/getWebhookInfo")->json();
        if (!empty($info['result']['url'])) {
            $this->line("Active URL: " . $info['result']['url']);
            $this->line("Pending: " . ($info['result']['pending_update_count'] ?? 0));
        }
    }
}
