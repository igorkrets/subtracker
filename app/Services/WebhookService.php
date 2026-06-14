<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    public function sendNotification(Service $service, User $user): void
    {
        $webhooks = Webhook::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $this->send($webhook, $service);
        }
    }

    public function send(Webhook $webhook, Service $service): bool
    {
        $payload = $this->buildPayload($webhook, $service);
        $body = json_encode($payload);

        $headers = array_merge(
            ['Content-Type' => 'application/json'],
            $webhook->headers ?? []
        );

        if ($webhook->secret) {
            $headers['X-SubTracker-Signature'] = 'sha256=' . hash_hmac('sha256', $body, $webhook->secret);
        }

        $startTime = microtime(true);
        try {
            $method = strtolower($webhook->method);
            $response = Http::withHeaders($headers)->$method($webhook->url, $payload);
            $statusCode = $response->status();
            $responseBody = substr($response->body(), 0, 1000);
        } catch (\Exception $e) {
            $statusCode = 0;
            $responseBody = $e->getMessage();
        }

        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'service_id' => $service->id,
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'sent_at' => now(),
        ]);

        return $statusCode >= 200 && $statusCode < 300;
    }

    private function buildPayload(Webhook $webhook, Service $service): array
    {
        if ($webhook->payload_template) {
            return $webhook->payload_template;
        }

        return [
            'event' => 'subscription.expiring',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'ip' => $service->ip,
                'url' => $service->url,
                'expires_at' => $service->expires_at?->toDateString(),
                'days_left' => $service->days_left,
                'cost' => $service->cost,
                'currency' => $service->currency,
                'provider' => $service->provider_name,
                'group' => $service->group?->name,
            ],
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
