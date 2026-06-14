<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\Service;
use App\Models\User;
use App\Services\WebhookService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWebhookNotificationJob implements ShouldQueue
{
    use Queueable;

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

        $webhookService->sendNotification($this->service, $this->user);

        NotificationLog::create([
            'user_id' => $this->user->id,
            'service_id' => $this->service->id,
            'channel' => 'webhook',
            'message' => "Expiry webhook for {$this->service->name}",
            'sent_at' => now(),
            'sent_date' => $today,
            'status' => 'sent',
        ]);
    }
}
