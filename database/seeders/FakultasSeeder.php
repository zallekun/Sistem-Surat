<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fakultas;

class FakultasSeeder extends Seeder
{
    public function run(): void
    {
        Fakultas::create([
            'nama_fakultas' => 'Fakultas Sains dan Informatika',
            'kode_fakultas' => 'FSI',
        ]);
    }
}