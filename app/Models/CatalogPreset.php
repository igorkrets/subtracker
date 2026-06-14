<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPreset extends Model
{
    protected $fillable = [
        'type_slug', 'name', 'slug', 'icon', 'icon_set', 'color',
        'default_url', 'aliases', 'region', 'is_popular', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'aliases' => 'array',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'type_slug', 'slug');
    }
}
