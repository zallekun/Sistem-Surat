<?php
// fix_button_style_and_edit_error.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING BUTTON STYLE AND EDIT ROUTE ERROR ===\n\n";

// 1. First, let's check what's in the edit form
$editFile = 'resources/views/staff/surat/edit.blade.php';
if (file_exists($editFile)) {
    $content = file_get_contents($editFile);
    
    // Check the exact form tag
    preg_match('/<form[^>]*>/', $content, $matches);
    echo "Current form tag: " . ($matches[0] ?? 'Not found') . "\n";
    
    // Fix the form to ensure it has the ID
    $content = preg_replace(
        '/<form[^>]*action="[^"]*staff\.surat\.update[^"]*"[^>]*>/',
        '<form action="{{ route(\'staff.surat.update\', $surat->id) }}" method="POST" enctype="multipart/form-data">',
        $content
    );
    
    file_put_contents($editFile, $content);
    echo "✓ Fixed edit form route\n";
}

// 2. Update button styles in index.blade.php with better UI
$indexFile = 'resources/views/staff/surat/index.blade.php';
$content = file_get_contents($indexFile);

// Backup
file_put_contents($indexFile . '.backup_' . date('YmdHis'), $content);

// Find and replace the action buttons section
$oldButtonPattern = '/<div class="flex items-center justify-center gap-\d+">(.*?)<\/div>\s*<\/td>/s';

if (preg_match($oldButtonPattern, $content, $matches)) {
    $oldButtons = $matches[0];
    
    // Create new button template with better styling
    $newButtons = '<div class="flex items-center justify-center gap-2">
                                {{-- View button - always visible --}}
                                <a href="{{ route(\'surat.show\', $surat->id) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded-lg transition-colors duration-150 group relative"
                                   title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                
                                {{-- Edit button - if owner --}}
                                @if($canEdit)
                                <a href="{{ route(\'surat.edit\', $surat->id) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-600 hover:bg-yellow-200 rounded-lg transition-colors duration-150"
                                   title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                
                                {{-- Submit button for draft --}}
                                @if($surat->created_by === Auth::id() && $statusCode === \'draft\')
                                <button onclick="confirmSubmit({{ $surat->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-600 hover:bg-indigo-200 rounded-lg transition-colors duration-150"
                                        title="Kirim ke Kaprodi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                </button>
                                @endif
                                
                                {{-- Approve button - for kaprodi --}}
                                @if($canApprove)
                                <button onclick="confirmApprove({{ $surat->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors duration-150"
                                        title="Setujui">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                
                                <button onclick="confirmReject({{ $surat->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors duration-150"
                                        title="Tolak">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>';
    
    // Replace in content
    $content = str_replace($oldButtons, $newButtons, $content);
    file_put_contents($indexFile, $content);
    echo "✓ Updated button styles with colored backgrounds\n";
}

// 3. Check and fix the controller edit method
$controllerFile = app_path('Http/Controllers/SuratController.php');
$controllerContent = file_get_contents($controllerFile);

// Check if edit method exists and returns proper data
if (!str_contains($controllerContent, 'public function edit($id)')) {
    echo "Adding edit method to controller...\n";
    
    $editMethod = '
    /**
     * Show the form for editing the specified surat.
     */
    public function edit($id)
    {
        $surat = Surat::findOrFail($id);
        
        // Authorization check
        if ($surat->created_by !== Auth::id()) {
            return redirect()->route(\'surat.index\')
                ->with(\'error\', \'Anda tidak memiliki izin untuk mengedit surat ini.\');
        }
        
        // Only draft can be edited
        if ($surat->currentStatus->kode_status !== \'draft\') {
            return redirect()->route(\'surat.show\', $surat->id)
                ->with(\'error\', \'Hanya surat dengan status draft yang dapat diedit.\');
        }
        
        // Get necessary data for form
        $jenisSurats = JenisSurat::orderBy(\'nama_jenis\')->get();
        $tujuanJabatans = Jabatan::whereIn(\'nama_jabatan\', [\'Dekan\', \'Wakil Dekan\', \'Rektor\', \'Wakil Rektor\'])
            ->orderBy(\'nama_jabatan\')->get();
        
        return view(\'staff.surat.edit\', compact(\'surat\', \'jenisSurats\', \'tujuanJabatans\'));
    }
';
    
    // Insert before update method
    $controllerContent = preg_replace(
        '/(public function update)/',
        $editMethod . "\n\n$1",
        $controllerContent
    );
    
    file_put_contents($controllerFile, $controllerContent);
    echo "✓ Added edit method to controller\n";
}

echo "\n=== BUTTON STYLES ===\n";
echo "Buttons now have:\n";
echo "- Blue background for View (bg-blue-100)\n";
echo "- Yellow background for Edit (bg-yellow-100)\n";
echo "- Indigo background for Submit (bg-indigo-100)\n";
echo "- Green background for Approve (bg-green-100)\n";
echo "- Red background for Reject (bg-red-100)\n";
echo "- Size: 32x32px (w-8 h-8)\n";
echo "- Rounded corners (rounded-lg)\n";
echo "- Hover effects with darker shade\n";

echo "\n=== DONE ===\n";