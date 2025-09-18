<?php
// fix_edit_link.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING EDIT LINK IN INDEX ===\n\n";

// Check the index file
$indexFile = 'resources/views/staff/surat/index.blade.php';
$content = file_get_contents($indexFile);

// Find edit link
if (preg_match('/route\([\'"]([^\'"]*\.edit)[\'"](?:,\s*([^\)]*))?\)/', $content, $matches)) {
    $routeName = $matches[1];
    $parameter = $matches[2] ?? '';
    
    echo "Current edit route: $routeName\n";
    echo "Parameter: " . ($parameter ?: "MISSING!") . "\n";
    
    if ($routeName === 'staff.surat.edit' && !$parameter) {
        echo "❌ Missing ID parameter\n";
        
        // Fix it
        $content = preg_replace(
            "/route\('staff\.surat\.edit'\)/",
            "route('staff.surat.edit', \$surat->id)",
            $content
        );
        file_put_contents($indexFile, $content);
        echo "✓ Fixed!\n";
    } elseif ($routeName === 'surat.edit' && !$parameter) {
        echo "❌ Missing ID parameter\n";
        
        $content = preg_replace(
            "/route\('surat\.edit'\)/",
            "route('surat.edit', \$surat->id)",
            $content
        );
        file_put_contents($indexFile, $content);
        echo "✓ Fixed!\n";
    } else {
        echo "✓ Edit link looks OK\n";
    }
}

// Also check for submit button visibility in detail view
echo "\n=== CHECKING SUBMIT BUTTON IN DETAIL ===\n";

$showFile = 'resources/views/staff/surat/show.blade.php';
if (file_exists($showFile)) {
    $content = file_get_contents($showFile);
    
    if (str_contains($content, 'surat.submit') || str_contains($content, 'Kirim')) {
        echo "✓ Submit button code exists\n";
        
        // Check if condition is correct
        if (str_contains($content, "currentStatus->kode_status === 'draft'")) {
            echo "✓ Has draft status check\n";
        } else {
            echo "⚠ Missing draft status check\n";
        }
        
        if (str_contains($content, 'created_by === Auth::id()')) {
            echo "✓ Has ownership check\n";
        } else {
            echo "⚠ Missing ownership check\n";
        }
    } else {
        echo "❌ Submit button not found\n";
        echo "Adding submit button...\n";
        
        // Find a good place to add it - after the Edit button
        if (str_contains($content, 'Edit Surat')) {
            $pattern = '/(Edit Surat.*?<\/a>)/s';
            if (preg_match($pattern, $content, $matches)) {
                $editSection = $matches[1];
                
                $submitButton = '
                    @if($surat->currentStatus->kode_status === \'draft\' && $surat->created_by === Auth::id())
                    <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline-block ml-2">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm(\'Kirim surat ke Kaprodi untuk review?\')"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Kirim ke Kaprodi
                        </button>
                    </form>
                    @endif';
                
                $content = str_replace($editSection, $editSection . $submitButton, $content);
                file_put_contents($showFile, $content);
                echo "✓ Added submit button\n";
            }
        }
    }
}

// Test with actual data
echo "\n=== TESTING WITH ACTUAL DATA ===\n";
$surat = \App\Models\Surat::where('status_id', 1)->first(); // Draft status
if ($surat) {
    echo "Draft surat found: ID {$surat->id}\n";
    echo "Created by: {$surat->created_by}\n";
    echo "Status: " . ($surat->currentStatus->kode_status ?? 'unknown') . "\n";
    
    // Test route generation
    try {
        $editUrl = route('surat.edit', $surat->id);
        echo "✓ Edit URL works: $editUrl\n";
    } catch (\Exception $e) {
        echo "❌ Error generating edit URL: " . $e->getMessage() . "\n";
    }
}

echo "\n=== DONE ===\n";