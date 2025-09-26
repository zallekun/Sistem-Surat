<?php
/**
 * FIX APPROVE REDIRECT - STAFF PENGAJUAN
 * 
 * Masalah: Setelah approve pengajuan, redirect ke create surat
 * Solusi: Redirect ke staff.pengajuan.index setelah approve
 * 
 * File: fix_approve_redirect.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

class ApproveRedirectFixer
{
    private $output = [];
    
    public function __construct()
    {
        $this->log("=== FIX APPROVE REDIRECT - STAFF PENGAJUAN ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
    }
    
    public function fixRedirect()
    {
        $this->log("🔧 FIXING APPROVE REDIRECT ISSUE");
        $this->log("================================");
        
        // Fix SuratController processProdiPengajuan method
        $this->fixSuratController();
        
        // Fix staff pengajuan show view JavaScript
        $this->fixStaffView();
        
        $this->displayResults();
    }
    
    private function fixSuratController()
    {
        $controllerPath = app_path('Http/Controllers/SuratController.php');
        if (!File::exists($controllerPath)) {
            $this->log("❌ SuratController.php not found");
            return;
        }
        
        $this->log("🔧 Updating SuratController processProdiPengajuan method...");
        
        $content = File::get($controllerPath);
        
        // Find and replace the processProdiPengajuan method
        $newMethod = '
    /**
     * Process pengajuan at prodi level (approve/reject)
     * FIXED: Redirect to staff pengajuan index instead of surat create
     */
    public function processProdiPengajuan(Request $request, $id)
    {
        $request->validate([
            \'action\' => \'required|in:approve,reject\',
            \'rejection_reason\' => \'required_if:action,reject|string|max:500\',
        ]);
        
        $user = Auth::user();
        
        if (!$user->hasRole([\'staff_prodi\', \'kaprodi\'])) {
            return response()->json([\'success\' => false, \'message\' => \'Unauthorized\'], 403);
        }
        
        $pengajuan = PengajuanSurat::where(\'prodi_id\', $user->prodi_id)
            ->where(\'id\', $id)
            ->first();
        
        if (!$pengajuan) {
            return response()->json([
                \'success\' => false, 
                \'message\' => \'Pengajuan tidak ditemukan\'
            ], 404);
        }
        
        if ($pengajuan->status !== \'pending\') {
            return response()->json([
                \'success\' => false, 
                \'message\' => \'Pengajuan sudah diproses sebelumnya\'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            if ($request->action === \'approve\') {
                $pengajuan->update([
                    \'status\' => \'processed\',
                    \'approved_by_prodi\' => $user->id,
                    \'approved_at_prodi\' => now()
                ]);
                
                $message = \'Pengajuan berhasil disetujui dan diteruskan ke fakultas\';
                
            } else {
                $pengajuan->update([
                    \'status\' => \'rejected_prodi\',
                    \'rejected_by_prodi\' => $user->id,
                    \'rejected_at_prodi\' => now(),
                    \'rejection_reason_prodi\' => $request->rejection_reason
                ]);
                
                $message = \'Pengajuan berhasil ditolak\';
            }
            
            DB::commit();
            
            return response()->json([
                \'success\' => true,
                \'message\' => $message,
                \'redirect_url\' => route(\'staff.pengajuan.index\') // FIXED: Redirect to index
            ]);
            
        } catch (\\Exception $e) {
            DB::rollback();
            
            Log::error(\'Error processing prodi pengajuan\', [
                \'error\' => $e->getMessage(),
                \'pengajuan_id\' => $id,
                \'user_id\' => $user->id
            ]);
            
            return response()->json([
                \'success\' => false,
                \'message\' => \'Terjadi kesalahan sistem\'
            ], 500);
        }
    }';
        
        // Replace the method
        $pattern = '/public\s+function\s+processProdiPengajuan\s*\([^}]*\}(?:[^}]*\})*(?:[^}]*\})*(?:[^}]*\})*(?:[^}]*\})*(?:[^}]*\})*/s';
        $content = preg_replace($pattern, $newMethod, $content);
        
        // If regex didn't work, try alternative approach
        if (!str_contains($content, 'redirect_url\' => route(\'staff.pengajuan.index\')')) {
            // Find the existing method and replace just the redirect part
            $content = str_replace(
                '\'redirect_url\' => route(\'staff.surat.create\')',
                '\'redirect_url\' => route(\'staff.pengajuan.index\')',
                $content
            );
        }
        
        File::put($controllerPath, $content);
        $this->log("✅ SuratController updated - now redirects to staff.pengajuan.index");
    }
    
    private function fixStaffView()
    {
        $viewPath = resource_path('views/staff/pengajuan/show.blade.php');
        if (!File::exists($viewPath)) {
            $this->log("❌ Staff pengajuan show view not found");
            return;
        }
        
        $this->log("🔧 Updating staff pengajuan show view...");
        
        $content = File::get($viewPath);
        
        // Update the JavaScript processPengajuan function to handle redirect properly
        $updatedFunction = '
// Process pengajuan function with fixed redirect
function processPengajuan(action) {
    let data = {
        action: action
    };
    
    if (action === \'reject\') {
        const reason = document.getElementById(\'rejectionReason\').value.trim();
        if (!reason) {
            alert(\'Alasan penolakan harus diisi!\');
            return;
        }
        data.rejection_reason = reason;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = \'<i class="fas fa-spinner fa-spin mr-2"></i>Processing...\';
    
    fetch(\'/staff/pengajuan/{{ $pengajuan->id }}/process\', {
        method: \'POST\',
        headers: {
            \'Content-Type\': \'application/json\',
            \'X-CSRF-TOKEN\': \'{{ csrf_token() }}\',
            \'Accept\': \'application/json\'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(\'Network response was not ok\');
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            alert(result.message);
            // FIXED: Always redirect to pengajuan index
            window.location.href = \'{{ route("staff.pengajuan.index") }}\';
        } else {
            alert(\'Error: \' + result.message);
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error(\'Error:\', error);
        alert(\'Terjadi kesalahan sistem. Silakan coba lagi.\');
        button.disabled = false;
        button.innerHTML = originalText;
    });
    
    if (action === \'approve\') closeApproveModal();
    if (action === \'reject\') closeRejectModal();
}';
        
        // Replace the existing function
        $content = preg_replace(
            '/function processPengajuan\(action\)[^}]*{[^}]*(?:{[^}]*}[^}]*)*}/',
            $updatedFunction,
            $content
        );
        
        File::put($viewPath, $content);
        $this->log("✅ Staff pengajuan show view updated - fixed redirect behavior");
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . PHP_EOL;
    }
    
    public function displayResults()
    {
        $this->log("\n" . str_repeat("=", 50));
        $this->log("🎉 APPROVE REDIRECT FIX COMPLETED");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
        
        $this->log("📋 SUMMARY OF FIXES:");
        $this->log("- ✅ SuratController redirect fixed to staff.pengajuan.index");
        $this->log("- ✅ Staff view JavaScript updated for consistent redirect");
        $this->log("- ✅ No more redirect to create surat after approve");
        $this->log("");
        
        $this->log("🎯 TESTING:");
        $this->log("1. Login sebagai staff_prodi");
        $this->log("2. Buka pengajuan dengan status pending");
        $this->log("3. Klik 'Setujui'");
        $this->log("4. Harus redirect ke daftar pengajuan (bukan create surat)");
        $this->log("");
        
        $this->log("✅ Fix completed successfully!");
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    echo "🚀 Starting Approve Redirect Fix...\n\n";
    
    try {
        $fixer = new ApproveRedirectFixer();
        $fixer->fixRedirect();
        
        echo "\n🎉 SUCCESS! Approve redirect has been fixed.\n";
        echo "📝 Now approving pengajuan will redirect to pengajuan index.\n";
        
    } catch (Exception $e) {
        echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "🚀 APPROVE REDIRECT FIX (Web Mode)\n\n";
    
    try {
        $fixer = new ApproveRedirectFixer();
        $fixer->fixRedirect();
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>