<?php
// debug_edit_route_issue.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG EDIT ROUTE ISSUE ===\n\n";

// 1. Check the edit form in the view
echo "1. CHECKING EDIT VIEW FILE:\n";
$editFile = 'resources/views/staff/surat/edit.blade.php';
if (file_exists($editFile)) {
    $content = file_get_contents($editFile);
    
    // Find all form tags
    preg_match_all('/<form[^>]*>/', $content, $formMatches);
    foreach ($formMatches[0] as $idx => $form) {
        echo "   Form #" . ($idx + 1) . ": " . htmlspecialchars($form) . "\n";
    }
    
    // Check for route calls
    preg_match_all("/route\(['\"]([^'\"]+)['\"]([^)]*)\)/", $content, $routeMatches);
    echo "\n   Routes used in view:\n";
    foreach ($routeMatches[1] as $idx => $route) {
        $params = $routeMatches[2][$idx];
        echo "   - $route" . ($params ? " with params: $params" : " (NO PARAMS!)") . "\n";
    }
    
    // Check if $surat variable is used
    if (str_contains($content, '$surat->id')) {
        echo "\n   ✓ View uses \$surat->id\n";
    } else {
        echo "\n   ✗ View doesn't use \$surat->id\n";
    }
    
    // Check the exact line causing the error
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (str_contains($line, "staff.surat.update")) {
            echo "\n   Line " . ($lineNum + 1) . " contains staff.surat.update:\n";
            echo "   " . trim($line) . "\n";
        }
    }
}

// 2. Check controller edit method
echo "\n2. CHECKING CONTROLLER:\n";
$controllerFile = app_path('Http/Controllers/SuratController.php');
$controllerContent = file_get_contents($controllerFile);

// Find edit method
if (preg_match('/public\s+function\s+edit\s*\(([^)]*)\)\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/s', $controllerContent, $match)) {
    $params = trim($match[1]);
    echo "   Edit method signature: edit($params)\n";
    
    // Check what it returns
    if (preg_match("/return\s+view\(['\"]([^'\"]+)['\"]\s*,\s*([^)]+)\)/", $match[2], $viewMatch)) {
        $viewName = $viewMatch[1];
        $viewData = $viewMatch[2];
        echo "   Returns view: $viewName\n";
        echo "   With data: $viewData\n";
        
        if (!str_contains($viewData, 'surat')) {
            echo "   ✗ WARNING: Not passing 'surat' variable!\n";
        }
    }
}

// 3. Test with actual data
echo "\n3. TESTING WITH ACTUAL DATA:\n";
$testSurat = \App\Models\Surat::where('status_id', 1)->first();
if ($testSurat) {
    echo "   Test surat ID: {$testSurat->id}\n";
    
    // Test route generation
    try {
        $updateUrl = route('staff.surat.update', $testSurat->id);
        echo "   ✓ Route generation works: $updateUrl\n";
    } catch (\Exception $e) {
        echo "   ✗ Route generation failed: " . $e->getMessage() . "\n";
    }
}

// 4. Create the fix
echo "\n4. CREATING FIX:\n";

// Fix the form in edit view
$content = file_get_contents($editFile);

// Replace any form with staff.surat.update that doesn't have $surat->id
$content = preg_replace(
    '/<form([^>]*)action="[^"]*route\([\'"]staff\.surat\.update[\'"][^\)]*\)"([^>]*)>/',
    '<form$1action="{{ route(\'staff.surat.update\', $surat->id) }}"$2>',
    $content
);

// Also check for @method directive
if (!str_contains($content, "@method('PUT')") && !str_contains($content, '@method("PUT")')) {
    // Add @method after @csrf
    $content = str_replace('@csrf', "@csrf\n    @method('PUT')", $content);
}

file_put_contents($editFile, $content);
echo "   ✓ Fixed form action route\n";
echo "   ✓ Added @method('PUT') directive\n";

// Also ensure controller passes $surat
$controllerContent = file_get_contents($controllerFile);
if (!str_contains($controllerContent, "compact('surat'")) {
    // Fix the edit method to pass surat
    $controllerContent = preg_replace(
        "/(public\s+function\s+edit\s*\([^)]*\)\s*\{[^}]+)return\s+view\(['\"]([^'\"]+)['\"]([^)]*)\);/s",
        "$1" . '$surat = Surat::findOrFail($id);' . "\n        return view('$2', compact('surat'));",
        $controllerContent
    );
    file_put_contents($controllerFile, $controllerContent);
    echo "   ✓ Fixed controller to pass \$surat\n";
}

echo "\n=== DONE ===\n";
echo "The issue should be fixed. Clear cache and try again:\n";
echo "php artisan view:clear\n";
echo "php artisan cache:clear\n";