<?php
// check_and_fix_edit_form.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING AND FIXING EDIT FORM ===\n\n";

// Check the edit view files
$editFiles = [
    'resources/views/surat/edit.blade.php',
    'resources/views/staff/surat/edit.blade.php'
];

foreach ($editFiles as $file) {
    if (file_exists($file)) {
        echo "Found: $file\n";
        $content = file_get_contents($file);
        
        // Find form tag
        if (preg_match('/<form[^>]*>/', $content, $matches)) {
            echo "Current form tag: " . $matches[0] . "\n";
            
            // Check if it's missing the ID parameter
            if (str_contains($matches[0], "route('staff.surat.update')") && !str_contains($matches[0], '$surat')) {
                echo "❌ Missing ID parameter in route\n";
                
                // Fix it
                $content = preg_replace(
                    "/route\('staff\.surat\.update'\)/",
                    "route('staff.surat.update', \$surat->id)",
                    $content
                );
                file_put_contents($file, $content);
                echo "✓ Fixed!\n";
                
            } elseif (str_contains($matches[0], "route('surat.update')") && !str_contains($matches[0], '$surat')) {
                echo "❌ Missing ID parameter in route\n";
                
                // Fix it
                $content = preg_replace(
                    "/route\('surat\.update'\)/",
                    "route('surat.update', \$surat->id)",
                    $content
                );
                file_put_contents($file, $content);
                echo "✓ Fixed!\n";
                
            } else {
                echo "✓ Form route looks OK or using different pattern\n";
            }
        } else {
            echo "⚠ No form tag found\n";
        }
        
        echo "---\n";
    }
}

// Now add submit button to show.blade.php
echo "\n=== ADDING SUBMIT BUTTON TO DETAIL VIEW ===\n";

$showFile = 'resources/views/staff/surat/show.blade.php';
if (file_exists($showFile)) {
    $content = file_get_contents($showFile);
    
    // Look for the card-footer or action section
    if (!str_contains($content, 'Kirim ke Kaprodi') && !str_contains($content, 'surat.submit')) {
        
        // Find where to insert - look for Edit button
        if (str_contains($content, 'route(\'surat.edit\'')) {
            $pattern = '/(<a[^>]*route\(\'surat\.edit\'[^>]*>.*?<\/a>)/s';
            
            if (preg_match($pattern, $content, $matches)) {
                $editButton = $matches[1];
                
                $submitButton = '
                        @if($surat->currentStatus->kode_status === \'draft\' && $surat->created_by === Auth::id())
                        <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline-block ml-2">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm(\'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?\')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Kirim ke Kaprodi
                            </button>
                        </form>
                        @endif';
                
                // Insert after edit button
                $content = str_replace($editButton, $editButton . $submitButton, $content);
                file_put_contents($showFile, $content);
                echo "✓ Added submit button to detail view\n";
            }
        } else {
            // Try to find a different location - maybe in card-footer
            if (str_contains($content, 'card-footer') || str_contains($content, 'flex justify-end')) {
                $pattern = '/(class="[^"]*flex[^"]*justify-end[^"]*"[^>]*>)/';
                
                if (preg_match($pattern, $content, $matches)) {
                    $divTag = $matches[1];
                    
                    $submitButton = $divTag . '
                        @if($surat->currentStatus->kode_status === \'draft\' && $surat->created_by === Auth::id())
                        <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm(\'Kirim surat ke Kaprodi untuk review?\')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 mr-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Kirim
                            </button>
                        </form>
                        @endif
                        ';
                    
                    $content = str_replace($divTag, $submitButton, $content);
                    file_put_contents($showFile, $content);
                    echo "✓ Added submit button to detail view (in flex container)\n";
                }
            }
        }
    } else {
        echo "✓ Submit button already exists in detail view\n";
    }
}

// Check what routes are actually being used
echo "\n=== ROUTES CHECK ===\n";
exec('php artisan route:list --name=surat', $output);
foreach ($output as $line) {
    if (str_contains($line, 'update') || str_contains($line, 'edit') || str_contains($line, 'submit')) {
        echo $line . "\n";
    }
}

echo "\n=== DONE ===\n";
echo "Try accessing edit page again. If still error, the issue might be in how the edit link is called.\n";