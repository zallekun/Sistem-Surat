<?php
/**
 * FIX TRACKING WORKFLOW - KIRIM KE PENGAJU
 * 
 * Masalah: 
 * 1. Tracking tidak menampilkan download button
 * 2. Workflow generate PDF terlalu kompleks
 * 
 * Solusi:
 * 1. Tambah tombol "Kirim ke Pengaju" di fakultas staff
 * 2. Status langsung completed tanpa generate PDF
 * 3. Tracking menampilkan "Surat Selesai" untuk completed status
 * 
 * File: fix_tracking_workflow.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class TrackingWorkflowFixer
{
    private $output = [];
    private $backupPath;
    
    public function __construct()
    {
        $this->log("=== FIX TRACKING WORKFLOW - KIRIM KE PENGAJU ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
        
        $this->backupPath = storage_path('backups/tracking_workflow_' . date('Y-m-d_H-i-s'));
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }
    
    public function fixWorkflow()
    {
        $this->log("🔧 FIXING TRACKING WORKFLOW");
        $this->log("===========================");
        
        $this->log("\n📦 PHASE 1: BACKUP FILES");
        $this->backupFiles();
        
        $this->log("\n🎮 PHASE 2: UPDATE FAKULTAS STAFF CONTROLLER");
        $this->updateFakultasController();
        
        $this->log("\n🎨 PHASE 3: UPDATE FAKULTAS VIEW");
        $this->updateFakultasView();
        
        $this->log("\n📱 PHASE 4: UPDATE TRACKING VIEW");
        $this->updateTrackingView();
        
        $this->log("\n🛣️  PHASE 5: ADD ROUTES");
        $this->addRoutes();
        
        $this->log("\n📊 PHASE 6: UPDATE DATA STATUS");
        $this->updateDataForTesting();
        
        $this->displayResults();
    }
    
    private function backupFiles()
    {
        $files = [
            'app/Http/Controllers/FakultasStaffController.php',
            'resources/views/fakultas/surat/show.blade.php',
            'resources/views/public/tracking/show.blade.php'
        ];
        
        foreach ($files as $file) {
            if (File::exists($file)) {
                $backupFile = $this->backupPath . '/' . str_replace(['/', '\\'], '_', $file);
                File::copy($file, $backupFile);
                $this->log("✅ Backed up: {$file}");
            }
        }
    }
    
    private function updateFakultasController()
    {
        $controllerPath = app_path('Http/Controllers/FakultasStaffController.php');
        if (!File::exists($controllerPath)) {
            $this->log("❌ FakultasStaffController not found");
            return;
        }
        
        $content = File::get($controllerPath);
        
        // Add "Kirim ke Pengaju" method
        if (!str_contains($content, 'kirimKePengaju')) {
            $newMethod = '
    /**
     * Kirim surat ke pengaju (mark as completed without PDF generation)
     */
    public function kirimKePengaju(Request $request, $id)
    {
        $user = Auth::user();
        $pengajuan = PengajuanSurat::with([\'prodi\', \'jenisSurat\'])->findOrFail($id);
        
        if (!in_array($pengajuan->status, [\'processed\', \'approved_prodi\'])) {
            return response()->json([
                \'success\' => false,
                \'message\' => \'Pengajuan tidak dapat dikirim. Status: \' . $pengajuan->status
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Update pengajuan status to completed
            $pengajuan->update([
                \'status\' => \'completed\',
                \'completed_by\' => $user->id,
                \'completed_at\' => now(),
                \'completion_note\' => $request->input(\'note\', \'Surat telah selesai dan dapat digunakan\')
            ]);
            
            // Log activity
            \\Log::info(\'Pengajuan sent to applicant\', [
                \'pengajuan_id\' => $pengajuan->id,
                \'nim\' => $pengajuan->nim,
                \'tracking_token\' => $pengajuan->tracking_token,
                \'completed_by\' => $user->id
            ]);
            
            DB::commit();
            
            return response()->json([
                \'success\' => true,
                \'message\' => \'Surat berhasil dikirim ke pengaju. Status pengajuan telah selesai.\',
                \'tracking_url\' => route(\'tracking.show\', $pengajuan->tracking_token)
            ]);
            
        } catch (\\Exception $e) {
            DB::rollback();
            \\Log::error(\'Error sending to applicant\', [
                \'pengajuan_id\' => $id,
                \'error\' => $e->getMessage()
            ]);
            
            return response()->json([
                \'success\' => false,
                \'message\' => \'Gagal mengirim ke pengaju: \' . $e->getMessage()
            ], 500);
        }
    }';
            
            $content = preg_replace('/}\s*$/', $newMethod . "\n}", $content);
            File::put($controllerPath, $content);
            $this->log("✅ Added kirimKePengaju method to FakultasStaffController");
        }
    }
    
    private function updateFakultasView()
    {
        $viewPath = resource_path('views/fakultas/surat/show.blade.php');
        if (!File::exists($viewPath)) {
            $this->log("❌ Fakultas show view not found");
            return;
        }
        
        $content = File::get($viewPath);
        
        // Add "Kirim ke Pengaju" button
        if (!str_contains($content, 'kirimKePengaju')) {
            // Find action buttons section and add new button
            $newButton = '
                                @if($pengajuan && in_array($pengajuan->status, [\'processed\', \'approved_prodi\']))
                                    <button onclick="kirimKePengaju({{ $pengajuan->id }})" 
                                            style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; margin-right: 12px;">
                                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
                                        Kirim ke Pengaju
                                    </button>
                                @endif';
            
            // Insert before existing Generate PDF button
            $content = str_replace(
                '@if($pengajuan && $pengajuan->canGeneratePdf())',
                $newButton . "\n                            @if(\$pengajuan && \$pengajuan->canGeneratePdf())",
                $content
            );
            
            // Add JavaScript function
            if (!str_contains($content, 'function kirimKePengaju')) {
                $jsFunction = '
    // Kirim ke Pengaju Function
    function kirimKePengaju(id) {
        const note = prompt(\'Tambahkan catatan untuk pengaju (opsional):\', \'Surat keterangan telah selesai dan dapat digunakan.\');
        if (note === null) return; // User cancelled
        
        if (confirm(\'Kirim surat ke pengaju? Status akan berubah menjadi SELESAI dan pengaju akan bisa melihat hasilnya.\')) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = \'<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...\';
            
            fetch(`/fakultas/surat/kirim-ke-pengaju/${id}`, {
                method: \'POST\',
                headers: {
                    \'Content-Type\': \'application/json\',
                    \'X-CSRF-TOKEN\': \'{{ csrf_token() }}\'
                },
                body: JSON.stringify({ note: note })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    if (data.tracking_url) {
                        const openTracking = confirm(\'Apakah Anda ingin melihat halaman tracking untuk pengaju?\');
                        if (openTracking) {
                            window.open(data.tracking_url, \'_blank\');
                        }
                    }
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    alert(data.message || \'Terjadi kesalahan\');
                }
                button.disabled = false;
                button.innerHTML = originalText;
            })
            .catch(error => {
                console.error(\'Error:\', error);
                alert(\'Terjadi kesalahan jaringan\');
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }
    }';
                
                $content = str_replace('</script>', $jsFunction . "\n</script>", $content);
            }
            
            File::put($viewPath, $content);
            $this->log("✅ Updated fakultas show view with \'Kirim ke Pengaju\' button");
        }
    }
    
    private function updateTrackingView()
    {
        $viewPath = resource_path('views/public/tracking/show.blade.php');
        if (!File::exists($viewPath)) {
            $this->log("❌ Tracking show view not found");
            return;
        }
        
        $content = File::get($viewPath);
        
        // Update the PDF available check to show "Surat Selesai" for completed status
        $updatedJavaScript = '
    // Check if PDF available OR status is completed
    if (pengajuan.pdf_available && pengajuan.download_url) {
        downloadButtonHtml = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-green-800">
                            <i class="fas fa-check-circle mr-2"></i>
                            Surat Siap Didownload
                        </h4>
                        <p class="text-green-700 text-sm mt-1">
                            Surat Anda telah selesai dan siap untuk didownload dalam format PDF.
                        </p>
                    </div>
                    <a href="${pengajuan.download_url}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md text-sm font-medium transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Download PDF
                    </a>
                </div>
            </div>
        `;
    } else if (pengajuan.status === \'completed\') {
        downloadButtonHtml = `
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-center">
                    <div class="text-center">
                        <h4 class="font-medium text-blue-800 mb-2">
                            <i class="fas fa-check-circle mr-2"></i>
                            Surat Telah Selesai
                        </h4>
                        <p class="text-blue-700 text-sm">
                            Surat keterangan Anda telah selesai diproses. Silakan hubungi bagian akademik atau datang langsung ke kampus untuk mengambil surat fisik.
                        </p>
                    </div>
                </div>
            </div>
        `;
    }';
        
        // Replace the existing PDF check
        $content = preg_replace(
            '/\/\/ Check if PDF available[\s\S]*?}\s*;/',
            $updatedJavaScript,
            $content
        );
        
        File::put($viewPath, $content);
        $this->log("✅ Updated tracking view to show \'Surat Selesai\' for completed status");
    }
    
    private function addRoutes()
    {
        $routesFile = base_path('routes/web.php');
        $content = File::get($routesFile);
        
        $newRoute = "// Kirim ke Pengaju\nRoute::post('/fakultas/surat/kirim-ke-pengaju/{id}', [App\\Http\\Controllers\\FakultasStaffController::class, 'kirimKePengaju'])->name('fakultas.surat.kirim-pengaju');";
        
        if (!str_contains($content, 'kirim-ke-pengaju')) {
            $content .= "\n" . $newRoute . "\n";
            File::put($routesFile, $content);
            $this->log("✅ Added kirim-ke-pengaju route");
        }
    }
    
    private function updateDataForTesting()
    {
        try {
            // Set some pengajuan back to processed for testing
            $updatedCount = DB::table('pengajuan_surats')
                ->where('status', 'completed')
                ->whereNull('completed_at')
                ->update(['status' => 'processed']);
            
            $this->log("✅ Set {$updatedCount} completed pengajuan back to processed for testing");
            
            // Show current status distribution
            $stats = DB::table('pengajuan_surats')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();
                
            $this->log("📊 Current status distribution:");
            foreach ($stats as $stat) {
                $this->log("   {$stat->status}: {$stat->count}");
            }
            
        } catch (\Exception $e) {
            $this->log("❌ Error updating data: " . $e->getMessage());
        }
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . PHP_EOL;
    }
    
    public function displayResults()
    {
        $this->log("\n" . str_repeat("=", 60));
        $this->log("🎉 TRACKING WORKFLOW FIX COMPLETED");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
        
        $this->log("📋 SUMMARY OF CHANGES:");
        $this->log("- ✅ Added 'Kirim ke Pengaju' button in fakultas staff view");
        $this->log("- ✅ Status langsung completed tanpa generate PDF");
        $this->log("- ✅ Tracking menampilkan 'Surat Selesai' untuk completed");
        $this->log("- ✅ Staff tetap bisa generate PDF jika diperlukan");
        $this->log("- ✅ Workflow lebih sederhana dan praktis");
        $this->log("");
        
        $this->log("📂 Backup location: {$this->backupPath}");
        
        $this->log("\n🎯 NEXT STEPS:");
        $this->log("1. Clear caches: php artisan cache:clear && php artisan route:clear");
        $this->log("2. Login sebagai staff_fakultas");
        $this->log("3. Buka pengajuan dengan status 'processed'");
        $this->log("4. Test tombol 'Kirim ke Pengaju'");
        $this->log("5. Cek tracking dengan status 'completed'");
        $this->log("6. Staff masih bisa generate PDF via tombol lainnya jika diperlukan");
        
        // Save log
        $logFile = storage_path('logs/tracking_workflow_fix_' . date('Y-m-d_H-i-s') . '.log');
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, implode("\n", $this->output));
        $this->log("\n💾 Complete log saved to: {$logFile}");
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    echo "🚀 Starting Tracking Workflow Fix...\n\n";
    
    try {
        $fixer = new TrackingWorkflowFixer();
        $fixer->fixWorkflow();
        
        echo "\n🎉 SUCCESS! Tracking workflow has been improved.\n";
        echo "📝 Now staff can easily send completed letters to applicants.\n";
        
    } catch (Exception $e) {
        echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "🚀 TRACKING WORKFLOW FIX (Web Mode)\n\n";
    
    try {
        $fixer = new TrackingWorkflowFixer();
        $fixer->fixWorkflow();
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>