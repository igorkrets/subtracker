<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBlocked
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_blocked) {
            Auth::logout();
            return redirect('/login')->withErrors(['email' => 'Ваш аккаунт заблокирован. Обратитесь к администратору.']);
        }

        return $next($request);
    }
}
