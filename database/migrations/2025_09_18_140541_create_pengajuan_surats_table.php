<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_surats', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_token', 32)->unique();
            $table->string('nim', 20);
            $table->string('nama_mahasiswa', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            
            // Reference ke tabel existing
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('cascade');
            $table->foreignId('jenis_surat_id')->constrained('jenis_surat')->onDelete('cascade');
            
            $table->text('keperluan');
            $table->json('additional_data')->nullable();
            
            $table->enum('status', ['pending', 'processed', 'completed', 'rejected'])->default('pending');
            
            // Reference ke surat yang dibuat (jika sudah diproses)
            $table->foreignId('surat_id')->nullable()->constrained('surat')->onDelete('set null');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Indexes untuk performance
            $table->index(['status', 'prodi_id']);
            $table->index('created_at');
            $table->index('tracking_token');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_surats');
    }
};