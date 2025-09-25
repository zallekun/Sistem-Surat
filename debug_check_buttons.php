<?php
/**
 * Debug Script untuk Check Button Generate Surat
 * File: debug_check_buttons.php
 * 
 * Jalankan: php debug_check_buttons.php
 */

class ButtonDebugger {
    private $errors = [];
    private $warnings = [];
    
    public function run() {
        echo "\n=====================================\n";
        echo "   DEBUG CHECK BUTTONS FAKULTAS     \n";
        echo "=====================================\n\n";
        
        $this->step1_CheckViewFile();
        $this->step2_CheckControllerLogic();
        $this->step3_CheckRoutes();
        $this->step4_CheckUserRole();
        $this->step5_TestData();
        $this->step6_FixView();
        
        $this->printSummary();
    }
    
    private function step1_CheckViewFile() {
        echo "[STEP 1] Checking View File\n";
        echo "----------------------------\n";
        
        $viewPath = 'resources/views/shared/pengajuan/show.blade.php';
        
        if (!file_exists($viewPath)) {
            $this->errors[] = "View file not found: $viewPath";
            echo "❌ View file not found\n\n";
            return;
        }
        
        $content = file_get_contents($viewPath);
        
        // Check for button conditions
        $checks = [
            'isFromFakultas check' => 'isFromFakultas',
            'Status check' => "in_array(\$status, ['processed', 'approved_prodi'])",
            'Preview button' => 'previewSuratFSI',
            'Generate button' => 'generateSuratFSI',
            'MA check' => "kode_surat === 'MA'"
        ];
        
        foreach ($checks as $name => $pattern) {
            if (strpos($content, $pattern) !== false) {
                echo "✅ Found: $name\n";
            } else {
                $this->warnings[] = "Missing in view: $name";
                echo "⚠️  Missing: $name\n";
            }
        }
        
        // Check actual button code
        if (strpos($content, '@if($isFromFakultas)') !== false) {
            echo "✅ Faculty condition exists\n";
            
            // Extract and show the button section
            preg_match('/@if\(\$isFromFakultas\)(.*?)@elseif/s', $content, $matches);
            if (isset($matches[1])) {
                $buttonSection = substr($matches[1], 0, 500);
                echo "\n📄 Button Section Preview:\n";
                echo "---\n" . trim($buttonSection) . "\n---\n";
            }
        }
        
        echo "\n";
    }
    
    private function step2_CheckControllerLogic() {
        echo "[STEP 2] Checking Controller Logic\n";
        echo "-----------------------------------\n";
        
        $controllerPath = 'app/Http/Controllers/FakultasStaffController.php';
        
        if (!file_exists($controllerPath)) {
            $this->errors[] = "Controller not found: $controllerPath";
            echo "❌ Controller file not found\n\n";
            return;
        }
        
        $content = file_get_contents($controllerPath);
        
        // Check show method
        if (preg_match('/public function show\([^)]*\).*?\{(.*?)\n    \}/s', $content, $matches)) {
            $showMethod = $matches[1];
            
            // Check what's being set
            $checks = [
                'surat->type = pengajuan' => '$surat->type = \'pengajuan\'',
                'original_pengajuan set' => '$surat->original_pengajuan = $pengajuan',
                'view return' => 'return view(',
                'shared view' => 'shared.pengajuan.show'
            ];
            
            foreach ($checks as $name => $pattern) {
                if (strpos($showMethod, $pattern) !== false) {
                    echo "✅ $name\n";
                } else {
                    $this->warnings[] = "Controller missing: $name";
                    echo "⚠️  Missing: $name\n";
                }
            }
        } else {
            echo "❌ Cannot find show() method\n";
        }
        
        echo "\n";
    }
    
    private function step3_CheckRoutes() {
        echo "[STEP 3] Checking Routes\n";
        echo "-------------------------\n";
        
        $routesPath = 'routes/web.php';
        $content = file_get_contents($routesPath);
        
        $routes = [
            'FSI Preview' => 'fakultas/surat/fsi/preview',
            'FSI Generate' => 'fakultas/surat/fsi/generate-pdf',
            'SuratFSIController' => 'SuratFSIController'
        ];
        
        foreach ($routes as $name => $pattern) {
            if (strpos($content, $pattern) !== false) {
                echo "✅ Route exists: $name\n";
            } else {
                $this->errors[] = "Route missing: $name";
                echo "❌ Missing route: $name\n";
            }
        }
        
        echo "\n";
    }
    
