<?php
// debug_view_styles.php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== VIEW STYLING DEBUG ===\n\n";

// Find all blade files with tables
$viewPath = resource_path('views');
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewPath));
$bladeFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && str_contains($file->getBasename(), '.blade.php')) {
        $content = file_get_contents($file->getPathname());
        if (str_contains($content, '<table') || str_contains($content, 'table-')) {
            $relativePath = str_replace([$viewPath . DIRECTORY_SEPARATOR, '.blade.php', DIRECTORY_SEPARATOR], ['', '', '.'], $file->getFilename());
            $bladeFiles[] = [
                'file' => $file->getPathname(),
                'view' => str_replace($viewPath . DIRECTORY_SEPARATOR, '', str_replace('.blade.php', '', str_replace(DIRECTORY_SEPARATOR, '.', $file->getPathname()))),
                'content' => $content
            ];
        }
    }
}

echo "Found " . count($bladeFiles) . " blade files with tables:\n";

// Analyze table styling patterns
$tableClasses = [];
$cardClasses = [];
$buttonClasses = [];
$badgeClasses = [];

foreach ($bladeFiles as $file) {
    echo "\n--- " . basename($file['file']) . " ---\n";
    
    // Extract table classes
    preg_match_all('/<table[^>]*class="([^"]*)"/', $file['content'], $matches);
    if (!empty($matches[1])) {
        echo "Table classes: " . implode(', ', $matches[1]) . "\n";
        $tableClasses = array_merge($tableClasses, $matches[1]);
    }
    
    // Extract card classes
    preg_match_all('/<div[^>]*class="card[^"]*"/', $file['content'], $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            preg_match('/class="([^"]*)"/', $match, $classMatch);
            if (!empty($classMatch[1])) {
                $cardClasses[] = $classMatch[1];
            }
        }
    }
    
    // Extract button classes for actions
    preg_match_all('/<a[^>]*class="btn[^"]*"/', $file['content'], $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            preg_match('/class="([^"]*)"/', $match, $classMatch);
            if (!empty($classMatch[1]) && str_contains($classMatch[1], 'btn-')) {
                $buttonClasses[] = $classMatch[1];
            }
        }
    }
    
    // Extract badge/status classes
    preg_match_all('/<span[^>]*class="badge[^"]*"/', $file['content'], $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            preg_match('/class="([^"]*)"/', $match, $classMatch);
            if (!empty($classMatch[1])) {
                $badgeClasses[] = $classMatch[1];
            }
        }
    }
}

// Get most common patterns
echo "\n=== MOST COMMON STYLING PATTERNS ===\n";

$tableClassCounts = array_count_values($tableClasses);
arsort($tableClassCounts);
echo "\nTable Classes:\n";
foreach (array_slice($tableClassCounts, 0, 3) as $class => $count) {
    echo "  - \"$class\" (used $count times)\n";
}

$cardClassCounts = array_count_values($cardClasses);
arsort($cardClassCounts);
echo "\nCard Classes:\n";
foreach (array_slice($cardClassCounts, 0, 3) as $class => $count) {
    echo "  - \"$class\" (used $count times)\n";
}

$buttonClassCounts = array_count_values($buttonClasses);
arsort($buttonClassCounts);
echo "\nButton Classes (Actions):\n";
foreach (array_slice($buttonClassCounts, 0, 5) as $class => $count) {
    echo "  - \"$class\" (used $count times)\n";
}

$badgeClassCounts = array_count_values($badgeClasses);
arsort($badgeClassCounts);
echo "\nBadge Classes (Status):\n";
foreach (array_slice($badgeClassCounts, 0, 5) as $class => $count) {
    echo "  - \"$class\" (used $count times)\n";
}

// Check for DataTables usage
echo "\n=== DATATABLES CHECK ===\n";
$hasDataTables = false;
foreach ($bladeFiles as $file) {
    if (str_contains($file['content'], 'DataTable') || str_contains($file['content'], 'dataTable')) {
        echo "DataTables found in: " . basename($file['file']) . "\n";
        $hasDataTables = true;
        
        // Extract DataTable initialization
        preg_match('/\$\([\'"]#([^\'"]+)[\'"]\)\.DataTable\([^}]*\}/s', $file['content'], $match);
        if (!empty($match[0])) {
            echo "DataTable config snippet:\n";
            echo substr($match[0], 0, 200) . "...\n";
        }
    }
}

// Check for icon usage
echo "\n=== ICON USAGE ===\n";
$iconPatterns = [
    'FontAwesome' => ['<i class="fa', '<i class="fas', '<i class="far', '<i class="fab'],
    'Bootstrap Icons' => ['<i class="bi-', '<i class="bi '],
    'Material Icons' => ['<i class="material-icons', '<span class="material-icons'],
];

foreach ($iconPatterns as $library => $patterns) {
    foreach ($bladeFiles as $file) {
        foreach ($patterns as $pattern) {
            if (str_contains($file['content'], $pattern)) {
                echo "$library found in: " . basename($file['file']) . "\n";
                break 2;
            }
        }
    }
}

// Check specific surat views for reference
echo "\n=== SURAT VIEWS ANALYSIS ===\n";
$suratViews = ['surat.index', 'kaprodi.surat.index', 'admin.surat.index'];
foreach ($suratViews as $viewName) {
    if (view()->exists($viewName)) {
        echo "Found: $viewName\n";
        $viewFile = str_replace('.', '/', $viewName) . '.blade.php';
        $fullPath = resource_path('views/' . $viewFile);
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
            
            // Extract key styling elements
            preg_match('/<table[^>]*class="([^"]*)"/', $content, $tableMatch);
            preg_match('/<div[^>]*class="card[^"]*"/', $content, $cardMatch);
            
            if (!empty($tableMatch[1])) {
                echo "  Table class: " . $tableMatch[1] . "\n";
            }
            if (!empty($cardMatch[0])) {
                preg_match('/class="([^"]*)"/', $cardMatch[0], $classMatch);
                echo "  Card class: " . $classMatch[1] . "\n";
            }
        }
    }
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "Based on the analysis, use these classes for consistency:\n";
if (!empty($tableClassCounts)) {
    $mostCommonTable = array_key_first($tableClassCounts);
    echo "- Table: \"$mostCommonTable\"\n";
}
if (!empty($cardClassCounts)) {
    $mostCommonCard = array_key_first($cardClassCounts);
    echo "- Card wrapper: \"$mostCommonCard\"\n";
}
if ($hasDataTables) {
    echo "- Use DataTables for table functionality\n";
}
echo "- Action buttons: btn btn-sm with btn-info/btn-warning/btn-danger\n";
echo "- Status badges: badge with appropriate color classes\n";

echo "\n=== END DEBUG ===\n";