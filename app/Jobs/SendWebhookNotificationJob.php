<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\Service;
use App\Models\User;
use App\Services\WebhookService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class SendWebhookNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;
    public array $backoff = [60, 300, 900];

    public function __construct(
        private Service $service,
        private User $user
    ) {}

    public function handle(WebhookService $webhookService): void
    {
        $today = Carbon::now($this->user->timezone)->toDateString();

        $alreadySent = NotificationLog::where('service_id', $this->service->id)
            ->where('sent_date', $today)
            ->where('channel', 'webhook')
            ->exists();

        if ($alreadySent) return;

        $sent = $webhookService->sendNotification($this->service, $this->user);

        if (!$sent) {
            throw new RuntimeException("Webhook notification failed for service {$this->service->id}");
        }

        NotificationLog::create([
            'user_id' => $this->user->id,
            'service_id' => $this->service->id,
            'channel' => 'webhook',
            'message' => "Expiry webhook for {$this->service->name}",
            'sent_at' => now(),
            'sent_date' => $today,
            'status' => 'sent',
            'attempt' => $this->attempts(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        $today = Carbon::now($this->user->timezone)->toDateString();

        $alreadyLogged = NotificationLog::where('service_id', $this->service->id)
            ->where('sent_date', $today)
            ->where('channel', 'webhook')
            ->exists();

        if ($alreadyLogged) return;

        NotificationLog::create([
            'user_id' => $this->user->id,
            'service_id' => $this->service->id,
            'channel' => 'webhook',
            'message' => "Expiry webhook for {$this->service->name}",
            'sent_at' => now(),
            'sent_date' => $today,
            'status' => 'failed',
            'attempt' => $this->attempts(),
            'error' => $e->getMessage(),
        ]);
    }
}
