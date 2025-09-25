<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DosenWaliInformatikaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosenWali = [
            [
                'nama' => 'Asep Id Hadiana, S.Si., M.Kom., Ph.D',
                'nid' => '412180078',
                'prodi_id' => 3, // Informatika - sesuaikan dengan ID prodi Anda
                'is_active' => true,
            ],
            [
                'nama' => 'Rezki Yuniarti, S.Si, M.T.',
                'nid' => '412174182',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Prof. Dr. Heni Nurani Hartikayanti, SE., M.Si., Ak',
                'nid' => '412119960',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Dr. Esmeralda Contessa Djamal, S.T., MT',
                'nid' => '412127670',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Dr. Eddie Krishna Putra, Drs., M.T.',
                'nid' => '412110561',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Dr. Asep Najmurrokhman, S.T., M.T.',
                'nid' => '412132571',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Agus Komarudin, S.Kom, M.T.',
                'nid' => '412175878',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Yulison Herry Chrisnanto, S.T., M.T.',
                'nid' => '412166863',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Wina Witanti, S.T, M.T.',
                'nid' => '412176273',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Gunawan Abdillah, S.Si., M.Cs.',
                'nid' => '412157175',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Fatan Kasyidi, S.Kom., M.T.',
                'nid' => '412100992',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Herdi Ashaury, S.Kom., M.T',
                'nid' => '412198688',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Puspita Nurul Sabrina, S.Kom., M.T',
                'nid' => '412190585',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Fajri Rakhmat Umbara, S.T., M.T',
                'nid' => '412185888',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Ridwan Ilyas, S.Kom., M.T',
                'nid' => '412182990',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Melina, S.Si., M.Si.',
                'nid' => '412100879',
                'prodi_id' => 3,
                'is_active' => true,
            ],
            [
                'nama' => 'Edvin Ramadhan, S.Kom., M.T',
                'nid' => '412105388',
                'prodi_id' => 3,
                'is_active' => true,
            ],
        ];

        // Insert data
        foreach ($dosenWali as $dosen) {
            DB::table('dosen_wali')->insert(array_merge($dosen, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}