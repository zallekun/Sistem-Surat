<?php
// debug-route-registration.php
// Jalankan: php debug-route-registration.php

echo "=== DEBUGGING ROUTE REGISTRATION ISSUE ===\n\n";

echo "1. Checking Routes File Content\n";
echo str_repeat("-", 50) . "\n";

$webRoutesFile = 'routes/web.php';
$content = file_get_contents($webRoutesFile);
$lines = explode("\n", $content);

// Show staff section specifically
echo "Looking for staff routes section:\n";
$inStaffSection = false;
$staffLines = [];

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    
    if (strpos($line, "Route::prefix('staff')") !== false) {
        $inStaffSection = true;
        $staffLines[] = ($i + 1) . ": " . $line;
    } elseif ($inStaffSection) {
        $staffLines[] = ($i + 1) . ": " . $line;
        
        if (strpos($line, '});') !== false) {
            break;
        }
    }
}

if (!empty($staffLines)) {
    echo "Staff routes section:\n";
    foreach ($staffLines as $line) {
        echo "  " . $line . "\n";
    }
} else {
    echo "Staff routes section not found!\n";
}

echo "\n2. Manual Route Registration Test\n";
echo str_repeat("-", 50) . "\n";

// Create a test routes file to see what's happening
$testRoutesContent = <<<'TESTROUTES'
<?php

use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;

// Test minimal staff routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
    });
});
TESTROUTES;

file_put_contents('test-routes.php', $testRoutesContent);

// Test this simple route file
$testScript = <<<'TEST'
<?php
require __DIR__.'/vendor/autoload.php';

// Test with minimal routes
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Load test routes
require __DIR__.'/test-routes.php';

echo "Testing with minimal route definition:\n";

try {
    $url = route('staff.surat.create');
    echo "✓ staff.surat.create: $url\n";
} catch (Exception $e) {
    echo "✗ staff.surat.create: " . $e->getMessage() . "\n";
}

try {
    $url = route('staff.surat.store');
    echo "✓ staff.surat.store: $url\n";
} catch (Exception $e) {
    echo "✗ staff.surat.store: " . $e->getMessage() . "\n";
}
TEST;

file_put_contents('test-minimal-routes.php', $testScript);
$testOutput = shell_exec('php test-minimal-routes.php 2>&1');
echo "Minimal route test result:\n";
echo $testOutput;

echo "\n3. Checking Current Routes File Syntax\n";
echo str_repeat("-", 50) . "\n";

// Check for syntax errors in routes file
echo "Checking routes file syntax...\n";
$syntaxCheck = shell_exec('php -l routes/web.php 2>&1');
echo $syntaxCheck;

if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✓ Routes file syntax is valid\n";
} else {
    echo "✗ Syntax errors detected in routes file\n";
}

echo "\n4. Creating Direct Fix\n";
echo str_repeat("-", 50) . "\n";

// Backup current routes
$backup = $webRoutesFile . '.directfix.' . date('YmdHis');
copy($webRoutesFile, $backup);
echo "Backup created: $backup\n";

// Read current content and find staff section
$content = file_get_contents($webRoutesFile);
$lines = explode("\n", $content);

// Find and replace staff section completely
$newLines = [];
$skipLines = false;
$addedStaffSection = false;

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    
    // Start skipping when we find staff section
    if (strpos($line, "Route::prefix('staff')") !== false) {
        $skipLines = true;
        
        // Add our corrected staff section
        $newStaffSection = [
            "    // Staff routes - Fixed",
            "    Route::prefix('staff')->name('staff.')->middleware(['auth'])->group(function () {",
            "        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');",
            "        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');",
            "        Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');",
            "        Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');",
            "        Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');",
            "    });"
        ];
        
        $newLines = array_merge($newLines, $newStaffSection);
        $addedStaffSection = true;
        continue;
    }
    
    // Stop skipping when we find the end of staff section
    if ($skipLines && strpos($line, '});') !== false) {
        $skipLines = false;
        continue;
    }
    
    // Add line if we're not skipping
    if (!$skipLines) {
        $newLines[] = $line;
    }
}

// Write the corrected file
file_put_contents($webRoutesFile, implode("\n", $newLines));
echo "✓ Applied direct fix to staff routes section\n";

echo "\n5. Testing After Direct Fix\n";
echo str_repeat("-", 50) . "\n";

// Clear cache again
shell_exec('php artisan route:clear 2>&1');
shell_exec('php artisan optimize:clear 2>&1');

// Test routes again
$testScript2 = <<<'TEST2'
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing staff routes after direct fix:\n";

$routes = [
    'staff.surat.create',
    'staff.surat.store',
    'staff.surat.submit',
    'staff.surat.tracking',
    'staff.surat.download'
];

foreach ($routes as $routeName) {
    try {
        if ($routeName === 'staff.surat.store') {
            $url = route($routeName);
        } else {
            $url = route($routeName, ['id' => 1]);
        }
        echo "✓ $routeName: $url\n";
    } catch (Exception $e) {
        echo "✗ $routeName: " . $e->getMessage() . "\n";
    }
}

// Also check route collection
echo "\nChecking route collection:\n";
$router = app('router');
$allRoutes = $router->getRoutes();
$staffRoutes = [];

foreach ($allRoutes as $route) {
    $name = $route->getName();
    if ($name && strpos($name, 'staff.surat') !== false) {
        $staffRoutes[] = $name . ' => ' . $route->uri();
    }
}

if (!empty($staffRoutes)) {
    echo "Found staff surat routes:\n";
    foreach ($staffRoutes as $route) {
        echo "  " . $route . "\n";
    }
} else {
    echo "No staff surat routes found in collection\n";
}
TEST2;

file_put_contents('test-after-fix.php', $testScript2);
$testOutput2 = shell_exec('php test-after-fix.php 2>&1');
echo $testOutput2;

echo "\n6. Alternative: Add Routes Manually\n";
echo str_repeat("-", 50) . "\n";

// If still not working, create a separate routes file
$separateRoutesContent = <<<'SEPARATE'
<?php

use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;

// Separate staff routes file
Route::middleware(['auth'])->group(function () {
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
        Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');
        Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');
        Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');
    });
});
SEPARATE;

file_put_contents('routes/staff.php', $separateRoutesContent);
echo "✓ Created separate staff routes file: routes/staff.php\n";

// Add require to main routes file if needed
$mainRoutesContent = file_get_contents($webRoutesFile);
if (strpos($mainRoutesContent, "require __DIR__.'/staff.php'") === false) {
    $mainRoutesContent .= "\n\n// Include staff routes\nrequire __DIR__.'/staff.php';\n";
    file_put_contents($webRoutesFile, $mainRoutesContent);
    echo "✓ Added staff routes include to main routes file\n";
}

echo "\n=== SOLUTION SUMMARY ===\n";
echo "Multiple approaches applied:\n";
echo "1. Direct fix to staff routes section\n";
echo "2. Created separate staff routes file\n";
echo "3. Cleared all caches\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Restart server: php artisan serve\n";
echo "2. Test staff routes manually\n";
echo "3. If still failing, check RouteServiceProvider.php\n";

echo "\nStaff routes should now be available!\n";

// Cleanup
unlink('test-routes.php');
unlink('test-minimal-routes.php');
unlink('test-after-fix.php');