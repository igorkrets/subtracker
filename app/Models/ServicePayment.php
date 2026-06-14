<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePayment extends Model
{
    protected $fillable = [
        'service_id', 'user_id', 'amount', 'currency',
        'paid_at', 'period_from', 'period_to', 'source', 'note',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'amount' => 'decimal:2',
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
