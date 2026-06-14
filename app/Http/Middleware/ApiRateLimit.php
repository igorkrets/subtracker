<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimit
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'api:' . ($request->user()?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Too many requests. Limit: 60/min.'],
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
