<?php
// check-fakultas-routes.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

echo "=== CHECKING FAKULTAS ROUTES ===\n\n";

$routes = Route::getRoutes();
$fakultasRoutes = [];

foreach ($routes as $route) {
    if (strpos($route->getName(), 'fakultas') !== false) {
        $fakultasRoutes[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName()
        ];
    }
}

if (empty($fakultasRoutes)) {
    echo "❌ No fakultas routes found!\n";
} else {
    echo "✅ Found " . count($fakultasRoutes) . " fakultas routes:\n\n";
    
    echo str_pad("METHOD", 10) . str_pad("URI", 30) . str_pad("NAME", 30) . "ACTION\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($fakultasRoutes as $route) {
        echo str_pad($route['method'], 10);
        echo str_pad($route['uri'], 30);
        echo str_pad($route['name'], 30);
        echo $route['action'] . "\n";
    }
}

echo "\n=== EXPECTED ROUTES CHECK ===\n\n";

$expectedRoutes = [
    'fakultas.surat.index' => 'GET|HEAD fakultas/surat',
    'fakultas.surat.show' => 'GET|HEAD fakultas/surat/{surat}',
    'fakultas.surat.approve' => 'POST fakultas/surat/{surat}/approve',
    'fakultas.surat.reject' => 'POST fakultas/surat/{surat}/reject',
    'fakultas.surat.updateStatus' => 'PATCH fakultas/surat/{surat}/status',
];

foreach ($expectedRoutes as $name => $expected) {
    $exists = Route::has($name);
    if ($exists) {
        echo "✅ $name exists\n";
    } else {
        echo "❌ $name MISSING - Expected: $expected\n";
    }
}

echo "\n=== MIDDLEWARE CHECK ===\n\n";

foreach ($fakultasRoutes as $route) {
    $routeObj = Route::getRoutes()->getByName($route['name']);
    if ($routeObj) {
        $middleware = $routeObj->middleware();
        echo $route['name'] . ": " . implode(', ', $middleware) . "\n";
    }
}