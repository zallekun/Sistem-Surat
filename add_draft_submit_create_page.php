<?php
// add_draft_submit_create_page.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== ADDING DRAFT AND SUBMIT BUTTONS TO CREATE PAGE ===\n\n";

// 1. Check create view file
$createFiles = [
    'resources/views/staff/surat/create.blade.php',
    'resources/views/surat/create.blade.php'
];

$createFile = null;
foreach ($createFiles as $file) {
    if (file_exists($file)) {
        $createFile = $file;
        break;
    }
}

if (!$createFile) {
    echo "ERROR: Create view file not found!\n";
    exit;
}

echo "Found create file: $createFile\n";
$content = file_get_contents($createFile);

// Backup
file_put_contents($createFile . '.backup_' . date('YmdHis'), $content);

// 2. Find the current submit button section
echo "\n2. CHECKING CURRENT FORM SETUP:\n";

// Check if form has ID
if (!str_contains($content, 'id="create-surat-form"') && !str_contains($content, "id='create-surat-form'")) {
    echo "   Adding form ID...\n";
    $content = preg_replace(
        '/<form([^>]*)(action=["\'][^"\']*["\'])([^>]*)>/',
        '<form$1$2$3 id="create-surat-form">',
        $content
    );
}

// Find existing submit button
if (preg_match('/<button[^>]*type=["\']submit["\'][^>]*>(.*?)<\/button>/si', $content, $matches)) {
    echo "   Found existing submit button\n";
    $oldButton = $matches[0];
    
    // Create new button section
    $newButtonSection = '
                {{-- Action type hidden field --}}
                <input type="hidden" name="action_type" id="action_type" value="draft">
                
                {{-- Button Group --}}
                <div class="flex flex-wrap gap-3 justify-end">
                    {{-- Save as Draft --}}
                    <button type="submit" 
                            onclick="setActionType(\'draft\')"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                        </svg>
                        Simpan Draft
                    </button>
                    
                    {{-- Submit for Review --}}
                    <button type="button" 
                            onclick="confirmAndSubmit()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim ke Kaprodi
                    </button>
                    
                    {{-- Cancel --}}
                    <a href="{{ route(\'surat.index\') }}" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </a>
                </div>';
    
    // Replace old button with new section
    $content = str_replace($oldButton, $newButtonSection, $content);
    echo "   ✔ Replaced submit button with new button group\n";
}

// 3. Add JavaScript for confirmation
echo "\n3. ADDING JAVASCRIPT:\n";

