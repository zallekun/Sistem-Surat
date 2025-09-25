<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratGeneratedTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('surat_generated')) {
            Schema::create('surat_generated', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengajuan_id')->constrained('pengajuan_surat');
                $table->string('nomor_surat')->unique();
                $table->foreignId('barcode_signature_id')->nullable();
                $table->string('file_path')->nullable();
                $table->foreignId('generated_by')->constrained('users');
                $table->string('signed_by')->nullable();
                $table->timestamp('signed_at')->nullable();
                $table->enum('status', ['draft', 'generated', 'signed', 'completed'])->default('generated');
                $table->timestamps();
                
                $table->index('pengajuan_id');
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists('surat_generated');
    }
}