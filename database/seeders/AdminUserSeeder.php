<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sub.syspage.ru'],
            [
                'name' => 'Admin',
                'password' => bcrypt('change_me_please'),
                'is_admin' => true,
                'api_token' => Str::random(64),
                'tg_code' => Str::random(16),
                'timezone' => 'Europe/Moscow',
                'default_currency' => 'RUB',
            ]
        );
    }
}
