<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    private function ok($data, int $total = null, int $page = 1): \Illuminate\Http\JsonResponse
    {
        $response = ['success' => true, 'data' => $data];
        if ($total !== null) {
            $response['meta'] = ['total' => $total, 'page' => $page];
        }
        return response()->json($response);
    }

    private function err(string $message, int $code = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => false, 'error' => ['message' => $message]], $code);
    }

    public function me()
    {
        $user = Auth::user();
        return $this->ok([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'timezone' => $user->timezone,
            'default_currency' => $user->default_currency,
            'tg_connected' => (bool)$user->tg_chat_id,
        ]);
    }

    public function services(Request $request)
    {
        $query = Auth::user()->services()->with(['group', 'serviceType']);

        if ($request->filled('group_id')) $query->where('group_id', $request->group_id);
        if ($request->filled('expiring_in_days')) {
            $query->where('expires_at', '<=', now()->addDays((int)$request->expiring_in_days));
        }

        $sort = $request->get('sort', 'expires_asc');
        match ($sort) {
            'expires_desc' => $query->orderBy('expires_at', 'desc'),
            'name_asc' => $query->orderBy('name'),
            default => $query->orderByRaw('expires_at IS NULL ASC')->orderBy('expires_at'),
        };

        $paginator = $query->paginate(50);
        $data = $paginator->map(fn($s) => $this->serviceArray($s));

        return $this->ok($data, $paginator->total(), $paginator->currentPage());
    }

    public function showService(int $id)
    {
        $service = Auth::user()->services()->findOrFail($id);
        return $this->ok($this->serviceArray($service));
    }

    public function storeService(Request $request)
    {
        $data = $this->validateService($request);
        $service = Auth::user()->services()->create($data);
        return $this->ok($this->serviceArray($service));
    }

    public function updateService(Request $request, int $id)
    {
        $service = Auth::user()->services()->findOrFail($id);
        $data = $this->validateService($request);
        $service->update($data);
        return $this->ok($this->serviceArray($service));
    }

    public function destroyService(int $id)
    {
        Auth::user()->services()->findOrFail($id)->delete();
        return $this->ok(['deleted' => true]);
    }

    public function expiringServices()
    {
        $services = Auth::user()->services()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(30))
            ->orderBy('expires_at')
            ->get()
            ->map(fn($s) => $this->serviceArray($s));
        return $this->ok($services, count($services));
    }

    public function groups()
    {
        $groups = Auth::user()->groups()->withCount('services')->orderBy('sort_order')->get();
        return $this->ok($groups, $groups->count());
    }

    public function storeGroup(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string'],
        ]);
        $group = Auth::user()->groups()->create($data);
        return $this->ok($group);
    }

    public function updateGroup(Request $request, int $id)
    {
        $group = Auth::user()->groups()->findOrFail($id);
        $group->update($request->validate(['name' => ['string', 'max:100'], 'color' => ['nullable', 'string']]));
        return $this->ok($group);
    }

    public function destroyGroup(int $id)
    {
        Auth::user()->groups()->findOrFail($id)->delete();
        return $this->ok(['deleted' => true]);
    }

    private function serviceArray(Service $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'ip' => $s->ip,
            'url' => $s->url,
            'group_id' => $s->group_id,
            'group_name' => $s->group?->name,
            'type_slug' => $s->type_slug,
            'expires_at' => $s->expires_at?->toDateString(),
            'days_left' => $s->days_left,
            'status' => $s->status,
            'cost' => $s->cost,
            'currency' => $s->currency,
            'billing_cycle' => $s->billing_cycle,
            'provider_name' => $s->provider_name,
            'provider_url' => $s->provider_url,
            'notifications_enabled' => $s->notifications_enabled,
            'created_at' => $s->created_at->toIso8601String(),
        ];
    }

    private function validateService(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url'],
            'ip' => ['nullable', 'string', 'max:45'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'type_slug' => ['nullable', 'string'],
            'expires_at' => ['nullable', 'date'],
            'billing_cycle' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'provider_name' => ['nullable', 'string'],
            'provider_url' => ['nullable', 'url'],
            'notes' => ['nullable', 'string'],
            'notifications_enabled' => ['nullable', 'boolean'],
        ]);
    }
}
