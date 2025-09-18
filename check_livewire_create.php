<?php
// check_livewire_create.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING FOR LIVEWIRE COMPONENT ===\n\n";

// Check if there's a Livewire component for create
$livewireFiles = [
    'app/Http/Livewire/CreateSurat.php',
    'app/Http/Livewire/SuratCreate.php',
    'app/Http/Livewire/Staff/CreateSurat.php',
    'app/Livewire/CreateSurat.php',
    'app/Livewire/SuratCreate.php',
    'app/Livewire/Staff/CreateSurat.php'
];

$foundLivewire = false;
foreach ($livewireFiles as $file) {
    if (file_exists($file)) {
        echo "Found Livewire component: $file\n";
        $foundLivewire = true;
        
        // Check the render method
        $content = file_get_contents($file);
        if (preg_match('/public function render\(\)(.*?)\{(.*?)\}/s', $content, $matches)) {
            $renderContent = $matches[2];
            if (preg_match("/view\(['\"]([^'\"]+)['\"]/", $renderContent, $viewMatch)) {
                echo "Component renders view: " . $viewMatch[1] . "\n";
                
                // Now check this view file
                $viewFile = 'resources/views/' . str_replace('.', '/', $viewMatch[1]) . '.blade.php';
                if (file_exists($viewFile)) {
                    echo "Livewire view file: $viewFile\n\n";
                    
                    // Update this file instead
                    $viewContent = file_get_contents($viewFile);
                    
                    // Backup
                    file_put_contents($viewFile . '.backup_livewire', $viewContent);
                    
                    // Look for BUAT SURAT button
                    if (preg_match('/<button[^>]*wire:click[^>]*>.*?BUAT SURAT.*?<\/button>/si', $viewContent, $buttonMatch)) {
                        echo "Found Livewire button with wire:click\n";
                        
                        // Replace with new buttons
                        $newButtons = '
                <div class="flex flex-wrap gap-3 justify-end mt-4">
                    <button type="button"
                            onclick="window.location.href=\'{{ route(\'surat.index\') }}\'"
                            class="px-5 py-2.5 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition-all duration-200">
                        Batal
                    </button>
                    
                    <button type="button" 
                            wire:click="saveDraft"
                            class="px-5 py-2.5 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                        Simpan Draft
                    </button>
                    
                    <button type="button" 
                            wire:click="confirmSubmit"
                            class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all duration-200">
                        Kirim ke Kaprodi
                    </button>
                </div>';
                        
                        $viewContent = str_replace($buttonMatch[0], $newButtons, $viewContent);
                        file_put_contents($viewFile, $viewContent);
                        echo "✔ Updated Livewire view\n";
                        
                        // Now update the Livewire component class
                        echo "\nUpdating Livewire component methods...\n";
                        
                        // Add methods to component
                        if (!str_contains($content, 'public function saveDraft')) {
                            $newMethods = '
    public function saveDraft()
    {
        $this->validate();
        
        // Save as draft
        $surat = $this->createSurat(\'draft\');
        
        session()->flash(\'success\', \'Surat berhasil disimpan sebagai draft.\');
        return redirect()->route(\'surat.show\', $surat->id);
    }
    
    public function confirmSubmit()
    {
        $this->dispatchBrowserEvent(\'confirm-submit\');
    }
    
    public function submitForReview()
    {
        $this->validate();
        
        // Save and submit
        $surat = $this->createSurat(\'submit\');
        
        session()->flash(\'success\', \'Surat berhasil dikirim ke Kaprodi untuk review.\');
        return redirect()->route(\'surat.show\', $surat->id);
    }
    
    private function createSurat($actionType)
    {
        // Your existing save logic here
        // Add status handling based on $actionType
        
        $statusId = 1; // Default draft
        if ($actionType === \'submit\') {
            $reviewStatus = \App\Models\StatusSurat::where(\'kode_status\', \'review_kaprodi\')->first();
            if ($reviewStatus) {
                $statusId = $reviewStatus->id;
            }
        }
        
        // Create surat with appropriate status
        // ... your create logic ...
        
        return $surat;
    }';
                            
                            // Insert before last closing brace
                            $lastBrace = strrpos($content, '}');
                            $content = substr($content, 0, $lastBrace) . $newMethods . "\n}";
                            file_put_contents($file, $content);
                            echo "✔ Added Livewire methods\n";
                        }
                        
                        // Add JavaScript for confirmation
                        if (!str_contains($viewContent, 'confirm-submit')) {
                            $jsScript = '
<script>
window.addEventListener(\'confirm-submit\', event => {
    if (confirm(\'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review? Surat yang sudah dikirim tidak dapat diedit kembali.\')) {
        @this.submitForReview();
    }
});
</script>';
                            
                            $viewContent = file_get_contents($viewFile);
                            $viewContent .= $jsScript;
                            file_put_contents($viewFile, $viewContent);
                            echo "✔ Added confirmation script\n";
                        }
                    }
                }
            }
        }
        break;
    }
}

if (!$foundLivewire) {
    echo "No Livewire component found for create page\n";
    echo "\nChecking main create view again...\n";
    
    $createFile = 'resources/views/staff/surat/create.blade.php';
    if (file_exists($createFile)) {
        $content = file_get_contents($createFile);
        
        // Check if it uses @livewire directive
        if (preg_match('/@livewire\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
            echo "Main view uses Livewire component: " . $matches[1] . "\n";
        } else {
            echo "Main view does not use Livewire\n";
            
            // Let's check what's actually rendering the button
            echo "\nSearching for any button-like elements...\n";
            
            // Search more broadly
            $searches = [
                'type="submit"',
                'type="button"',
                'BUAT',
                'Submit',
                'Simpan'
            ];
            
            foreach ($searches as $search) {
                if (stripos($content, $search) !== false) {
                    echo "Found: '$search' in file\n";
                    
                    // Get context
                    $pos = stripos($content, $search);
                    $context = substr($content, max(0, $pos - 100), 200);
                    echo "Context: " . htmlspecialchars($context) . "\n\n";
                }
            }
        }
    }
}

echo "\n=== DONE ===\n";
echo "Clear all caches:\n";
echo "php artisan view:clear\n";
echo "php artisan livewire:discover\n";
echo "php artisan optimize:clear\n";