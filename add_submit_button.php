<?php
// add_submit_button.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== ADDING SUBMIT BUTTON TO TABLE ===\n\n";

// Backup
$backupDir = storage_path('backup_submit_' . date('Y-m-d_H-i-s'));
mkdir($backupDir, 0777, true);

$file = 'resources/views/staff/surat/index.blade.php';
copy($file, $backupDir . '/index.blade.php');

// Read current content
$content = file_get_contents($file);

// Find the action buttons section and modify it
$oldActionSection = '@if($canEdit)
                                <a href="{{ route(\'surat.edit\', $surat->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 text-yellow-600 hover:bg-yellow-50 rounded transition-colors duration-150 group relative"
                                   title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                        Edit
                                    </span>
                                </a>
                                @endif';

$newActionSection = '@if($canEdit)
                                <a href="{{ route(\'surat.edit\', $surat->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 text-yellow-600 hover:bg-yellow-50 rounded transition-colors duration-150 group relative"
                                   title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                        Edit
                                    </span>
                                </a>
                                @endif
                                
                                {{-- Submit button for draft --}}
                                @if($surat->created_by === Auth::id() && $statusCode === \'draft\')
                                <button onclick="confirmSubmit({{ $surat->id }})" 
                                        class="inline-flex items-center justify-center w-7 h-7 text-blue-600 hover:bg-blue-50 rounded transition-colors duration-150 group relative"
                                        title="Kirim">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                        Kirim
                                    </span>
                                </button>
                                @endif';

$content = str_replace($oldActionSection, $newActionSection, $content);

// Add JavaScript function for submit
$oldScriptSection = 'function confirmReject(id) {
    const reason = prompt(\'Masukkan alasan penolakan:\');
    if (reason) {
        const form = document.createElement(\'form\');
        form.method = \'POST\';
        form.action = `/surat/${id}/reject`;
        
        const token = document.createElement(\'input\');
        token.type = \'hidden\';
        token.name = \'_token\';
        token.value = \'{{ csrf_token() }}\';
        
        const reasonInput = document.createElement(\'input\');
        reasonInput.type = \'hidden\';
        reasonInput.name = \'reason\';
        reasonInput.value = reason;
        
        form.appendChild(token);
        form.appendChild(reasonInput);
        document.body.appendChild(form);
        form.submit();
    }
}';

$newScriptSection = 'function confirmSubmit(id) {
    if (confirm(\'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?\')) {
        const form = document.createElement(\'form\');
        form.method = \'POST\';
        form.action = `/surat/${id}/submit`;
        
        const token = document.createElement(\'input\');
        token.type = \'hidden\';
        token.name = \'_token\';
        token.value = \'{{ csrf_token() }}\';
        
        form.appendChild(token);
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmReject(id) {
    const reason = prompt(\'Masukkan alasan penolakan:\');
    if (reason) {
        const form = document.createElement(\'form\');
        form.method = \'POST\';
        form.action = `/surat/${id}/reject`;
        
        const token = document.createElement(\'input\');
        token.type = \'hidden\';
        token.name = \'_token\';
        token.value = \'{{ csrf_token() }}\';
        
        const reasonInput = document.createElement(\'input\');
        reasonInput.type = \'hidden\';
        reasonInput.name = \'reason\';
        reasonInput.value = reason;
        
        form.appendChild(token);
        form.appendChild(reasonInput);
        document.body.appendChild(form);
        form.submit();
    }
}';

$content = str_replace($oldScriptSection, $newScriptSection, $content);

// Save updated file
file_put_contents($file, $content);

echo "✓ Added submit button to table actions\n";

// Also check if submit method exists and works correctly
echo "\n=== CHECKING SUBMIT METHOD ===\n";

$controllerFile = app_path('Http/Controllers/SuratController.php');
$controllerContent = file_get_contents($controllerFile);

if (!str_contains($controllerContent, 'function submit')) {
    echo "⚠ Submit method not found, adding it...\n";
    
    // Add submit method before the last closing brace
    $submitMethod = '
    /**
     * Submit surat from draft to review
     */
    public function submit(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);
        
        // Check if user is owner and status is draft
        if ($surat->created_by !== Auth::id()) {
            return redirect()->back()->with(\'error\', \'Anda tidak dapat mengirim surat ini\');
        }
        
        if ($surat->currentStatus->kode_status !== \'draft\') {
            return redirect()->back()->with(\'error\', \'Hanya surat draft yang dapat dikirim\');
        }
        
        // Update status to review_kaprodi
        $reviewStatus = StatusSurat::where(\'kode_status\', \'review_kaprodi\')->first();
        
        if (!$reviewStatus) {
            // Fallback to diajukan if review_kaprodi doesn\'t exist
            $reviewStatus = StatusSurat::where(\'kode_status\', \'diajukan\')->first();
        }
        
        if (!$reviewStatus) {
            return redirect()->back()->with(\'error\', \'Status review tidak ditemukan\');
        }
        
        DB::beginTransaction();
        try {
            $surat->update([
                \'status_id\' => $reviewStatus->id,
                \'updated_by\' => Auth::id()
            ]);
            
            // Add to status history
            if (class_exists(\'App\Models\StatusHistory\')) {
                StatusHistory::create([
                    \'surat_id\' => $surat->id,
                    \'status_id\' => $reviewStatus->id,
                    \'user_id\' => Auth::id(),
                    \'keterangan\' => \'Surat dikirim untuk review Kaprodi\'
                ]);
            }
            
            DB::commit();
            
            return redirect()->route(\'surat.show\', $surat->id)
                           ->with(\'success\', \'Surat berhasil dikirim ke Kaprodi untuk review\');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(\'error\', \'Gagal mengirim surat: \' . $e->getMessage());
        }
    }
';
    
    // Insert before last closing brace
    $lastBrace = strrpos($controllerContent, '}');
    $controllerContent = substr_replace($controllerContent, $submitMethod . "\n", $lastBrace, 0);
    file_put_contents($controllerFile, $controllerContent);
    echo "✓ Submit method added to controller\n";
} else {
    echo "✓ Submit method already exists\n";
}

echo "\n=== DONE ===\n";
echo "Submit button will now appear for draft surat created by the user\n";
echo "Backup saved at: $backupDir\n";