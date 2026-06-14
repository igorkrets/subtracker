<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServicePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceRenewController extends Controller
{
    public function renew(Request $request, Service $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);

        $request->validate([
            'months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'cycle' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
            'record_payment' => ['nullable', 'boolean'],
        ]);

        $today = Carbon::today();
        $old = $service->expires_at;
        $base = $old && $old->isAfter($today) ? $old : $today;
        $recordPayment = $request->boolean('record_payment', (bool)$service->cost);

        $newExpiry = null;

        if ($request->filled('months')) {
            $newExpiry = $base->copy()->addMonths((int)$request->months);
        } elseif ($request->boolean('cycle') && $service->billing_cycle) {
            $newExpiry = match ($service->billing_cycle) {
                'monthly' => $base->copy()->addMonth(),
                'quarterly' => $base->copy()->addMonths(3),
                'semiannual' => $base->copy()->addMonths(6),
                'yearly' => $base->copy()->addYear(),
                'custom' => $service->billing_interval_days
                    ? $base->copy()->addDays($service->billing_interval_days)
                    : $base->copy()->addMonth(),
                default => $base->copy()->addMonth(),
            };
        } elseif ($request->filled('expires_at')) {
            $newExpiry = Carbon::parse($request->expires_at);
        }

        abort_if(!$newExpiry, 422);

        DB::transaction(function () use ($service, $old, $newExpiry, $today, $recordPayment) {
            $service->update([
                'expires_at' => $newExpiry,
                'renewed_at' => $today,
                'last_paid_at' => $recordPayment ? $today : $service->last_paid_at,
            ]);

            if ($recordPayment && $service->cost) {
                ServicePayment::create([
                    'service_id' => $service->id,
                    'user_id' => $service->user_id,
                    'amount' => $service->cost,
                    'currency' => $service->currency,
                    'paid_at' => $today,
                    'period_from' => $old ?? $today,
                    'period_to' => $newExpiry,
                    'source' => 'renew_button',
                ]);
            }

            // Reset notification dedup for today so new thresholds can fire
            \App\Models\NotificationLog::where('service_id', $service->id)
                ->where('sent_date', $today->toDateString())
                ->delete();
        });

        $service->refresh();

        return response()->json([
            'success' => true,
            'data' => array_merge($service->toArray(), [
                'days_left' => $service->days_left,
                'status' => $service->status,
                'expires_at' => $service->expires_at?->toDateString(),
            ]),
        ]);
    }
}
