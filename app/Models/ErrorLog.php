<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'message', 'trace', 'file', 'line', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
