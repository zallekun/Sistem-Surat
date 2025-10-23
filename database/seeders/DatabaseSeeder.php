<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FakultasSeeder::class,
            ProdiSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class, // Added PermissionSeeder
            JabatanSeeder::class,
            UserSeeder::class,
            StatusSuratSeeder::class,
            JenisSuratSeeder::class, // Added JenisSuratSeeder
            SuratSeeder::class,
            TrackingSeeder::class,
        ]);
    }
}
