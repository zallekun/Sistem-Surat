<?php
// fix_routes.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING ROUTE ISSUES ===\n\n";

// Check existing routes
echo "Current surat routes:\n";
$routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(function ($route) {
    return str_contains($route->getName() ?? '', 'surat') || str_contains($route->uri(), 'surat');
});

foreach ($routes as $route) {
    $name = $route->getName() ?? 'no-name';
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    echo "- $name: $methods $uri\n";
}

// Fix the routes file
$routesFile = base_path('routes/web.php');
$routesContent = file_get_contents($routesFile);

// Backup routes file
$backupFile = base_path('routes/web.php.backup.' . date('YmdHis'));
file_put_contents($backupFile, $routesContent);
echo "\nBacked up routes to: $backupFile\n";

// Add missing routes if not exist
$routesToAdd = "
// Staff Surat Routes
Route::middleware(['auth', 'role:staff_prodi,kaprodi'])->group(function () {
    Route::get('/staff/surat', [App\Http\Controllers\SuratController::class, 'staffIndex'])->name('staff.surat.index');
    Route::get('/staff/surat/create', [App\Http\Controllers\SuratController::class, 'create'])->name('staff.surat.create');
    Route::post('/staff/surat', [App\Http\Controllers\SuratController::class, 'store'])->name('staff.surat.store');
    Route::get('/staff/surat/{id}', [App\Http\Controllers\SuratController::class, 'show'])->name('staff.surat.show');
    Route::get('/staff/surat/{id}/edit', [App\Http\Controllers\SuratController::class, 'edit'])->name('staff.surat.edit');
    Route::put('/staff/surat/{id}', [App\Http\Controllers\SuratController::class, 'update'])->name('staff.surat.update');
    Route::delete('/staff/surat/{id}', [App\Http\Controllers\SuratController::class, 'destroy'])->name('staff.surat.destroy');
});
";

// Check if routes already exist
if (!str_contains($routesContent, 'staff.surat.show')) {
    echo "\n✓ Adding missing staff.surat routes\n";
    
    // Find a good place to insert (after auth middleware)
    $insertPosition = strpos($routesContent, "Route::middleware(['auth'])->group(function () {");
    if ($insertPosition !== false) {
        $insertPosition = strpos($routesContent, "});", $insertPosition) + 3;
        $routesContent = substr_replace($routesContent, "\n" . $routesToAdd, $insertPosition, 0);
        file_put_contents($routesFile, $routesContent);
        echo "✓ Routes added successfully\n";
    } else {
        echo "⚠ Could not find suitable position to insert routes\n";
        echo "Please add these routes manually:\n";
        echo $routesToAdd;
    }
} else {
    echo "✓ Routes already exist\n";
}

// Also fix the controller store method to use correct route
$controllerFile = app_path('Http/Controllers/SuratController.php');
$controllerContent = file_get_contents($controllerFile);

// Fix redirect after store
$controllerContent = str_replace(
    "return redirect()->route('staff.surat.show', \$surat->id)",
    "return redirect()->route('surat.show', \$surat->id)",
    $controllerContent
);

// Or if using named route that doesn't exist
$controllerContent = str_replace(
    "route('staff.surat.show'",
    "route('surat.show'",
    $controllerContent
);

file_put_contents($controllerFile, $controllerContent);

echo "\n✓ Fixed controller redirects\n";

// Create quick fix for immediate use
echo "\n=== QUICK FIX ===\n";
echo "If you need immediate fix without adding routes, ";
echo "change the redirect in SuratController store() method from:\n";
echo "  return redirect()->route('staff.surat.show', \$surat->id)\n";
echo "To:\n";
echo "  return redirect()->route('surat.show', \$surat->id)\n";
echo "Or:\n";
echo "  return redirect('/surat/' . \$surat->id)\n";

echo "\n=== DONE ===\n";