<?php

namespace App\Services;

use App\Models\User;

class BackupService
{
    public function exportJson(User $user): array
    {
        return [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'timezone' => $user->timezone,
                'default_currency' => $user->default_currency,
            ],
            'groups' => $user->groups()->withTrashed()->get()->map(fn($g) => [
                'name' => $g->name,
                'color' => $g->color,
                'icon' => $g->icon,
                'icon_set' => $g->icon_set,
                'sort_order' => $g->sort_order,
                'notifications_enabled' => $g->notifications_enabled,
            ]),
            'services' => $user->services()->withTrashed()->get()->map(fn($s) => [
                'name' => $s->name,
                'url' => $s->url,
                'ip' => $s->ip,
                'type_slug' => $s->type_slug,
                'icon' => $s->icon,
                'icon_set' => $s->icon_set,
                'color' => $s->color,
                'expires_at' => $s->expires_at?->toDateString(),
                'billing_cycle' => $s->billing_cycle,
                'cost' => $s->cost,
                'currency' => $s->currency,
                'provider_name' => $s->provider_name,
                'provider_url' => $s->provider_url,
                'notes' => $s->notes,
                'notifications_enabled' => $s->notifications_enabled,
            ]),
            'service_payments' => $user->servicePayments()->get()->map(fn($p) => [
                'amount' => $p->amount,
                'currency' => $p->currency,
                'paid_at' => $p->paid_at?->toDateString(),
            ]),
            'notification_rules' => $user->notificationRules()->get()->map(fn($r) => [
                'channel' => $r->channel,
                'days_before' => $r->days_before,
                'is_global' => $r->is_global,
                'is_active' => $r->is_active,
            ]),
            'webhooks' => $user->webhooks()->get()->map(fn($w) => [
                'name' => $w->name,
                'url' => $w->url,
                'method' => $w->method,
                'is_active' => $w->is_active,
            ]),
        ];
    }
}
