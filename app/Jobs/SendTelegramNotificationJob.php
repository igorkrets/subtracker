<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\Service;
use App\Models\User;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Service $service,
        private User $user
    ) {}

    public function handle(TelegramService $telegram): void
    {
        $today = Carbon::now($this->user->timezone)->toDateString();

        $alreadySent = NotificationLog::where('service_id', $this->service->id)
            ->where('sent_date', $today)
            ->where('channel', 'tg')
            ->exists();

        if ($alreadySent) return;

        $sent = $telegram->sendNotification($this->service, $this->user);

        NotificationLog::create([
            'user_id' => $this->user->id,
            'service_id' => $this->service->id,
            'channel' => 'tg',
            'message' => "Expiry notification for {$this->service->name}",
            'sent_at' => now(),
            'sent_date' => $today,
            'status' => $sent ? 'sent' : 'failed',
        ]);
    }
}
