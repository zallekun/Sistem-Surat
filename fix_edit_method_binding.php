<?php
// fix_edit_method_binding.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING EDIT METHOD BINDING ISSUE ===\n\n";

$controllerFile = app_path('Http/Controllers/SuratController.php');
$content = file_get_contents($controllerFile);

// Backup
file_put_contents($controllerFile . '.backup_binding', $content);

// Fix the edit method to use ID instead of model binding
$oldEditPattern = '/public\s+function\s+edit\s*\(\s*Surat\s+\$surat\s*\)/';
$newEdit = 'public function edit($id)';

if (preg_match($oldEditPattern, $content)) {
    echo "Found model binding in edit method, changing to ID parameter...\n";
    
    // Replace the method signature
    $content = preg_replace($oldEditPattern, $newEdit, $content);
    
    // Now fix the method body to fetch the surat
    $content = preg_replace(
        '/(public\s+function\s+edit\s*\(\s*\$id\s*\)\s*\{)/',
        "$1\n        \$surat = Surat::findOrFail(\$id);",
        $content
    );
    
    file_put_contents($controllerFile, $content);
    echo "✓ Fixed edit method to use ID parameter\n";
}

// Also check update method
if (preg_match('/public\s+function\s+update\s*\([^)]*Surat\s+\$surat[^)]*\)/', $content)) {
    echo "\nFound model binding in update method, fixing...\n";
    
    // Fix update method too
    $content = preg_replace(
        '/public\s+function\s+update\s*\(([^,)]+),\s*Surat\s+\$surat\s*\)/',
        'public function update($1, $id)',
        $content
    );
    
    // Add fetching surat in update method
    $content = preg_replace(
        '/(public\s+function\s+update\s*\([^)]+,\s*\$id\s*\)\s*\{)/',
        "$1\n        \$surat = Surat::findOrFail(\$id);",
        $content
    );
    
    file_put_contents($controllerFile, $content);
    echo "✓ Fixed update method to use ID parameter\n";
}

// Verify the changes
echo "\n=== VERIFYING CHANGES ===\n";
$newContent = file_get_contents($controllerFile);

// Check edit method
if (preg_match('/public\s+function\s+edit\s*\(([^)]*)\)/', $newContent, $match)) {
    echo "Edit method signature: edit(" . $match[1] . ")\n";
}

// Check update method  
if (preg_match('/public\s+function\s+update\s*\(([^)]*)\)/', $newContent, $match)) {
    echo "Update method signature: update(" . $match[1] . ")\n";
}

echo "\n=== DONE ===\n";
echo "Model binding removed, using ID parameters instead.\n";
echo "Clear cache and try again:\n";
echo "php artisan route:clear\n";
echo "php artisan view:clear\n";