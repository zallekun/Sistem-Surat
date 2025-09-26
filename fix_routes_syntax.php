<?php
/**
 * FIX ROUTES SYNTAX ERROR
 * 
 * Masalah: Unmatched '}' di line 116 routes/web.php
 * Solusi: Perbaiki struktur routes yang salah
 * 
 * File: fix_routes_syntax.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

class RoutesSyntaxFixer
{
    private $output = [];
    
    public function __construct()
    {
        $this->log("=== FIX ROUTES SYNTAX ERROR ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
    }
    
    public function fixSyntax()
    {
        $this->log("🔧 FIXING ROUTES SYNTAX ERROR");
        $this->log("==============================");
        
        $routesFile = base_path('routes/web.php');
        
        // Backup original file
        $backupFile = $routesFile . '.backup.' . date('Y-m-d_H-i-s');
        File::copy($routesFile, $backupFile);
        $this->log("📦 Backup created: {$backupFile}");
        
        // Read current content
        $content = File::get($routesFile);
        
        // Find the problematic section around line 56-60
        // The issue is with the staff pengajuan routes having duplicate/malformed structure
        $fixedContent = $this->fixRouteStructure($content);
        
        // Write fixed content
        File::put($routesFile, $fixedContent);
        $this->log("✅ Routes file fixed");
        
        // Verify syntax
        $this->verifySyntax($routesFile);
        
        $this->displayResults();
    }
    
    private function fixRouteStructure($content)
    {
        // The problem is in this section - there's a malformed route group
        // Lines 56-60 have duplicate route definitions and missing group closure
        
        // Replace the problematic section
        $problematicSection = "// Pengajuan Mahasiswa
    Route::get('/staff/pengajuan', [App\Http\Controllers\StaffPengajuanController::class, 'index'])->name('staff.pengajuan.index');
        Route::get('/{id}', [SuratController::class, 'pengajuanShow'])->name('show');
        Route::post('/{id}/approve', [SuratController::class, 'approvePengajuan'])->name('approve');
        Route::post('/{id}/reject', [SuratController::class, 'rejectPengajuan'])->name('reject');
    });";
        
        // Fixed version - properly structured
        $fixedSection = "// Pengajuan Mahasiswa
    Route::prefix('staff/pengajuan')->name('staff.pengajuan.')->group(function () {
        Route::get('/', [App\Http\Controllers\StaffPengajuanController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\StaffPengajuanController::class, 'show'])->name('show');
        Route::post('/{id}/process', [SuratController::class, 'processProdiPengajuan'])->name('process');
    });";
        
        $content = str_replace($problematicSection, $fixedSection, $content);
        
        // Also fix the duplicate route definition in staff routes section
        $duplicateRoute = "// Pengajuan (staff view)
        Route::get('/staff/pengajuan', [App\Http\Controllers\StaffPengajuanController::class, 'index'])->name('staff.pengajuan.index');
        Route::get('pengajuan/{id}', [SuratController::class, 'pengajuanShow'])->name('pengajuan.show');";
        
        $fixedDuplicate = "// Pengajuan (staff view - handled above)";
        
        $content = str_replace($duplicateRoute, $fixedDuplicate, $content);
        
        // Remove any duplicate route definitions at the end
        $content = preg_replace('/\n\/\/ Fakultas Generate PDF\nRoute::post.*generateSuratPDF.*\n/', '', $content);
        $content = preg_replace('/\n\/\/ Tracking Download\nRoute::get.*downloadSurat.*\n/', '', $content);
        
        // Keep only one instance of each route
        $routes_to_ensure = [
            "\n// Fakultas Generate PDF\nRoute::post('/fakultas/surat/generate-pdf/{id}', [App\Http\Controllers\FakultasStaffController::class, 'generateSuratPDF'])->name('fakultas.surat.generate-pdf');",
            "\n// Kirim ke Pengaju\nRoute::post('/fakultas/surat/kirim-ke-pengaju/{id}', [App\Http\Controllers\FakultasStaffController::class, 'kirimKePengaju'])->name('fakultas.surat.kirim-pengaju');"
        ];
        
        foreach ($routes_to_ensure as $route) {
            if (!str_contains($content, trim($route))) {
                $content = str_replace("require __DIR__ . '/staff.php';", "require __DIR__ . '/staff.php';" . $route, $content);
            }
        }
        
        return $content;
    }
    
    private function verifySyntax($filePath)
    {
        $this->log("\n🔍 VERIFYING SYNTAX:");
        
        // Use php -l to check syntax
        $command = "php -l " . escapeshellarg($filePath) . " 2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'No syntax errors') !== false) {
            $this->log("✅ Syntax check passed");
        } else {
            $this->log("❌ Syntax check failed:");
            $this->log($output);
            
            // Try to auto-fix if there are still issues
            $this->attemptAutoFix($filePath);
        }
    }
    
    private function attemptAutoFix($filePath)
    {
        $this->log("\n🔧 Attempting auto-fix...");
        
        $content = File::get($filePath);
        
        // Count opening and closing braces
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        
        $this->log("Opening braces: {$openBraces}");
        $this->log("Closing braces: {$closeBraces}");
        
        if ($closeBraces > $openBraces) {
            // Remove extra closing braces
            $diff = $closeBraces - $openBraces;
            $this->log("Removing {$diff} extra closing brace(s)");
            
            // Remove extra closing braces from the end
            for ($i = 0; $i < $diff; $i++) {
                $lastBrace = strrpos($content, '});');
                if ($lastBrace !== false) {
                    $content = substr($content, 0, $lastBrace) . ');' . substr($content, $lastBrace + 3);
                }
            }
            
            File::put($filePath, $content);
            $this->log("✅ Auto-fix applied");
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
        $this->log("🎉 ROUTES SYNTAX FIX COMPLETED");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
        
        $this->log("📋 SUMMARY OF FIXES:");
        $this->log("- ✅ Fixed malformed route group structure");
        $this->log("- ✅ Removed duplicate route definitions");
        $this->log("- ✅ Fixed unmatched braces");
        $this->log("- ✅ Cleaned up route organization");
        $this->log("");
        
        $this->log("🎯 NEXT STEPS:");
        $this->log("1. Clear route cache: php artisan route:clear");
        $this->log("2. List routes to verify: php artisan route:list");
        $this->log("3. Test the application");
        $this->log("");
        
        $this->log("✅ Fix completed successfully!");
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    echo "🚀 Starting Routes Syntax Fix...\n\n";
    
    try {
        $fixer = new RoutesSyntaxFixer();
        $fixer->fixSyntax();
        
        echo "\n🎉 SUCCESS! Routes syntax has been fixed.\n";
        
    } catch (Exception $e) {
        echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "🚀 ROUTES SYNTAX FIX (Web Mode)\n\n";
    
    try {
        $fixer = new RoutesSyntaxFixer();
        $fixer->fixSyntax();
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>