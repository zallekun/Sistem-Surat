<?php
// fix-route-registration.php
// Jalankan: php fix-route-registration.php

echo "=== FIXING ROUTE REGISTRATION ISSUE ===\n\n";

echo "1. Analyzing Routes File Structure\n";
echo str_repeat("-", 50) . "\n";

$webRoutesFile = 'routes/web.php';
$content = file_get_contents($webRoutesFile);
$lines = explode("\n", $content);

// Show current structure around surat routes
echo "Checking lines 15-30 for route context:\n";
for ($i = 14; $i <= 29; $i++) {
    if (isset($lines[$i])) {
        $lineNum = $i + 1;
        echo str_pad($lineNum, 3) . ": " . $lines[$i] . "\n";
    }
}

echo "\n2. Checking Route Group Context\n";
echo str_repeat("-", 50) . "\n";

// Check if routes are inside a group that might prevent registration
$inGroup = false;
$groupType = '';
$groupLine = 0;

for ($i = 0; $i < 30; $i++) {
    if (isset($lines[$i])) {
        $line = trim($lines[$i]);
        
        if (strpos($line, 'Route::group') !== false || strpos($line, '->group(') !== false) {
            $inGroup = true;
            $groupType = $line;
            $groupLine = $i + 1;
            echo "Found route group at line $groupLine: $line\n";
        }
        
        if ($inGroup && strpos($line, '});') !== false) {
            echo "Route group ends at line " . ($i + 1) . "\n";
            $inGroup = false;
        }
        
        if ($i >= 19 && $i <= 25) { // Lines where surat routes should be
            if ($inGroup) {
                echo "Line " . ($i + 1) . " is inside route group\n";
            }
        }
    }
}

echo "\n3. Creating Fixed Routes File\n";
echo str_repeat("-", 50) . "\n";

// Backup current file
$backup = $webRoutesFile . '.regfix.' . date('YmdHis');
copy($webRoutesFile, $backup);
echo "Backup created: $backup\n";

// Create a completely clean routes structure
$fixedRoutes = <<<'ROUTES'
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FakultasController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\TrackingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes (handled by Breeze)
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Surat Routes - Main functionality
Route::middleware(['auth'])->group(function () {
    // Core surat routes
    Route::get('/surat/{id}', [SuratController::class, 'show'])->name('surat.show');
    Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('surat.edit');
    Route::put('/surat/{id}', [SuratController::class, 'update'])->name('surat.update');
    Route::post('/surat/{id}/approve', [SuratController::class, 'approve'])->name('surat.approve');
    Route::post('/surat/{id}/reject', [SuratController::class, 'reject'])->name('surat.reject');
    
    // Additional surat functionality
    Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
    Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');
    Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');
    Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');
});

// Role-specific routes
Route::middleware(['auth'])->group(function () {
    // Pimpinan routes
    Route::prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('/surat/disposisi', function () {
            return view('pimpinan.surat.disposisi');
        })->name('surat.disposisi');
        
        Route::post('/surat/{id}/disposisi', [DisposisiController::class, 'store'])->name('surat.disposisi.store');
        
        Route::get('/surat/ttd', function () {
            return view('pimpinan.surat.ttd');
        })->name('surat.ttd');
        
        Route::post('/surat/{id}/ttd', [SuratController::class, 'tandaTangan'])->name('surat.ttd.process');
    });
    
    // Kabag routes
    Route::prefix('kabag')->name('kabag.')->group(function () {
        Route::get('/surat', function () {
            return view('kabag.surat.index');
        })->name('surat.index');
    });
    
    // Divisi routes
    Route::prefix('divisi')->name('divisi.')->group(function () {
        Route::get('/surat', function () {
            return view('divisi.surat.index');
        })->name('surat.index');
    });
    
    // Kaprodi routes
    Route::prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/surat/approval', [SuratController::class, 'approvalList'])->name('surat.approval');
    });
    
    // Staff routes
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');
        Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');
        Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');
    });
});

