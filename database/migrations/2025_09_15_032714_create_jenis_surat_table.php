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
        Schema::create('jenis_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jenis', 100);
            $table->string('kode_surat', 20)->unique();
            $table->text('template')->nullable();
            $table->string('format_nomor', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            
            $table->index('kode_surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_surat');
    }
};
