<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'user_id', 'name', 'url', 'secret', 'method',
        'headers', 'payload_template', 'is_active',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload_template' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function webhookLogs()
    {
        return $this->hasMany(WebhookLog::class);
    }
}
