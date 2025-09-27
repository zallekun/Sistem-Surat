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
        // Tambah kolom untuk surat pengantar
        Schema::table('pengajuan_surats', function (Blueprint $table) {
            $table->string('surat_pengantar_url', 500)->nullable()->after('surat_generated_id');
            $table->string('surat_pengantar_nomor', 100)->nullable()->after('surat_pengantar_url');
            $table->timestamp('surat_pengantar_generated_at')->nullable()->after('surat_pengantar_nomor');
            $table->foreignId('surat_pengantar_generated_by')->nullable()->after('surat_pengantar_generated_at')
                  ->constrained('users')->nullOnDelete();
            $table->text('ttd_kaprodi_image')->nullable()->after('surat_pengantar_generated_by')
                  ->comment('Base64 atau URL gambar TTD Kaprodi');
            $table->string('nota_dinas_number', 50)->nullable()->after('ttd_kaprodi_image');
        });

        // Update enum status untuk tambah 'pengantar_generated'
        DB::statement("ALTER TABLE pengajuan_surats 
            MODIFY COLUMN status ENUM(
                'pending',
                'processed',
                'approved_prodi',
                'rejected_prodi',
                'approved_fakultas',
                'rejected_fakultas',
                'sedang_ditandatangani',
                'completed',
                'rejected',
                'submitted',
                'pengantar_generated'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_surats', function (Blueprint $table) {
            $table->dropForeign(['surat_pengantar_generated_by']);
            $table->dropColumn([
                'surat_pengantar_url',
                'surat_pengantar_nomor',
                'surat_pengantar_generated_at',
                'surat_pengantar_generated_by',
                'ttd_kaprodi_image',
                'nota_dinas_number'
            ]);
        });

        // Rollback enum status
        DB::statement("ALTER TABLE pengajuan_surats 
            MODIFY COLUMN status ENUM(
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
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};