<?php

namespace App\Http\Controllers;

use App\Models\CatalogPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    public function json()
    {
        $path = public_path('catalog.json');
        if (!file_exists($path)) {
            return response()->json(['types' => [], 'presets' => []]);
        }
        return response()->file($path, ['Content-Type' => 'application/json']);
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $presets = CatalogPreset::where('is_active', true)
            ->where(fn($b) => $b
                ->where('name', 'like', "%{$q}%")
                ->orWhereJsonContains('aliases', $q)
            )
            ->orderBy('is_popular', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'type_slug', 'icon', 'icon_set', 'color', 'default_url', 'aliases', 'is_popular']);

        return response()->json(['data' => $presets]);
    }
}
