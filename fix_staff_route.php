<?php
// fix_staff_route.php

require_once __DIR__ . '/vendor/autoload.php';

echo "=== FIXING STAFF ROUTE ===\n\n";

$routeFile = 'routes/web.php';
$content = file_get_contents($routeFile);
$backup = file_put_contents($routeFile . '.backup_' . date('YmdHis'), $content);

echo "1. BACKING UP routes/web.php\n";
echo "   ✓ Backup created\n\n";

echo "2. FIXING LINE 53\n";
echo "   Current: Route::get('/staff/surat', [App\\Http\\Controllers\\SuratController::class, 'staffIndex'])\n";
echo "   Fixed to: Route::get('/staff/surat', [App\\Http\\Controllers\\SuratController::class, 'index'])\n\n";

// Replace staffIndex with index
$content = str_replace(
    "SuratController::class, 'staffIndex']",
    "SuratController::class, 'index']",
    $content
);

file_put_contents($routeFile, $content);

echo "3. VERIFYING FIX\n";
$lines = file($routeFile);
echo "   Line 53: " . trim($lines[52]) . "\n\n";

echo "=== DONE ===\n";
echo "The route now calls index() instead of staffIndex()\n";
echo "Run: php artisan route:clear\n";