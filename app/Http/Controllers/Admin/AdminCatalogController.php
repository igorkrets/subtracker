<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogPreset;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class AdminCatalogController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $types = ServiceType::orderBy('sort_order')->get();
        $presets = CatalogPreset::orderBy('sort_order')->get();
        return view('admin.catalog', compact('types', 'presets'));
    }

    public function storeType(Request $request)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $data = $request->validate([
            'slug' => ['required', 'string', 'unique:service_types,slug'],
            'name' => ['required', 'string'],
            'name_ru' => ['required', 'string'],
            'icon' => ['required', 'string'],
            'color' => ['nullable', 'string'],
            'default_billing_cycle' => ['nullable', 'string'],
            'default_notify_days' => ['nullable', 'array'],
        ]);
        $type = ServiceType::create($data);
        return response()->json(['success' => true, 'data' => $type]);
    }

    public function updateType(Request $request, ServiceType $type)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $type->update($request->validate([
            'name' => ['sometimes', 'string'],
            'name_ru' => ['sometimes', 'string'],
            'icon' => ['sometimes', 'string'],
            'color' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]));
        return response()->json(['success' => true, 'data' => $type]);
    }

    public function destroyType(ServiceType $type)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $type->delete();
        return response()->json(['success' => true]);
    }

    public function storePreset(Request $request)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $data = $request->validate([
            'type_slug' => ['required', 'string'],
            'name' => ['required', 'string'],
            'slug' => ['required', 'string', 'unique:catalog_presets,slug'],
            'icon' => ['required', 'string'],
            'icon_set' => ['nullable', 'string'],
            'color' => ['nullable', 'string'],
            'aliases' => ['nullable', 'array'],
        ]);
        $preset = CatalogPreset::create($data);
        return response()->json(['success' => true, 'data' => $preset]);
    }

    public function updatePreset(Request $request, CatalogPreset $preset)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $preset->update($request->all());
        return response()->json(['success' => true, 'data' => $preset]);
    }

    public function destroyPreset(CatalogPreset $preset)
    {
        abort_unless(Auth::user()->is_admin, 403);
        $preset->delete();
        return response()->json(['success' => true]);
    }

    public function build()
    {
        abort_unless(Auth::user()->is_admin, 403);
        Artisan::call('catalog:build');
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'catalog.json перестроен']);
        }
        return back()->with('success', 'catalog.json пересобран');
    }
}
