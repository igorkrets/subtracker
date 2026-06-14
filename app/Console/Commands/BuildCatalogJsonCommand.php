<?php

namespace App\Console\Commands;

use App\Models\CatalogPreset;
use App\Models\ServiceType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class BuildCatalogJsonCommand extends Command
{
    protected $signature = 'catalog:build';
    protected $description = 'Build public/catalog.json from database';

    public function handle(): void
    {
        $types = ServiceType::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($t) => [
                'slug' => $t->slug,
                'name' => $t->name,
                'name_ru' => $t->name_ru,
                'icon' => $t->icon,
                'icon_set' => $t->icon_set,
                'color' => $t->color,
                'default_billing_cycle' => $t->default_billing_cycle,
                'default_notify_days' => $t->default_notify_days ?? [7, 14, 30],
                'icon_url' => "/icons/lucide/{$t->icon}.svg",
            ]);

        $presets = CatalogPreset::where('is_active', true)
            ->orderBy('is_popular', 'desc')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($p) => [
                'name' => $p->name,
                'slug' => $p->slug,
                'type_slug' => $p->type_slug,
                'icon' => $p->icon,
                'icon_set' => $p->icon_set,
                'color' => $p->color,
                'default_url' => $p->default_url,
                'aliases' => $p->aliases ?? [],
                'region' => $p->region,
                'is_popular' => $p->is_popular,
                'icon_url' => $p->icon_set === 'lucide'
                    ? "/icons/lucide/{$p->icon}.svg"
                    : "/icons/brands/{$p->icon}.svg",
            ]);

        $catalog = [
            'version' => '1.0',
            'generated_at' => now()->toIso8601String(),
            'types' => $types,
            'presets' => $presets,
        ];

        $json = json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(public_path('catalog.json'), $json);
        Cache::forget('catalog_json');

        $this->info('catalog.json built: ' . count($types) . ' types, ' . count($presets) . ' presets.');
    }
}
