<?php

namespace App\Services;

use App\Models\NotificationRule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRuleService
{
    public function getApplicableRules(User $user, Service $service): Collection
    {
        // Service-level rules
        $serviceRules = NotificationRule::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->get();

        if ($serviceRules->isNotEmpty()) {
            return $serviceRules;
        }

        // Group-level rules
        if ($service->group_id) {
            $groupRules = NotificationRule::where('user_id', $user->id)
                ->where('group_id', $service->group_id)
                ->where('is_active', true)
                ->get();

            if ($groupRules->isNotEmpty()) {
                return $groupRules;
            }
        }

        // Global rules
        return NotificationRule::where('user_id', $user->id)
            ->where('is_global', true)
            ->where('is_active', true)
            ->get();
    }
}
