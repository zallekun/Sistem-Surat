<?php
// database/migrations/2024_xx_xx_create_tracking_histories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackingHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('tracking_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengajuan_id');
            $table->string('status', 50);
            $table->text('description');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['pengajuan_id', 'created_at']);
            
            // Foreign keys
            $table->foreign('pengajuan_id')->references('id')->on('pengajuan_surats')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracking_histories');
    }
}