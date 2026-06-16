<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'google_id',
        'api_token', 'tg_chat_id', 'tg_code', 'tg_connected_at',
        'is_blocked', 'is_admin', 'timezone', 'default_currency', 'display_currency', 'sort_preference',
        'max_services', 'max_notification_rules', 'max_webhooks',
    ];

    protected $hidden = ['password', 'remember_token', 'api_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tg_connected_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    public function notificationRules()
    {
        return $this->hasMany(NotificationRule::class);
    }

    public function servicePayments()
    {
        return $this->hasMany(ServicePayment::class);
    }

    public function maxServices(): int
    {
        return $this->max_services ?? AppSetting::current()->max_services;
    }

    public function maxNotificationRules(): int
    {
        return $this->max_notification_rules ?? AppSetting::current()->max_notification_rules;
    }

    public function maxWebhooks(): int
    {
        return $this->max_webhooks ?? AppSetting::current()->max_webhooks;
    }
}
