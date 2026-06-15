<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NotificationRule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function showForm()
    {
        abort_if(!config('app.register_enable'), 403, 'Регистрация временно закрыта.');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        abort_if(!config('app.register_enable'), 403);

        $ipKey = 'register_ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, 3)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $hours = ceil($seconds / 3600);
            throw ValidationException::withMessages([
                'email' => ["Слишком много регистраций с вашего IP. Повторите через {$hours} ч."],
            ]);
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'timezone' => ['nullable', 'timezone'],
        ]);

        $user = User::create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'api_token'        => Str::random(64),
            'tg_code'          => Str::upper(Str::random(16)),
            'timezone'         => $data['timezone'] ?? 'UTC',
            'default_currency' => config('app.default_currency', 'RUB'),
        ]);

        // Default: notify 7 days before expiry via Telegram
        NotificationRule::create([
            'user_id'    => $user->id,
            'channel'    => 'tg',
            'days_before' => 7,
            'is_global'  => true,
        ]);

        RateLimiter::hit($ipKey, 24 * 3600);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
