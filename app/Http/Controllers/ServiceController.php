<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $maxOrder = Auth::user()->services()->max('sort_order') ?? 0;
        $service = Auth::user()->services()->create(array_merge($data, ['sort_order' => $maxOrder + 1]));
        $service->load('group', 'serviceType');
        return response()->json(['success' => true, 'data' => $this->serviceData($service)]);
    }

    public function update(Request $request, Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);
        $data = $this->validated($request);
        $service->update($data);
        $service->load('group', 'serviceType');
        return response()->json(['success' => true, 'data' => $this->serviceData($service)]);
    }

    public function destroy(Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);
        $service->delete();
        return response()->json(['success' => true]);
    }

    public function sort(Request $request)
    {
        $data = $request->validate(['items' => ['required', 'array']]);
        foreach ($data['items'] as $item) {
            Auth::user()->services()
                ->where('id', $item['id'])
                ->update([
                    'sort_order' => $item['order'],
                    'group_id' => $item['group_id'] ?? null,
                ]);
        }
        return response()->json(['success' => true]);
    }

    public function updateExpiry(Request $request, Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);
        $request->validate(['expires_at' => ['nullable', 'date']]);
        $service->update(['expires_at' => $request->expires_at]);
        return response()->json(['success' => true, 'data' => $this->serviceData($service)]);
    }

    public function duplicate(Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);
        $new = $service->replicate(['expires_at', 'renewed_at', 'last_paid_at', 'trial_ends_at']);
        $new->name = $service->name . ' (копия)';
        $new->sort_order = Auth::user()->services()->max('sort_order') + 1;
        $new->save();
        return response()->json(['success' => true, 'data' => $this->serviceData($new)]);
    }

    public function toggleNotifications(Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);
        $service->update(['notifications_enabled' => !$service->notifications_enabled]);
        return response()->json(['success' => true, 'notifications_enabled' => $service->notifications_enabled]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'ip' => ['nullable', 'string', 'max:45'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'type_slug' => ['nullable', 'string'],
            'catalog_preset_id' => ['nullable', 'integer'],
            'icon' => ['nullable', 'string', 'max:100'],
            'icon_set' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:7'],
            'expires_at' => ['nullable', 'date'],
            'billing_cycle' => ['nullable', 'string'],
            'billing_interval_days' => ['nullable', 'integer', 'min:1'],
            'auto_renew' => ['nullable', 'boolean'],
            'is_trial' => ['nullable', 'boolean'],
            'trial_ends_at' => ['nullable', 'date'],
            'notify_days' => ['nullable', 'array'],
            'notify_days.*' => ['integer'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'encrypted_notes' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'provider_name' => ['nullable', 'string', 'max:255'],
            'provider_url' => ['nullable', 'url', 'max:500'],
            'notifications_enabled' => ['nullable', 'boolean'],
        ]);
    }

    private function serviceData(Service $service): array
    {
        return array_merge($service->toArray(), [
            'days_left' => $service->days_left,
            'status' => $service->status,
            'expires_at' => $service->expires_at?->toDateString(),
            'group_name' => $service->group?->name,
        ]);
    }
}
