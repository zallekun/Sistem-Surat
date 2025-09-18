<?php
// fix_create_buttons.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING CREATE PAGE BUTTONS ===\n\n";

// Find the create view
$createFile = 'resources/views/staff/surat/create.blade.php';
if (!file_exists($createFile)) {
    $createFile = 'resources/views/surat/create.blade.php';
}

if (file_exists($createFile)) {
    $content = file_get_contents($createFile);
    
    // Backup
    file_put_contents($createFile . '.backup_' . date('YmdHis'), $content);
    
    echo "Analyzing current form structure...\n";
    
    // Find the submit button - looking for "BUAT SURAT"
    if (preg_match('/<button[^>]*>(.*?BUAT SURAT.*?)<\/button>/si', $content, $matches)) {
        $oldButton = $matches[0];
        echo "Found button: " . strip_tags($oldButton) . "\n";
        
        // Create new button group
        $newButtons = '
                {{-- Hidden field for action type --}}
                <input type="hidden" name="action_type" id="action_type" value="draft">
                
                {{-- Button Group --}}
                <div class="flex gap-3 justify-end">
                    <button type="button"
                            onclick="window.location.href=\'{{ route(\'surat.index\') }}\'"
                            class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Batal
                    </button>
                    
                    <button type="submit" 
                            onclick="setActionType(\'draft\')"
                            class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                        </svg>
                        Simpan Draft
                    </button>
                    
                    <button type="button" 
                            onclick="confirmSubmit()"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim ke Kaprodi
                    </button>
                </div>';
        
        // Replace the button
        $content = str_replace($oldButton, $newButtons, $content);
        echo "✔ Replaced single button with button group\n";
    }
    
    // Make sure form has an ID
    if (!str_contains($content, 'id="create-surat-form"')) {
        $content = preg_replace(
            '/<form([^>]*)>/',
            '<form$1 id="create-surat-form">',
            $content,
            1
        );
        echo "✔ Added form ID\n";
    }
    
    // Add JavaScript at the end if not exists
    if (!str_contains($content, 'function confirmSubmit()')) {
        $javascript = '

@push(\'scripts\')
<script>
function setActionType(type) {
    document.getElementById(\'action_type\').value = type;
    console.log(\'Action type set to:\', type);
    return true;
}

function confirmSubmit() {
    // Check if SweetAlert2 is available
    if (typeof Swal !== \'undefined\') {
        Swal.fire({
            title: \'Konfirmasi Pengiriman\',
            html: \'<div class="text-left">\' +
                  \'<p class="mb-2">Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?</p>\' +
                  \'<div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mt-4">\' +
                  \'<p class="text-sm text-yellow-700">\' +
                  \'<strong>Perhatian:</strong> Surat yang sudah dikirim tidak dapat diedit kembali.\' +
                  \'</p>\' +
                  \'</div>\' +
                  \'</div>\',
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
                // Show loading
                Swal.fire({
                    title: \'Mengirim...\',
                    text: \'Surat sedang dikirim ke Kaprodi\',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                document.getElementById(\'create-surat-form\').submit();
            }
        });
    } else {
        // Fallback to native confirm
        if (confirm(\'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?\\n\\nPerhatian: Surat yang sudah dikirim tidak dapat diedit kembali.\')) {
            setActionType(\'submit\');
            document.getElementById(\'create-surat-form\').submit();
        }
    }
}

// Add form validation
document.addEventListener(\'DOMContentLoaded\', function() {
    const form = document.getElementById(\'create-surat-form\');
    if (form) {
        form.addEventListener(\'submit\', function(e) {
            const actionType = document.getElementById(\'action_type\').value;
            console.log(\'Submitting with action:\', actionType);
            
            // Basic validation
            const perihal = document.querySelector(\'[name="perihal"]\');
            if (perihal && !perihal.value.trim()) {
                e.preventDefault();
                alert(\'Perihal harus diisi!\');
                return false;
            }
        });
    }
});
</script>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush';
        
        $content .= $javascript;
        echo "✔ Added JavaScript functions\n";
    }
    
    // Save the file
    file_put_contents($createFile, $content);
    echo "\n✔ View file updated successfully\n";
} else {
    echo "ERROR: Create view file not found!\n";
}

// Update controller to handle action_type
echo "\n=== UPDATING CONTROLLER ===\n";
$controllerFile = app_path('Http/Controllers/SuratController.php');
$controllerContent = file_get_contents($controllerFile);

// Check if store method already handles action_type
if (!str_contains($controllerContent, "request->input('action_type')")) {
    echo "Updating store method to handle action types...\n";
    
    // Find the store method and add action_type handling
    $pattern = '/(public\s+function\s+store.*?\{.*?)(return\s+redirect.*?;)/s';
    
    if (preg_match($pattern, $controllerContent, $matches)) {
        $methodStart = $matches[1];
        $originalReturn = $matches[2];
        
        $newLogic = $methodStart . '
        // Handle action type
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
            }
            
            return redirect()->route(\'surat.show\', $surat->id)
                ->with(\'success\', \'Surat berhasil dikirim ke Kaprodi untuk review.\');
        }
        
        // Default for draft
        return redirect()->route(\'surat.show\', $surat->id)
            ->with(\'success\', \'Surat berhasil disimpan sebagai draft.\');';
        
        $controllerContent = str_replace($matches[0], $newLogic, $controllerContent);
        file_put_contents($controllerFile, $controllerContent);
        echo "✔ Controller updated\n";
    }
}

echo "\n=== DONE ===\n";
echo "The create page now has:\n";
echo "1. 'Simpan Draft' button - saves as draft\n";
echo "2. 'Kirim ke Kaprodi' button - with confirmation popup\n";
echo "3. 'Batal' button - to cancel\n";
echo "\nClear cache and test:\n";
echo "php artisan view:clear\n";
echo "php artisan cache:clear\n";