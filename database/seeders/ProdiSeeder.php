<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prodi;
use App\Models\Fakultas;

class ProdiSeeder extends Seeder
{
    public function run()
    {
        $fakultasSainsInformatika = Fakultas::where('kode_fakultas', 'FSI')->first();

        $prodis = [
            [
                'nama_prodi' => 'Kimia',
                'kode_prodi' => 'KIM',
                'fakultas_id' => $fakultasSainsInformatika->id,
            ],
            [
                'nama_prodi' => 'Sistem Informasi',
                'kode_prodi' => 'SI',
                'fakultas_id' => $fakultasSainsInformatika->id,
            ],
            [
                'nama_prodi' => 'Informatika',
                'kode_prodi' => 'IF',
                'fakultas_id' => $fakultasSainsInformatika->id,
            ],
        ];

        foreach ($prodis as $prodi) {
            Prodi::firstOrCreate(['kode_prodi' => $prodi['kode_prodi']], $prodi);
        }
    }
}