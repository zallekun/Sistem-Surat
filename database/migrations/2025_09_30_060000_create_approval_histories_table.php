<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create approval_histories table
        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship
            $table->string('approvable_type'); // PengajuanSurat, Surat, etc
            $table->unsignedBigInteger('approvable_id');
            
            // Action details
            $table->enum('action', [
                'processed',
                'approved_prodi',
                'rejected_prodi',
                'approved_fakultas',
                'rejected_fakultas',
                'printed',
                'completed',
                'rejected',
                'reviewed',
                'forwarded',
                'signed'
            ]);
            
            // Who performed this action
            $table->foreignId('performed_by')
                ->constrained('users')
                ->onDelete('restrict');
            
            // Additional info
            $table->text('notes')->nullable(); // Rejection reason, catatan, dll
            $table->json('metadata')->nullable(); // Extra data jika diperlukan
            
            $table->timestamps();
            
            // Indexes
            $table->index(['approvable_type', 'approvable_id']);
            $table->index('performed_by');
            $table->index('action');
            $table->index('created_at');
        });
        
        // 2. Migrate existing approval data from pengajuan_surats
        $this->migrateExistingApprovalData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
    }
    
    /**
     * Migrate existing approval data to approval_histories
     */
    private function migrateExistingApprovalData(): void
    {
        $pengajuans = DB::table('pengajuan_surats')->get();
        
        foreach ($pengajuans as $pengajuan) {
            $histories = [];
            
            // Map old columns to new approval history records
            $approvalMappings = [
                'processed' => ['by' => 'processed_by', 'at' => 'processed_at', 'notes' => null],
                'approved_prodi' => ['by' => 'approved_by_prodi', 'at' => 'approved_at_prodi', 'notes' => null],
                'rejected_prodi' => ['by' => 'rejected_by_prodi', 'at' => 'rejected_at_prodi', 'notes' => 'rejection_reason_prodi'],
                'approved_fakultas' => ['by' => 'approved_by_fakultas', 'at' => 'approved_at_fakultas', 'notes' => null],
                'rejected_fakultas' => ['by' => 'rejected_by_fakultas', 'at' => 'rejected_at_fakultas', 'notes' => 'rejection_reason_fakultas'],
                'printed' => ['by' => 'printed_by', 'at' => 'printed_at', 'notes' => null],
                'completed' => ['by' => 'completed_by', 'at' => 'completed_at', 'notes' => null],
                'rejected' => ['by' => 'rejected_by', 'at' => 'rejected_at', 'notes' => 'rejection_reason'],
            ];
            
            foreach ($approvalMappings as $action => $columns) {
                $userId = $pengajuan->{$columns['by']};
                $timestamp = $pengajuan->{$columns['at']};
                $notes = $columns['notes'] ? $pengajuan->{$columns['notes']} : null;
                
                // Only create history if user_id and timestamp exist
                if ($userId && $timestamp) {
                    $histories[] = [
                        'approvable_type' => 'App\\Models\\PengajuanSurat',
                        'approvable_id' => $pengajuan->id,
                        'action' => $action,
                        'performed_by' => $userId,
                        'notes' => $notes,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }
            
            // Insert all histories for this pengajuan
            if (!empty($histories)) {
                DB::table('approval_histories')->insert($histories);
            }
        }
        
        // Log migration result
        $migratedCount = DB::table('approval_histories')->count();
        \Log::info("Migrated {$migratedCount} approval history records from pengajuan_surats");
    }
};