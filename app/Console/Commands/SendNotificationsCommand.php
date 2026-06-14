<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramNotificationJob;
use App\Jobs\SendWebhookNotificationJob;
use App\Models\NotificationLog;
use App\Models\Service;
use App\Services\NotificationRuleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendNotificationsCommand extends Command
{
    protected $signature = 'notifications:send';
    protected $description = 'Send scheduled expiry notifications';

    public function __construct(private NotificationRuleService $ruleService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $today = Carbon::today();

        Service::with(['user', 'group'])
            ->whereNotNull('expires_at')
            ->where('notifications_enabled', true)
            ->whereNull('deleted_at')
            ->chunk(100, function ($services) use ($today) {
                foreach ($services as $service) {
                    $user = $service->user;
                    $userToday = Carbon::now($user->timezone)->startOfDay();
                    $daysLeft = $userToday->diffInDays(Carbon::parse($service->expires_at), false);

                    $rules = $this->ruleService->getApplicableRules($user, $service);

                    foreach ($rules as $rule) {
                        if ((int)$daysLeft !== (int)$rule->days_before) continue;

                        $alreadySent = NotificationLog::where('service_id', $service->id)
                            ->where('sent_date', $userToday->toDateString())
                            ->where('channel', $rule->channel)
                            ->exists();

                        if ($alreadySent) continue;

                        if ($rule->channel === 'tg' && $user->tg_chat_id) {
                            dispatch(new SendTelegramNotificationJob($service, $user));
                        } elseif ($rule->channel === 'webhook') {
                            dispatch(new SendWebhookNotificationJob($service, $user));
                        }
                    }
                }
            });

        $this->info('Notifications dispatched.');
    }
}
