<?php
// debug_and_fix_create_buttons.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUGGING AND FIXING CREATE BUTTONS ===\n\n";

// Find the create view file
$createFile = 'resources/views/staff/surat/create.blade.php';
if (!file_exists($createFile)) {
    $createFile = 'resources/views/surat/create.blade.php';
}

if (file_exists($createFile)) {
    $content = file_get_contents($createFile);
    
    // Backup
    file_put_contents($createFile . '.backup_debug_' . date('YmdHis'), $content);
    
    echo "1. ANALYZING CURRENT STRUCTURE:\n";
    
    // Look for the button more broadly
    preg_match_all('/<button[^>]*>.*?<\/button>/si', $content, $buttonMatches);
    echo "   Found " . count($buttonMatches[0]) . " button(s)\n";
    
    foreach ($buttonMatches[0] as $idx => $button) {
        echo "   Button " . ($idx + 1) . ": " . strip_tags($button) . "\n";
    }
    
    // Look for form closing tag
    if (preg_match('/<\/form>/i', $content, $formMatch, PREG_OFFSET_CAPTURE)) {
        $formEndPos = $formMatch[0][1];
        echo "\n2. Found form closing tag at position: " . $formEndPos . "\n";
        
        // Find what's before the </form> tag
        $beforeFormEnd = substr($content, $formEndPos - 500, 500);
        
        // Look for the submit button area
        if (preg_match('/(.*?)(<button[^>]*>.*?BUAT SURAT.*?<\/button>)(.*?)$/si', $beforeFormEnd, $matches)) {
            echo "3. Found BUAT SURAT button\n";
            
            // Create new buttons section
            $newButtonSection = '
                {{-- Hidden field for action type --}}
                <input type="hidden" name="action_type" id="action_type" value="draft">
                
                {{-- Button Actions --}}
                <div class="flex flex-wrap gap-3 justify-end mt-6">
                    <button type="button"
                            onclick="window.location.href=\'{{ route(\'surat.index\') }}\'"
                            class="px-5 py-2.5 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </button>
                    
                    <button type="submit" 
                            name="save_draft"
                            onclick="setActionType(\'draft\')"
                            class="px-5 py-2.5 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                        </svg>
                        Simpan Draft
                    </button>
                    
                    <button type="button" 
                            onclick="confirmAndSubmit()"
                            class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim ke Kaprodi
                    </button>
                </div>
            ';
            
            // Replace the entire button section
            $pattern = '/<button[^>]*>.*?BUAT SURAT.*?<\/button>/si';
            $content = preg_replace($pattern, $newButtonSection, $content);
            
            echo "4. Replaced button section\n";
        }
    }
    
    // Make sure form has ID
    if (!str_contains($content, 'id="create-surat-form"') && !str_contains($content, "id='create-surat-form'")) {
        $content = preg_replace(
            '/<form([^>]*)>/',
            '<form$1 id="create-surat-form">',
            $content,
            1
        );
        echo "5. Added form ID\n";
    }
    
    // Add JavaScript if not present
    if (!str_contains($content, 'function confirmAndSubmit')) {
        $javascript = '
@section(\'scripts\')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function setActionType(type) {
    document.getElementById(\'action_type\').value = type;
    return true;
}

function confirmAndSubmit() {
    Swal.fire({
        title: \'Konfirmasi Pengiriman\',
        html: `
            <div class="text-left">
                <p class="mb-3">Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?</p>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Perhatian:</strong> Surat yang sudah dikirim tidak dapat diedit kembali.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `,
        icon: \'question\',
        showCancelButton: true,
        confirmButtonColor: \'#2563eb\',
        cancelButtonColor: \'#6b7280\',
        confirmButtonText: \'Ya, Kirim!\',
        cancelButtonText: \'Batal\',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            setActionType(\'submit\');
            document.getElementById(\'create-surat-form\').submit();
        }
    });
}
</script>
@endsection';
        
        // Add script section
        $content .= "\n" . $javascript;
        echo "6. Added JavaScript functions\n";
    }
    
    // Save the updated file
    file_put_contents($createFile, $content);
    echo "\n✔ View file updated\n";
    
} else {
    echo "ERROR: Create view file not found!\n";
}

echo "\n=== CHECKING CONTROLLER ===\n";
$controllerFile = app_path('Http/Controllers/SuratController.php');
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    // Check if store method handles action_type
    if (str_contains($controllerContent, "request->input('action_type')")) {
        echo "✔ Controller already handles action_type\n";
    } else {
        echo "⚠ Controller needs update for action_type handling\n";
        echo "You may need to update the store() method manually\n";
    }
}

echo "\n=== DONE ===\n";
echo "Clear cache and refresh the page:\n";
echo "php artisan view:clear\n";
echo "php artisan cache:clear\n";
echo "\nThe page should now show:\n";
echo "- Batal button (gray)\n";
echo "- Simpan Draft button (dark gray)\n"; 
echo "- Kirim ke Kaprodi button (blue)\n";