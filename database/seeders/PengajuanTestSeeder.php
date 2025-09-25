<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengajuanSurat;
use App\Models\JenisSurat;
use App\Models\Prodi;
use Illuminate\Support\Str;

class PengajuanTestSeeder extends Seeder
{
    public function run()
    {
        $jenisSurat = JenisSurat::first();
        $prodi = Prodi::first();
        
        if (!$jenisSurat || !$prodi) {
            $this->command->error('Jenis Surat atau Prodi tidak ditemukan. Jalankan seeder utama dulu.');
            return;
        }

        $pengajuanData = [
            [
                'nim' => '2023001',
                'nama_mahasiswa' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@student.unjani.ac.id',
                'phone' => '081234567001',
                'status' => 'pending', // Valid status
                'keperluan' => 'Beasiswa Dikti',
                'additional_data' => [
                    'semester' => 'Genap',
                    'tahun_akademik' => '2024/2025',
                    'nama_orang_tua' => 'Budi Santoso',
                    'tempat_lahir_ortu' => 'Jakarta',
                    'tanggal_lahir_ortu' => '15-05-1975',
                    'nip_ortu' => '197505151999031001',
                    'pangkat_ortu' => 'Pembina IV/a',
                    'pekerjaan_ortu' => 'PNS',
                    'instansi_ortu' => 'Kementerian Pendidikan',
                    'alamat_kantor_ortu' => 'Jl. Sudirman No. 1 Jakarta',
                    'alamat_rumah_ortu' => 'Jl. Merdeka No. 123 Bandung'
                ]
            ],
            [
                'nim' => '2023002',
                'nama_mahasiswa' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@student.unjani.ac.id',
                'phone' => '081234567002',
                'status' => 'pending',
                'keperluan' => 'Tunjangan Keluarga',
                'additional_data' => [
                    'semester' => 'Ganjil',
                    'tahun_akademik' => '2024/2025',
                    'nama_orang_tua' => 'Hasan Basri',
                    'tempat_lahir_ortu' => 'Bandung',
                    'tanggal_lahir_ortu' => '20-08-1970',
                    'nip_ortu' => '197008201995031002',
                    'pangkat_ortu' => 'Penata III/c',
                    'pekerjaan_ortu' => 'TNI',
                    'instansi_ortu' => 'Kodam III Siliwangi',
                    'alamat_kantor_ortu' => 'Jl. Aceh No. 50 Bandung',
                    'alamat_rumah_ortu' => 'Komplek TNI Blok A No. 15'
                ]
            ],
            [
                'nim' => '2023003',
                'nama_mahasiswa' => 'Budi Pratama',
                'email' => 'budi.pratama@student.unjani.ac.id',
                'phone' => '081234567003',
                'status' => 'processed', // Valid status
                'keperluan' => 'Asuransi Kesehatan',
                'additional_data' => [
                    'semester' => 'Genap',
                    'tahun_akademik' => '2024/2025',
                    'nama_orang_tua' => 'Agus Suryanto',
                    'tempat_lahir_ortu' => 'Surabaya',
                    'tanggal_lahir_ortu' => '10-03-1968',
                    'pekerjaan_ortu' => 'Wiraswasta',
                    'instansi_ortu' => 'PT. Maju Jaya',
                    'alamat_kantor_ortu' => 'Jl. Industri No. 88 Cimahi',
                    'alamat_rumah_ortu' => 'Perumahan Griya Asri No. 45'
                ]
            ],
            [
                'nim' => '2023004',
                'nama_mahasiswa' => 'Dewi Lestari',
                'email' => 'dewi.lestari@student.unjani.ac.id',
                'phone' => '081234567004',
                'status' => 'processed', // Valid status
                'keperluan' => 'Beasiswa Bank BRI',
                'additional_data' => [
                    'semester' => 'Ganjil',
                    'tahun_akademik' => '2024/2025',
                    'nama_orang_tua' => 'Sutrisno',
                    'tempat_lahir_ortu' => 'Yogyakarta',
                    'tanggal_lahir_ortu' => '25-12-1972',
                    'nip_ortu' => '197212251998031003',
                    'pangkat_ortu' => 'Penata Muda III/a',
                    'pekerjaan_ortu' => 'Guru',
                    'instansi_ortu' => 'SMA Negeri 1 Cimahi',
                    'alamat_kantor_ortu' => 'Jl. Pendidikan No. 10 Cimahi',
                    'alamat_rumah_ortu' => 'Jl. Cihanjuang No. 78 Cimahi'
                ]
            ],
            [
                'nim' => '2023005',
                'nama_mahasiswa' => 'Rizky Firmansyah',
                'email' => 'rizky.firmansyah@student.unjani.ac.id',
                'phone' => '081234567005',
                'status' => 'completed', // Valid status
                'keperluan' => 'KIP Kuliah',
                'additional_data' => [
                    'semester' => 'Genap',
                    'tahun_akademik' => '2024/2025',
                    'nama_orang_tua' => 'Dadang Hermawan',
                    'tempat_lahir_ortu' => 'Garut',
                    'tanggal_lahir_ortu' => '15-07-1969',
                    'pekerjaan_ortu' => 'Petani',
                    'alamat_rumah_ortu' => 'Desa Sukamaju RT 03/05 Garut'
                ]
            ]
        ];

        foreach ($pengajuanData as $data) {
            PengajuanSurat::create([
                'tracking_token' => 'TRK-' . strtoupper(Str::random(8)),
                'nim' => $data['nim'],
                'nama_mahasiswa' => $data['nama_mahasiswa'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'prodi_id' => $prodi->id,
                'jenis_surat_id' => $jenisSurat->id,
                'keperluan' => $data['keperluan'],
                'status' => $data['status'],
                'additional_data' => $data['additional_data'],
                'created_at' => now()->subDays(rand(1, 7)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('5 pengajuan test berhasil dibuat!');
        $this->command->table(
            ['NIM', 'Nama', 'Status', 'Keperluan'],
            collect($pengajuanData)->map(function ($item) {
                return [
                    $item['nim'],
                    $item['nama_mahasiswa'],
                    $item['status'],
                    $item['keperluan']
                ];
            })
        );
    }
}