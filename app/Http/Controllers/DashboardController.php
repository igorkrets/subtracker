<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Service;
use App\Services\ExchangeRateService;
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

        // Sort: if provided in request, save to user; otherwise use user's saved preference
        $validSorts = ['manual','expires_asc','expires_desc','name_asc','name_desc','cost_asc','cost_desc','created_desc'];
        if ($request->has('sort') && in_array($request->get('sort'), $validSorts)) {
            $sort = $request->get('sort');
            if ($user->sort_preference !== $sort) {
                $user->update(['sort_preference' => $sort]);
            }
        } else {
            $sort = $user->sort_preference ?? 'manual';
        }
        match ($sort) {
            'expires_asc'  => $query->orderByRaw('expires_at IS NULL ASC')->orderBy('expires_at'),
            'expires_desc' => $query->orderByRaw('expires_at IS NULL ASC')->orderBy('expires_at', 'desc'),
            'name_asc'     => $query->orderBy('name'),
            'name_desc'    => $query->orderBy('name', 'desc'),
            'cost_asc'     => $query->orderBy('cost'),
            'cost_desc'    => $query->orderBy('cost', 'desc'),
            'created_desc' => $query->orderBy('created_at', 'desc'),
            default        => $query->orderByRaw('COALESCE(sort_order, 999999) ASC')->orderBy('id'),
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

        // Refresh exchange rates in background (cached 6h, triggered by any visit)
        $rates = ExchangeRateService::getRates();

        // Spend analytics
        $displayCurrency = $user->display_currency ?? 'RUB';
        $spendStats = $this->calculateSpend($user, $displayCurrency, $rates);

        // Mode: grouped or flat
        $mode = $request->get('mode', 'grouped');

        // Grouped services (for grouped mode)
        $groupedServices = [];
        if ($mode === 'grouped') {
            $groupedServices = $this->getGroupedServices($user, $services->items());
        }

        // Per-group monthly spend in display currency (all services, not paginated)
        $groupSpend = $this->calculateGroupSpend($user, $displayCurrency, $rates);

        $currencies = ExchangeRateService::CURRENCIES;

        return view('dashboard.index', compact(
            'services', 'groups', 'stats', 'spendStats', 'mode', 'groupedServices',
            'displayCurrency', 'currencies', 'groupSpend', 'sort'
        ));
    }

    public function updateCurrency(Request $request)
    {
        $data = $request->validate([
            'currency' => ['required', 'string', 'size:3', 'in:' . implode(',', ExchangeRateService::CURRENCIES)],
        ]);
        Auth::user()->update(['display_currency' => $data['currency']]);
        return response()->json(['success' => true]);
    }

    private function calculateSpend($user, string $displayCurrency, array $rates): array
    {
        $services = $user->services()
            ->whereNotNull('cost')
            ->whereNotNull('billing_cycle')
            ->where('billing_cycle', '!=', 'one_time')
            ->get();

        $totalMonthly = 0.0;
        $byCurrency   = [];

        foreach ($services as $s) {
            $nativeCurrency = $s->currency ?? 'RUB';
            $monthlyNative  = match ($s->billing_cycle) {
                'monthly'    => (float) $s->cost,
                'quarterly'  => (float) $s->cost / 3,
                'semiannual' => (float) $s->cost / 6,
                'yearly'     => (float) $s->cost / 12,
                'custom'     => $s->billing_interval_days
                    ? (float) $s->cost / ($s->billing_interval_days / 30)
                    : 0,
                default => 0,
            };

            // Keep native breakdown (for tooltip / info)
            $byCurrency[$nativeCurrency] = ($byCurrency[$nativeCurrency] ?? 0) + $monthlyNative;

            // Convert to display currency for total
            $totalMonthly += ExchangeRateService::convert($monthlyNative, $nativeCurrency, $displayCurrency, $rates);
        }

        return [
            'total_monthly'    => $totalMonthly,
            'total_yearly'     => $totalMonthly * 12,
            'display_currency' => $displayCurrency,
            'by_currency'      => $byCurrency,   // native breakdown, for info
        ];
    }

    private function calculateGroupSpend($user, string $displayCurrency, array $rates): array
    {
        $rows = $user->services()
            ->whereNotNull('cost')
            ->whereNotNull('billing_cycle')
            ->where('billing_cycle', '!=', 'one_time')
            ->get(['group_id', 'cost', 'currency', 'billing_cycle', 'billing_interval_days']);

        $spend = [];
        foreach ($rows as $s) {
            $key = $s->group_id ?? 'null';
            $monthly = match ($s->billing_cycle) {
                'monthly'    => (float) $s->cost,
                'quarterly'  => (float) $s->cost / 3,
                'semiannual' => (float) $s->cost / 6,
                'yearly'     => (float) $s->cost / 12,
                'custom'     => $s->billing_interval_days
                    ? (float) $s->cost / ($s->billing_interval_days / 30)
                    : 0,
                default => 0,
            };
            $spend[$key] = ($spend[$key] ?? 0.0)
                + ExchangeRateService::convert($monthly, $s->currency ?? 'RUB', $displayCurrency, $rates);
        }

        return $spend;
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

        // Show all named groups (even empty), hide "Без группы" if it has no services
        return array_filter($grouped, fn($g) => $g['group'] !== null || !empty($g['services']));
    }
}