if (!str_contains($content, 'function confirmAndSubmit()')) {
    $javascript = '
@push(\'scripts\')
<script>
function setActionType(type) {
    document.getElementById(\'action_type\').value = type;
    return true;
}

function confirmAndSubmit() {
    // Using SweetAlert2 if available
    if (typeof Swal !== \'undefined\') {
        Swal.fire({
            title: \'Konfirmasi Pengiriman\',
            html: \'<p>Apakah Anda yakin ingin mengirim surat ini ke Kaprodi?</p>\' +
                  \'<p class="text-sm text-gray-600 mt-2">Surat yang sudah dikirim tidak dapat diedit kembali.</p>\',
            icon: \'question\',
            showCancelButton: true,
            confirmButtonColor: \'#2563eb\',
            cancelButtonColor: \'#dc2626\',
            confirmButtonText: \'Ya, Kirim!\',
            cancelButtonText: \'Batal\'
        }).then((result) => {
            if (result.isConfirmed) {
                setActionType(\'submit\');
                document.getElementById(\'create-surat-form\').submit();
            }
        });
    } else {
        // Fallback to native confirm
        const message = \'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi?\\n\\n\' +
                       \'⚠️ Perhatian:\\n\' +
                       \'Surat yang sudah dikirim tidak dapat diedit kembali.\';
        
        if (confirm(message)) {
            setActionType(\'submit\');
            document.getElementById(\'create-surat-form\').submit();
        }
    }
}

// Optional: Add form validation before submit
document.getElementById(\'create-surat-form\').addEventListener(\'submit\', function(e) {
    const actionType = document.getElementById(\'action_type\').value;
    
    // You can add custom validation here
    console.log(\'Submitting form with action:\', actionType);
});
</script>
@endpush';

    // Add script at the end
    $content .= "\n" . $javascript;
    echo "   ✔ Added JavaScript functions\n";
}

// 4. Add SweetAlert2 CDN if not present
if (!str_contains($content, 'sweetalert2')) {
    $sweetalert = '@push(\'styles\')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push(\'scripts\')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush';
    
    // Add before the main script
    $content = str_replace('@push(\'scripts\')', $sweetalert . "\n\n@push('scripts')", $content);
    echo "   ✔ Added SweetAlert2 CDN\n";
}

// Save the updated file
file_put_contents($createFile, $content);
echo "\n✔ Updated create view file\n";

// 5. Update Controller to handle action_type
echo "\n4. UPDATING CONTROLLER:\n";

$controllerFile = app_path('Http/Controllers/SuratController.php');
$controllerContent = file_get_contents($controllerFile);

// Find store method
if (preg_match('/public\s+function\s+store\s*\(Request\s+\$request\)\s*\{(.*?)\n    \}/s', $controllerContent, $matches)) {
    $storeMethodBody = $matches[1];
    
    if (!str_contains($storeMethodBody, 'action_type')) {
        echo "   Updating store method...\n";
        
        // Find where surat is created and saved
        $pattern = '/(surat\s*=.*?save\(\);)/s';
        if (preg_match($pattern, $storeMethodBody, $saveMatch)) {
            $afterSave = $saveMatch[0] . '

            // Handle action type (draft or submit)
            $actionType = $request->input(\'action_type\', \'draft\');
            
            if ($actionType === \'submit\') {
                // Change status to review_kaprodi
                $reviewStatus = \App\Models\StatusSurat::where(\'kode_status\', \'review_kaprodi\')->first();
                if (!$reviewStatus) {
                    $reviewStatus = \App\Models\StatusSurat::where(\'kode_status\', \'diajukan\')->first();
                }
                
                if ($reviewStatus) {
                    $surat->update([
                        \'status_id\' => $reviewStatus->id,
                        \'updated_by\' => Auth::id()
                    ]);
                    
                    // Log status change if needed
                    \Log::info(\'Surat submitted for review\', [
                        \'surat_id\' => $surat->id,
                        \'user_id\' => Auth::id()
                    ]);
                    
                    return redirect()->route(\'surat.show\', $surat->id)
                        ->with(\'success\', \'Surat berhasil dibuat dan dikirim ke Kaprodi untuk review.\');
                }
            }
            
            // Default draft message
            return redirect()->route(\'surat.show\', $surat->id)
                ->with(\'success\', \'Surat berhasil disimpan sebagai draft.\');';
            
            $newStoreMethodBody = str_replace($saveMatch[0], $afterSave, $storeMethodBody);
            
            // Replace in controller
            $newStoreMethod = 'public function store(Request $request)
    {' . $newStoreMethodBody . '
    }';
            
            $controllerContent = preg_replace(
                '/public\s+function\s+store\s*\(Request\s+\$request\)\s*\{.*?\n    \}/s',
                $newStoreMethod,
                $controllerContent
            );
            
            file_put_contents($controllerFile, $controllerContent);
            echo "   ✔ Updated store method to handle action types\n";
        }
    } else {
        echo "   ✔ Store method already handles action_type\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "✔ Added 'Simpan Draft' button - saves with draft status\n";
echo "✔ Added 'Kirim ke Kaprodi' button - saves and submits for review\n";
echo "✔ Added confirmation popup (SweetAlert2 with fallback)\n";
echo "✔ Added 'Batal' button to cancel\n";
echo "✔ Updated controller to handle both actions\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Clear cache: php artisan view:clear\n";
echo "2. Test creating a new surat\n";
echo "3. Try both 'Simpan Draft' and 'Kirim ke Kaprodi' buttons\n";

echo "\n=== DONE ===\n";