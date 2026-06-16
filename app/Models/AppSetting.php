<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = ['max_services', 'max_notification_rules', 'max_webhooks'];

    public static function current(): self
    {
        $attributes = Cache::rememberForever('app_settings', function () {
            return static::firstOrCreate([], [
                'max_services' => 500,
                'max_notification_rules' => 4,
                'max_webhooks' => 5,
            ])->getAttributes();
        });

        return (new static())->newFromBuilder($attributes);
    }

    public static function forget(): void
    {
        Cache::forget('app_settings');
    }
}
