<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'group_id', 'type_slug', 'catalog_preset_id',
        'name', 'url', 'ip', 'icon', 'icon_set', 'color',
        'expires_at', 'renewed_at', 'billing_cycle', 'billing_interval_days',
        'auto_renew', 'is_trial', 'trial_ends_at', 'last_paid_at',
        'notify_days', 'notes', 'cost', 'currency',
        'provider_name', 'provider_url', 'notifications_enabled', 'sort_order',
    ];

    protected $casts = [
        'notify_days' => 'array',
        'expires_at' => 'date',
        'renewed_at' => 'date',
        'trial_ends_at' => 'date',
        'last_paid_at' => 'date',
        'auto_renew' => 'boolean',
        'is_trial' => 'boolean',
        'notifications_enabled' => 'boolean',
        'cost' => 'decimal:2',
    ];

    public function getDaysLeftAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($this->expires_at->startOfDay(), false);
    }

    public function getStatusAttribute(): string
    {
        $days = $this->days_left;
        if ($days === null) return 'none';
        if ($days < 0) return 'overdue';
        if ($days <= 7) return 'critical';
        if ($days <= 30) return 'soon';
        return 'ok';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'type_slug', 'slug');
    }

    public function catalogPreset()
    {
        return $this->belongsTo(CatalogPreset::class);
    }

    public function servicePayments()
    {
        return $this->hasMany(ServicePayment::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }
}
