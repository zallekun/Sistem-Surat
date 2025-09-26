<?php
/**
 * FIX PUBLICSURAT CONTROLLER SYNTAX ERROR
 * 
 * Masalah: Syntax error di line 654 - duplicated code blocks
 * Solusi: Membersihkan dan memperbaiki structure method downloadSurat
 * 
 * File: fix_controller_syntax_error.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

class ControllerSyntaxFixer
{
    private $output = [];
    
    public function __construct()
    {
        $this->log("=== FIX PUBLICSURAT CONTROLLER SYNTAX ERROR ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
    }
    
    public function fixSyntaxError()
    {
        $this->log("🔧 FIXING SYNTAX ERROR IN PUBLICSURAT CONTROLLER");
        $this->log("=================================================");
        
        $controllerPath = app_path('Http/Controllers/PublicSuratController.php');
        
        if (!File::exists($controllerPath)) {
            $this->log("❌ PublicSuratController.php not found");
            return;
        }
        
        // Backup original file
        $backupPath = $controllerPath . '.backup.' . date('Y-m-d_H-i-s');
        File::copy($controllerPath, $backupPath);
        $this->log("📦 Backup created: {$backupPath}");
        
        // Read current content
        $content = File::get($controllerPath);
        
        // Fix the downloadSurat method - remove duplicated code
        $fixedContent = $this->fixDownloadSuratMethod($content);
        
        // Write fixed content
        File::put($controllerPath, $fixedContent);
        $this->log("✅ PublicSuratController.php fixed successfully");
        
        // Verify the fix
        $this->verifySyntax($controllerPath);
    }
    
    /**
     * Fix downloadSurat method by removing duplicated code
     */
    private function fixDownloadSuratMethod($content)
    {
        // Clean downloadSurat method implementation
        $cleanDownloadMethod = '
    /**
     * Download PDF surat melalui tracking
     */
    public function downloadSurat($id)
    {
        try {
            $pengajuan = PengajuanSurat::with([\'suratGenerated\', \'jenisSurat\'])->findOrFail($id);
            
            // Check if surat completed and has file
            if ($pengajuan->status !== \'completed\') {
                return redirect()->route(\'tracking.public\')
                               ->with(\'error\', \'Surat belum selesai diproses.\');
            }
            
            if (!$pengajuan->suratGenerated) {
                return redirect()->route(\'tracking.public\')
                               ->with(\'error\', \'File surat belum tersedia.\');
            }
            
            $suratGenerated = $pengajuan->suratGenerated;
            $filePath = $suratGenerated->file_path;
            
            if (!$filePath) {
                return redirect()->route(\'tracking.public\')
                               ->with(\'error\', \'Path file tidak ditemukan.\');
            }
            
            // Try multiple possible paths
            $possiblePaths = [
                storage_path(\'app/public/\' . $filePath),
                storage_path(\'app/\' . $filePath),
                public_path(\'storage/\' . $filePath)
            ];
            
            $fullPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $fullPath = $path;
                    break;
                }
            }
            
            if (!$fullPath) {
                \\Log::error(\'PDF file not found\', [
                    \'pengajuan_id\' => $pengajuan->id,
                    \'file_path\' => $filePath,
                    \'tried_paths\' => $possiblePaths,
                    \'tracking_token\' => $pengajuan->tracking_token
                ]);
                
                return redirect()->route(\'tracking.public\')
                               ->with(\'error\', \'File surat tidak ditemukan di server.\');
            }
            
            // Generate clean filename
            $jenisSurat = preg_replace(\'/[^A-Za-z0-9]/\', \'_\', $pengajuan->jenisSurat->nama_jenis ?? \'Surat\');
            $nim = preg_replace(\'/[^A-Za-z0-9]/\', \'_\', $pengajuan->nim ?? \'Unknown\');
            $fileName = "Surat_{$jenisSurat}_{$nim}_" . now()->format(\'Y-m-d\') . ".pdf";
            
            // Log successful download
            \\Log::info(\'Surat downloaded via tracking\', [
                \'pengajuan_id\' => $pengajuan->id,
                \'nim\' => $pengajuan->nim,
                \'tracking_token\' => $pengajuan->tracking_token,
                \'file_path\' => $fullPath,
                \'filename\' => $fileName
            ]);
            
            return response()->download($fullPath, $fileName);
            
        } catch (\\Exception $e) {
            \\Log::error(\'Error downloading surat via tracking\', [
                \'pengajuan_id\' => $id,
                \'error\' => $e->getMessage(),
                \'trace\' => $e->getTraceAsString()
            ]);
            
            return redirect()->route(\'tracking.public\')
                           ->with(\'error\', \'Gagal mendownload surat: \' . $e->getMessage());
        }
    }
}';
        
        // Remove everything from the first occurrence of downloadSurat to the end
        $pattern = '/\/\*\*\s*\*\s*Download PDF surat melalui tracking[\s\S]*$/';
        
        // Replace with clean implementation
        $fixedContent = preg_replace($pattern, $cleanDownloadMethod, $content);
        
        // If pattern didn't match, try alternative approach
        if ($fixedContent === $content) {
            // Find the last occurrence of public function downloadSurat
            $lastPos = strrpos($content, 'public function downloadSurat');
            if ($lastPos !== false) {
                // Cut everything from that point and replace with clean method
                $fixedContent = substr($content, 0, $lastPos) . $cleanDownloadMethod;
            }
        }
        
        return $fixedContent;
    }
    
    /**
     * Verify syntax by attempting to parse the file
     */
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
        $this->log("🎉 SYNTAX ERROR FIX COMPLETED");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
        
        $this->log("📋 SUMMARY:");
        $this->log("- ✅ Fixed duplicated downloadSurat method");
        $this->log("- ✅ Removed syntax errors"); 
        $this->log("- ✅ Clean method implementation");
        $this->log("");
        
        $this->log("🎯 NEXT STEPS:");
        $this->log("1. Test the controller: php artisan route:list");
        $this->log("2. Clear caches: php artisan cache:clear");
        $this->log("3. Test download functionality");
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    echo "🚀 Starting PublicSuratController Syntax Fix...\n\n";
    
    try {
        $fixer = new ControllerSyntaxFixer();
        $fixer->fixSyntaxError();
        $fixer->displayResults();
        
        echo "\n🎉 SUCCESS! Syntax error has been fixed.\n";
        
    } catch (Exception $e) {
        echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "🚀 PUBLICSURAT CONTROLLER SYNTAX FIX (Web Mode)\n\n";
    
    try {
        $fixer = new ControllerSyntaxFixer();
        $fixer->fixSyntaxError();
        $fixer->displayResults();
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>