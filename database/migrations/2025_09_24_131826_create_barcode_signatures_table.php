<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarcodeSignaturesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('barcode_signatures')) {
            Schema::create('barcode_signatures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('fakultas_id')->nullable();
                $table->string('pejabat_nama');
                $table->string('pejabat_nid')->nullable();
                $table->string('pejabat_jabatan');
                $table->string('pejabat_pangkat')->nullable();
                $table->string('barcode_path');
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->index('fakultas_id');
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists('barcode_signatures');
    }
}