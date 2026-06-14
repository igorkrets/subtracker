<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['success' => false, 'error' => ['message' => 'Unauthorized']], 401);
        }

        $user = User::where('api_token', $token)->where('is_blocked', false)->first();

        if (!$user) {
            return response()->json(['success' => false, 'error' => ['message' => 'Invalid token']], 401);
        }

        Auth::login($user);
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
