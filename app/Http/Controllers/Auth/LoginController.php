<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ipKey = 'login_ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $hours = ceil($seconds / 3600);
            throw ValidationException::withMessages([
                'email' => ["IP заблокирован из-за подозрительной активности. Повторите через {$hours} ч."],
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($ipKey, 3 * 3600);
            $attempts = RateLimiter::attempts($ipKey);
            $remaining = max(0, 10 - $attempts);
            throw ValidationException::withMessages([
                'email' => ["Неверный email или пароль. Осталось попыток: {$remaining}."],
            ]);
        }

        RateLimiter::clear($ipKey);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
