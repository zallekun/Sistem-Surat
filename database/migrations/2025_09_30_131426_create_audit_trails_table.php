<?php
// database/migrations/2025_09_30_000002_create_audit_trails_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // force_complete, reopen, reset_status, delete, etc
            $table->string('model_type'); // App\Models\PengajuanSurat
            $table->unsignedBigInteger('model_id');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('reason'); // Alasan admin intervention
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index(['model_type', 'model_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};