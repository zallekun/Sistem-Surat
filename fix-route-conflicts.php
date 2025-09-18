<?php
// fix-route-conflicts.php
// Jalankan: php fix-route-conflicts.php

echo "=== FIXING ROUTE CONFLICTS AND REGISTRATION ===\n\n";

echo "1. Analyzing Route Conflicts\n";
echo str_repeat("-", 50) . "\n";

$webRoutesFile = 'routes/web.php';
$content = file_get_contents($webRoutesFile);
$lines = explode("\n", $content);

echo "Found multiple surat route definitions:\n";
$suratRouteLines = [];
$conflicts = [];

foreach ($lines as $index => $line) {
    if (stripos($line, 'surat') !== false && strpos($line, 'Route::') !== false) {
        $lineInfo = "Line " . ($index + 1) . ": " . trim($line);
        $suratRouteLines[] = $lineInfo;
        
        // Check for conflicts
        if (strpos($line, 'surat.edit') !== false) {
            $conflicts['edit'][] = $index + 1;
        }
        if (strpos($line, 'surat.approve') !== false) {
            $conflicts['approve'][] = $index + 1;
        }
        if (strpos($line, 'surat.reject') !== false) {
            $conflicts['reject'][] = $index + 1;
        }
    }
}

foreach ($suratRouteLines as $line) {
    echo "  " . $line . "\n";
}

echo "\nConflicts detected:\n";
foreach ($conflicts as $route => $lineNumbers) {
    if (count($lineNumbers) > 1) {
        echo "  $route route defined on lines: " . implode(', ', $lineNumbers) . "\n";
    }
}

echo "\n2. Backing Up and Cleaning Routes File\n";
echo str_repeat("-", 50) . "\n";

// Backup routes file
$backup = $webRoutesFile . '.conflicts.' . date('YmdHis');
copy($webRoutesFile, $backup);
echo "Backup created: $backup\n";

// Clean up duplicate and conflicting routes
$cleanedLines = [];
$seenRoutes = [];
$skip = false;

foreach ($lines as $index => $line) {
    $trimmed = trim($line);
    
    // Skip empty lines at the beginning
    if (empty($trimmed) && empty($cleanedLines)) {
        continue;
    }
    
    // Check for resource route that might conflict
    if (strpos($trimmed, "Route::resource('surat'") !== false) {
        echo "Found resource route on line " . ($index + 1) . " - this might conflict with individual routes\n";
        $cleanedLines[] = "// " . $line . " // Commented out - conflicts with individual routes";
        continue;
    }
    
    // Check for duplicate route definitions
    if (strpos($trimmed, 'Route::') !== false && strpos($trimmed, 'surat') !== false) {
        // Extract route signature for duplicate checking
        $routeSignature = '';
        if (preg_match('/Route::(get|post|put|patch|delete)\s*\(\s*[\'"]([^\'"]+)[\'"].*?->name\s*\(\s*[\'"]([^\'"]+)[\'"]\)/', $trimmed, $matches)) {
            $routeSignature = $matches[1] . ':' . $matches[2] . ':' . $matches[3];
        }
        
        if (!empty($routeSignature)) {
            if (isset($seenRoutes[$routeSignature])) {
                echo "Skipping duplicate route: $routeSignature on line " . ($index + 1) . "\n";
                $cleanedLines[] = "// " . $line . " // Duplicate route - commented out";
                continue;
            } else {
                $seenRoutes[$routeSignature] = $index + 1;
            }
        }
    }
    
    $cleanedLines[] = $line;
}

// Write cleaned routes
file_put_contents($webRoutesFile, implode("\n", $cleanedLines));
echo "Routes file cleaned of duplicates\n";

echo "\n3. Adding Proper Surat Routes\n";
echo str_repeat("-", 50) . "\n";

// Ensure we have the correct surat routes without conflicts
$properSuratRoutes = [
    "",
    "// Surat Routes - Main functionality",
    "Route::middleware(['auth'])->group(function () {",
    "    Route::get('/surat/{id}', [SuratController::class, 'show'])->name('surat.show');",
    "    Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('surat.edit');",
    "    Route::put('/surat/{id}', [SuratController::class, 'update'])->name('surat.update');",
    "    Route::post('/surat/{id}/approve', [SuratController::class, 'approve'])->name('surat.approve');",
    "    Route::post('/surat/{id}/reject', [SuratController::class, 'reject'])->name('surat.reject');",
    "});",
    ""
];

// Check if we need to add proper routes
$hasProperRoutes = false;
$content = file_get_contents($webRoutesFile);

if (strpos($content, 'surat.edit') !== false && 
    strpos($content, 'surat.update') !== false && 
    strpos($content, 'surat.approve') !== false && 
    strpos($content, 'surat.reject') !== false) {
    $hasProperRoutes = true;
}

