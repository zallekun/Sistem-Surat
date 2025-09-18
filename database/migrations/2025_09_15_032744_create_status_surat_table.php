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
        Schema::create('status_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_status', 50);
            $table->string('kode_status', 20)->unique();
            $table->string('warna', 20)->nullable(); // untuk UI
            $table->integer('urutan')->default(0);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            
            $table->index('kode_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_surat');
    }
};
