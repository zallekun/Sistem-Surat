<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100)->unique();
            $table->date('tanggal_surat');
            $table->string('perihal');
            $table->text('isi_surat')->nullable();
            $table->enum('tipe_surat', ['masuk', 'keluar']);
            $table->enum('sifat_surat', ['biasa', 'segera', 'sangat_segera', 'rahasia']);
            $table->string('lampiran', 50)->nullable();
            $table->text('keterangan')->nullable();
            
            // Foreign keys
            $table->foreignId('jenis_id')->nullable()->constrained('jenis_surat')->nullOnDelete();
            $table->foreignId('status_id')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            
            // Additional fields for surat masuk
            $table->string('asal_surat', 200)->nullable();
            $table->string('nomor_surat_masuk', 100)->nullable();
            $table->date('tanggal_diterima')->nullable();
            
            // Additional fields for surat keluar  
            $table->string('tujuan', 200)->nullable();
            $table->string('tembusan')->nullable();
            
            // File attachments
            $table->string('file_surat')->nullable();
            $table->string('file_lampiran')->nullable();
            
            // Workflow
            $table->enum('status', ['draft', 'review', 'approved', 'rejected', 'sent', 'archived'])
                  ->default('draft');
            $table->timestamp('tanggal_kirim')->nullable();
            $table->timestamp('tanggal_arsip')->nullable();
            
            // Update tracking
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('nomor_surat');
            $table->index('tanggal_surat');
            $table->index('tipe_surat');
            $table->index('status');
            $table->index('created_by');
            $table->index(['tipe_surat', 'status']);
            $table->index(['tanggal_surat', 'tipe_surat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
