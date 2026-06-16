<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'service_id', 'channel', 'message', 'sent_at', 'status', 'sent_date', 'attempt', 'error',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'sent_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
