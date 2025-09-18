<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'Sistem Manajemen Surat',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nama aplikasi'
            ],
            [
                'key' => 'app_logo',
                'value' => 'logo.png',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Logo aplikasi'
            ],
            [
                'key' => 'fakultas_name',
                'value' => 'Fakultas Teknik',
                'type' => 'string',
                'group' => 'institution',
                'description' => 'Nama fakultas'
            ],
            [
                'key' => 'fakultas_code',
                'value' => 'FT',
                'type' => 'string',
                'group' => 'institution',
                'description' => 'Kode fakultas'
            ],
            [
                'key' => 'university_name',
                'value' => 'Universitas XYZ',
                'type' => 'string',
                'group' => 'institution',
                'description' => 'Nama universitas'
            ],
            [
                'key' => 'auto_archive_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Jumlah hari sebelum surat otomatis diarsipkan'
            ],
            [
                'key' => 'max_upload_size',
                'value' => '10485760',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Maksimal ukuran file upload (bytes)'
            ],
            [
                'key' => 'allowed_file_types',
                'value' => 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Tipe file yang diizinkan'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
