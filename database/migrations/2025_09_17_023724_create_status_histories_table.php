<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('status_histories')) {
            Schema::create('status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('surat_id')->constrained('surat')->onDelete('cascade');
                $table->foreignId('status_id')->constrained('status_surat');
                $table->foreignId('user_id')->constrained('users');
                $table->text('keterangan')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->index(['surat_id', 'created_at']);
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists('status_histories');
    }
};