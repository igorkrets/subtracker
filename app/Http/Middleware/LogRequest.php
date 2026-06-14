<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogRequest
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        $ms = (int)((microtime(true) - $start) * 1000);

        $path = $request->path();
        if (str_starts_with($path, '_ignition') || str_starts_with($path, 'vendor') || str_starts_with($path, 'health')) {
            return $response;
        }

        try {
            RequestLog::create([
                'user_id' => Auth::id(),
                'method' => $request->method(),
                'path' => '/' . $path,
                'status_code' => $response->getStatusCode(),
                'ip' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'response_time_ms' => $ms,
                'created_at' => now(),
            ]);
        } catch (\Exception) {}

        return $response;
    }
}
