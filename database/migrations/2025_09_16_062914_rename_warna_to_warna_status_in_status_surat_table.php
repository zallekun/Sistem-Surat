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
        Schema::table('status_surat', function (Blueprint $table) {
            $table->renameColumn('warna', 'warna_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_surat', function (Blueprint $table) {
            $table->renameColumn('warna_status', 'warna');
        });
    }
};