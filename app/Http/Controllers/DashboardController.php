<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->services()->with(['group', 'serviceType']);

        // Search
        if ($search = $request->get('q')) {
            $q = '%' . $search . '%';
            $query->where(fn($b) => $b
                ->where('name', 'like', $q)
                ->orWhere('ip', 'like', $q)
                ->orWhere('url', 'like', $q)
                ->orWhere('notes', 'like', $q)
                ->orWhere('provider_name', 'like', $q)
            );
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $today = today();
            match ($status) {
                'today' => $query->whereDate('expires_at', $today),
                'week' => $query->whereBetween('expires_at', [$today, $today->copy()->addDays(7)]),
                'month' => $query->whereBetween('expires_at', [$today, $today->copy()->addDays(30)]),
                'overdue' => $query->where('expires_at', '<', $today),
                default => null,
            };
        }

        // Filter by group
        if ($groupId = $request->get('group_id')) {
            $query->where('group_id', $groupId);
        }

        // Sort
        $sort = $request->get('sort', 'expires_asc');
        match ($sort) {
            'expires_desc' => $query->orderByRaw('expires_at IS NULL ASC')->orderBy('expires_at', 'desc'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'cost_asc' => $query->orderBy('cost'),
            'cost_desc' => $query->orderBy('cost', 'desc'),
            'created_desc' => $query->orderBy('created_at', 'desc'),
            default => $query->orderByRaw('expires_at IS NULL ASC')->orderBy('expires_at'),
        };

        $services = $query->paginate(50)->withQueryString();

        // Groups for display
        $groups = $user->groups()->withCount('services')->orderBy('sort_order')->get();

        // Summary stats
        $today = today();
        $stats = [
            'today' => $user->services()->whereDate('expires_at', $today)->count(),
            'week' => $user->services()->whereBetween('expires_at', [$today, $today->copy()->addDays(7)])->count(),
            'month' => $user->services()->whereBetween('expires_at', [$today, $today->copy()->addDays(30)])->count(),
            'total' => $user->services()->count(),
        ];

        // Spend analytics
        $spendStats = $this->calculateSpend($user);

        // Mode: grouped or flat
        $mode = $request->get('mode', 'grouped');

        // Grouped services (for grouped mode)
        $groupedServices = [];
        if ($mode === 'grouped') {
            $groupedServices = $this->getGroupedServices($user, $services->items());
        }

        return view('dashboard.index', compact(
            'services', 'groups', 'stats', 'spendStats', 'mode', 'groupedServices'
        ));
    }

    private function calculateSpend($user): array
    {
        $services = $user->services()
            ->whereNotNull('cost')
            ->whereNotNull('billing_cycle')
            ->where('billing_cycle', '!=', 'one_time')
            ->get();

        $monthly = [];
        $yearly = [];

        foreach ($services as $s) {
            $currency = $s->currency;
            $monthlyAmount = match ($s->billing_cycle) {
                'monthly' => $s->cost,
                'quarterly' => $s->cost / 3,
                'semiannual' => $s->cost / 6,
                'yearly' => $s->cost / 12,
                'custom' => $s->billing_interval_days ? $s->cost / ($s->billing_interval_days / 30) : 0,
                default => 0,
            };
            $monthly[$currency] = ($monthly[$currency] ?? 0) + $monthlyAmount;
            $yearly[$currency] = ($yearly[$currency] ?? 0) + ($monthlyAmount * 12);
        }

        return ['monthly' => $monthly, 'yearly' => $yearly];
    }

    private function getGroupedServices($user, array $services): array
    {
        $groups = $user->groups()->orderBy('sort_order')->get()->keyBy('id');
        $grouped = [];

        // Add group entries
        foreach ($groups as $group) {
            $grouped[$group->id] = ['group' => $group, 'services' => []];
        }
        // Ungrouped
        $grouped[null] = ['group' => null, 'services' => []];

        foreach ($services as $service) {
            $groupId = $service->group_id;
            if (!isset($grouped[$groupId])) {
                $grouped[$groupId] = ['group' => $service->group, 'services' => []];
            }
            $grouped[$groupId]['services'][] = $service;
        }

        // Remove empty groups (except null)
        return array_filter($grouped, fn($g) => !empty($g['services']) || $g['group'] === null);
    }
}
