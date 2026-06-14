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
        $timezones = $this->curatedTimezones();
        $notificationRules = $user->notificationRules()->orderBy('channel')->orderBy('days_before')->get();
        $webhooks = $user->webhooks()->latest()->get();
        return view('dashboard.settings', compact('user', 'timezones', 'notificationRules', 'webhooks'));
    }

    private function curatedTimezones(): array
    {
        $list = [
            'Pacific/Honolulu'               => 'Гонолулу',
            'America/Anchorage'              => 'Анкоридж',
            'America/Los_Angeles'            => 'Лос-Анджелес, Сан-Франциско',
            'America/Denver'                 => 'Денвер, Феникс',
            'America/Chicago'                => 'Чикаго, Даллас',
            'America/New_York'               => 'Нью-Йорк, Майами',
            'America/Sao_Paulo'              => 'Сан-Паулу',
            'America/Argentina/Buenos_Aires' => 'Буэнос-Айрес',
            'Atlantic/Azores'                => 'Азорские острова',
            'UTC'                            => 'UTC',
            'Europe/London'                  => 'Лондон',
            'Europe/Lisbon'                  => 'Лиссабон',
            'Europe/Paris'                   => 'Париж',
            'Europe/Berlin'                  => 'Берлин',
            'Europe/Rome'                    => 'Рим',
            'Europe/Madrid'                  => 'Мадрид',
            'Europe/Amsterdam'               => 'Амстердам',
            'Europe/Stockholm'               => 'Стокгольм',
            'Europe/Warsaw'                  => 'Варшава',
            'Europe/Prague'                  => 'Прага',
            'Europe/Vienna'                  => 'Вена',
            'Europe/Kyiv'                    => 'Киев',
            'Europe/Riga'                    => 'Рига',
            'Europe/Tallinn'                 => 'Таллин',
            'Europe/Vilnius'                 => 'Вильнюс',
            'Europe/Helsinki'                => 'Хельсинки',
            'Europe/Bucharest'               => 'Бухарест',
            'Europe/Sofia'                   => 'София',
            'Europe/Athens'                  => 'Афины',
            'Europe/Minsk'                   => 'Минск',
            'Europe/Moscow'                  => 'Москва, Санкт-Петербург',
            'Europe/Istanbul'                => 'Стамбул',
            'Europe/Kaliningrad'             => 'Калининград',
            'Asia/Riyadh'                    => 'Эр-Рияд, Кувейт',
            'Asia/Tehran'                    => 'Тегеран',
            'Europe/Samara'                  => 'Самара',
            'Asia/Yerevan'                   => 'Ереван',
            'Asia/Tbilisi'                   => 'Тбилиси',
            'Asia/Baku'                      => 'Баку',
            'Asia/Dubai'                     => 'Дубай, Абу-Даби',
            'Asia/Kabul'                     => 'Кабул',
            'Asia/Yekaterinburg'             => 'Екатеринбург',
            'Asia/Tashkent'                  => 'Ташкент',
            'Asia/Dushanbe'                  => 'Душанбе',
            'Asia/Ashgabat'                  => 'Ашхабад',
            'Asia/Karachi'                   => 'Карачи',
            'Asia/Kolkata'                   => 'Мумбаи, Нью-Дели',
            'Asia/Kathmandu'                 => 'Катманду',
            'Asia/Omsk'                      => 'Омск',
            'Asia/Almaty'                    => 'Алматы',
            'Asia/Bishkek'                   => 'Бишкек',
            'Asia/Dhaka'                     => 'Дакка',
            'Asia/Novosibirsk'               => 'Новосибирск',
            'Asia/Krasnoyarsk'               => 'Красноярск',
            'Asia/Bangkok'                   => 'Бангкок, Джакарта',
            'Asia/Irkutsk'                   => 'Иркутск',
            'Asia/Shanghai'                  => 'Пекин, Шанхай',
            'Asia/Hong_Kong'                 => 'Гонконг',
            'Asia/Singapore'                 => 'Сингапур, Куала-Лумпур',
            'Asia/Yakutsk'                   => 'Якутск',
            'Asia/Tokyo'                     => 'Токио, Осака',
            'Asia/Seoul'                     => 'Сеул',
            'Asia/Vladivostok'               => 'Владивосток',
            'Australia/Sydney'               => 'Сидней',
            'Australia/Brisbane'             => 'Брисбен',
            'Asia/Magadan'                   => 'Магадан',
            'Asia/Kamchatka'                 => 'Камчатка, Петропавловск',
            'Pacific/Auckland'               => 'Окленд',
        ];

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = [];
        foreach ($list as $tzId => $city) {
            try {
                $tz     = new \DateTimeZone($tzId);
                $offset = $tz->getOffset($now);
                $hours  = intdiv(abs($offset), 3600);
                $mins   = (abs($offset) % 3600) / 60;
                $sign   = $offset >= 0 ? '+' : '−';
                $label  = $mins > 0
                    ? sprintf('UTC%s%d:%02d', $sign, $hours, $mins)
                    : sprintf('UTC%s%d', $sign, $hours);
                $result[$tzId] = "{$city} ({$label})";
            } catch (\Throwable) {
                // Skip invalid timezone
            }
        }

        return $result;
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
