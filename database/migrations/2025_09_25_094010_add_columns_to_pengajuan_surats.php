<?php
// database/migrations/2024_xx_xx_add_columns_to_pengajuan_surats.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddColumnsToPengajuanSurats extends Migration
{
    public function up()
    {
        // First, update the status enum to include new statuses
        DB::statement("ALTER TABLE pengajuan_surats MODIFY COLUMN status ENUM(
            'pending',
            'processed',
            'approved_prodi',
            'rejected_prodi',
            'approved_fakultas',
            'rejected_fakultas', 
            'sedang_ditandatangani',
            'completed',
            'rejected'
        ) NOT NULL DEFAULT 'pending'");
        
        // Add new columns
        Schema::table('pengajuan_surats', function (Blueprint $table) {
            // Column for edited surat data
            $table->json('surat_data')->nullable()->after('additional_data');
            
            // Printing tracking
            $table->timestamp('printed_at')->nullable()->after('notes');
            $table->unsignedBigInteger('printed_by')->nullable()->after('printed_at');
            
            // Completion tracking
            $table->timestamp('completed_at')->nullable()->after('printed_by');
            $table->unsignedBigInteger('completed_by')->nullable()->after('completed_at');
            
            // Link to surat_generated
            $table->unsignedBigInteger('surat_generated_id')->nullable()->after('surat_id');
            
            // Add foreign keys
            $table->foreign('printed_by')->references('id')->on('users');
            $table->foreign('completed_by')->references('id')->on('users');
            $table->foreign('surat_generated_id')->references('id')->on('surat_generated');
        });
    }

    public function down()
    {
        Schema::table('pengajuan_surats', function (Blueprint $table) {
            $table->dropForeign(['printed_by']);
            $table->dropForeign(['completed_by']);
            $table->dropForeign(['surat_generated_id']);
            
            $table->dropColumn([
                'surat_data',
                'printed_at',
                'printed_by',
                'completed_at',
                'completed_by',
                'surat_generated_id'
            ]);
        });
        
        // Revert status enum
        DB::statement("ALTER TABLE pengajuan_surats MODIFY COLUMN status ENUM(
            'pending',
            'processed',
            'completed',
            'rejected'
        ) NOT NULL DEFAULT 'pending'");
    }
}