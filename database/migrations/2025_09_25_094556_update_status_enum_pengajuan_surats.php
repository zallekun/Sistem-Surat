<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateStatusEnumPengajuanSurats extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE pengajuan_surats MODIFY COLUMN status ENUM(
            'pending',
            'processed',
            'approved_prodi',
            'rejected_prodi',
            'approved_fakultas', 
            'rejected_fakultas',
            'sedang_ditandatangani',
            'completed',
            'rejected',
            'submitted'
        ) NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE pengajuan_surats MODIFY COLUMN status ENUM(
            'pending',
            'processed',
            'completed', 
            'rejected',
            'submitted'
        ) NOT NULL DEFAULT 'pending'");
    }
}