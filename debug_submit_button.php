<?php
// debug_submit_button.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG SUBMIT BUTTON ISSUE ===\n\n";

// 1. Check status flow
echo "1. STATUS FLOW CHECK:\n";
$statuses = \App\Models\StatusSurat::orderBy('urutan')->get();
foreach ($statuses as $status) {
    echo "   {$status->id}. {$status->nama_status} (kode: {$status->kode_status})\n";
}

// 2. Check what status allows submission
echo "\n2. CHECKING DRAFT STATUS:\n";
$draftStatus = \App\Models\StatusSurat::where('kode_status', 'draft')->first();
if ($draftStatus) {
    echo "   Draft status ID: {$draftStatus->id}\n";
    echo "   Draft status name: {$draftStatus->nama_status}\n";
}

// 3. Check view files for submit button
echo "\n3. CHECKING VIEW FILES FOR SUBMIT BUTTON:\n";

$viewsToCheck = [
    'surat/show.blade.php' => 'Detail view',
    'staff/surat/show.blade.php' => 'Staff detail view',
    'staff/surat/index.blade.php' => 'Staff index view'
];

foreach ($viewsToCheck as $file => $desc) {
    $fullPath = resource_path('views/' . $file);
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        echo "\n   $desc ($file):\n";
        
        // Check for submit/kirim button
        if (preg_match('/(submit|kirim|ajukan)/i', $content, $matches)) {
            echo "   ✓ Found submit-related text\n";
            
            // Extract the button/link code
            if (preg_match('/<(button|a)[^>]*(submit|kirim|ajukan)[^>]*>/i', $content, $buttonMatch)) {
                echo "   Button code: " . substr($buttonMatch[0], 0, 100) . "...\n";
            }
        } else {
            echo "   ✗ No submit button found\n";
        }
        
        // Check for status conditions
        if (preg_match('/@if.*draft.*/', $content)) {
            echo "   ✓ Has draft status condition\n";
        }
    } else {
        echo "   File not found: $file\n";
    }
}

// 4. Check controller for submit logic
echo "\n4. CHECKING CONTROLLER FOR SUBMIT LOGIC:\n";
$controllerFile = app_path('Http/Controllers/SuratController.php');
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    // Look for submit/ajukan method
    if (preg_match('/function\s+(submit|ajukan|kirim)[^{]*{/i', $content, $match)) {
        echo "   ✓ Found method: " . $match[0] . "\n";
    } else {
        echo "   ✗ No submit method found in controller\n";
    }
}

// 5. Check routes for submit
echo "\n5. CHECKING ROUTES:\n";
$routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(function ($route) {
    $uri = $route->uri();
    $name = $route->getName() ?? '';
    return str_contains($uri, 'submit') || str_contains($uri, 'ajukan') || str_contains($name, 'submit');
});

if ($routes->count() > 0) {
    foreach ($routes as $route) {
        echo "   - " . $route->getName() . ": " . $route->uri() . "\n";
    }
} else {
    echo "   ✗ No submit routes found\n";
}

// 6. Sample surat to check status
echo "\n6. SAMPLE SURAT CHECK:\n";
$surat = \App\Models\Surat::latest()->first();
if ($surat) {
    echo "   Latest surat ID: {$surat->id}\n";
    echo "   Status: " . ($surat->currentStatus->nama_status ?? 'No status') . "\n";
    echo "   Status code: " . ($surat->currentStatus->kode_status ?? 'No code') . "\n";
    echo "   Created by: " . $surat->created_by . "\n";
}

// 7. Expected flow
echo "\n7. EXPECTED FLOW:\n";
echo "   1. Create surat → Status: Draft\n";
echo "   2. Submit/Kirim → Status: Diajukan/Review Kaprodi\n";
echo "   3. Kaprodi approve → Status: Disetujui Kaprodi\n";
echo "   4. Forward to Fakultas → Status: Diproses Fakultas\n";

echo "\n=== RECOMMENDATION ===\n";
echo "Need to add Submit/Kirim button for draft surat that:\n";
echo "1. Shows only when status = 'draft'\n";
echo "2. Shows only for surat creator\n";
echo "3. Changes status from 'draft' to 'diajukan' or 'review_kaprodi'\n";

echo "\n=== END DEBUG ===\n";