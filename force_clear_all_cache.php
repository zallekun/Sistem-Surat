<?php
// force_clear_all_cache.php

echo "=== FORCE CLEARING ALL CACHE ===\n\n";

// 1. Clear bootstrap cache
echo "1. CLEARING BOOTSTRAP CACHE\n";
$bootstrapCache = 'bootstrap/cache';
if (is_dir($bootstrapCache)) {
    $files = glob($bootstrapCache . '/*.php');
    foreach ($files as $file) {
        if (basename($file) !== '.gitignore') {
            unlink($file);
            echo "   Deleted: " . basename($file) . "\n";
        }
    }
}

// 2. Clear route cache specifically
echo "\n2. CLEARING ROUTE CACHE FILES\n";
$cacheFiles = [
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/routes.php',
    'storage/framework/cache/routes.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "   Deleted: $file\n";
    }
}

// 3. Clear framework cache
echo "\n3. CLEARING FRAMEWORK CACHE\n";
$frameworkCache = 'storage/framework/cache/data';
if (is_dir($frameworkCache)) {
    $files = glob($frameworkCache . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "   Cleared framework cache\n";
}

// 4. Double check routes file
echo "\n4. VERIFYING ROUTES FILE\n";
$routeFile = 'routes/web.php';
$content = file_get_contents($routeFile);

if (strpos($content, 'staffIndex') !== false) {
    echo "   WARNING: 'staffIndex' still found in routes/web.php!\n";
    echo "   Replacing again...\n";
    
    $content = str_replace('staffIndex', 'index', $content);
    file_put_contents($routeFile, $content);
    echo "   ✓ Fixed\n";
} else {
    echo "   ✓ No 'staffIndex' found in routes/web.php\n";
}

echo "\n=== DONE ===\n";
echo "Now run these commands:\n";
echo "1. php artisan route:clear\n";
echo "2. php artisan cache:clear\n";
echo "3. php artisan config:clear\n";
echo "4. Restart Laragon (or at least restart Apache/Nginx)\n";