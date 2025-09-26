<?php
/**
 * VIEW DEBUG ANALYZER
 * 
 * Script untuk menganalisis semua view, layout, dan CSS yang terlibat
 * dalam route /staff/pengajuan/21 dan mengidentifikasi konflik
 * 
 * File: view_debug_analyzer.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ViewDebugAnalyzer
{
    private $output = [];
    private $conflicts = [];
    private $cssRules = [];
    private $jsConflicts = [];
    
    public function __construct()
    {
        echo "🔍 VIEW DEBUG ANALYZER\n";
        echo "=====================\n\n";
    }
    
    public function analyzeRoute($routePath = '/staff/pengajuan/21')
    {
        echo "Analyzing route: {$routePath}\n";
        echo str_repeat("=", 50) . "\n\n";
        
        $this->analyzeRouteDefinition($routePath);
        $this->analyzeViewHierarchy();
        $this->analyzeCSSConflicts();
        $this->analyzeJavaScriptConflicts();
        $this->analyzeLayoutInheritance();
        $this->analyzeTailwindConflicts();
        $this->generateReport();
        
        return $this->conflicts;
    }
    
    private function analyzeRouteDefinition($routePath)
    {
        $this->log("📍 ROUTE ANALYSIS");
        $this->log("=================");
        
        // Get route info
        $routes = collect(Route::getRoutes())->filter(function($route) use ($routePath) {
            return str_contains($route->uri(), 'staff/pengajuan/{id}');
        });
        
        foreach ($routes as $route) {
            $this->log("Route: " . $route->uri());
            $this->log("Controller: " . $route->getAction()['controller'] ?? 'N/A');
            $this->log("Middleware: " . implode(', ', $route->middleware()));
            
            // Check if controller exists
            $controller = $route->getAction()['controller'] ?? '';
            if ($controller) {
                $controllerClass = explode('@', $controller)[0];
                $controllerPath = app_path('Http/Controllers/' . str_replace('App\\Http\\Controllers\\', '', $controllerClass) . '.php');
                
                if (File::exists($controllerPath)) {
                    $this->log("✅ Controller exists: {$controllerPath}");
                    $this->analyzeController($controllerPath);
                } else {
                    $this->addConflict("CRITICAL", "Controller not found: {$controllerPath}");
                }
            }
        }
    }
    
    private function analyzeController($controllerPath)
    {
        $content = File::get($controllerPath);
        
        // Find view calls
        preg_match_all('/return\s+view\([\'"]([^\'\"]+)[\'"]/', $content, $viewMatches);
        
        if (!empty($viewMatches[1])) {
            foreach ($viewMatches[1] as $viewName) {
                $this->log("View called: {$viewName}");
                $this->analyzeViewFile($viewName);
            }
        }
    }
    
    private function analyzeViewHierarchy()
    {
        $this->log("\n🏗️ VIEW HIERARCHY ANALYSIS");
        $this->log("===========================");
        
        // Main views involved
        $views = [
            'layouts.app' => resource_path('views/layouts/app.blade.php'),
            'staff.pengajuan.show' => resource_path('views/staff/pengajuan/show.blade.php')
        ];
        
        foreach ($views as $viewName => $path) {
            $this->analyzeViewFile($viewName, $path);
        }
    }
    
    private function analyzeViewFile($viewName, $path = null)
    {
        if (!$path) {
            $path = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        }
        
        $this->log("\n📄 Analyzing view: {$viewName}");
        $this->log("Path: {$path}");
        
        if (!File::exists($path)) {
            $this->addConflict("ERROR", "View file not found: {$path}");
            return;
        }
        
        $content = File::get($path);
        $lineCount = substr_count($content, "\n") + 1;
        $this->log("Lines: {$lineCount}");
        
        // Check extends/includes
        preg_match_all('/@extends\([\'"]([^\'\"]+)[\'"]\)/', $content, $extends);
        preg_match_all('/@include\([\'"]([^\'\"]+)[\'"]\)/', $content, $includes);
        
        if (!empty($extends[1])) {
            $this->log("Extends: " . implode(', ', $extends[1]));
        }
        
        if (!empty($includes[1])) {
            $this->log("Includes: " . implode(', ', $includes[1]));
        }
        
        // Analyze CSS in this view
        $this->analyzeCSSInView($content, $viewName);
        
        // Analyze JavaScript in this view
        $this->analyzeJavaScriptInView($content, $viewName);
        
        // Check for potential conflicts
        $this->checkViewConflicts($content, $viewName);
    }
    
    private function analyzeCSSConflicts()
    {
        $this->log("\n🎨 CSS CONFLICTS ANALYSIS");
        $this->log("=========================");
        
        $allCSSRules = [];
        
        // Collect all CSS from views
        foreach ($this->cssRules as $viewName => $rules) {
            $this->log("\nCSS in {$viewName}:");
            foreach ($rules as $rule) {
                $this->log("  - {$rule['selector']}: {$rule['property']}");
                $allCSSRules[] = $rule;
            }
        }
        
        // Find conflicting rules
        $this->findCSSConflicts($allCSSRules);
    }
    
    private function analyzeCSSInView($content, $viewName)
    {
        // Extract CSS from <style> tags and @push('styles')
        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $content, $styleTags);
        preg_match_all('/@push\([\'"]styles[\'"]\)(.*?)@endpush/s', $content, $pushStyles);
        
        $allStyles = array_merge($styleTags[1] ?? [], $pushStyles[1] ?? []);
        
        if (empty($allStyles)) {
            return;
        }
        
        $this->log("CSS blocks found: " . count($allStyles));
        
        $viewRules = [];
        
        foreach ($allStyles as $styleContent) {
            // Extract CSS rules
            preg_match_all('/([^{]+)\{([^}]+)\}/', $styleContent, $cssMatches);
            
            for ($i = 0; $i < count($cssMatches[1]); $i++) {
                $selector = trim($cssMatches[1][$i]);
                $declarations = $cssMatches[2][$i];
                
                // Extract individual properties
                preg_match_all('/([^:]+):\s*([^;]+)/', $declarations, $propMatches);
                
                for ($j = 0; $j < count($propMatches[1]); $j++) {
                    $property = trim($propMatches[1][$j]);
                    $value = trim($propMatches[2][$j]);
                    
                    $viewRules[] = [
                        'selector' => $selector,
                        'property' => $property,
                        'value' => $value,
                        'important' => strpos($value, '!important') !== false
                    ];
                }
            }
        }
        
        $this->cssRules[$viewName] = $viewRules;
        
        // Count !important usage
        $importantCount = count(array_filter($viewRules, fn($rule) => $rule['important']));
        if ($importantCount > 10) {
            $this->addConflict("WARNING", "{$viewName} has {$importantCount} !important declarations");
        }
    }
    
    private function analyzeJavaScriptInView($content, $viewName)
    {
        // Count script blocks
        $scriptCount = substr_count($content, '<script>') + substr_count($content, "@push('scripts')");
        
        if ($scriptCount > 0) {
            $this->log("JavaScript blocks: {$scriptCount}");
            
            // Extract function names
            preg_match_all('/function\s+(\w+)\s*\(/', $content, $functions);
            
            if (!empty($functions[1])) {
                $this->jsConflicts[$viewName] = $functions[1];
                $this->log("Functions defined: " . implode(', ', $functions[1]));
            }
            
            // Check for jQuery vs Vanilla JS mixing
            $jqueryCount = substr_count($content, '$') + substr_count($content, 'jQuery');
            $vanillaCount = substr_count($content, 'document.') + substr_count($content, 'getElementById');
            
            if ($jqueryCount > 0 && $vanillaCount > 0) {
                $this->addConflict("INFO", "{$viewName} mixes jQuery ({$jqueryCount}) and Vanilla JS ({$vanillaCount})");
            }
        }
    }
    
    private function findCSSConflicts($allRules)
    {
        $selectorGroups = [];
        
        // Group rules by selector
        foreach ($allRules as $rule) {
            $selectorGroups[$rule['selector']][] = $rule;
        }
        
        // Find conflicting properties
        foreach ($selectorGroups as $selector => $rules) {
            $properties = [];
            
            foreach ($rules as $rule) {
                if (isset($properties[$rule['property']])) {
                    $this->addConflict(
                        "CONFLICT",
                        "Selector '{$selector}' has conflicting '{$rule['property']}' values"
                    );
                }
                $properties[$rule['property']] = $rule['value'];
            }
        }
    }
    
    private function analyzeJavaScriptConflicts()
    {
        $this->log("\n⚡ JAVASCRIPT CONFLICTS");
        $this->log("======================");
        
        $allFunctions = [];
        
        foreach ($this->jsConflicts as $viewName => $functions) {
            foreach ($functions as $functionName) {
                if (isset($allFunctions[$functionName])) {
                    $this->addConflict(
                        "CONFLICT",
                        "Function '{$functionName}' defined in multiple views: {$allFunctions[$functionName]} and {$viewName}"
                    );
                } else {
                    $allFunctions[$functionName] = $viewName;
                }
            }
        }
        
        $this->log("Total functions found: " . count($allFunctions));
    }
    
    private function analyzeLayoutInheritance()
    {
        $this->log("\n🏛️ LAYOUT INHERITANCE");
        $this->log("=====================");
        
        $showView = resource_path('views/staff/pengajuan/show.blade.php');
        $layoutView = resource_path('views/layouts/app.blade.php');
        
        if (!File::exists($showView) || !File::exists($layoutView)) {
            $this->addConflict("ERROR", "Required view files missing");
            return;
        }
        
        $showContent = File::get($showView);
        $layoutContent = File::get($layoutView);
        
        // Check sections and stacks
        preg_match_all('/@section\([\'"]([^\'\"]+)[\'"]\)/', $showContent, $sections);
        preg_match_all('/@push\([\'"]([^\'\"]+)[\'"]\)/', $showContent, $pushes);
        preg_match_all('/@stack\([\'"]([^\'\"]+)[\'"]\)/', $layoutContent, $stacks);
        
        $this->log("Sections in show view: " . implode(', ', $sections[1] ?? []));
        $this->log("Pushes in show view: " . implode(', ', $pushes[1] ?? []));
        $this->log("Stacks in layout: " . implode(', ', $stacks[1] ?? []));
        
        // Check for mismatched stacks
        $pushNames = $pushes[1] ?? [];
        $stackNames = $stacks[1] ?? [];
        
        foreach ($pushNames as $pushName) {
            if (!in_array($pushName, $stackNames)) {
                $this->addConflict("WARNING", "Push '{$pushName}' has no corresponding stack in layout");
            }
        }
    }
    
    private function analyzeTailwindConflicts()
    {
        $this->log("\n💨 TAILWIND CONFLICTS");
        $this->log("=====================");
        
        $showView = resource_path('views/staff/pengajuan/show.blade.php');
        $content = File::get($showView);
        
        // Common conflicting patterns
        $conflictPatterns = [
            'position' => ['absolute', 'relative', 'fixed', 'sticky'],
            'display' => ['block', 'inline', 'flex', 'grid', 'hidden'],
            'z-index' => ['z-0', 'z-10', 'z-20', 'z-30', 'z-40', 'z-50', 'z-auto'],
            'overflow' => ['overflow-hidden', 'overflow-auto', 'overflow-scroll']
        ];
        
        foreach ($conflictPatterns as $property => $classes) {
            $foundClasses = [];
            
            foreach ($classes as $class) {
                if (strpos($content, $class) !== false) {
                    $foundClasses[] = $class;
                }
            }
            
            if (count($foundClasses) > 3) {
                $this->addConflict("INFO", "Multiple {$property} classes found: " . implode(', ', $foundClasses));
            }
        }
    }
    
    private function checkViewConflicts($content, $viewName)
    {
        // Check for common problematic patterns
        $problems = [
            'body.*overflow.*hidden' => "Body overflow hidden detected - may prevent scrolling",
            'position.*fixed.*z-index.*99' => "Very high z-index detected - may cause layering issues",
            '!important.*!important.*!important' => "Multiple !important in single declaration",
            'style="[^"]*z-index' => "Inline z-index styles - hard to override",
        ];
        
        foreach ($problems as $pattern => $description) {
            if (preg_match("/$pattern/i", $content)) {
                $this->addConflict("WARNING", "{$viewName}: {$description}");
            }
        }
    }
    
    private function addConflict($level, $message)
    {
        $this->conflicts[] = ['level' => $level, 'message' => $message];
        $icon = $level === 'CRITICAL' ? '🔴' : ($level === 'ERROR' ? '❌' : ($level === 'CONFLICT' ? '⚠️' : 'ℹ️'));
        $this->log("{$icon} {$level}: {$message}");
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . "\n";
    }
    
    private function generateReport()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 DETAILED ANALYSIS REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        // Categorize conflicts
        $critical = array_filter($this->conflicts, fn($c) => $c['level'] === 'CRITICAL');
        $errors = array_filter($this->conflicts, fn($c) => $c['level'] === 'ERROR');
        $conflicts = array_filter($this->conflicts, fn($c) => $c['level'] === 'CONFLICT');
        $warnings = array_filter($this->conflicts, fn($c) => $c['level'] === 'WARNING');
        
        echo "Critical Issues: " . count($critical) . "\n";
        echo "Errors: " . count($errors) . "\n";
        echo "Conflicts: " . count($conflicts) . "\n";
        echo "Warnings: " . count($warnings) . "\n";
        echo "Total Issues: " . count($this->conflicts) . "\n\n";
        
        if (count($this->conflicts) > 0) {
            echo "🔧 SPECIFIC FIXES NEEDED:\n";
            echo str_repeat("-", 30) . "\n";
            
            foreach ($this->conflicts as $conflict) {
                echo "• {$conflict['message']}\n";
            }
            
            echo "\n💡 RECOMMENDED ACTIONS:\n";
            echo str_repeat("-", 25) . "\n";
            
            if (count($critical) > 0) {
                echo "1. Fix critical issues immediately\n";
            }
            
            if (count($conflicts) > 0) {
                echo "2. Resolve CSS/JS conflicts\n";
            }
            
            echo "3. Consolidate CSS into single coherent system\n";
            echo "4. Remove excessive !important usage\n";
            echo "5. Standardize z-index hierarchy\n";
            echo "6. Clear all caches and rebuild assets\n";
        } else {
            echo "✅ NO MAJOR CONFLICTS FOUND\n";
            echo "The view structure appears to be clean.\n";
        }
        
        // Save detailed report
        $reportPath = storage_path('logs/view_analysis_' . date('Y-m-d_H-i-s') . '.log');
        File::put($reportPath, implode("\n", $this->output));
        echo "\n📁 Full report saved to: {$reportPath}\n";
    }
}

// Execute analysis
try {
    $analyzer = new ViewDebugAnalyzer();
    $analyzer->analyzeRoute('/staff/pengajuan/21');
} catch (Exception $e) {
    echo "❌ ANALYSIS ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>