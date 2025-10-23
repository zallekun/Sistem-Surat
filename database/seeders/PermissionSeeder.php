<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::debug('PermissionSeeder: Running...');
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Log::debug('PermissionSeeder: Cache reset.');

        // Define permissions
        $permissions = [
            // Admin Dashboard
            'view_admin_dashboard',

            // Pengajuan Management
            'view_pengajuan',
            'delete_pengajuan',
            'restore_pengajuan',
            'force_complete_pengajuan',
            'reopen_pengajuan',
            'change_status_pengajuan',

            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'reset_user_password',
            'toggle_user_status',

            // Master Data - Prodi
            'view_prodi',
            'create_prodi',
            'edit_prodi',
            'delete_prodi',

            // Master Data - Jenis Surat
            'view_jenis_surat',
            'create_jenis_surat',
            'edit_jenis_surat',
            'delete_jenis_surat',

            // Master Data - Fakultas
            'view_fakultas',
            'create_fakultas',
            'edit_fakultas',
            'delete_fakultas',

            // Master Data - Dosen Wali
            'view_dosen_wali',
            'create_dosen_wali',
            'edit_dosen_wali',
            'delete_dosen_wali',

            // Audit Trail
            'view_audit_trail',
            'export_audit_trail',

            // Export
            'export_pengajuan',

            // Settings
            'view_settings',
            'update_settings',
            'clear_cache',
            'test_email',

            // Barcode Management
            'view_barcode_signatures',
            'manage_barcode_signatures',
        ];

        Log::debug('PermissionSeeder: Defined permissions count', ['count' => count($permissions)]);

        foreach ($permissions as $permission) {
            $createdPermission = Permission::firstOrCreate(['name' => $permission]);
            Log::debug('PermissionSeeder: Processed permission', ['name' => $permission, 'id' => $createdPermission->id ?? 'N/A']);
        }
        Log::debug('PermissionSeeder: All permissions processed.');

        // Assign all permissions to the 'admin' role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            dump('PermissionSeeder: Admin role found', ['id' => $adminRole->id]);
            $adminRole->givePermissionTo(Permission::all());
            dump('PermissionSeeder: Permissions assigned to admin role.');
        } else {
            dump('PermissionSeeder: Admin role not found!');
        }
        dump('PermissionSeeder: Finished.');
    }
}