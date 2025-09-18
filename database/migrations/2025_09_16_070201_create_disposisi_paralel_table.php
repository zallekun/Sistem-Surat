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
        Schema::create('disposisi_paralel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat')->onDelete('cascade');
            $table->foreignId('dari_jabatan_id')->constrained('jabatan')->onDelete('cascade');
            $table->json('kepada_jabatan_ids'); // Array of jabatan IDs
            $table->json('status_per_jabatan'); // Status for each jabatan ID (e.g., {'jabatan_id': 'pending/completed'})
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisi_paralel');
    }
};