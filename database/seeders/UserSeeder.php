<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Jabatan;
use App\Models\Prodi;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $dekanRole = Role::where('name', 'dekan')->first();
        $wadekRole = Role::where('name', 'wadek')->first(); // Generic wadek role, might be removed later
        $kabagTuRole = Role::where('name', 'kabag_tu')->first();
        $kaprodiRole = Role::where('name', 'kaprodi')->first();
        $staffFakultasRole = Role::where('name', 'staff_fakultas')->first();
        $staffProdiRole = Role::where('name', 'staff_prodi')->first();

        $dekanJabatan = Jabatan::where('kode_jabatan', 'DEKAN')->first();
        $wadek1Jabatan = Jabatan::where('kode_jabatan', 'WADEK1')->first();
        $wadek2Jabatan = Jabatan::where('kode_jabatan', 'WADEK2')->first();
        $wadek3Jabatan = Jabatan::where('kode_jabatan', 'WADEK3')->first();
        $kabagTuJabatan = Jabatan::where('kode_jabatan', 'KABAG_TU')->first();
        $kaprodiJabatan = Jabatan::where('kode_jabatan', 'KAPRODI')->first();
        $staffFakultasJabatan = Jabatan::where('kode_jabatan', 'STAFF_FAK')->first();
        $staffProdiJabatan = Jabatan::where('kode_jabatan', 'STAFF_PRODI')->first();
        $adminJabatan = Jabatan::where('kode_jabatan', 'ADMIN')->first();

        $informatikaProdi = Prodi::where('kode_prodi', 'IF')->first();
        $sistemInformasiProdi = Prodi::where('kode_prodi', 'SI')->first();
        $kimiaProdi = Prodi::where('kode_prodi', 'KIM')->first();

        $users = [
            [
                'nama' => 'Administrator',
                'email' => 'admin@sistemsurat.com',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'jabatan_id' => $adminJabatan->id,
                'nip' => '1234567890',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Dr. Dekan, M.Kom',
                'email' => 'dekan@sistemsurat.com',
                'password' => Hash::make('dekan123'),
                'role_id' => $dekanRole->id,
                'jabatan_id' => $dekanJabatan->id,
                'nip' => '1234567891',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Dr. Wakil Dekan 1, M.Si',
                'email' => 'wadek1@sistemsurat.com',
                'password' => Hash::make('wadek123'),
                'role_id' => $wadekRole->id, // Assuming a generic wadek role for now
                'jabatan_id' => $wadek1Jabatan->id,
                'nip' => '1234567892',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Dr. Wakil Dekan 2, M.Si',
                'email' => 'wadek2@sistemsurat.com',
                'password' => Hash::make('wadek234'),
                'role_id' => $wadekRole->id,
                'jabatan_id' => $wadek2Jabatan->id,
                'nip' => '1234567897',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Dr. Wakil Dekan 3, M.Si',
                'email' => 'wadek3@sistemsurat.com',
                'password' => Hash::make('wadek456'),
                'role_id' => $wadekRole->id,
                'jabatan_id' => $wadek3Jabatan->id,
                'nip' => '1234567898',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Kabag TU',
                'email' => 'kabagtu@sistemsurat.com',
                'password' => Hash::make('kabagtu123'),
                'role_id' => $kabagTuRole->id,
                'jabatan_id' => $kabagTuJabatan->id,
                'nip' => '1234567893',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Kaprodi Informatika',
                'email' => 'kaprodi.if@sistemsurat.com',
                'password' => Hash::make('kaprodi123'),
                'role_id' => $kaprodiRole->id,
                'jabatan_id' => $kaprodiJabatan->id,
                'prodi_id' => $informatikaProdi->id,
                'nip' => '1234567894',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Staff Fakultas',
                'email' => 'staff.fakultas@sistemsurat.com',
                'password' => Hash::make('staff123'),
                'role_id' => $staffFakultasRole->id,
                'jabatan_id' => $staffFakultasJabatan->id,
                'prodi_id' => $informatikaProdi->id, // Assign a prodi_id for Staff Fakultas
                'nip' => '1234567895',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Staff Prodi Informatika',
                'email' => 'staff.if@sistemsurat.com',
                'password' => Hash::make('staff123'),
                'role_id' => $staffProdiRole->id,
                'jabatan_id' => $staffProdiJabatan->id,
                'prodi_id' => $informatikaProdi->id,
                'nip' => '1234567896',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Staff Prodi Sistem Informasi',
                'email' => 'staff.si@sistemsurat.com',
                'password' => Hash::make('staff123'),
                'role_id' => $staffProdiRole->id,
                'jabatan_id' => $staffProdiJabatan->id,
                'prodi_id' => $sistemInformasiProdi->id,
                'nip' => '1234567899',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'nama' => 'Staff Prodi Kimia',
                'email' => 'staff.kimia@sistemsurat.com',
                'password' => Hash::make('staff123'),
                'role_id' => $staffProdiRole->id,
                'jabatan_id' => $staffProdiJabatan->id,
                'prodi_id' => $kimiaProdi->id,
                'nip' => '1234567877',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }
    }
}

