<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns if they don't exist
        $columns = [
            'approved_by_prodi' => 'bigint unsigned',
            'approved_at_prodi' => 'timestamp',
            'rejected_by_prodi' => 'bigint unsigned',
            'rejected_at_prodi' => 'timestamp',
            'rejection_reason_prodi' => 'text',
            'approved_by_fakultas' => 'bigint unsigned',
            'approved_at_fakultas' => 'timestamp',
            'rejected_by_fakultas' => 'bigint unsigned',
            'rejected_at_fakultas' => 'timestamp',
            'rejection_reason_fakultas' => 'text',
            'notes' => 'text',
            'direct_to_fakultas' => 'boolean'
        ];

        foreach ($columns as $column => $type) {
            if (!Schema::hasColumn('pengajuan_surats', $column)) {
                if ($type === 'boolean') {
                    DB::statement("ALTER TABLE pengajuan_surats ADD COLUMN {$column} TINYINT(1) DEFAULT 0");
                } elseif ($type === 'timestamp') {
                    DB::statement("ALTER TABLE pengajuan_surats ADD COLUMN {$column} TIMESTAMP NULL");
                } elseif ($type === 'text') {
                    DB::statement("ALTER TABLE pengajuan_surats ADD COLUMN {$column} TEXT NULL");
                } elseif ($type === 'bigint unsigned') {
                    DB::statement("ALTER TABLE pengajuan_surats ADD COLUMN {$column} BIGINT UNSIGNED NULL");
                }
            }
        }

        // Add foreign keys if they don't exist
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'pengajuan_surats' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        
        $existingFKs = array_column($foreignKeys, 'CONSTRAINT_NAME');

        if (!in_array('pengajuan_surats_approved_by_prodi_foreign', $existingFKs)) {
            DB::statement('ALTER TABLE pengajuan_surats ADD CONSTRAINT pengajuan_surats_approved_by_prodi_foreign FOREIGN KEY (approved_by_prodi) REFERENCES users(id) ON DELETE SET NULL');
        }
        if (!in_array('pengajuan_surats_rejected_by_prodi_foreign', $existingFKs)) {
            DB::statement('ALTER TABLE pengajuan_surats ADD CONSTRAINT pengajuan_surats_rejected_by_prodi_foreign FOREIGN KEY (rejected_by_prodi) REFERENCES users(id) ON DELETE SET NULL');
        }
        if (!in_array('pengajuan_surats_approved_by_fakultas_foreign', $existingFKs)) {
            DB::statement('ALTER TABLE pengajuan_surats ADD CONSTRAINT pengajuan_surats_approved_by_fakultas_foreign FOREIGN KEY (approved_by_fakultas) REFERENCES users(id) ON DELETE SET NULL');
        }
        if (!in_array('pengajuan_surats_rejected_by_fakultas_foreign', $existingFKs)) {
            DB::statement('ALTER TABLE pengajuan_surats ADD CONSTRAINT pengajuan_surats_rejected_by_fakultas_foreign FOREIGN KEY (rejected_by_fakultas) REFERENCES users(id) ON DELETE SET NULL');
        }

        // Add indexes if they don't exist
        $indexes = DB::select("SHOW INDEX FROM pengajuan_surats");
        $existingIndexes = array_column($indexes, 'Key_name');

        if (!in_array('pengajuan_surats_status_direct_to_fakultas_index', $existingIndexes)) {
            DB::statement('CREATE INDEX pengajuan_surats_status_direct_to_fakultas_index ON pengajuan_surats(status, direct_to_fakultas)');
        }
        if (!in_array('pengajuan_surats_approved_at_prodi_index', $existingIndexes)) {
            DB::statement('CREATE INDEX pengajuan_surats_approved_at_prodi_index ON pengajuan_surats(approved_at_prodi)');
        }
    }

    public function down(): void
    {
        Schema::table('pengajuan_surats', function (Blueprint $table) {
            // Drop foreign keys
            try { $table->dropForeign(['approved_by_prodi']); } catch (\Exception $e) {}
            try { $table->dropForeign(['rejected_by_prodi']); } catch (\Exception $e) {}
            try { $table->dropForeign(['approved_by_fakultas']); } catch (\Exception $e) {}
            try { $table->dropForeign(['rejected_by_fakultas']); } catch (\Exception $e) {}
        });
        
        // Drop indexes
        try { DB::statement('DROP INDEX pengajuan_surats_status_direct_to_fakultas_index ON pengajuan_surats'); } catch (\Exception $e) {}
        try { DB::statement('DROP INDEX pengajuan_surats_approved_at_prodi_index ON pengajuan_surats'); } catch (\Exception $e) {}
        
        // Drop columns
        $columns = ['approved_by_prodi', 'approved_at_prodi', 'rejected_by_prodi', 
                   'rejected_at_prodi', 'rejection_reason_prodi', 'approved_by_fakultas', 
                   'approved_at_fakultas', 'rejected_by_fakultas', 'rejected_at_fakultas', 
                   'rejection_reason_fakultas', 'notes', 'direct_to_fakultas'];
        
        foreach ($columns as $column) {
            if (Schema::hasColumn('pengajuan_surats', $column)) {
                Schema::table('pengajuan_surats', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};