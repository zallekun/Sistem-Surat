<?php
/**
 * Debug Script untuk Masalah "Undefined variable $pengajuan"
 * File: debug_undefined_variable.php
 * 
 * Cara penggunaan:
 * 1. Simpan file ini di root Laravel project
 * 2. Jalankan: php debug_undefined_variable.php
 * 3. Atau akses via browser: /debug_undefined_variable.php
 */

// Load Laravel environment
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UndefinedVariableDebugger
{
    private $output = [];
    
    public function __construct()
    {
        $this->log("=== DEBUG UNDEFINED VARIABLE PENGAJUAN ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
    }
    
    /**
     * Run comprehensive debug untuk undefined variable issues
     */
    public function runDebug()
    {
        $this->debugRouteControllerMapping();
        $this->debugViewFiles();
        $this->debugControllerMethods();
        $this->debugVariableUsage();
        $this->suggestFixes();
        $this->displayResults();
    }
    
    /**
     * Debug mapping route ke controller
     */
    private function debugRouteControllerMapping()
    {
        $this->log("🔍 ROUTE-CONTROLLER MAPPING DEBUG");
        $this->log("==================================");
        
        try {
            $routes = collect(Route::getRoutes())->map(function($route) {
                return [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'controller' => $this->extractControllerName($route->getActionName())
                ];
            });
            
            // Focus pada routes yang bermasalah
            $problematicRoutes = $routes->filter(function($route) {
                return str_contains($route['uri'], 'fakultas') || 
                       str_contains($route['uri'], 'staff') ||
                       str_contains($route['name'] ?? '', 'pengajuan') ||
                       str_contains($route['name'] ?? '', 'surat');
            });
            
            $this->log("📍 ROUTES YANG BERPOTENSI BERMASALAH:");
            
            foreach ($problematicRoutes as $route) {
                $this->log("   {$route['method']} /{$route['uri']}");
                $this->log("   Name: {$route['name']}");
                $this->log("   Controller: {$route['controller']}");
                $this->log("   Action: {$route['action']}");
                
                // Check jika ada view yang mungkin dipanggil
                $possibleViews = $this->getPossibleViews($route['name'], $route['uri']);
                if (!empty($possibleViews)) {
                    $this->log("   Possible Views: " . implode(', ', $possibleViews));
                }
                $this->log("");
            }
            
        } catch (Exception $e) {
            $this->log("❌ Route mapping debug failed: " . $e->getMessage());
        }
        
        $this->log("");
    }
    
    /**
     * Debug view files yang menggunakan variable pengajuan
     */
    private function debugViewFiles()
    {
        $this->log("📄 VIEW FILES DEBUG");
        $this->log("===================");
        
        try {
            $viewsPath = resource_path('views');
            $viewFiles = $this->getAllBladeFiles($viewsPath);
            
            $this->log("Total view files found: " . count($viewFiles));
            $this->log("");
            
            $problematicViews = [];
            
            foreach ($viewFiles as $file) {
                $content = File::get($file);
                $relativePath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file);
                $relativePath = str_replace('\\', '/', $relativePath);
                
                // Check untuk penggunaan variable $pengajuan
                if (preg_match_all('/\$pengajuan(?!\w)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $problematicViews[] = [
                        'file' => $relativePath,
                        'full_path' => $file,
                        'usage_count' => count($matches[0]),
                        'line_numbers' => $this->getLineNumbers($content, $matches[0])
                    ];
                }
            }
            
            $this->log("🚨 FILES MENGGUNAKAN \$pengajuan:");
            if (!empty($problematicViews)) {
                foreach ($problematicViews as $view) {
                    $this->log("   ❌ {$view['file']}");
                    $this->log("      Usage: {$view['usage_count']} times");
                    $this->log("      Lines: " . implode(', ', $view['line_numbers']));
                    
                    // Check if file also uses $pengajuans (plural)
                    $content = File::get($view['full_path']);
                    if (preg_match('/\$pengajuans(?!\w)/', $content)) {
                        $this->log("      ✅ Also uses \$pengajuans (plural)");
                    } else {
                        $this->log("      ⚠️  Does NOT use \$pengajuans (plural)");
                    }
                    
                    // Show first few usages for context
                    $this->showVariableContext($view['full_path'], '$pengajuan');
                    $this->log("");
                }
            } else {
                $this->log("   ✅ No problematic views found");
            }
            
        } catch (Exception $e) {
            $this->log("❌ View files debug failed: " . $e->getMessage());
        }
        
        $this->log("");
    }
    
    /**
     * Debug controller methods yang mungkin tidak mengirim variable
     */
    private function debugControllerMethods()
    {
        $this->log("🎮 CONTROLLER METHODS DEBUG");
        $this->log("===========================");
        
        try {
            $controllersPath = app_path('Http/Controllers');
            $controllers = $this->getAllPHPFiles($controllersPath);
            
            foreach ($controllers as $controller) {
                $content = File::get($controller);
                $relativePath = str_replace($controllersPath . DIRECTORY_SEPARATOR, '', $controller);
                $relativePath = str_replace('\\', '/', $relativePath);
                
                // Check untuk return view statements
                if (preg_match_all('/return\s+view\s*\(\s*[\'"]([^\'"]+)[\'"].*?\)/', $content, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $viewName = $match[1];
                        
                        // Check if view name contains problematic patterns
                        if (str_contains($viewName, 'fakultas') || str_contains($viewName, 'staff')) {
                            $this->log("   📍 {$relativePath}");
                            $this->log("      Returns view: {$viewName}");
                            
                            // Check compact/with variables
                            $compactVars = $this->extractCompactVariables($match[0], $content);
                            if (!empty($compactVars)) {
                                $this->log("      Variables sent: " . implode(', ', $compactVars));
                                
                                // Check if $pengajuan is sent
                                if (in_array('pengajuan', $compactVars)) {
                                    $this->log("      ✅ Sends \$pengajuan");
                                } else {
                                    $this->log("      ❌ Does NOT send \$pengajuan");
                                }
                                
                                if (in_array('pengajuans', $compactVars)) {
                                    $this->log("      ✅ Sends \$pengajuans (plural)");
                                }
                            } else {
                                $this->log("      ⚠️  No variables detected in compact/with");
                            }
                            
                            $this->log("");
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            $this->log("❌ Controller methods debug failed: " . $e->getMessage());
        }
        
        $this->log("");
    }
    
    /**
     * Debug penggunaan variable di view secara detail
     */
    private function debugVariableUsage()
    {
        $this->log("🔬 DETAILED VARIABLE USAGE ANALYSIS");
        $this->log("===================================");
        
        // Focus pada specific problematic files dari error message
        $problematicFiles = [
            'resources/views/fakultas/surat/show.blade.php',
            'resources/views/staff/pengajuan/index.blade.php'
        ];
        
        foreach ($problematicFiles as $file) {
            $fullPath = base_path($file);
            
            if (File::exists($fullPath)) {
                $this->log("📄 FILE: {$file}");
                $content = File::get($fullPath);
                
                // Check all variable usage patterns
                $this->analyzeVariablePatterns($content, $file);
                
                // Check for @php blocks that might handle variables
                if (preg_match_all('/@php(.*?)@endphp/s', $content, $phpBlocks)) {
                    $this->log("   📝 @php blocks found: " . count($phpBlocks[0]));
                    foreach ($phpBlocks[1] as $i => $block) {
                        if (str_contains($block, '$pengajuan')) {
                            $this->log("      Block " . ($i + 1) . " handles \$pengajuan");
                        }
                    }
                }
                
                // Check for isset() checks
                if (preg_match_all('/isset\s*\(\s*\$pengajuan/', $content, $issetChecks)) {
                    $this->log("   ✅ Has isset() checks: " . count($issetChecks[0]));
                } else {
                    $this->log("   ❌ No isset() safety checks found");
                }
                
                $this->log("");
            } else {
                $this->log("📄 FILE NOT FOUND: {$file}");
                $this->log("");
            }
        }
    }
    
    /**
     * Suggest fixes berdasarkan analysis
     */
    private function suggestFixes()
    {
        $this->log("💡 SUGGESTED FIXES");
        $this->log("==================");
        
        $this->log("1. CONTROLLER FIXES:");
        $this->log("   - Ensure all controller methods send required variables");
        $this->log("   - Use consistent variable naming (pengajuan vs pengajuans)");
        $this->log("   - Add null checks before sending to views");
        $this->log("");
        
        $this->log("2. VIEW FIXES:");
        $this->log("   - Add @php blocks with isset() checks at top of views");
        $this->log("   - Use null coalescing operator (??) for safe access");
        $this->log("   - Initialize variables with default values");
        $this->log("");
        
        $this->log("3. IMMEDIATE FIX for staff/pengajuan/index.blade.php:");
        $this->log("   Add this at the top:");
        $this->log("   @php");
        $this->log("       if (!isset(\$pengajuans)) {");
        $this->log("           \$pengajuans = isset(\$pengajuan) ? collect([\$pengajuan]) : collect([]);");
        $this->log("       }");
        $this->log("   @endphp");
        $this->log("");
        
        $this->log("4. IMMEDIATE FIX for fakultas/surat/show.blade.php:");
        $this->log("   Add this at the top:");
        $this->log("   @php");
        $this->log("       if (!isset(\$pengajuan)) {");
        $this->log("           \$pengajuan = null;");
        $this->log("       }");
        $this->log("   @endphp");
        $this->log("");
        
        $this->log("5. CONTROLLER PATTERN TO USE:");
        $this->log("   public function show(\$id) {");
        $this->log("       // ... logic ...");
        $this->log("       \$pengajuan = PengajuanSurat::find(\$id) ?? null;");
        $this->log("       return view('view.name', compact('pengajuan'));");
        $this->log("   }");
        $this->log("");
    }
    
    // Helper methods
    private function extractControllerName($actionName)
    {
        if (str_contains($actionName, '@')) {
            return explode('@', $actionName)[0];
        }
        return $actionName;
    }
    
    private function getPossibleViews($routeName, $uri)
    {
        $views = [];
        
        if ($routeName) {
            // Convert route name to possible view path
            $viewPath = str_replace('.', '/', $routeName);
            $views[] = $viewPath;
        }
        
        // Convert URI to possible view path
        $uriPath = trim($uri, '/');
        $uriPath = str_replace(['/', '{', '}'], ['.', '', ''], $uriPath);
        if ($uriPath) {
            $views[] = $uriPath;
        }
        
        return array_unique($views);
    }
    
    private function getAllBladeFiles($directory)
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php' && 
                str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    private function getAllPHPFiles($directory)
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    private function getLineNumbers($content, $matches)
    {
        $lines = [];
        foreach ($matches as $match) {
            $lineNumber = substr_count(substr($content, 0, $match[1]), "\n") + 1;
            $lines[] = $lineNumber;
        }
        return array_unique($lines);
    }
    
    private function showVariableContext($filePath, $variable)
    {
        try {
            $content = File::get($filePath);
            $lines = explode("\n", $content);
            
            foreach ($lines as $lineNum => $line) {
                if (str_contains($line, $variable)) {
                    $this->log("      Line " . ($lineNum + 1) . ": " . trim($line));
                    break; // Show only first usage
                }
            }
        } catch (Exception $e) {
            $this->log("      Context extraction failed");
        }
    }
    
    private function extractCompactVariables($returnStatement, $fullContent)
    {
        $variables = [];
        
        // Check for compact() usage
        if (preg_match_all('/compact\s*\(\s*([^)]+)\)/', $returnStatement, $compactMatches)) {
            foreach ($compactMatches[1] as $compactArgs) {
                // Extract quoted strings
                if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $compactArgs, $varMatches)) {
                    $variables = array_merge($variables, $varMatches[1]);
                }
            }
        }
        
        // Check for with() usage
        if (preg_match_all('/with\s*\(\s*[\'"]([^\'"]+)[\'"]/', $returnStatement, $withMatches)) {
            $variables = array_merge($variables, $withMatches[1]);
        }
        
        return array_unique($variables);
    }
    
    private function analyzeVariablePatterns($content, $filename)
    {
        // Check all $pengajuan usages
        if (preg_match_all('/\$pengajuan(?!\w)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $this->log("   🔍 \$pengajuan usage: " . count($matches[0]) . " times");
        }
        
        // Check all $pengajuans usages  
        if (preg_match_all('/\$pengajuans(?!\w)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $this->log("   🔍 \$pengajuans usage: " . count($matches[0]) . " times");
        }
        
        // Check for foreach loops
        if (preg_match_all('/foreach\s*\(\s*\$(\w+)\s+as\s+\$(\w+)/', $content, $foreachMatches, PREG_SET_ORDER)) {
            foreach ($foreachMatches as $match) {
                $this->log("   🔄 Foreach: \${$match[1]} as \${$match[2]}");
            }
        }
        
        // Check for @if/@isset usage
        if (preg_match_all('/@(?:if|isset)\s*\(\s*([^)]+)\)/', $content, $conditionalMatches)) {
            foreach ($conditionalMatches[1] as $condition) {
                if (str_contains($condition, '$pengajuan')) {
                    $this->log("   ✅ Has conditional check: " . trim($condition));
                }
            }
        }
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . PHP_EOL;
    }
    
    public function displayResults()
    {
        $this->log("\n" . "=".str_repeat("=", 50));
        $this->log("DEBUG COMPLETED - UNDEFINED VARIABLE ANALYSIS");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("Total lines: " . count($this->output));
        
        // Save to file
        $logFile = storage_path('logs/undefined_variable_debug_' . date('Y-m-d_H-i-s') . '.log');
        file_put_contents($logFile, implode("\n", $this->output));
        $this->log("Log saved to: {$logFile}");
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    // Command line execution
    echo "🔍 Starting Undefined Variable Debug Analysis...\n\n";
    
    $debugger = new UndefinedVariableDebugger();
    $debugger->runDebug();
    
} else {
    // Web execution
    header('Content-Type: text/plain');
    
    echo "🔍 UNDEFINED VARIABLE DEBUG ANALYSIS (Web Mode)\n\n";
    
    $debugger = new UndefinedVariableDebugger();
    $debugger->runDebug();
}
?>