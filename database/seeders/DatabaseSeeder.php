<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServiceTypeSeeder::class,
            CatalogPresetSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