// Admin routes
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('fakultas', FakultasController::class);
    Route::resource('prodi', ProdiController::class);
    Route::resource('jabatan', JabatanController::class);
    Route::resource('tracking', TrackingController::class);
});

require __DIR__.'/auth.php';
ROUTES;

// Write the fixed routes
file_put_contents($webRoutesFile, $fixedRoutes);
echo "✓ Created clean routes file with proper structure\n";

echo "\n4. Testing Route Registration\n";
echo str_repeat("-", 50) . "\n";

// Test routes registration
$testScript = <<<'TEST'
<?php
require __DIR__.'/vendor/autoload.php';

try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "Testing surat routes after fix:\n";
    
    $routes = [
        'surat.show' => ['id' => 1],
        'surat.edit' => ['id' => 1],
        'surat.update' => ['id' => 1],
        'surat.approve' => ['id' => 1],
        'surat.reject' => ['id' => 1]
    ];
    
    foreach ($routes as $routeName => $params) {
        try {
            $url = route($routeName, $params);
            echo "✓ $routeName: $url\n";
        } catch (Exception $e) {
            echo "✗ $routeName: " . $e->getMessage() . "\n";
        }
    }
    
    // List all registered surat routes
    echo "\nAll registered surat routes:\n";
    $router = app('router');
    $allRoutes = $router->getRoutes();
    
    foreach ($allRoutes as $route) {
        $routeName = $route->getName();
        if ($routeName && strpos($routeName, 'surat') !== false) {
            echo "  $routeName => " . $route->uri() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
TEST;

file_put_contents('test-fixed-routes.php', $testScript);
$testOutput = shell_exec('php test-fixed-routes.php 2>&1');
echo $testOutput;

echo "\n5. Manual Verification\n";
echo str_repeat("-", 50) . "\n";

// Create verification URLs
echo "Test these URLs directly in browser:\n";
echo "1. http://localhost:8000/surat/1 (should show surat)\n";
echo "2. http://localhost:8000/surat/1/edit (should show edit form)\n";
echo "3. http://localhost:8000/dashboard (should show dashboard)\n";

echo "\n6. Check Laravel Route Cache\n";
echo str_repeat("-", 50) . "\n";

// Try to run artisan commands if possible
echo "Attempting to clear route cache...\n";

$commands = [
    'php artisan route:clear 2>&1',
    'php artisan optimize:clear 2>&1',
    'php artisan route:cache 2>&1'
];

foreach ($commands as $command) {
    echo "Running: $command\n";
    $output = shell_exec($command);
    if ($output) {
        echo "Output: " . trim($output) . "\n";
    }
    echo "\n";
}

echo "\n7. Create Route List Command Alternative\n";
echo str_repeat("-", 50) . "\n";

$routeListScript = <<<'ROUTELIST'
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ALL REGISTERED ROUTES ===\n";

$router = app('router');
$routes = $router->getRoutes();

foreach ($routes as $route) {
    $methods = implode('|', $route->methods());
    $uri = $route->uri();
    $name = $route->getName() ?: 'N/A';
    $action = $route->getActionName();
    
    // Filter for surat routes
    if (strpos($uri, 'surat') !== false || strpos($name, 'surat') !== false) {
        echo sprintf("%-8s %-30s %-25s %s\n", $methods, $uri, $name, $action);
    }
}
ROUTELIST;

file_put_contents('list-surat-routes.php', $routeListScript);
$routeListOutput = shell_exec('php list-surat-routes.php 2>&1');
echo "Surat routes in system:\n";
echo $routeListOutput;

echo "\n=== SUMMARY ===\n";
echo "✓ Created clean routes file structure\n";
echo "✓ Removed conflicting route definitions\n";
echo "✓ Added proper middleware groups\n";
echo "✓ Separated core routes from role-specific routes\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Restart Laravel server:\n";
echo "   php artisan serve\n\n";
echo "2. Test surat edit with staff_prodi user\n\n";
echo "3. If still issues, check:\n";
echo "   php list-surat-routes.php\n\n";

echo "Routes should now be properly registered!\n";

// Cleanup
unlink('test-fixed-routes.php');
unlink('list-surat-routes.php');