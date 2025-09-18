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
        Schema::table('surat', function (Blueprint $table) {
            // Remove the old 'tujuan' column
            $table->dropColumn('tujuan');

            // Add the new 'tujuan_jabatan_id' foreign key column
            $table->foreignId('tujuan_jabatan_id')->nullable()->constrained('jabatan')->onDelete('set null')->after('perihal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropConstrainedForeignId('tujuan_jabatan_id');

            // Re-add the old 'tujuan' column
            $table->string('tujuan', 200)->nullable()->after('perihal');
        });
    }
};