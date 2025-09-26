<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToSuratGeneratedTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_generated', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('surat_generated', 'signed_url')) {
                $table->string('signed_url', 500)->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('surat_generated', 'notes')) {
                $table->text('notes')->nullable()->after('metadata');
            }
            if (!Schema::hasColumn('surat_generated', 'nomor_surat')) {
                $table->string('nomor_surat', 255)->nullable()->after('pengajuan_id');
            }
            if (!Schema::hasColumn('surat_generated', 'signed_by')) {
                $table->string('signed_by', 255)->nullable()->after('signed_url');
            }
            if (!Schema::hasColumn('surat_generated', 'signed_at')) {
                $table->timestamp('signed_at')->nullable()->after('signed_by');
            }
            if (!Schema::hasColumn('surat_generated', 'generated_by')) {
                // Make sure it's NULLABLE to support SET NULL foreign key
                $table->unsignedBigInteger('generated_by')->nullable()->after('signed_at');
                
                // Add foreign key constraint with nullable column
                $table->foreign('generated_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');  // This will work now since column is nullable
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_generated', function (Blueprint $table) {
            // Drop foreign key first if it exists
            try {
                $table->dropForeign(['generated_by']);
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
            }
            
            // Drop columns if they exist
            $columnsToCheck = ['signed_url', 'notes', 'nomor_surat', 'signed_by', 'signed_at', 'generated_by'];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('surat_generated', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}