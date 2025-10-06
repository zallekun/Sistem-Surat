<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newValues = [
            'processed',
            'approved_prodi',
            'rejected_prodi',
            'approved_fakultas',
            'rejected_fakultas',
            'surat_generated',
            'printed',
            'completed',
            'rejected',
            'reviewed',
            'forwarded',
            'signed',
            'admin_change_status',
            'admin_force_complete',
            'admin_reopen',
        ];
        $enumValues = "'" . implode("','", $newValues) . "'";
        DB::statement("ALTER TABLE approval_histories MODIFY COLUMN action ENUM({$enumValues}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $oldValues = [
            'processed',
            'approved_prodi',
            'rejected_prodi',
            'approved_fakultas',
            'rejected_fakultas',
            'surat_generated',
            'printed',
            'completed',
            'rejected',
            'reviewed',
            'forwarded',
            'signed',
        ];
        $enumValues = "'" . implode("','", $oldValues) . "'";
        DB::statement("ALTER TABLE approval_histories MODIFY COLUMN action ENUM({$enumValues}) NOT NULL");
    }
};