    private function step4_CheckUserRole() {
        echo "[STEP 4] Checking User & Role Setup\n";
        echo "------------------------------------\n";
        
        try {
            require_once 'vendor/autoload.php';
            $app = require_once 'bootstrap/app.php';
            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            $kernel->bootstrap();
            
            // Check if there's a staff_fakultas user
            $staffFakultas = \App\Models\User::whereHas('role', function($q) {
                $q->where('name', 'staff_fakultas');
            })->first();
            
            if ($staffFakultas) {
                echo "✅ Found staff_fakultas user: " . $staffFakultas->nama . "\n";
            } else {
                $this->warnings[] = "No staff_fakultas user found";
                echo "⚠️  No staff_fakultas user found in database\n";
            }
            
            // Check role middleware
            if (class_exists('\App\Http\Middleware\CheckRole')) {
                echo "✅ CheckRole middleware exists\n";
            } else {
                echo "⚠️  CheckRole middleware not found\n";
            }
            
        } catch (Exception $e) {
            echo "⚠️  Cannot check database: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function step5_TestData() {
        echo "[STEP 5] Checking Test Data\n";
        echo "----------------------------\n";
        
        try {
            // Check for pengajuan with status 'processed'
            $processedCount = \App\Models\PengajuanSurat::where('status', 'processed')->count();
            echo "📊 Pengajuan with status 'processed': $processedCount\n";
            
            if ($processedCount == 0) {
                $this->warnings[] = "No pengajuan with status 'processed'";
                echo "⚠️  No processed pengajuan found - buttons won't show!\n";
            }
            
            // Check jenis surat MA
            $maCount = \App\Models\PengajuanSurat::whereHas('jenisSurat', function($q) {
                $q->where('kode_surat', 'MA');
            })->where('status', 'processed')->count();
            
            echo "📊 Processed MA (Mahasiswa Aktif) surat: $maCount\n";
            
            if ($maCount == 0) {
                echo "⚠️  No MA surat with processed status\n";
            }
            
            // Check BarcodeSignature
            if (class_exists('\App\Models\BarcodeSignature')) {
                $barcodeCount = \App\Models\BarcodeSignature::where('is_active', true)->count();
                echo "📊 Active barcode signatures: $barcodeCount\n";
                
                if ($barcodeCount == 0) {
                    $this->warnings[] = "No active barcode signatures";
                    echo "⚠️  No barcode signatures available\n";
                }
            } else {
                echo "❌ BarcodeSignature model not found\n";
            }
            
        } catch (Exception $e) {
            echo "⚠️  Error checking data: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function step6_FixView() {
        echo "[STEP 6] Attempting to Fix View\n";
        echo "--------------------------------\n";
        
        $viewPath = 'resources/views/shared/pengajuan/show.blade.php';
        
        if (!file_exists($viewPath)) {
            echo "❌ Cannot fix - view file not found\n";
            return;
        }
        
        // Backup first
        $backupPath = $viewPath . '.backup_' . date('Y-m-d_His');
        copy($viewPath, $backupPath);
        echo "✅ Backup created: " . basename($backupPath) . "\n";
        
        $content = file_get_contents($viewPath);
        
        // Find the Action Buttons section
        if (strpos($content, '<!-- Action Buttons -->') === false) {
            echo "⚠️  Cannot find Action Buttons section marker\n";
            return;
        }
        
        // Check if the correct button code exists
        $correctButtonCode = '@if($isFromFakultas)
            @if(in_array($status, [\'processed\', \'approved_prodi\']))
                @if($jenisSurat && $jenisSurat->kode_surat === \'MA\')
                    <button onclick="previewSuratFSI({{ isset($surat) ? $surat->id : $pengajuan->id }})"';
        
        if (strpos($content, 'previewSuratFSI') === false) {
            echo "⚠️  Buttons not found in view - need to add them\n";
            
            // Prepare the correct button section
            $buttonSection = $this->getCorrectButtonSection();
            
            // Replace the action buttons section
            $pattern = '/<!-- Action Buttons -->.*?<\/div>\s*<\/div>\s*<\/div>/s';
            $content = preg_replace($pattern, $buttonSection, $content);
            
            file_put_contents($viewPath, $content);
            echo "✅ View file updated with correct buttons\n";
        } else {
            echo "✅ Buttons already exist in view\n";
        }
        
        echo "\n";
    }
    
    private function getCorrectButtonSection() {
        return '<!-- Action Buttons -->
<div class="flex justify-between items-center pt-6 border-t border-gray-200">
    @if(config(\'app.debug\'))
        <div class="text-xs text-gray-500 bg-yellow-50 px-2 py-1 rounded">
            Status: {{ $status }} | 
            Context: {{ $isFromFakultas ? \'Fakultas\' : \'Prodi\' }} |
            Data: {{ $additionalData ? count($additionalData) . \' items\' : \'none\' }}
        </div>
    @else
        <div></div>
    @endif
    
    <div class="flex space-x-3">
        @if($isFromFakultas)
            @if(in_array($status, [\'processed\', \'approved_prodi\']))
                @if($jenisSurat && $jenisSurat->kode_surat === \'MA\')
                    <button onclick="previewSuratFSI({{ isset($surat) ? $surat->id : $pengajuan->id }})" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                        <i class="fas fa-eye mr-2"></i>
                        Preview Surat
                    </button>
                    <button onclick="generateSuratFSI({{ isset($surat) ? $surat->id : $pengajuan->id }})" 
                            class="inline-flex items-center px-5 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Generate PDF Surat
                    </button>
                @else
                    <button onclick="generateSurat({{ isset($surat) ? $surat->id : $pengajuan->id }})" 
                            class="inline-flex items-center px-5 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700">
                        <i class="fas fa-file-alt mr-2"></i>
                        Generate Surat
                    </button>
                @endif
            @else
                <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-md">
                    <i class="fas fa-clock mr-2"></i>
                    Menunggu Approval Prodi
                </span>
            @endif
        @elseif(!$isFromFakultas && $status === \'pending\' && auth()->check() && auth()->user()->hasRole([\'staff_prodi\', \'kaprodi\']))
            <button onclick="showRejectModal()" 
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                <i class="fas fa-times mr-2"></i>
                Tolak
            </button>
            <button onclick="showApproveConfirm()" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                <i class="fas fa-check mr-2"></i>
                Setujui & Teruskan ke Fakultas
            </button>
        @else
            <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-md">
                <i class="fas fa-info-circle mr-2"></i>
                Status: {{ ucwords(str_replace(\'_\', \' \', $status)) }}
            </span>
        @endif
    </div>
</div>';
    }
    
    private function printSummary() {
        echo "\n=====================================\n";
        echo "           SUMMARY                   \n";
        echo "=====================================\n\n";
        
        if (count($this->errors) > 0) {
            echo "❌ ERRORS FOUND:\n";
            foreach ($this->errors as $error) {
                echo "   - $error\n";
            }
            echo "\n";
        }
        
        if (count($this->warnings) > 0) {
            echo "⚠️  WARNINGS:\n";
            foreach ($this->warnings as $warning) {
                echo "   - $warning\n";
            }
            echo "\n";
        }
        
        if (count($this->errors) == 0 && count($this->warnings) == 0) {
            echo "✅ No issues found!\n";
        }
        
        echo "\nPOSSIBLE ISSUES:\n";
        echo "1. Status pengajuan bukan 'processed' atau 'approved_prodi'\n";
        echo "2. Jenis surat bukan 'MA' (Mahasiswa Aktif)\n";
        echo "3. User bukan staff_fakultas\n";
        echo "4. View tidak mendeteksi isFromFakultas = true\n";
        echo "5. Routes untuk SuratFSIController belum ada\n";
        
        echo "\nTO FIX MANUALLY:\n";
        echo "1. Update status pengajuan: UPDATE pengajuan_surat SET status='processed' WHERE id=X;\n";
        echo "2. Check user role: Pastikan login sebagai staff_fakultas\n";
        echo "3. Check jenis surat: Pastikan kode_surat = 'MA'\n";
        echo "4. Run: php debug_surat_barcode.php (jika routes belum ada)\n\n";
    }
}

// Run
try {
    if (!file_exists('artisan')) {
        throw new Exception("Must run from Laravel root!");
    }
    
    $debugger = new ButtonDebugger();
    $debugger->run();
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>