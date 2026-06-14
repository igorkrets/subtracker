<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $timezones = \DateTimeZone::listIdentifiers();
        $notificationRules = $user->notificationRules()->orderBy('channel')->orderBy('days_before')->get();
        $webhooks = $user->webhooks()->latest()->get();
        return view('dashboard.settings', compact('user', 'timezones', 'notificationRules', 'webhooks'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'timezone' => ['required', 'timezone'],
            'default_currency' => ['required', 'string', 'size:3'],
        ]);
        $user->update($data);
        return back()->with('success', 'Профиль обновлён');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Пароль изменён');
    }

    public function regenerateToken()
    {
        $token = Str::random(64);
        Auth::user()->update(['api_token' => $token]);
        return back()->with('success', 'API-токен обновлён');
    }

    public function unlinkTelegram()
    {
        Auth::user()->update(['tg_chat_id' => null, 'tg_connected_at' => null]);
        return back()->with('success', 'Telegram отвязан');
    }
}
