<?php
/**
 * Debug Script untuk Masalah Variable Spesifik - Analisis Mendalam
 * File: debug_specific_variable_issue.php
 * 
 * Focus pada masalah spesifik dari hasil debug sebelumnya
 */

// Load Laravel environment
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class SpecificVariableDebugger
{
    private $output = [];
    private $problematicFiles = [];
    
    public function __construct()
    {
        $this->log("=== ANALISIS MASALAH VARIABLE SPESIFIK ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
        
        // Define problematic files from debug results
        $this->problematicFiles = [
            'staff/pengajuan/index.blade.php' => [
                'controller' => 'SuratController@pengajuanIndex',
                'sends_pengajuans' => true,
                'uses_pengajuan_singular' => true,
                'usage_count' => 34
            ],
            'fakultas/surat/show.blade.php' => [
                'controller' => 'FakultasStaffController@show',
                'sends_pengajuan' => true,
                'has_safety_checks' => true,
                'usage_count' => 25
            ]
        ];
    }
    
    /**
     * Run specific analysis untuk masalah yang sudah teridentifikasi
     */
    public function runSpecificAnalysis()
    {
        $this->analyzeStaffPengajuanIndex();
        $this->analyzeFakultasSuratShow();
        $this->analyzeControllerVariableConsistency();
        $this->generateSpecificFixes();
        $this->displayResults();
    }
    
    /**
     * Analisis mendalam staff/pengajuan/index.blade.php
     */
    private function analyzeStaffPengajuanIndex()
    {
        $this->log("🔍 ANALISIS STAFF/PENGAJUAN/INDEX.BLADE.PHP");
        $this->log("===========================================");
        
        $filePath = resource_path('views/staff/pengajuan/index.blade.php');
        
        if (!File::exists($filePath)) {
            $this->log("❌ File tidak ditemukan: {$filePath}");
            return;
        }
        
        $content = File::get($filePath);
        
        // Analisis baris pertama untuk safety check
        $lines = explode("\n", $content);
        $firstLines = array_slice($lines, 0, 10);
        
        $this->log("📋 BARIS PERTAMA FILE (10 baris):");
        foreach ($firstLines as $i => $line) {
            $lineNum = $i + 1;
            $this->log("   {$lineNum}: " . trim($line));
        }
        
        // Check apakah ada safety check
        $hasSafetyCheck = false;
        $safetyCheckLines = [];
        
        foreach ($lines as $i => $line) {
            if (str_contains($line, 'isset($pengajuans)') || str_contains($line, 'isset($pengajuan)')) {
                $hasSafetyCheck = true;
                $safetyCheckLines[] = ($i + 1) . ": " . trim($line);
            }
        }
        
        if ($hasSafetyCheck) {
            $this->log("✅ Safety checks ditemukan:");
            foreach ($safetyCheckLines as $checkLine) {
                $this->log("   {$checkLine}");
            }
        } else {
            $this->log("❌ TIDAK ADA safety checks untuk variables");
        }
        
        // Analisis penggunaan $pengajuan vs $pengajuans
        $pengajuanUsage = [];
        $pengajuansUsage = [];
        
        foreach ($lines as $i => $line) {
            // Find $pengajuan usage (not $pengajuans)
            if (preg_match('/\$pengajuan(?!s)/', $line)) {
                $pengajuanUsage[] = ($i + 1) . ": " . trim($line);
            }
            
            // Find $pengajuans usage
            if (preg_match('/\$pengajuans/', $line)) {
                $pengajuansUsage[] = ($i + 1) . ": " . trim($line);
            }
        }
        
        $this->log("\n📊 PENGGUNAAN VARIABLES:");
        $this->log("   \$pengajuan (singular): " . count($pengajuanUsage) . " usage");
        $this->log("   \$pengajuans (plural): " . count($pengajuansUsage) . " usage");
        
        if (count($pengajuanUsage) > 0) {
            $this->log("\n🚨 PROBLEMATIC \$pengajuan USAGE (5 pertama):");
            foreach (array_slice($pengajuanUsage, 0, 5) as $usage) {
                $this->log("   {$usage}");
            }
        }
        
        if (count($pengajuansUsage) > 0) {
            $this->log("\n✅ CORRECT \$pengajuans USAGE (5 pertama):");
            foreach (array_slice($pengajuansUsage, 0, 5) as $usage) {
                $this->log("   {$usage}");
            }
        }
        
        // Check for loops
        $this->analyzeLoopStructure($content, $lines);
        
        $this->log("");
    }
    
    /**
     * Analisis mendalam fakultas/surat/show.blade.php
     */
    private function analyzeFakultasSuratShow()
    {
        $this->log("🔍 ANALISIS FAKULTAS/SURAT/SHOW.BLADE.PHP");
        $this->log("=========================================");
        
        $filePath = resource_path('views/fakultas/surat/show.blade.php');
        
        if (!File::exists($filePath)) {
            $this->log("❌ File tidak ditemukan: {$filePath}");
            return;
        }
        
        $content = File::get($filePath);
        $lines = explode("\n", $content);
        
        // Check @php blocks
        $phpBlocks = [];
        $inPhpBlock = false;
        $currentBlock = [];
        $blockStart = 0;
        
        foreach ($lines as $i => $line) {
            if (str_contains($line, '@php')) {
                $inPhpBlock = true;
                $blockStart = $i + 1;
                $currentBlock = [trim($line)];
            } elseif (str_contains($line, '@endphp')) {
                $inPhpBlock = false;
                $currentBlock[] = trim($line);
                $phpBlocks[] = [
                    'start' => $blockStart,
                    'end' => $i + 1,
                    'content' => $currentBlock
                ];
                $currentBlock = [];
            } elseif ($inPhpBlock) {
                $currentBlock[] = trim($line);
            }
        }
        
        $this->log("📋 PHP BLOCKS DITEMUKAN: " . count($phpBlocks));
        foreach ($phpBlocks as $i => $block) {
            $this->log("   Block " . ($i + 1) . " (lines {$block['start']}-{$block['end']}):");
            foreach ($block['content'] as $blockLine) {
                $this->log("      {$blockLine}");
            }
            $this->log("");
        }
        
        // Check variable definitions in @php blocks
        $variableDefinitions = [];
        foreach ($phpBlocks as $block) {
            foreach ($block['content'] as $line) {
                if (str_contains($line, '$pengajuan') || str_contains($line, '$surat') || str_contains($line, '$status')) {
                    $variableDefinitions[] = trim($line);
                }
            }
        }
        
        if (!empty($variableDefinitions)) {
            $this->log("📝 VARIABLE DEFINITIONS dalam @php blocks:");
            foreach ($variableDefinitions as $def) {
                $this->log("   {$def}");
            }
        } else {
            $this->log("❌ TIDAK ADA variable definitions dalam @php blocks");
        }
        
        // Check conditional usage
        $conditionalUsage = [];
        foreach ($lines as $i => $line) {
            if (preg_match('/@if\s*\(\s*[^)]*\$pengajuan/', $line) || 
                preg_match('/@isset\s*\(\s*\$pengajuan/', $line)) {
                $conditionalUsage[] = ($i + 1) . ": " . trim($line);
            }
        }
        
        if (!empty($conditionalUsage)) {
            $this->log("\n✅ CONDITIONAL CHECKS untuk \$pengajuan:");
            foreach ($conditionalUsage as $usage) {
                $this->log("   {$usage}");
            }
        } else {
            $this->log("\n⚠️  TIDAK ADA conditional checks yang memadai");
        }
        
        $this->log("");
    }
    
    /**
     * Analisis loop structure dalam file
     */
    private function analyzeLoopStructure($content, $lines)
    {
        $this->log("\n🔄 ANALISIS LOOP STRUCTURE:");
        
        $foreachLoops = [];
        $inLoop = false;
        $loopLevel = 0;
        
        foreach ($lines as $i => $line) {
            if (preg_match('/@foreach\s*\(\s*\$(\w+)\s+as\s+\$(\w+)/', $line, $matches)) {
                $foreachLoops[] = [
                    'line' => $i + 1,
                    'content' => trim($line),
                    'collection' => $matches[1],
                    'item' => $matches[2]
                ];
            }
        }
        
        if (!empty($foreachLoops)) {
            $this->log("   Foreach loops ditemukan:");
            foreach ($foreachLoops as $loop) {
                $this->log("      Line {$loop['line']}: {$loop['content']}");
                $this->log("         Collection: \${$loop['collection']}, Item: \${$loop['item']}");
            }
            
            // Check if there's mismatch
            $hasProblematicLoop = false;
            foreach ($foreachLoops as $loop) {
                if ($loop['collection'] === 'pengajuans' && $loop['item'] === 'pengajuan') {
                    $this->log("      ✅ Correct loop: \$pengajuans as \$pengajuan");
                } elseif ($loop['collection'] === 'pengajuan') {
                    $this->log("      ❌ PROBLEMATIC: Trying to loop over \$pengajuan (should be \$pengajuans)");
                    $hasProblematicLoop = true;
                }
            }
            
            if ($hasProblematicLoop) {
                $this->log("      🚨 FOUND LOOP MISMATCH ISSUE!");
            }
        } else {
            $this->log("   Tidak ada foreach loops ditemukan");
        }
    }
    
    /**
     * Analisis konsistensi variable di controllers
     */
    private function analyzeControllerVariableConsistency()
    {
        $this->log("🎮 ANALISIS KONSISTENSI CONTROLLER VARIABLES");
        $this->log("===========================================");
        
        // Analyze SuratController@pengajuanIndex
        $this->analyzeControllerMethod(
            'SuratController', 
            'pengajuanIndex', 
            'staff.pengajuan.index',
            app_path('Http/Controllers/SuratController.php')
        );
        
        // Analyze FakultasStaffController@show
        $this->analyzeControllerMethod(
            'FakultasStaffController', 
            'show', 
            'fakultas.surat.show',
            app_path('Http/Controllers/FakultasStaffController.php')
        );
        
        $this->log("");
    }
    
    /**
     * Analisis method controller spesifik
     */
    private function analyzeControllerMethod($controllerName, $methodName, $viewName, $filePath)
    {
        $this->log("📍 {$controllerName}@{$methodName}:");
        
        if (!File::exists($filePath)) {
            $this->log("   ❌ File controller tidak ditemukan: {$filePath}");
            return;
        }
        
        $content = File::get($filePath);
        
        // Extract method content
        $methodPattern = "/public\s+function\s+{$methodName}\s*\([^{]*\{(.*?)\n\s*}/s";
        if (preg_match($methodPattern, $content, $matches)) {
            $methodContent = $matches[1];
            
            // Look for return view statements
            if (preg_match_all('/return\s+view\s*\(\s*[\'"]([^\'"]+)[\'"][^;]*compact\s*\(\s*([^)]+)\)/', $methodContent, $viewMatches, PREG_SET_ORDER)) {
                foreach ($viewMatches as $match) {
                    $returnedView = $match[1];
                    $compactVars = $match[2];
                    
                    $this->log("   📤 Returns view: {$returnedView}");
                    $this->log("   📦 Compact variables: {$compactVars}");
                    
                    // Parse compact variables
                    $variables = [];
                    if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $compactVars, $varMatches)) {
                        $variables = $varMatches[1];
                    }
                    
                    $hasPengajuan = in_array('pengajuan', $variables);
                    $hasPengajuans = in_array('pengajuans', $variables);
                    
                    $this->log("   📊 Variable analysis:");
                    $this->log("      - \$pengajuan: " . ($hasPengajuan ? "✅ SENT" : "❌ NOT SENT"));
                    $this->log("      - \$pengajuans: " . ($hasPengajuans ? "✅ SENT" : "❌ NOT SENT"));
                    
                    // Check expected vs actual
                    if ($returnedView === 'staff.pengajuan.index' || str_contains($returnedView, 'pengajuan.index')) {
                        if (!$hasPengajuans) {
                            $this->log("      🚨 MISMATCH: View expects \$pengajuans but controller doesn't send it!");
                        }
                        if ($hasPengajuan) {
                            $this->log("      ⚠️  WARNING: Sending \$pengajuan to index view (should be \$pengajuans)");
                        }
                    }
                    
                    if ($returnedView === 'fakultas.surat.show' && !$hasPengajuan) {
                        $this->log("      🚨 POTENTIAL ISSUE: View might expect \$pengajuan");
                    }
                }
            } else {
                $this->log("   ❌ Tidak dapat menemukan return view statement");
            }
        } else {
            $this->log("   ❌ Tidak dapat menemukan method {$methodName}");
        }
        
        $this->log("");
    }
    
    /**
     * Generate fix yang spesifik untuk setiap masalah
     */
    private function generateSpecificFixes()
    {
        $this->log("💊 SOLUSI SPESIFIK UNTUK SETIAP MASALAH");
        $this->log("=====================================");
        
        // Fix 1: staff/pengajuan/index.blade.php
        $this->log("1. 🔧 FIX UNTUK STAFF/PENGAJUAN/INDEX.BLADE.PHP:");
        $this->log("   MASALAH: View menggunakan \$pengajuan tapi controller mengirim \$pengajuans");
        $this->log("");
        $this->log("   SOLUSI A - Update View (RECOMMENDED):");
        $this->log("   Tambahkan di baris pertama file:");
        $this->log("   @php");
        $this->log("       // Fix undefined variable pengajuan");
        $this->log("       if (!isset(\$pengajuans)) {");
        $this->log("           \$pengajuans = collect([]);");
        $this->log("       }");
        $this->log("   @endphp");
        $this->log("");
        $this->log("   ATAU ubah semua \$pengajuan menjadi \$item dalam foreach:");
        $this->log("   @foreach(\$pengajuans as \$item)");
        $this->log("       {{ \$item->status }} // bukan \$pengajuan->status");
        $this->log("   @endforeach");
        $this->log("");
        
        // Fix 2: fakultas/surat/show.blade.php
        $this->log("2. 🔧 FIX UNTUK FAKULTAS/SURAT/SHOW.BLADE.PHP:");
        $this->log("   MASALAH: Error terjadi dalam kondisi tertentu saat \$pengajuan tidak dikirim");
        $this->log("");
        $this->log("   SOLUSI - Strengthen Safety Checks:");
        $this->log("   Update @php block di awal file menjadi:");
        $this->log("   @php");
        $this->log("       // Initialize all variables safely");
        $this->log("       \$pengajuan = \$pengajuan ?? null;");
        $this->log("       \$surat = \$surat ?? null;");
        $this->log("       ");
        $this->log("       // Set default values");
        $this->log("       \$jenisSurat = null;");
        $this->log("       \$status = 'unknown';");
        $this->log("       \$additionalData = null;");
        $this->log("       \$statusStyle = 'background-color: #f3f4f6; color: #374151;';");
        $this->log("       ");
        $this->log("       if (\$pengajuan) {");
        $this->log("           \$jenisSurat = \$pengajuan->jenisSurat ?? null;");
        $this->log("           \$status = \$pengajuan->status ?? 'unknown';");
        $this->log("           // ... rest of logic");
        $this->log("       }");
        $this->log("   @endphp");
        $this->log("");
        
        // Fix 3: Controller fixes
        $this->log("3. 🔧 FIX UNTUK CONTROLLERS:");
        $this->log("");
        $this->log("   A. SuratController@pengajuanIndex - ENSURE CONSISTENT NAMING:");
        $this->log("   public function pengajuanIndex() {");
        $this->log("       // ... query logic ...");
        $this->log("       \$pengajuans = \$query->paginate(15);");
        $this->log("       ");
        $this->log("       // ALWAYS send pengajuans (plural) to index views");
        $this->log("       return view('staff.pengajuan.index', compact('pengajuans'));");
        $this->log("   }");
        $this->log("");
        $this->log("   B. FakultasStaffController@show - ENSURE VARIABLES ALWAYS SET:");
        $this->log("   public function show(\$id) {");
        $this->log("       // Initialize with defaults");
        $this->log("       \$pengajuan = null;");
        $this->log("       \$surat = null;");
        $this->log("       ");
        $this->log("       // ... existing logic ...");
        $this->log("       ");
        $this->log("       // ALWAYS return both variables");
        $this->log("       return view('fakultas.surat.show', compact('surat', 'pengajuan'));");
        $this->log("   }");
        $this->log("");
        
        // Priority fixes
        $this->log("🚨 PRIORITY ORDER:");
        $this->log("   1. Fix staff/pengajuan/index.blade.php (highest impact)");
        $this->log("   2. Strengthen fakultas/surat/show.blade.php safety checks");
        $this->log("   3. Update controller methods for consistency");
        $this->log("");
        
        // Commands to run
        $this->log("⚡ IMMEDIATE ACTIONS:");
        $this->log("   1. Backup current files");
        $this->log("   2. Apply fixes in order");
        $this->log("   3. Test each page after applying fix");
        $this->log("   4. Clear view cache: php artisan view:clear");
        $this->log("");
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . PHP_EOL;
    }
    
    public function displayResults()
    {
        $this->log("\n" . "=".str_repeat("=", 60));
        $this->log("ANALISIS SELESAI - SPECIFIC VARIABLE ISSUE");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("Total lines: " . count($this->output));
        
        // Save detailed log
        $logFile = storage_path('logs/specific_variable_debug_' . date('Y-m-d_H-i-s') . '.log');
        file_put_contents($logFile, implode("\n", $this->output));
        $this->log("Detailed log saved to: {$logFile}");
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    echo "🔍 Starting Specific Variable Issue Analysis...\n\n";
    
    $debugger = new SpecificVariableDebugger();
    $debugger->runSpecificAnalysis();
    
} else {
    header('Content-Type: text/plain');
    echo "🔍 SPECIFIC VARIABLE ISSUE ANALYSIS (Web Mode)\n\n";
    
    $debugger = new SpecificVariableDebugger();
    $debugger->runSpecificAnalysis();
}
?>