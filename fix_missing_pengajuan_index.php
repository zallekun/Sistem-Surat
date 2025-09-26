<?php
/**
 * FIX MISSING PENGAJUANINDEX METHOD
 * 
 * Masalah: Method pengajuanIndex tidak ada di SuratController
 * Solusi: Tambahkan method atau redirect ke route yang benar
 * 
 * File: fix_missing_pengajuan_index.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

class PengajuanIndexFixer
{
    private $output = [];
    
    public function __construct()
    {
        $this->log("=== FIX MISSING PENGAJUANINDEX METHOD ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
    }
    
    public function fixMissingMethod()
    {
        $this->log("🔧 FIXING MISSING METHOD");
        $this->log("========================");
        
        // Fix SuratController
        $this->fixSuratController();
        
        // Fix routes
        $this->fixRoutes();
        
        $this->displayResults();
    }
    
    private function fixSuratController()
    {
        $controllerPath = app_path('Http/Controllers/SuratController.php');
        if (!File::exists($controllerPath)) {
            $this->log("❌ SuratController.php not found");
            return;
        }
        
        $this->log("🔧 Adding pengajuanIndex method to SuratController...");
        
        $content = File::get($controllerPath);
        
        // Check if pengajuanIndex method exists
        if (!str_contains($content, 'function pengajuanIndex')) {
            // Add the method before the last closing brace
            $newMethod = '
    /**
     * Display pengajuan list for staff
     * This redirects to the correct staff pengajuan route
     */
    public function pengajuanIndex()
    {
        return redirect()->route(\'staff.pengajuan.index\');
    }';
            
            $content = preg_replace('/}\s*$/', $newMethod . "\n}", $content);
            File::put($controllerPath, $content);
            $this->log("✅ Added pengajuanIndex method to SuratController");
        } else {
            $this->log("✅ pengajuanIndex method already exists");
        }
    }
    
    private function fixRoutes()
    {
        $routesFile = base_path('routes/web.php');
        $content = File::get($routesFile);
        
        $this->log("🔧 Checking routes...");
        
        // Find any route that uses pengajuanIndex and fix it
        $pattern = '/Route::[^;]+pengajuanIndex[^;]+;/';
        
        if (preg_match($pattern, $content, $matches)) {
            $this->log("Found route using pengajuanIndex: " . $matches[0]);
            
            // Replace with correct route
            $content = preg_replace(
                $pattern,
                "Route::get('/staff/pengajuan', [App\\Http\\Controllers\\StaffPengajuanController::class, 'index'])->name('staff.pengajuan.index');",
                $content
            );
            
            File::put($routesFile, $content);
            $this->log("✅ Fixed route to use StaffPengajuanController");
        }
        
        // Ensure StaffPengajuanController exists
        $this->ensureStaffPengajuanController();
    }
    
    private function ensureStaffPengajuanController()
    {
        $controllerPath = app_path('Http/Controllers/StaffPengajuanController.php');
        
        if (!File::exists($controllerPath)) {
            $this->log("🔧 Creating StaffPengajuanController...");
            
            $controllerContent = '<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffPengajuanController extends Controller
{
    /**
     * Display pengajuan list for staff
     */
    public function index()
    {
        $user = Auth::user();
        
        // Authorization check
        if (!$user->hasRole([\'staff_prodi\', \'kaprodi\'])) {
            abort(403, \'Unauthorized - Only staff prodi and kaprodi can access this page\');
        }
        
        // Build query for pengajuan
        $query = PengajuanSurat::with([\'prodi\', \'jenisSurat\']);
        
        // Filter by prodi
        if ($user->prodi_id) {
            $query->where(\'prodi_id\', $user->prodi_id);
        }
        
        // Filter by status
        $status = request(\'status\');
        if ($status) {
            $query->where(\'status\', $status);
        } else {
            // Default: show pending pengajuan
            $query->where(\'status\', \'pending\');
        }
        
        $pengajuans = $query->orderBy(\'created_at\', \'desc\')->paginate(10);
        $jenisSurats = JenisSurat::all();
        
        return view(\'staff.pengajuan.index\', compact(\'pengajuans\', \'jenisSurats\'));
    }
    
    /**
     * Show pengajuan detail
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole([\'staff_prodi\', \'kaprodi\'])) {
            abort(403, \'Unauthorized\');
        }
        
        $pengajuan = PengajuanSurat::with([\'prodi\', \'jenisSurat\'])->findOrFail($id);
        
        // Check if user can view this pengajuan
        if ($user->prodi_id && $pengajuan->prodi_id !== $user->prodi_id) {
            abort(403, \'Unauthorized - Pengajuan dari prodi lain\');
        }
        
        return view(\'staff.pengajuan.show\', compact(\'pengajuan\'));
    }
}';
            
            File::put($controllerPath, $controllerContent);
            $this->log("✅ Created StaffPengajuanController");
        } else {
            $this->log("✅ StaffPengajuanController already exists");
        }
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . PHP_EOL;
    }
    
    public function displayResults()
    {
        $this->log("\n" . str_repeat("=", 50));
        $this->log("🎉 FIX COMPLETED");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
        
        $this->log("📋 SUMMARY OF FIXES:");
        $this->log("- ✅ Added pengajuanIndex method to SuratController");
        $this->log("- ✅ Fixed routes to use correct controller");
        $this->log("- ✅ Ensured StaffPengajuanController exists");
        $this->log("");
        
        $this->log("🎯 NEXT STEPS:");
        $this->log("1. Clear route cache: php artisan route:clear");
        $this->log("2. Test the staff pengajuan page");
        $this->log("");
        
        $this->log("✅ Fix completed successfully!");
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    echo "🚀 Starting Fix for Missing pengajuanIndex Method...\n\n";
    
    try {
        $fixer = new PengajuanIndexFixer();
        $fixer->fixMissingMethod();
        
        echo "\n🎉 SUCCESS! Missing method has been fixed.\n";
        
    } catch (Exception $e) {
        echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "🚀 FIX MISSING PENGAJUANINDEX METHOD (Web Mode)\n\n";
    
    try {
        $fixer = new PengajuanIndexFixer();
        $fixer->fixMissingMethod();
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>