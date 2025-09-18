<?php
// fix_duplicate_edit_method.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING DUPLICATE EDIT METHOD ===\n\n";

$controllerFile = app_path('Http/Controllers/SuratController.php');
$content = file_get_contents($controllerFile);

// Backup
file_put_contents($controllerFile . '.backup_' . date('YmdHis'), $content);

// Find all edit methods
preg_match_all('/public\s+function\s+edit\s*\([^)]*\)\s*\{/i', $content, $matches, PREG_OFFSET_CAPTURE);

echo "Found " . count($matches[0]) . " edit methods\n";

if (count($matches[0]) > 1) {
    echo "Removing duplicate edit methods...\n";
    
    // Keep only the first edit method
    // Find the complete first method
    $firstMethodStart = $matches[0][0][1];
    
    // Find the end of first method (matching closing brace)
    $braceCount = 0;
    $inMethod = false;
    $firstMethodEnd = 0;
    
    for ($i = $firstMethodStart; $i < strlen($content); $i++) {
        if ($content[$i] === '{') {
            $braceCount++;
            $inMethod = true;
        } elseif ($content[$i] === '}') {
            $braceCount--;
            if ($inMethod && $braceCount === 0) {
                $firstMethodEnd = $i + 1;
                break;
            }
        }
    }
    
    $firstMethod = substr($content, $firstMethodStart, $firstMethodEnd - $firstMethodStart);
    echo "First edit method preserved (line ~" . substr_count(substr($content, 0, $firstMethodStart), "\n") . ")\n";
    
    // Remove all other edit methods
    for ($i = count($matches[0]) - 1; $i >= 1; $i--) {
        $methodStart = $matches[0][$i][1];
        
        // Find the end of this method
        $braceCount = 0;
        $inMethod = false;
        $methodEnd = 0;
        
        for ($j = $methodStart; $j < strlen($content); $j++) {
            if ($content[$j] === '{') {
                $braceCount++;
                $inMethod = true;
            } elseif ($content[$j] === '}') {
                $braceCount--;
                if ($inMethod && $braceCount === 0) {
                    $methodEnd = $j + 1;
                    break;
                }
            }
        }
        
        // Remove this duplicate method
        $content = substr($content, 0, $methodStart) . substr($content, $methodEnd);
        echo "Removed duplicate edit method at line ~" . substr_count(substr($content, 0, $methodStart), "\n") . "\n";
    }
    
    file_put_contents($controllerFile, $content);
    echo "✓ Fixed duplicate methods\n";
} else {
    echo "✓ No duplicate edit methods found\n";
}

// Verify the remaining edit method is correct
if (preg_match('/public\s+function\s+edit\s*\(([^)]*)\)\s*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}/s', $content, $match)) {
    $params = trim($match[1]);
    $body = $match[2];
    
    echo "\nEdit method signature: edit($params)\n";
    
    // Check if it returns the correct view
    if (str_contains($body, "view('staff.surat.edit'") || str_contains($body, 'view("staff.surat.edit"')) {
        echo "✓ Returns staff.surat.edit view\n";
    } else {
        echo "⚠ May not return the correct view\n";
    }
    
    // Check if it passes $surat
    if (str_contains($body, "compact('surat'")) {
        echo "✓ Passes \$surat to view\n";
    } else {
        echo "⚠ May not pass \$surat to view\n";
    }
}

echo "\n=== DONE ===\n";
echo "Duplicate edit method removed. Try accessing the page again.\n";