if (!$hasProperRoutes) {
    echo "Adding proper surat routes...\n";
    
    $lines = explode("\n", $content);
    
    // Find a good place to add routes (near the end but before closing)
    $insertPosition = -1;
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        if (trim($lines[$i]) !== '' && strpos($lines[$i], '?>') === false) {
            $insertPosition = $i + 1;
            break;
        }
    }
    
    if ($insertPosition >= 0) {
        array_splice($lines, $insertPosition, 0, $properSuratRoutes);
        file_put_contents($webRoutesFile, implode("\n", $lines));
        echo "✓ Added proper surat routes\n";
    }
} else {
    echo "✓ Proper surat routes already exist\n";
}

echo "\n4. Manual Cache Clear\n";
echo str_repeat("-", 50) . "\n";

// Since artisan command failed, clear cache files manually
$cacheDirectories = [
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/config.php',
    'bootstrap/cache/services.php',
    'storage/framework/cache/data',
    'storage/framework/views'
];

foreach ($cacheDirectories as $path) {
    if (file_exists($path)) {
        if (is_file($path)) {
            unlink($path);
            echo "✓ Deleted cache file: $path\n";
        } elseif (is_dir($path)) {
            // Clear directory contents
            $files = glob($path . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            echo "✓ Cleared cache directory: $path\n";
        }
    }
}

echo "\n5. Testing Routes Directly\n";
echo str_repeat("-", 50) . "\n";

// Create a route testing script
$routeTestScript = <<<'TEST'
<?php
// Route test script
require __DIR__.'/vendor/autoload.php';

try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "Testing individual surat routes:\n";
    
    $routes = [
        'surat.show' => 1,
        'surat.edit' => 1,
        'surat.update' => 1,
        'surat.approve' => 1,
        'surat.reject' => 1
    ];
    
    foreach ($routes as $routeName => $id) {
        try {
            $url = route($routeName, ['id' => $id]);
            echo "✓ $routeName: $url\n";
        } catch (Exception $e) {
            echo "✗ $routeName: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nTesting route list...\n";
    $router = app('router');
    $routes = $router->getRoutes();
    
    $suratRoutes = [];
    foreach ($routes as $route) {
        $routeName = $route->getName();
        if ($routeName && strpos($routeName, 'surat') !== false) {
            $suratRoutes[] = $routeName . ' => ' . $route->uri();
        }
    }
    
    if (!empty($suratRoutes)) {
        echo "\nRegistered surat routes:\n";
        foreach ($suratRoutes as $route) {
            echo "  " . $route . "\n";
        }
    } else {
        echo "\nNo surat routes found in route collection\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
TEST;

file_put_contents('test-surat-routes-manual.php', $routeTestScript);

echo "Running route test...\n";
$testOutput = shell_exec('php test-surat-routes-manual.php 2>&1');
echo $testOutput;

echo "\n6. Creating Simple Route Verification\n";
echo str_repeat("-", 50) . "\n";

// Create a simple verification script
$verifyScript = <<<'VERIFY'
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ROUTE VERIFICATION ===\n";

// Check if SuratController exists and has required methods
$controllerClass = 'App\\Http\\Controllers\\SuratController';
if (class_exists($controllerClass)) {
    echo "✓ SuratController exists\n";
    
    $requiredMethods = ['show', 'edit', 'update', 'approve', 'reject'];
    $reflection = new ReflectionClass($controllerClass);
    
    foreach ($requiredMethods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "  ✓ Method $method exists\n";
        } else {
            echo "  ✗ Method $method missing\n";
        }
    }
} else {
    echo "✗ SuratController not found\n";
}

// Test route registration
echo "\n=== ROUTE REGISTRATION TEST ===\n";
try {
    $router = app('router');
    $allRoutes = $router->getRoutes();
    
    $targetRoutes = ['surat.show', 'surat.edit', 'surat.update', 'surat.approve', 'surat.reject'];
    
    foreach ($targetRoutes as $targetRoute) {
        $found = false;
        foreach ($allRoutes as $route) {
            if ($route->getName() === $targetRoute) {
                echo "✓ $targetRoute registered\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "✗ $targetRoute NOT registered\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error checking routes: " . $e->getMessage() . "\n";
}
VERIFY;

file_put_contents('verify-routes.php', $verifyScript);
$verifyOutput = shell_exec('php verify-routes.php 2>&1');
echo $verifyOutput;

echo "\n=== SOLUTIONS ===\n";
echo "1. Try accessing directly in browser:\n";
echo "   http://localhost:8000/surat/1/edit\n";
echo "   http://localhost:8000/surat/1\n\n";

echo "2. Check route registration:\n";
echo "   php verify-routes.php\n\n";

echo "3. If still not working, restart server:\n";
echo "   php artisan serve\n\n";

echo "4. Check routes file manually:\n";
echo "   Look at lines 20-24 in routes/web.php\n\n";

echo "=== DEBUGGING ===\n";
echo "The issue appears to be route registration conflicts.\n";
echo "Routes are defined in the file but not registering properly.\n";
echo "This usually indicates middleware conflicts or duplicate definitions.\n";

// Cleanup
unlink('test-surat-routes-manual.php');
unlink('verify-routes.php');