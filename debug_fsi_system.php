<?php
/**
 * Debug Script untuk FSI Surat System
 * File: debug_fsi_system.php
 * 
 * Jalankan: php debug_fsi_system.php
 */

class FSISystemDebugger {
    private $errors = [];
    private $warnings = [];
    private $info = [];
    
    public function run() {
        echo "\n===== FSI SYSTEM COMPREHENSIVE DEBUG =====\n\n";
        
        $this->step1_CheckDatabase();
        $this->step2_CheckControllers();
        $this->step3_CheckRoutes();
        $this->step4_CheckViews();
        $this->step5_CheckModels();
        $this->step6_TestDataFlow();
        $this->step7_CheckPengajuanData();
        
        $this->printSummary();
    }
    
    private function step1_CheckDatabase() {
        echo "[STEP 1] Checking Database\n";
        echo "-----------------------------\n";
        
        try {
            require_once 'vendor/autoload.php';
            $app = require_once 'bootstrap/app.php';
            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            $kernel->bootstrap();
            
            // Test database connection
            \DB::connection()->getPdo();
            echo "✅ Database connection OK\n";
            
            // Check pengajuan_surat table
            $pengajuanCount = \DB::table('pengajuan_surat')->count();
            echo "📊 Total pengajuan_surat: $pengajuanCount\n";
            
            if ($pengajuanCount > 0) {
                // Get sample pengajuan data
                $sample = \DB::table('pengajuan_surat')
                    ->select('id', 'nim', 'nama_mahasiswa', 'status', 'jenis_surat_id', 'prodi_id', 'additional_data', 'created_at')
                    ->orderBy('id', 'desc')
                    ->first();
                
                echo "🔍 Sample pengajuan data:\n";
                echo "   ID: " . ($sample->id ?? 'NULL') . "\n";
                echo "   NIM: " . ($sample->nim ?? 'NULL') . "\n";
                echo "   Nama: " . ($sample->nama_mahasiswa ?? 'NULL') . "\n";
                echo "   Status: " . ($sample->status ?? 'NULL') . "\n";
                echo "   Jenis Surat ID: " . ($sample->jenis_surat_id ?? 'NULL') . "\n";
                echo "   Prodi ID: " . ($sample->prodi_id ?? 'NULL') . "\n";
                echo "   Additional Data: " . (substr($sample->additional_data ?? 'NULL', 0, 100)) . "\n";
                
                // Check related data
                if ($sample->jenis_surat_id) {
                    $jenisSurat = \DB::table('jenis_surat')->where('id', $sample->jenis_surat_id)->first();
                    echo "   Jenis Surat: " . ($jenisSurat->nama_jenis ?? 'NOT FOUND') . " (" . ($jenisSurat->kode_surat ?? 'NULL') . ")\n";
                }
                
                if ($sample->prodi_id) {
                    $prodi = \DB::table('prodi')->where('id', $sample->prodi_id)->first();
                    echo "   Prodi: " . ($prodi->nama_prodi ?? 'NOT FOUND') . "\n";
                    
                    if ($prodi && isset($prodi->fakultas_id)) {
                        $fakultas = \DB::table('fakultas')->where('id', $prodi->fakultas_id)->first();
                        echo "   Fakultas: " . ($fakultas->nama_fakultas ?? 'NOT FOUND') . "\n";
                    }
                }
            }
            
            // Check barcode_signatures table
            if (\Schema::hasTable('barcode_signatures')) {
                $barcodeCount = \DB::table('barcode_signatures')->where('is_active', true)->count();
                echo "📊 Active barcode signatures: $barcodeCount\n";
            } else {
                echo "❌ Table 'barcode_signatures' not found\n";
                $this->errors[] = "Missing barcode_signatures table";
            }
            
            // Check processed pengajuan for MA
            $processedMA = \DB::table('pengajuan_surat as p')
                ->join('jenis_surat as js', 'p.jenis_surat_id', '=', 'js.id')
                ->where('p.status', 'processed')
                ->where('js.kode_surat', 'MA')
                ->count();
            echo "📊 Processed MA pengajuan: $processedMA\n";
            
            if ($processedMA == 0) {
                $this->warnings[] = "No processed MA pengajuan found";
                
                // Try to find any MA pengajuan
                $anyMA = \DB::table('pengajuan_surat as p')
                    ->join('jenis_surat as js', 'p.jenis_surat_id', '=', 'js.id')
                    ->where('js.kode_surat', 'MA')
                    ->count();
                echo "📊 Total MA pengajuan (any status): $anyMA\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Database Error: " . $e->getMessage() . "\n";
            $this->errors[] = "Database connection failed";
        }
        
        echo "\n";
    }
    
    private function step2_CheckControllers() {
        echo "[STEP 2] Checking Controllers\n";
        echo "------------------------------\n";
        
        // Check FakultasStaffController
        $fakultasController = 'app/Http/Controllers/FakultasStaffController.php';
        if (file_exists($fakultasController)) {
            echo "✅ FakultasStaffController exists\n";
            
            $content = file_get_contents($fakultasController);
            
            // Check show method
            if (strpos($content, 'public function show(') !== false) {
                echo "✅ show() method exists\n";
                
                // Check for proper data setting
                $checks = [
                    '$surat->type = \'pengajuan\'' => 'Sets surat type',
                    '$surat->original_pengajuan = $pengajuan' => 'Sets original_pengajuan',
                    'return view(' => 'Returns view',
                    'fakultas.surat.show' => 'Uses correct view'
                ];
                
                foreach ($checks as $pattern => $description) {
                    if (strpos($content, $pattern) !== false) {
                        echo "✅ $description\n";
                    } else {
                        echo "⚠️  Missing: $description\n";
                        $this->warnings[] = "FakultasStaffController: $description";
                    }
                }
            } else {
                echo "❌ show() method not found\n";
                $this->errors[] = "FakultasStaffController missing show method";
            }
        } else {
            echo "❌ FakultasStaffController not found\n";
            $this->errors[] = "FakultasStaffController missing";
        }
        
        // Check SuratFSIController
        $fsiController = 'app/Http/Controllers/SuratFSIController.php';
        if (file_exists($fsiController)) {
            echo "✅ SuratFSIController exists\n";
            
            $content = file_get_contents($fsiController);
            if (strpos($content, 'public function preview(') !== false) {
                echo "✅ preview() method exists\n";
            } else {
                echo "❌ preview() method not found\n";
                $this->errors[] = "SuratFSIController missing preview method";
            }
            
            if (strpos($content, 'public function generatePdf(') !== false) {
                echo "✅ generatePdf() method exists\n";
            } else {
                echo "❌ generatePdf() method not found\n";
                $this->errors[] = "SuratFSIController missing generatePdf method";
            }
        } else {
            echo "❌ SuratFSIController not found\n";
            $this->errors[] = "SuratFSIController missing";
        }
        
        echo "\n";
    }
    
    private function step3_CheckRoutes() {
        echo "[STEP 3] Checking Routes\n";
        echo "-------------------------\n";
        
        $routesPath = 'routes/web.php';
        if (file_exists($routesPath)) {
            $content = file_get_contents($routesPath);
            
            $routes = [
                'fakultas/surat/fsi/preview' => 'FSI Preview route',
                'fakultas/surat/fsi/generate-pdf' => 'FSI Generate PDF route',
                'SuratFSIController' => 'SuratFSIController reference'
            ];
            
            foreach ($routes as $pattern => $description) {
                if (strpos($content, $pattern) !== false) {
                    echo "✅ $description\n";
                } else {
                    echo "❌ Missing: $description\n";
                    $this->errors[] = "Route missing: $description";
                }
            }
        } else {
            echo "❌ routes/web.php not found\n";
            $this->errors[] = "Routes file missing";
        }
        
        echo "\n";
    }
    
    private function step4_CheckViews() {
        echo "[STEP 4] Checking Views\n";
        echo "------------------------\n";
        
        $views = [
            'resources/views/shared/pengajuan/show.blade.php' => 'Shared detail view',
            'resources/views/surat/fsi/preview-with-signature.blade.php' => 'FSI Preview view',
            'resources/views/surat/pdf/fsi-surat-final.blade.php' => 'FSI PDF template',
            'resources/views/fakultas/surat/show.blade.php' => 'Fakultas detail view'
        ];
        
        foreach ($views as $path => $description) {
            if (file_exists($path)) {
                echo "✅ $description exists\n";
                
                // Check for key content
                if ($path === 'resources/views/shared/pengajuan/show.blade.php') {
                    $content = file_get_contents($path);
                    
                    $checks = [
                        'previewSuratFSI' => 'Preview FSI function',
                        'generateSuratFSI' => 'Generate FSI function',
                        '$isFromFakultas' => 'Fakultas context check',
                        'kode_surat === \'MA\'' => 'MA surat check'
                    ];
                    
                    foreach ($checks as $pattern => $desc) {
                        if (strpos($content, $pattern) !== false) {
                            echo "  ✅ Has: $desc\n";
                        } else {
                            echo "  ⚠️  Missing: $desc\n";
                            $this->warnings[] = "View missing: $desc";
                        }
                    }
                }
            } else {
                echo "❌ $description missing\n";
                $this->errors[] = "View missing: $description";
            }
        }
        
        echo "\n";
    }
    
    private function step5_CheckModels() {
        echo "[STEP 5] Checking Models\n";
        echo "-------------------------\n";
        
        try {
            // Check if models exist and can be loaded
            if (class_exists('\App\Models\PengajuanSurat')) {
                echo "✅ PengajuanSurat model exists\n";
                
                // Test model relationships
                $sample = \App\Models\PengajuanSurat::with(['jenisSurat', 'prodi.fakultas'])->first();
                if ($sample) {
                    echo "✅ Can load pengajuan with relationships\n";
                    echo "  Sample data:\n";
                    echo "    ID: {$sample->id}\n";
                    echo "    NIM: " . ($sample->nim ?? 'NULL') . "\n";
                    echo "    Nama: " . ($sample->nama_mahasiswa ?? 'NULL') . "\n";
                    echo "    Jenis Surat: " . ($sample->jenisSurat->nama_jenis ?? 'NULL') . "\n";
                    echo "    Prodi: " . ($sample->prodi->nama_prodi ?? 'NULL') . "\n";
                    echo "    Fakultas: " . ($sample->prodi->fakultas->nama_fakultas ?? 'NULL') . "\n";
                } else {
                    echo "⚠️  No pengajuan data found\n";
                    $this->warnings[] = "No pengajuan data in database";
                }
            } else {
                echo "❌ PengajuanSurat model not found\n";
                $this->errors[] = "PengajuanSurat model missing";
            }
            
            if (class_exists('\App\Models\BarcodeSignature')) {
                echo "✅ BarcodeSignature model exists\n";
                
                $barcodeCount = \App\Models\BarcodeSignature::where('is_active', true)->count();
                echo "  Active barcodes: $barcodeCount\n";
            } else {
                echo "❌ BarcodeSignature model not found\n";
                $this->errors[] = "BarcodeSignature model missing";
            }
            
        } catch (Exception $e) {
            echo "❌ Model Error: " . $e->getMessage() . "\n";
            $this->errors[] = "Model loading failed";
        }
        
        echo "\n";
    }
    
    private function step6_TestDataFlow() {
        echo "[STEP 6] Testing Data Flow\n";
        echo "---------------------------\n";
        
        try {
            // Test FakultasStaffController show method behavior
            $pengajuan = \App\Models\PengajuanSurat::with(['jenisSurat', 'prodi.fakultas'])->first();
            
            if (!$pengajuan) {
                echo "❌ No pengajuan found for testing\n";
                $this->errors[] = "No test data available";
                echo "\n";
                return;
            }
            
            echo "📊 Testing with Pengajuan ID: {$pengajuan->id}\n";
            
            // Simulate what FakultasStaffController should do
            $surat = new \stdClass();
            $surat->id = $pengajuan->id;
            $surat->type = 'pengajuan';
            $surat->pengajuan = $pengajuan;
            $surat->original_pengajuan = $pengajuan;
            
            echo "✅ Surat object created successfully\n";
            echo "  Type: {$surat->type}\n";
            echo "  Has pengajuan: " . (isset($surat->pengajuan) ? 'YES' : 'NO') . "\n";
            echo "  Has original_pengajuan: " . (isset($surat->original_pengajuan) ? 'YES' : 'NO') . "\n";
            
            // Test view data detection
            $isFromFakultas = isset($surat->type) && $surat->type === 'pengajuan' && isset($surat->original_pengajuan);
            echo "  isFromFakultas would be: " . ($isFromFakultas ? 'TRUE' : 'FALSE') . "\n";
            
            // Test additional data parsing
            if ($pengajuan->additional_data) {
                $additionalData = json_decode($pengajuan->additional_data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "✅ Additional data parses correctly\n";
                    echo "  Keys: " . implode(', ', array_keys($additionalData)) . "\n";
                } else {
                    echo "❌ Additional data JSON parsing failed\n";
                    $this->errors[] = "Additional data is not valid JSON";
                }
            } else {
                echo "⚠️  No additional data found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Data Flow Error: " . $e->getMessage() . "\n";
            $this->errors[] = "Data flow test failed";
        }
        
        echo "\n";
    }
    
    private function step7_CheckPengajuanData() {
        echo "[STEP 7] Analyzing Specific Pengajuan Issues\n";
        echo "---------------------------------------------\n";
        
        try {
            // Find pengajuan that should work with FSI system
            $maPengajuan = \DB::table('pengajuan_surat as p')
                ->join('jenis_surat as js', 'p.jenis_surat_id', '=', 'js.id')
                ->join('prodi as pr', 'p.prodi_id', '=', 'pr.id')
                ->join('fakultas as f', 'pr.fakultas_id', '=', 'f.id')
                ->where('js.kode_surat', 'MA')
                ->select('p.*', 'js.nama_jenis', 'js.kode_surat', 'pr.nama_prodi', 'f.nama_fakultas')
                ->orderBy('p.id', 'desc')
                ->first();
            
            if ($maPengajuan) {
                echo "✅ Found MA pengajuan for testing\n";
                echo "  ID: {$maPengajuan->id}\n";
                echo "  NIM: " . ($maPengajuan->nim ?? 'NULL') . "\n";
                echo "  Nama: " . ($maPengajuan->nama_mahasiswa ?? 'NULL') . "\n";
                echo "  Status: " . ($maPengajuan->status ?? 'NULL') . "\n";
                echo "  Jenis: {$maPengajuan->nama_jenis} ({$maPengajuan->kode_surat})\n";
                echo "  Prodi: {$maPengajuan->nama_prodi}\n";
                echo "  Fakultas: {$maPengajuan->nama_fakultas}\n";
                
                // Check if this should show buttons
                $shouldShowButtons = in_array($maPengajuan->status, ['processed', 'approved_prodi']);
                echo "  Should show FSI buttons: " . ($shouldShowButtons ? 'YES' : 'NO') . "\n";
                
                if (!$shouldShowButtons) {
                    echo "⚠️  Status '{$maPengajuan->status}' won't trigger FSI buttons\n";
                    echo "     Need status: 'processed' or 'approved_prodi'\n";
                    
                    // Offer to fix this
                    echo "\n🔧 To fix, run this SQL:\n";
                    echo "   UPDATE pengajuan_surat SET status='processed' WHERE id={$maPengajuan->id};\n";
                }
                
            } else {
                echo "❌ No MA (Mahasiswa Aktif) pengajuan found\n";
                $this->errors[] = "No MA pengajuan available for testing";
                
                // Check what jenis_surat exist
                $jenisAvailable = \DB::table('jenis_surat')->select('id', 'nama_jenis', 'kode_surat')->get();
                echo "📊 Available jenis_surat:\n";
                foreach ($jenisAvailable as $jenis) {
                    echo "   {$jenis->id}: {$jenis->nama_jenis} ({$jenis->kode_surat})\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Pengajuan Analysis Error: " . $e->getMessage() . "\n";
            $this->errors[] = "Pengajuan analysis failed";
        }
        
        echo "\n";
    }
    
    private function printSummary() {
        echo "===== SUMMARY =====\n";
        
        if (count($this->errors) > 0) {
            echo "❌ CRITICAL ERRORS:\n";
            foreach ($this->errors as $error) {
                echo "   • $error\n";
            }
            echo "\n";
        }
        
        if (count($this->warnings) > 0) {
            echo "⚠️  WARNINGS:\n";
            foreach ($this->warnings as $warning) {
                echo "   • $warning\n";
            }
            echo "\n";
        }
        
        if (count($this->errors) == 0 && count($this->warnings) == 0) {
            echo "✅ No major issues found!\n\n";
        }
        
        echo "🔧 QUICK FIXES:\n";
        echo "1. If no data showing: Check FakultasStaffController show() method\n";
        echo "2. If no buttons: Ensure status is 'processed' and jenis_surat is 'MA'\n";
        echo "3. If routes missing: Add FSI routes to web.php\n";
        echo "4. If preview broken: Check SuratFSIController exists\n";
        echo "\n";
        
        echo "🚀 NEXT STEPS:\n";
        echo "1. Fix critical errors first\n";
        echo "2. Update pengajuan status to 'processed' for testing\n";
        echo "3. Test with staff_fakultas user on MA pengajuan\n";
        echo "4. Check browser console for JavaScript errors\n";
        echo "\n";
    }
}

// Run the debugger
try {
    if (!file_exists('artisan')) {
        throw new Exception("Must run from Laravel root directory!");
    }
    
    $debugger = new FSISystemDebugger();
    $debugger->run();
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Make sure you're in Laravel root directory\n";
}