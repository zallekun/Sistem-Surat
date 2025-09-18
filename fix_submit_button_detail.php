<?php
// fix_submit_button_detail.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING SUBMIT BUTTON IN DETAIL VIEW ===\n\n";

$showFile = 'resources/views/staff/surat/show.blade.php';
if (!file_exists($showFile)) {
    $showFile = 'resources/views/surat/show.blade.php';
}

if (file_exists($showFile)) {
    $content = file_get_contents($showFile);
    
    // Backup
    $backup = $content;
    file_put_contents($showFile . '.backup', $backup);
    echo "Backup created: {$showFile}.backup\n";
    
    // Find the submit button and fix the condition
    if (str_contains($content, 'surat.submit')) {
        echo "Submit button found, fixing conditions...\n";
        
        // Fix the condition to check draft status properly
        $content = preg_replace(
            '/@if\s*\(\$surat->currentStatus\s*===\s*[\'"]draft[\'"]\s*&&/',
            '@if($surat->currentStatus->kode_status === \'draft\' &&',
            $content
        );
        
        // Also check for other variations
        $content = preg_replace(
            '/@if\s*\(\$surat->status\s*===\s*[\'"]draft[\'"]\s*&&/',
            '@if($surat->currentStatus->kode_status === \'draft\' &&',
            $content
        );
        
        file_put_contents($showFile, $content);
        echo "✓ Fixed status check condition\n";
    } else {
        echo "Submit button not found, adding it...\n";
        
        // Find where action buttons are (usually after card body or in a footer)
        // Look for Edit button and add Submit after it
        if (preg_match('/(href=[\'"][^\'"]*(edit)[^\'"]*[\'"][^>]*>.*?Edit.*?<\/a>)/si', $content, $matches)) {
            $editButton = $matches[0];
            
            $submitButton = '
                    
                    @if($surat->currentStatus->kode_status === \'draft\' && $surat->created_by === Auth::id())
                    <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline-block ml-2">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm(\'Kirim surat ini ke Kaprodi untuk review?\')"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Kirim ke Kaprodi
                        </button>
                    </form>
                    @endif';
            
            $content = str_replace($editButton, $editButton . $submitButton, $content);
            file_put_contents($showFile, $content);
            echo "✓ Added submit button after Edit button\n";
        } else {
            echo "Could not find Edit button, trying alternative location...\n";
            
            // Try to add before closing of a div that contains buttons
            if (preg_match('/(<div[^>]*class="[^"]*(?:flex|actions|buttons)[^"]*"[^>]*>.*?)(<\/div>)/si', $content, $matches)) {
                $buttonContainer = $matches[1];
                $closingDiv = $matches[2];
                
                $submitButton = '
                    @if($surat->currentStatus->kode_status === \'draft\' && $surat->created_by === Auth::id())
                    <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm(\'Kirim surat ini ke Kaprodi untuk review?\')"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Kirim ke Kaprodi
                        </button>
                    </form>
                    @endif
                    ';
                
                $newContent = $buttonContainer . $submitButton . $closingDiv;
                $content = str_replace($matches[0], $newContent, $content);
                file_put_contents($showFile, $content);
                echo "✓ Added submit button in button container\n";
            }
        }
    }
}

// Now let's test the actual conditions
echo "\n=== TESTING CONDITIONS ===\n";
$testSurat = \App\Models\Surat::where('status_id', 1)->first();
if ($testSurat) {
    echo "Test surat ID: {$testSurat->id}\n";
    echo "Created by: {$testSurat->created_by}\n";
    echo "Status: " . ($testSurat->currentStatus->kode_status ?? 'null') . "\n";
    
    // Test auth user
    $testUser = \App\Models\User::find($testSurat->created_by);
    if ($testUser) {
        echo "Creator: {$testUser->name}\n";
    }
    
    echo "\nFor submit button to show:\n";
    echo "1. Status must be 'draft': " . ($testSurat->currentStatus->kode_status === 'draft' ? '✓' : '✗') . "\n";
    echo "2. User must be creator: (login as user ID {$testSurat->created_by})\n";
}

echo "\n=== MANUAL CHECK ===\n";
echo "Please verify in browser:\n";
echo "1. Login as the user who created the draft surat\n";
echo "2. Go to surat detail page\n";
echo "3. Check if 'Kirim ke Kaprodi' button appears\n";

echo "\n=== DONE ===\n";