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
        Schema::create('fakultas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_fakultas', 100);
            $table->string('kode_fakultas', 20)->unique();
            $table->string('dekan_id')->nullable();
            $table->string('wadek1_id')->nullable();
            $table->string('wadek2_id')->nullable();
            $table->string('wadek3_id')->nullable();
            $table->timestamps();
            
            $table->index('kode_fakultas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fakultas');
    }
};
