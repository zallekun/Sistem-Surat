<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    public function run()
    {
        $jabatans = [
            [
                'nama_jabatan' => 'Dekan',
                'kode_jabatan' => 'DEKAN',
                'deskripsi' => 'Dekan Fakultas',
                'level' => 1
            ],
            [
                'nama_jabatan' => 'Wakil Dekan Bidang Akademik',
                'kode_jabatan' => 'WADEK1',
                'deskripsi' => 'Wakil Dekan Bidang Akademik',
                'level' => 2
            ],
            [
                'nama_jabatan' => 'Wakil Dekan Bidang Keuangan',
                'kode_jabatan' => 'WADEK2',
                'deskripsi' => 'Wakil Dekan Bidang Keuangan',
                'level' => 2
            ],
            [
                'nama_jabatan' => 'Wakil Dekan Bidang Kemahasiswaan',
                'kode_jabatan' => 'WADEK3',
                'deskripsi' => 'Wakil Dekan Bidang Kemahasiswaan',
                'level' => 2
            ],
            [
                'nama_jabatan' => 'Kepala Bagian TU',
                'kode_jabatan' => 'KABAG_TU',
                'deskripsi' => 'Kepala Bagian Tata Usaha',
                'level' => 3
            ],
            [
                'nama_jabatan' => 'Kepala Program Studi',
                'kode_jabatan' => 'KAPRODI',
                'deskripsi' => 'Kepala Program Studi',
                'level' => 3
            ],
            [
                'nama_jabatan' => 'Staff Fakultas',
                'kode_jabatan' => 'STAFF_FAK',
                'deskripsi' => 'Staff Administrasi Fakultas',
                'level' => 4
            ],
            [
                'nama_jabatan' => 'Staff Program Studi',
                'kode_jabatan' => 'STAFF_PRODI',
                'deskripsi' => 'Staff Administrasi Program Studi',
                'level' => 4
            ],
            [
                'nama_jabatan' => 'Administrator Sistem',
                'kode_jabatan' => 'ADMIN',
                'deskripsi' => 'Administrator Sistem',
                'level' => 0
            ]
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::firstOrCreate(['kode_jabatan' => $jabatan['kode_jabatan']], $jabatan);
        }
    }
}
