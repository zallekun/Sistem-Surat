<?php
/**
 * LAYOUT DEBUG SCRIPT
 * 
 * Script untuk debugging masalah layout, CSS conflicts, dan loading issues
 * 
 * File: debug_layout.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class LayoutDebugger
{
    private $output = [];
    private $issues = [];
    
    public function __construct()
    {
        $this->log("=== LARAVEL LAYOUT DEBUG ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
    }
    
    public function debugLayout()
    {
        $this->log("🔍 DEBUGGING LAYOUT ISSUES");
        $this->log("============================");
        
        $this->checkMainLayout();
        $this->checkCSSConflicts();
        $this->checkViewCaching();
        $this->checkAssetLoading();
        $this->checkZIndexIssues();
        $this->checkJavaScriptConflicts();
        
        $this->displayResults();
    }
    
    private function checkMainLayout()
    {
        $this->log("\n📋 CHECKING MAIN LAYOUT");
        $this->log("========================");
        
        $layoutPath = resource_path('views/layouts/app.blade.php');
        if (!File::exists($layoutPath)) {
            $this->addIssue("CRITICAL: Main layout file not found: {$layoutPath}");
            return;
        }
        
        $content = File::get($layoutPath);
        
        // Check for conflicting CSS frameworks
        $cssFrameworks = [
            'tailwind' => ['@tailwind', 'tailwind', 'tw-'],
            'bootstrap' => ['bootstrap', 'btn-', 'container-fluid'],
            'bulma' => ['bulma', 'is-', 'has-'],
            'foundation' => ['foundation', 'grid-x', 'cell']
        ];
        
        $foundFrameworks = [];
        foreach ($cssFrameworks as $framework => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    $foundFrameworks[] = $framework;
                    break;
                }
            }
        }
        
        if (count($foundFrameworks) > 1) {
            $this->addIssue("CONFLICT: Multiple CSS frameworks detected: " . implode(', ', array_unique($foundFrameworks)));
        } else {
            $this->log("✅ Single CSS framework detected: " . (empty($foundFrameworks) ? 'Custom' : implode(', ', $foundFrameworks)));
        }
        
        // Check viewport meta tag
        if (stripos($content, 'name="viewport"') === false) {
            $this->addIssue("WARNING: Viewport meta tag missing - mobile responsiveness affected");
        } else {
            $this->log("✅ Viewport meta tag found");
        }
        
        // Check for inline styles that might conflict
        $inlineStyleCount = preg_match_all('/style\s*=\s*["\'][^"\']*["\']/', $content);
        if ($inlineStyleCount > 5) {
            $this->addIssue("WARNING: Too many inline styles ({$inlineStyleCount}) - consider moving to CSS files");
        }
        
        // Check z-index declarations
        preg_match_all('/z-index\s*:\s*(\d+)/', $content, $zIndexes);
        if (!empty($zIndexes[1])) {
            $maxZIndex = max($zIndexes[1]);
            if ($maxZIndex > 9999) {
                $this->addIssue("WARNING: Very high z-index detected ({$maxZIndex}) - may cause layering issues");
            }
            $this->log("✅ Z-index range: " . min($zIndexes[1]) . " to " . $maxZIndex);
        }
    }
    
    private function checkCSSConflicts()
    {
        $this->log("\n🎨 CHECKING CSS CONFLICTS");
        $this->log("==========================");
        
        $showViewPath = resource_path('views/staff/pengajuan/show.blade.php');
        if (!File::exists($showViewPath)) {
            $this->addIssue("ERROR: show.blade.php not found");
            return;
        }
        
        $content = File::get($showViewPath);
        
        // Check for multiple @push('styles') 
        $stylesPushCount = substr_count($content, "@push('styles')");
        if ($stylesPushCount > 1) {
            $this->addIssue("CONFLICT: Multiple @push('styles') found ({$stylesPushCount}) - may cause CSS conflicts");
        }
        
        // Check for conflicting position declarations
        $positionConflicts = [
            'position: fixed' => 0,
            'position: absolute' => 0,
            'position: relative' => 0,
            'position: sticky' => 0
        ];
        
        foreach ($positionConflicts as $position => &$count) {
            $count = substr_count(strtolower($content), strtolower($position));
        }
        
        $totalPositions = array_sum($positionConflicts);
        if ($totalPositions > 10) {
            $this->addIssue("WARNING: High number of position declarations ({$totalPositions}) - potential layout conflicts");
        }
        
        $this->log("Position declarations found:");
        foreach ($positionConflicts as $position => $count) {
            if ($count > 0) {
                $this->log("  - {$position}: {$count}");
            }
        }
        
        // Check for !important overuse
        $importantCount = substr_count($content, '!important');
        if ($importantCount > 20) {
            $this->addIssue("WARNING: Overuse of !important ({$importantCount}) - indicates CSS specificity issues");
        } else {
            $this->log("✅ Reasonable use of !important: {$importantCount}");
        }
        
        // Check for overflow hidden on body
        if (stripos($content, 'overflow: hidden') !== false || stripos($content, 'overflow-y: hidden') !== false) {
            $this->log("⚠️  Found overflow: hidden - this might prevent body scroll");
        }
    }
    
    private function checkViewCaching()
    {
        $this->log("\n💾 CHECKING VIEW CACHING");
        $this->log("=========================");
        
        // Check if view cache is enabled
        $viewCachePath = storage_path('framework/views');
        if (!File::exists($viewCachePath)) {
            $this->addIssue("WARNING: View cache directory doesn't exist");
            return;
        }
        
        $cachedFiles = File::files($viewCachePath);
        $cacheSize = count($cachedFiles);
        
        if ($cacheSize > 100) {
            $this->log("⚠️  Large view cache ({$cacheSize} files) - consider clearing");
            $this->log("Run: php artisan view:clear");
        } else {
            $this->log("✅ View cache size normal: {$cacheSize} files");
        }
        
        // Check if specific view is cached
        $showViewHash = md5(resource_path('views/staff/pengajuan/show.blade.php'));
        $cachedShowView = collect($cachedFiles)->first(function ($file) use ($showViewHash) {
            return strpos($file->getFilename(), substr($showViewHash, 0, 10)) !== false;
        });
        
        if ($cachedShowView) {
            $cacheAge = now()->diffInMinutes($cachedShowView->getMTime());
            if ($cacheAge < 5) {
                $this->log("✅ show.blade.php cache is fresh ({$cacheAge} min old)");
            } else {
                $this->log("⚠️  show.blade.php cache is old ({$cacheAge} min) - may cause styling delays");
            }
        }
    }
    
    private function checkAssetLoading()
    {
        $this->log("\n📦 CHECKING ASSET LOADING");
        $this->log("==========================");
        
        // Check if Vite is being used
        $layoutContent = File::get(resource_path('views/layouts/app.blade.php'));
        
        $usingVite = stripos($layoutContent, '@vite') !== false;
        $usingMix = stripos($layoutContent, 'mix(') !== false;
        
        if ($usingVite && $usingMix) {
            $this->addIssue("CONFLICT: Both Vite and Laravel Mix detected - choose one");
        } elseif ($usingVite) {
            $this->log("✅ Using Vite for asset compilation");
            
            // Check if vite.config.js exists
            if (!File::exists(base_path('vite.config.js'))) {
                $this->addIssue("ERROR: vite.config.js not found");
            }
        } elseif ($usingMix) {
            $this->log("✅ Using Laravel Mix for asset compilation");
            
            // Check if webpack.mix.js exists
            if (!File::exists(base_path('webpack.mix.js'))) {
                $this->addIssue("ERROR: webpack.mix.js not found");
            }
        } else {
            $this->addIssue("WARNING: No asset compilation tool detected");
        }
        
        // Check public asset directories
        $publicCss = public_path('css');
        $publicJs = public_path('js');
        
        if (!File::exists($publicCss)) {
            $this->addIssue("WARNING: public/css directory missing");
        } else {
            $cssFiles = count(File::files($publicCss));
            $this->log("CSS files in public: {$cssFiles}");
        }
        
        if (!File::exists($publicJs)) {
            $this->addIssue("WARNING: public/js directory missing");
        } else {
            $jsFiles = count(File::files($publicJs));
            $this->log("JS files in public: {$jsFiles}");
        }
    }
    
    private function checkZIndexIssues()
    {
        $this->log("\n🏗️ CHECKING Z-INDEX ISSUES");
        $this->log("===========================");
        
        $showContent = File::get(resource_path('views/staff/pengajuan/show.blade.php'));
        
        // Find all z-index values
        preg_match_all('/z-(?:index-)?\[?(\d+)\]?|z-index\s*:\s*(\d+)/', $showContent, $matches);
        
        $zIndexes = array_filter(array_merge($matches[1], $matches[2]));
        
        if (empty($zIndexes)) {
            $this->log("⚠️  No z-index values found - layout stacking may be unpredictable");
        } else {
            $uniqueZIndexes = array_unique($zIndexes);
            sort($uniqueZIndexes);
            
            $this->log("Z-index values found: " . implode(', ', $uniqueZIndexes));
            
            // Check for problematic z-index values
            $maxZIndex = max($uniqueZIndexes);
            if ($maxZIndex > 9999) {
                $this->addIssue("WARNING: Extremely high z-index ({$maxZIndex}) - may conflict with browser chrome");
            }
            
            // Check for z-index gaps that might cause issues
            for ($i = 1; $i < count($uniqueZIndexes); $i++) {
                $diff = $uniqueZIndexes[$i] - $uniqueZIndexes[$i-1];
                if ($diff > 1000) {
                    $this->addIssue("INFO: Large z-index gap: {$uniqueZIndexes[$i-1]} to {$uniqueZIndexes[$i]}");
                }
            }
        }
        
        // Check for common z-index conflicts
        $commonElements = [
            'navbar' => [1000, 1030],
            'modal' => [1040, 1050],
            'tooltip' => [1070, 1080],
            'dropdown' => [1000, 1020]
        ];
        
        foreach ($commonElements as $element => $range) {
            $found = false;
            foreach ($zIndexes as $zIndex) {
                if ($zIndex >= $range[0] && $zIndex <= $range[1]) {
                    $this->log("✅ {$element} z-index in expected range: {$zIndex}");
                    $found = true;
                    break;
                }
            }
            if (!$found && strpos($showContent, $element) !== false) {
                $this->addIssue("WARNING: {$element} element found but z-index not in expected range {$range[0]}-{$range[1]}");
            }
        }
    }
    
    private function checkJavaScriptConflicts()
    {
        $this->log("\n⚡ CHECKING JAVASCRIPT CONFLICTS");
        $this->log("================================");
        
        $showContent = File::get(resource_path('views/staff/pengajuan/show.blade.php'));
        
        // Count script tags
        $scriptCount = substr_count($showContent, '<script>') + substr_count($showContent, "@push('scripts')");
        $this->log("Script sections found: {$scriptCount}");
        
        // Check for duplicate function definitions
        preg_match_all('/function\s+(\w+)\s*\(/', $showContent, $functions);
        $functionNames = $functions[1];
        $duplicates = array_filter(array_count_values($functionNames), function($count) { return $count > 1; });
        
        if (!empty($duplicates)) {
            $this->addIssue("CONFLICT: Duplicate function definitions: " . implode(', ', array_keys($duplicates)));
        } else {
            $this->log("✅ No duplicate function definitions");
        }
        
        // Check for console.log statements
        $consoleLogCount = substr_count($showContent, 'console.log');
        if ($consoleLogCount > 5) {
            $this->log("INFO: Many console.log statements ({$consoleLogCount}) - consider removing for production");
        }
        
        // Check for jQuery conflicts
        $jqueryCount = substr_count($showContent, '$') + substr_count($showContent, 'jQuery');
        $vanillaJsCount = substr_count($showContent, 'document.') + substr_count($showContent, 'getElementById');
        
        if ($jqueryCount > 0 && $vanillaJsCount > 0) {
            $this->log("INFO: Mixed jQuery ({$jqueryCount}) and Vanilla JS ({$vanillaJsCount}) usage");
        }
    }
    
    private function addIssue($issue)
    {
        $this->issues[] = $issue;
        $this->log("❌ {$issue}");
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . PHP_EOL;
    }
    
    public function displayResults()
    {
        $this->log("\n" . str_repeat("=", 60));
        $this->log("📊 DEBUG SUMMARY");
        $this->log("================");
        
        $criticalIssues = array_filter($this->issues, function($issue) {
            return strpos($issue, 'CRITICAL:') === 0;
        });
        
        $conflicts = array_filter($this->issues, function($issue) {
            return strpos($issue, 'CONFLICT:') === 0;
        });
        
        $warnings = array_filter($this->issues, function($issue) {
            return strpos($issue, 'WARNING:') === 0;
        });
        
        $this->log("Critical Issues: " . count($criticalIssues));
        $this->log("Conflicts: " . count($conflicts));
        $this->log("Warnings: " . count($warnings));
        $this->log("Total Issues: " . count($this->issues));
        
        if (!empty($this->issues)) {
            $this->log("\n🔧 RECOMMENDED FIXES:");
            $this->log("=====================");
            
            if (!empty($criticalIssues)) {
                $this->log("1. Fix critical issues first");
            }
            
            if (!empty($conflicts)) {
                $this->log("2. Resolve CSS/JS conflicts");
            }
            
            $this->log("3. Clear all caches:");
            $this->log("   php artisan view:clear");
            $this->log("   php artisan config:clear");
            $this->log("   php artisan route:clear");
            
            if (strpos(implode(' ', $this->issues), 'z-index') !== false) {
                $this->log("4. Review z-index hierarchy");
            }
            
            if (strpos(implode(' ', $this->issues), 'Vite') !== false || strpos(implode(' ', $this->issues), 'Mix') !== false) {
                $this->log("5. Rebuild assets: npm run build or npm run dev");
            }
        } else {
            $this->log("\n✅ NO MAJOR ISSUES FOUND");
            $this->log("Layout should be working correctly");
        }
        
        $this->log("\n📝 LOG FILE CREATED:");
        $logFile = storage_path('logs/layout_debug_' . date('Y-m-d_H-i-s') . '.log');
        File::put($logFile, implode("\n", $this->output));
        $this->log($logFile);
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    echo "🔍 Starting Layout Debug...\n\n";
    
    try {
        $debugger = new LayoutDebugger();
        $debugger->debugLayout();
        
        echo "\n✅ Debug completed successfully!\n";
        
    } catch (Exception $e) {
        echo "\n❌ DEBUG ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "🔍 LAYOUT DEBUG (Web Mode)\n\n";
    
    try {
        $debugger = new LayoutDebugger();
        $debugger->debugLayout();
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>