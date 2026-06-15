<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'method', 'path', 'status_code', 'ip', 'user_agent', 'response_time_ms', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
