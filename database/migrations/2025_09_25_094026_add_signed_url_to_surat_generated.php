<?php
// database/migrations/2024_xx_xx_add_signed_url_to_surat_generated.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignedUrlToSuratGenerated extends Migration
{
    public function up()
    {
        Schema::table('surat_generated', function (Blueprint $table) {
            $table->string('signed_url', 500)->nullable()->after('file_path');
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('surat_generated', function (Blueprint $table) {
            $table->dropColumn(['signed_url', 'notes']);
        });
    }
}