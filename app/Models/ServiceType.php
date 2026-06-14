<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = [
        'slug', 'name', 'name_ru', 'icon', 'icon_set', 'color',
        'default_billing_cycle', 'default_notify_days', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'default_notify_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function catalogPresets()
    {
        return $this->hasMany(CatalogPreset::class, 'type_slug', 'slug');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'type_slug', 'slug');
    }
}
