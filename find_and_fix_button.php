<?php
// find_and_fix_button.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FINDING AND FIXING BUTTON ===\n\n";

$createFile = 'resources/views/staff/surat/create.blade.php';
if (!file_exists($createFile)) {
    $createFile = 'resources/views/surat/create.blade.php';
}

if (file_exists($createFile)) {
    $content = file_get_contents($createFile);
    
    // Backup
    file_put_contents($createFile . '.backup_find_' . date('YmdHis'), $content);
    
    echo "1. Searching for 'BUAT SURAT' text in file...\n";
    
    // Search for BUAT SURAT text anywhere
    if (str_contains($content, 'BUAT SURAT')) {
        echo "   ✓ Found 'BUAT SURAT' text\n";
        
        // Look for it in different possible structures
        $patterns = [
            '/<button[^>]*>[\s]*BUAT SURAT[\s]*<\/button>/i',
            '/<a[^>]*class="[^"]*btn[^"]*"[^>]*>[\s]*BUAT SURAT[\s]*<\/a>/i',
            '/<input[^>]*type="submit"[^>]*value="BUAT SURAT"[^>]*>/i',
            '/BUAT SURAT/'
        ];
        
        foreach ($patterns as $idx => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                echo "   Pattern " . ($idx + 1) . " matched: " . htmlspecialchars(substr($matches[0], 0, 100)) . "\n";
                
                // Replace with new buttons
                $newButtons = '
                {{-- Hidden field for action type --}}
                <input type="hidden" name="action_type" id="action_type" value="draft">
                
                {{-- Button Actions --}}
                <div class="flex flex-wrap gap-3 justify-end mt-4">
                    <button type="button"
                            onclick="window.location.href=\'{{ route(\'surat.index\') }}\'"
                            class="px-5 py-2.5 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition-all duration-200">
                        Batal
                    </button>
                    
                    <button type="submit" 
                            onclick="setActionType(\'draft\')"
                            class="px-5 py-2.5 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                        Simpan Draft
                    </button>
                    
                    <button type="button" 
                            onclick="confirmAndSubmit()"
                            class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all duration-200">
                        Kirim ke Kaprodi
                    </button>
                </div>';
                
                $content = str_replace($matches[0], $newButtons, $content);
                echo "   ✓ Replaced button\n";
                break;
            }
        }
    } else {
        echo "   ✗ 'BUAT SURAT' text not found\n";
        echo "   Looking for submit area at end of form...\n";
        
        // Try to add buttons before </form>
        if (preg_match('/<\/form>/i', $content)) {
            $newButtons = '
                {{-- Hidden field for action type --}}
                <input type="hidden" name="action_type" id="action_type" value="draft">
                
                {{-- Button Actions --}}
                <div class="flex flex-wrap gap-3 justify-end mt-4">
                    <button type="button"
                            onclick="window.location.href=\'{{ route(\'surat.index\') }}\'"
                            class="px-5 py-2.5 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition-all duration-200">
                        Batal
                    </button>
                    
                    <button type="submit" 
                            onclick="setActionType(\'draft\')"
                            class="px-5 py-2.5 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                        Simpan Draft
                    </button>
                    
                    <button type="button" 
                            onclick="confirmAndSubmit()"
                            class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all duration-200">
                        Kirim ke Kaprodi
                    </button>
                </div>
                
            </form>';
            
            $content = str_replace('</form>', $newButtons, $content);
            echo "   ✓ Added buttons before form closing tag\n";
        }
    }
    
    // Add JavaScript
    if (!str_contains($content, 'function confirmAndSubmit')) {
        $javascript = '

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function setActionType(type) {
    document.getElementById(\'action_type\').value = type;
    return true;
}

function confirmAndSubmit() {
    Swal.fire({
        title: \'Konfirmasi Pengiriman\',
        text: \'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review? Surat yang sudah dikirim tidak dapat diedit kembali.\',
        icon: \'question\',
        showCancelButton: true,
        confirmButtonColor: \'#2563eb\',
        cancelButtonColor: \'#6b7280\',
        confirmButtonText: \'Ya, Kirim!\',
        cancelButtonText: \'Batal\'
    }).then((result) => {
        if (result.isConfirmed) {
            setActionType(\'submit\');
            document.getElementById(\'create-surat-form\').submit();
        }
    });
}
</script>';
        
        $content = str_replace('</body>', $javascript . "\n</body>", $content);
        echo "2. Added JavaScript functions\n";
    }
    
    file_put_contents($createFile, $content);
    echo "\n✔ File updated\n";
    
    // Let's also check what's actually in the file
    echo "\n3. Checking file structure:\n";
    if (preg_match_all('/@(extends|section|yield|include)\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
        echo "   Blade directives found:\n";
        foreach ($matches[2] as $directive) {
            echo "   - " . $directive . "\n";
        }
    }
}

echo "\n=== UPDATING CONTROLLER ===\n";
$controllerFile = app_path('Http/Controllers/SuratController.php');
$controllerContent = file_get_contents($controllerFile);

// Update store method to handle action_type
if (!str_contains($controllerContent, "request->input('action_type')")) {
    // Find store method
    if (preg_match('/public function store\(Request \$request\)(.*?)(\n    public function|\z)/s', $controllerContent, $matches)) {
        $storeMethod = $matches[1];
        
        // Add action_type handling before the final redirect
        $newStoreMethod = preg_replace(
            '/(return redirect.*?;)/',
            '// Handle action type
        $actionType = $request->input(\'action_type\', \'draft\');
        
        if ($actionType === \'submit\') {
            $reviewStatus = \App\Models\StatusSurat::where(\'kode_status\', \'review_kaprodi\')->first();
            if ($reviewStatus) {
                $surat->update([\'status_id\' => $reviewStatus->id]);
            }
            return redirect()->route(\'surat.show\', $surat->id)
                ->with(\'success\', \'Surat berhasil dikirim ke Kaprodi untuk review.\');
        }
        
        $1',
            $storeMethod
        );
        
        $controllerContent = str_replace($storeMethod, $newStoreMethod, $controllerContent);
        file_put_contents($controllerFile, $controllerContent);
        echo "✔ Updated controller store method\n";
    }
}

echo "\n=== DONE ===\n";
echo "Run these commands:\n";
echo "php artisan view:clear\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";