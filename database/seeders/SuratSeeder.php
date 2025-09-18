<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Surat;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\StatusSurat;
use App\Models\JenisSurat;
use App\Models\Jabatan;
use App\Models\Tracking;
use App\Models\Disposisi;
use App\Models\DisposisiParalel;
use Illuminate\Support\Facades\DB;

class SuratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clean up previous data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Surat::truncate();
        Disposisi::truncate();
        DisposisiParalel::truncate();
        Tracking::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Get necessary foreign key IDs and users
        $statusDraft = StatusSurat::where('kode_status', 'draft')->first();
        $jenisBiasa = JenisSurat::first();
        
        $staffProdiIF = User::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Staff Program Studi'))
                              ->whereHas('prodi', fn($q) => $q->where('nama_prodi', 'Informatika'))
                              ->first();

        $staffProdiSI = User::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Staff Program Studi'))
                              ->whereHas('prodi', fn($q) => $q->where('nama_prodi', 'Sistem Informasi'))
                              ->first();

        $staffFakultas = User::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Staff Fakultas'))->first();

        if (!$staffProdiIF || !$staffProdiSI || !$staffFakultas) {
            $this->command->error('Could not find required users (Staff Prodi IF, Staff Prodi SI, Staff Fakultas). Please run UserSeeder first.');
            return;
        }

        // 3. Create letters
        $this->command->info('Creating letters for testing...');

        // Letters for Staff Prodi Informatika
        for ($i = 1; $i <= 3; $i++) {
            Surat::factory()->create([
                'perihal' => 'Surat Tes dari Prodi Informatika ' . $i,
                'created_by' => $staffProdiIF->id,
                'prodi_id' => $staffProdiIF->prodi_id,
                'fakultas_id' => $staffProdiIF->prodi->fakultas_id,
                'status_id' => $statusDraft->id,
                'jenis_id' => $jenisBiasa->id,
            ]);
        }

        // Letters for Staff Prodi Sistem Informasi
        for ($i = 1; $i <= 2; $i++) {
            Surat::factory()->create([
                'perihal' => 'Surat Tes dari Prodi Sistem Informasi ' . $i,
                'created_by' => $staffProdiSI->id,
                'prodi_id' => $staffProdiSI->prodi_id,
                'fakultas_id' => $staffProdiSI->prodi->fakultas_id,
                'status_id' => $statusDraft->id,
                'jenis_id' => $jenisBiasa->id,
            ]);
        }

        // Letters for Staff Fakultas
        for ($i = 1; $i <= 2; $i++) {
            Surat::factory()->create([
                'perihal' => 'Surat Tes dari Staff Fakultas ' . $i,
                'created_by' => $staffFakultas->id,
                'prodi_id' => null, // As per the new requirement
                'fakultas_id' => $staffFakultas->prodi->fakultas_id,
                'status_id' => $statusDraft->id,
                'jenis_id' => $jenisBiasa->id,
            ]);
        }

        $this->command->info('Surat seeder finished.');
    }
}