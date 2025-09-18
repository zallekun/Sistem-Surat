<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisSurat;

class JenisSuratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jenisSurat = [
            ['nama_jenis' => 'Surat Biasa', 'kode_surat' => 'SB'],
            ['nama_jenis' => 'Surat Keputusan', 'kode_surat' => 'SK'],
            ['nama_jenis' => 'Surat Edaran', 'kode_surat' => 'SE'],
        ];

        foreach ($jenisSurat as $jenis) {
            JenisSurat::firstOrCreate(['kode_surat' => $jenis['kode_surat']], $jenis);
        }
    }
}