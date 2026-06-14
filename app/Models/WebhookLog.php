<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'webhook_id', 'service_id', 'status_code', 'response_body', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
