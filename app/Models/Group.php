<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'color', 'icon', 'icon_set', 'sort_order', 'notifications_enabled',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function getNearestExpiresAtAttribute(): ?string
    {
        return $this->services()
            ->whereNotNull('expires_at')
            ->min('expires_at');
    }
}
