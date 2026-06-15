<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NotificationRule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception) {
            return redirect()->route('login')->withErrors(['email' => 'Не удалось войти через Google. Попробуйте снова.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if ($user->is_blocked) {
                return redirect()->route('login')->withErrors(['email' => 'Аккаунт заблокирован.']);
            }

            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            if (!config('app.register_enable')) {
                return redirect()->route('login')->withErrors(['email' => 'Регистрация временно закрыта.']);
            }

            $ipKey = 'register_ip:' . request()->ip();
            if (RateLimiter::tooManyAttempts($ipKey, 3)) {
                return redirect()->route('login')->withErrors(['email' => 'Слишком много регистраций с вашего IP.']);
            }

            $user = User::create([
                'name'             => $googleUser->getName() ?? explode('@', $googleUser->getEmail())[0],
                'email'            => $googleUser->getEmail(),
                'google_id'        => $googleUser->getId(),
                'api_token'        => Str::random(64),
                'tg_code'          => Str::upper(Str::random(16)),
                'timezone'         => 'UTC',
                'default_currency' => config('app.default_currency', 'RUB'),
            ]);

            NotificationRule::create([
                'user_id'    => $user->id,
                'channel'    => 'tg',
                'days_before' => 7,
                'is_global'  => true,
            ]);

            RateLimiter::hit($ipKey, 24 * 3600);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
