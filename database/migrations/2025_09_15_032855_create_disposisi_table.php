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
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat')->onDelete('cascade');
            $table->foreignId('dari_user')->constrained('users')->restrictOnDelete();
            $table->foreignId('kepada_user')->constrained('users')->restrictOnDelete();
            $table->text('instruksi');
            $table->date('deadline')->nullable();
            $table->enum('prioritas', ['rendah', 'normal', 'tinggi', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'dibaca', 'diproses', 'selesai', 'ditolak'])
                  ->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_baca')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['surat_id', 'kepada_user']);
            $table->index(['kepada_user', 'status']);
            $table->index('deadline');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisi');
    }
};
