<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $roles = [
            ['name' => 'admin', 'deskripsi' => 'Administrator sistem'],
            ['name' => 'dekan', 'deskripsi' => 'Dekan Fakultas'],
            ['name' => 'wadek', 'deskripsi' => 'Wakil Dekan'],
            ['name' => 'kabag_tu', 'deskripsi' => 'Kepala Bagian Tata Usaha'],
            ['name' => 'kaprodi', 'deskripsi' => 'Kepala Program Studi'],
            ['name' => 'staff_fakultas', 'deskripsi' => 'Staff Fakultas'],
            ['name' => 'staff_prodi', 'deskripsi' => 'Staff Program Studi'],
            ['name' => 'user', 'deskripsi' => 'User biasa'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
