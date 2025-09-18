<?php
// add_detail_action_buttons.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== ADDING ACTION BUTTONS TO DETAIL VIEW ===\n\n";

// Find the show view file
$showFile = 'resources/views/staff/surat/show.blade.php';
if (!file_exists($showFile)) {
    $showFile = 'resources/views/surat/show.blade.php';
}

if (file_exists($showFile)) {
    $content = file_get_contents($showFile);
    
    // Backup
    file_put_contents($showFile . '.backup_actions', $content);
    
    // Find where "Aksi" section is
    if (preg_match('/(<[^>]*>\s*Aksi\s*<\/[^>]*>)(.*?)(<\/div>)/si', $content, $matches)) {
        echo "Found Aksi section, adding buttons...\n";
        
        $aksiHeader = $matches[1];
        $aksiContent = $matches[2];
        $closingDiv = $matches[3];
        
        // Create action buttons
        $actionButtons = '
                <div class="flex flex-wrap gap-2 mt-3">
                    {{-- Edit button for draft --}}
                    @if($surat->currentStatus->kode_status === \'draft\' && $surat->created_by === Auth::id())
                    <a href="{{ route(\'surat.edit\', $surat->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Surat
                    </a>
                    
                    {{-- Submit button for draft --}}
                    <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm(\'Kirim surat ini ke Kaprodi untuk review?\')"
                                class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Kirim ke Kaprodi
                        </button>
                    </form>
                    @endif
                    
                    {{-- Approve/Reject for kaprodi --}}
                    @if(Auth::user()->hasRole(\'kaprodi\') && in_array($surat->currentStatus->kode_status, [\'review_kaprodi\', \'diajukan\']))
                    <form action="{{ route(\'surat.approve\', $surat->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm(\'Setujui surat ini?\')"
                                class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui
                        </button>
                    </form>
                    
                    <button onclick="showRejectModal()" 
                            class="inline-flex items-center px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak
                    </button>
                    @endif
                    
                    {{-- Print button --}}
                    <button onclick="window.print()" 
                            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                        </svg>
                        Cetak
                    </button>
                    
                    {{-- Back button --}}
                    <a href="{{ route(\'surat.index\') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali
                    </a>
                </div>';
        
        // Replace the content
        $newContent = $aksiHeader . $actionButtons . $closingDiv;
        $content = str_replace($matches[0], $newContent, $content);
        
        // Add reject modal if not exists
        if (!str_contains($content, 'showRejectModal')) {
            $rejectModal = '
{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Tolak Surat</h3>
            <form action="{{ route(\'surat.reject\', $surat->id) }}" method="POST" class="mt-4">
                @csrf
                <div class="mt-2">
                    <label class="block text-sm font-medium text-gray-700">Alasan Penolakan</label>
                    <textarea name="reason" rows="4" required 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="closeRejectModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Tolak Surat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal() {
    document.getElementById(\'rejectModal\').classList.remove(\'hidden\');
}

function closeRejectModal() {
    document.getElementById(\'rejectModal\').classList.add(\'hidden\');
}
</script>';
            
            // Add before closing body tag
            $content = str_replace('</body>', $rejectModal . "\n</body>", $content);
        }
        
        file_put_contents($showFile, $content);
        echo "✓ Added action buttons to detail view\n";
    } else {
        echo "Could not find Aksi section, trying alternative approach...\n";
        
        // Try to add after the status or at the end of card
        if (preg_match('/(<div[^>]*class="[^"]*card[^"]*"[^>]*>.*?)<\/div>\s*<\/div>/si', $content, $matches)) {
            $cardContent = $matches[1];
            
            $actionSection = '
            <div class="border-t pt-4 mt-4">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Aksi</h3>
                <div class="flex flex-wrap gap-2">
                    @if($surat->currentStatus->kode_status === \'draft\' && $surat->created_by === Auth::id())
                    <a href="{{ route(\'surat.edit\', $surat->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    
                    <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" onclick="return confirm(\'Kirim ke Kaprodi?\')"
                                class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Kirim
                        </button>
                    </form>
                    @endif
                </div>
            </div>';
            
            $newContent = $cardContent . $actionSection . '</div></div>';
            $content = str_replace($matches[0], $newContent, $content);
            file_put_contents($showFile, $content);
            echo "✓ Added action section to detail view\n";
        }
    }
}

echo "\n=== DONE ===\n";
echo "Action buttons added to detail view.\n";
echo "Clear cache: php artisan view:clear\n";