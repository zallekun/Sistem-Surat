<?php
// debug_table_styles.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG TABLE STYLES IN SYSTEM ===\n\n";

// Find all blade files with tables
$viewPath = resource_path('views');
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewPath));

$tableStyles = [];
$viewsWithTables = [];

foreach ($iterator as $file) {
    if ($file->isFile() && str_contains($file->getBasename(), '.blade.php')) {
        $content = file_get_contents($file->getPathname());
        
        // Check if file contains table
        if (preg_match('/<table[^>]*>/', $content)) {
            $relativePath = str_replace([$viewPath . DIRECTORY_SEPARATOR, '.blade.php'], ['', ''], $file->getPathname());
            $viewName = str_replace(DIRECTORY_SEPARATOR, '.', $relativePath);
            
            // Extract table class
            preg_match_all('/<table[^>]*class=["\']([^"\']*)["\']/', $content, $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $tableClass) {
                    $tableStyles[$tableClass] = ($tableStyles[$tableClass] ?? 0) + 1;
                    $viewsWithTables[$viewName][] = $tableClass;
                }
            } else {
                $viewsWithTables[$viewName][] = 'no-class';
            }
        }
    }
}

echo "1. TABLE STYLES FOUND:\n";
echo str_repeat("-", 80) . "\n";
arsort($tableStyles);
foreach ($tableStyles as $style => $count) {
    echo "   " . str_pad($style, 60) . " (used $count times)\n";
}

echo "\n2. VIEWS WITH TABLES:\n";
echo str_repeat("-", 80) . "\n";
foreach ($viewsWithTables as $view => $styles) {
    echo "   $view\n";
    foreach (array_unique($styles) as $style) {
        echo "      - $style\n";
    }
}

// Analyze specific important views
echo "\n3. KEY SURAT VIEWS ANALYSIS:\n";
echo str_repeat("-", 80) . "\n";

$keyViews = [
    'staff.surat.index',
    'kaprodi.surat.index',
    'kaprodi.surat.approval',
    'fakultas.surat.index',
    'admin.surat.index'
];

foreach ($keyViews as $viewName) {
    if (view()->exists($viewName)) {
        $viewPath = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        if (file_exists($viewPath)) {
            $content = file_get_contents($viewPath);
            
            echo "\n   $viewName:\n";
            
            // Check table style
            if (preg_match('/<table[^>]*class=["\']([^"\']*)["\']/', $content, $match)) {
                echo "   Table class: {$match[1]}\n";
            }
            
            // Check if using Tailwind or Bootstrap
            $usingTailwind = false;
            $usingBootstrap = false;
            
            if (str_contains($match[1] ?? '', 'divide-y') || str_contains($match[1] ?? '', 'min-w-full')) {
                $usingTailwind = true;
            }
            if (str_contains($match[1] ?? '', 'table-striped') || str_contains($match[1] ?? '', 'table-bordered')) {
                $usingBootstrap = true;
            }
            
            echo "   Framework: ";
            if ($usingTailwind) echo "Tailwind CSS ";
            if ($usingBootstrap) echo "Bootstrap ";
            if (!$usingTailwind && !$usingBootstrap) echo "Custom/Unknown ";
            echo "\n";
            
            // Count table columns
            preg_match_all('/<th[^>]*>/', $content, $thMatches);
            echo "   Number of columns: " . count($thMatches[0]) . "\n";
            
            // Extract column headers
            preg_match_all('/<th[^>]*>([^<]*)<\/th>/', $content, $headerMatches);
            if (!empty($headerMatches[1])) {
                echo "   Column headers: " . implode(' | ', array_map('trim', $headerMatches[1])) . "\n";
            }
        }
    }
}

// Check CSS framework usage
echo "\n4. CSS FRAMEWORK DETECTION:\n";
echo str_repeat("-", 80) . "\n";

$layoutFile = resource_path('views/layouts/app.blade.php');
if (file_exists($layoutFile)) {
    $layoutContent = file_get_contents($layoutFile);
    
    if (str_contains($layoutContent, 'tailwindcss') || str_contains($layoutContent, 'tailwind')) {
        echo "   ✓ Tailwind CSS is included\n";
    }
    if (str_contains($layoutContent, 'bootstrap') || str_contains($layoutContent, 'bootstrap.min.css')) {
        echo "   ✓ Bootstrap is included\n";
    }
    if (str_contains($layoutContent, '@vite') || str_contains($layoutContent, 'app.css')) {
        echo "   ✓ Custom CSS via Vite/Laravel Mix\n";
    }
}

echo "\n5. RECOMMENDED STANDARD:\n";
echo str_repeat("-", 80) . "\n";

// Determine most common style
$mostCommon = array_key_first($tableStyles);
if (str_contains($mostCommon, 'divide-y')) {
    echo "   Current majority: Tailwind CSS\n";
    echo "   Recommended standard table class:\n";
    echo "   'min-w-full divide-y divide-gray-200'\n";
} elseif (str_contains($mostCommon, 'table')) {
    echo "   Current majority: Bootstrap\n";
    echo "   Recommended standard table class:\n";
    echo "   'table table-striped table-hover'\n";
} else {
    echo "   Mixed styles detected\n";
    echo "   Recommend choosing either:\n";
    echo "   - Tailwind: 'min-w-full divide-y divide-gray-200'\n";
    echo "   - Bootstrap: 'table table-striped table-hover'\n";
}

echo "\n6. STANDARD COLUMN STRUCTURE:\n";
echo str_repeat("-", 80) . "\n";
echo "   Recommended columns for surat tables:\n";
echo "   1. # (width: 5%)\n";
echo "   2. Nomor Surat (width: 15%)\n";
echo "   3. Perihal (width: 25%)\n";
echo "   4. Jenis Surat (width: 10%)\n";
echo "   5. Prodi (width: 10%)\n";
echo "   6. Dibuat Oleh (width: 12%)\n";
echo "   7. Tanggal (width: 10%)\n";
echo "   8. Status (width: 8%)\n";
echo "   9. Aksi (width: 5%)\n";

echo "\n=== END DEBUG ===\n";