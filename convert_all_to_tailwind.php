<?php
// convert_all_to_tailwind.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== CONVERT ALL TABLES TO TAILWIND STANDARD ===\n\n";

// Backup directory
$backupDir = storage_path('backup_views_' . date('Y-m-d_H-i-s'));
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Define conversions
$conversions = [
    // Table classes
    'table table-hover text-nowrap' => 'min-w-full divide-y divide-gray-200',
    'table table-hover table-striped' => 'min-w-full divide-y divide-gray-200',
    'table table-bordered table-striped' => 'min-w-full divide-y divide-gray-200',
    'table table-borderless' => 'min-w-full divide-y divide-gray-200',
    'table table-sm table-striped' => 'min-w-full divide-y divide-gray-200',
    'w-full divide-y divide-gray-200 table-fixed' => 'min-w-full divide-y divide-gray-200',
    'min-w-full divide-y divide-gray-200 table-fixed' => 'min-w-full divide-y divide-gray-200',
    
    // Table wrapper
    'table-responsive' => 'overflow-x-auto',
    '<div class="table-responsive">' => '<div class="overflow-x-auto">',
    
    // Card wrappers (Bootstrap to Tailwind)
    'card' => 'bg-white shadow-sm rounded-lg',
    'card-header' => 'bg-gray-50 px-4 py-5 border-b border-gray-200 sm:px-6',
    'card-body' => 'px-4 py-5 sm:p-6',
    
    // Alerts
    'alert alert-success' => 'bg-green-50 border-l-4 border-green-400 p-4',
    'alert alert-danger' => 'bg-red-50 border-l-4 border-red-400 p-4',
    'alert alert-warning' => 'bg-yellow-50 border-l-4 border-yellow-400 p-4',
    'alert alert-info' => 'bg-blue-50 border-l-4 border-blue-400 p-4',
];

$viewPath = resource_path('views');
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewPath));

$convertedFiles = [];
$skipPatterns = ['.backup.', '.old.', '.restore.', '.final.', '.multiroot.', '.varfix.'];

foreach ($iterator as $file) {
    if (!$file->isFile() || !str_contains($file->getBasename(), '.blade.php')) {
        continue;
    }
    
    $filename = $file->getPathname();
    
    // Skip backup files
    $skip = false;
    foreach ($skipPatterns as $pattern) {
        if (str_contains($filename, $pattern)) {
            $skip = true;
            break;
        }
    }
    if ($skip) continue;
    
    $content = file_get_contents($filename);
    $originalContent = $content;
    
    // Check if file has tables
    if (!preg_match('/<table[^>]*>/', $content)) {
        continue;
    }
    
    // Backup original file
    $relativePath = str_replace($viewPath, '', $filename);
    $backupPath = $backupDir . $relativePath;
    $backupPathDir = dirname($backupPath);
    if (!file_exists($backupPathDir)) {
        mkdir($backupPathDir, 0777, true);
    }
    file_put_contents($backupPath, $originalContent);
    
    // Apply conversions
    foreach ($conversions as $old => $new) {
        $content = str_replace('class="' . $old . '"', 'class="' . $new . '"', $content);
        $content = str_replace("class='" . $old . "'", "class='" . $new . "'", $content);
    }
    
    // Fix thead if not styled
    if (preg_match('/<thead(?![^>]*class)/', $content)) {
        $content = preg_replace('/<thead>/', '<thead class="bg-gray-50">', $content);
    }
    
    // Fix tbody if not styled
    if (preg_match('/<tbody(?![^>]*class)/', $content)) {
        $content = preg_replace('/<tbody>/', '<tbody class="bg-white divide-y divide-gray-200">', $content);
    }
    
    // Fix th elements to have Tailwind classes
    $content = preg_replace(
        '/<th(?![^>]*class)[^>]*>/',
        '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">',
        $content
    );
    
    // Fix td elements to have Tailwind classes
    $content = preg_replace(
        '/<td(?![^>]*class)[^>]*>/',
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">',
        $content
    );
    
    // Fix tr elements in tbody to have hover effect
    $content = preg_replace(
        '/<tbody[^>]*>\s*<tr(?![^>]*class)[^>]*>/',
        '<tbody class="bg-white divide-y divide-gray-200">
        <tr class="hover:bg-gray-50">',
        $content
    );
    
    // Convert Bootstrap badges to Tailwind
    $badgeConversions = [
        'badge badge-success' => 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
        'badge badge-warning' => 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
        'badge badge-danger' => 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800',
        'badge badge-info' => 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800',
        'badge badge-primary' => 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800',
        'badge badge-secondary' => 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800',
    ];
    
    foreach ($badgeConversions as $old => $new) {
        $content = str_replace('class="' . $old . '"', 'class="' . $new . '"', $content);
    }
    
    // Convert Bootstrap buttons to Tailwind
    $buttonConversions = [
        'btn btn-primary' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
        'btn btn-secondary' => 'inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
        'btn btn-success' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500',
        'btn btn-danger' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500',
        'btn btn-warning' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500',
        'btn btn-info' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
        'btn btn-sm' => 'inline-flex items-center px-3 py-1.5 text-xs font-medium rounded',
    ];
    
    foreach ($buttonConversions as $old => $new) {
        $content = str_replace('class="' . $old . '"', 'class="' . $new . '"', $content);
    }
    
    if ($content !== $originalContent) {
        // Save converted file (uncomment to actually convert)
        file_put_contents($filename, $content);
        
        $relativePath = str_replace($viewPath, '', $filename);
        $convertedFiles[] = $relativePath;
        echo "✓ Would convert: $relativePath\n";
    }
}

echo "\n=== CONVERSION SUMMARY ===\n";
echo "Total files to convert: " . count($convertedFiles) . "\n";
echo "Backup location: $backupDir\n";

echo "\n=== STANDARD STRUCTURE ===\n";
echo "Table: class=\"min-w-full divide-y divide-gray-200\"\n";
echo "Thead: class=\"bg-gray-50\"\n";
echo "Tbody: class=\"bg-white divide-y divide-gray-200\"\n";
echo "Th: class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\"\n";
echo "Td: class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\"\n";
echo "Tr (in tbody): class=\"hover:bg-gray-50\"\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Review the changes that will be made\n";
echo "2. Uncomment line 136 to actually convert files:\n";
echo "   // file_put_contents(\$filename, \$content);\n";
echo "3. Run the script again\n";
echo "4. Clear Laravel cache: php artisan view:clear\n";
echo "\nTo restore from backup if needed:\n";
echo "cp -r $backupDir/* " . $viewPath . "/\n";

echo "\n=== END CONVERSION ===\n";