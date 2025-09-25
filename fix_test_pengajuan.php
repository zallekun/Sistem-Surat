<?php
/**
 * Fixed Test Script untuk Pengajuan Status
 * File: fix_test_pengajuan.php
 */

echo "===== FIXED TEST & UPDATE PENGAJUAN STATUS =====\n\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    // Test 1: Cari pengajuan MA dengan proper handling
    echo "1. Mencari pengajuan Surat Mahasiswa Aktif (MA)...\n";
    
    $maPengajuan = \App\Models\PengajuanSurat::with(['jenisSurat', 'prodi.fakultas'])
        ->whereHas('jenisSurat', function($q) {
            $q->where('kode_surat', 'MA');
        })
        ->orderBy('id', 'desc')
        ->limit(5) // Limit untuk testing
        ->get();
    
    if ($maPengajuan->count() > 0) {
        echo "✅ Ditemukan {$maPengajuan->count()} pengajuan MA (showing top 5)\n\n";
        
        foreach ($maPengajuan as $i => $pengajuan) {
            echo "📋 Pengajuan #" . ($i + 1) . ":\n";
            echo "   ID: {$pengajuan->id}\n";
            echo "   NIM: {$pengajuan->nim}\n";
            echo "   Nama: {$pengajuan->nama_mahasiswa}\n";
            echo "   Status: {$pengajuan->status}\n";
            echo "   Jenis: {$pengajuan->jenisSurat->nama_jenis} ({$pengajuan->jenisSurat->kode_surat})\n";
            echo "   Prodi: {$pengajuan->prodi->nama_prodi}\n";
            echo "   Fakultas: " . ($pengajuan->prodi->fakultas->nama_fakultas ?? 'N/A') . "\n";
            echo "   Created: {$pengajuan->created_at->format('d/m/Y H:i')}\n";
            
            // Fixed additional data parsing
            if ($pengajuan->additional_data) {
                try {
                    // Handle both string and array types
                    if (is_string($pengajuan->additional_data)) {
                        $additionalData = json_decode($pengajuan->additional_data, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            echo "   Additional Data: JSON decode error\n";
                            continue;
                        }
                    } elseif (is_array($pengajuan->additional_data)) {
                        $additionalData = $pengajuan->additional_data;
                    } else {
                        echo "   Additional Data: Unknown format\n";
                        continue;
                    }
                    
                    echo "   Additional Data: " . implode(', ', array_keys($additionalData)) . "\n";
                    
                    if (isset($additionalData['orang_tua']['nama'])) {
                        echo "   Nama Ortu: {$additionalData['orang_tua']['nama']}\n";
                    }
                    
                    // Check if ready for FSI testing
                    $hasOrangTua = isset($additionalData['orang_tua']['nama']);
                    $readyForFSI = in_array($pengajuan->status, ['processed', 'approved_prodi']);
                    
                    if ($hasOrangTua && $readyForFSI) {
                        echo "   ✅ READY FOR FSI TESTING\n";
                    } elseif ($hasOrangTua && !$readyForFSI) {
                        echo "   ⚠️  Has data but status is '{$pengajuan->status}' (need 'processed')\n";
                    } elseif (!$hasOrangTua && $readyForFSI) {
                        echo "   ⚠️  Good status but missing orang_tua data\n";
                    } else {
                        echo "   ❌ Not ready (wrong status + missing data)\n";
                    }
                    
                } catch (\Exception $e) {
                    echo "   Additional Data: Parse error - " . $e->getMessage() . "\n";
                }
            } else {
                echo "   Additional Data: None\n";
            }
            
            echo "\n";
        }
        
        // Find the best candidate for FSI testing
        $bestCandidate = null;
        foreach ($maPengajuan as $pengajuan) {
            $additionalData = null;
            if ($pengajuan->additional_data) {
                if (is_string($pengajuan->additional_data)) {
                    $additionalData = json_decode($pengajuan->additional_data, true);
                } elseif (is_array($pengajuan->additional_data)) {
                    $additionalData = $pengajuan->additional_data;
                }
            }
            
            $hasOrangTua = isset($additionalData['orang_tua']['nama']);
            if ($hasOrangTua) {
                $bestCandidate = $pengajuan;
                break;
            }
        }
        
        if ($bestCandidate) {
            echo "🎯 Best candidate for FSI testing: ID {$bestCandidate->id}\n";
            echo "   Current status: {$bestCandidate->status}\n";
            
            // Auto-update to processed if needed
            if ($bestCandidate->status !== 'processed' && $bestCandidate->status !== 'approved_prodi') {
                echo "   Updating status to 'processed' for testing...\n";
                $bestCandidate->update(['status' => 'processed']);
                echo "   ✅ Status updated to 'processed'\n";
            }
            
            echo "\n🔗 Test URLs:\n";
            echo "   Detail: /fakultas/surat/{$bestCandidate->id}\n";
            echo "   Preview: /fakultas/surat/fsi/preview/{$bestCandidate->id}\n";
            
        } else {
            echo "❌ No suitable candidate found (all missing orang_tua data)\n";
        }
        
    } else {
        echo "❌ Tidak ditemukan pengajuan MA\n";
    }
    
    // Test 2: Check routes exist
    echo "\n2. Checking if FSI routes are accessible...\n";
    
    try {
        $routeCollection = app('router')->getRoutes();
        $fsiRoutes = [];
        
        foreach ($routeCollection as $route) {
            $uri = $route->uri();
            if (strpos($uri, 'fakultas/surat/fsi') !== false) {
                $fsiRoutes[] = $uri . ' [' . implode(',', $route->methods()) . ']';
            }
        }
        
        if (count($fsiRoutes) > 0) {
            echo "✅ FSI routes found:\n";
            foreach ($fsiRoutes as $route) {
                echo "   • {$route}\n";
            }
        } else {
            echo "❌ No FSI routes found!\n";
            echo "🔧 Add these routes to routes/web.php:\n\n";
            echo "Route::middleware(['auth', 'role:staff_fakultas'])->group(function () {\n";
            echo "    Route::get('fakultas/surat/fsi/preview/{id}', [App\\Http\\Controllers\\SuratFSIController::class, 'preview']);\n";
            echo "    Route::post('fakultas/surat/fsi/generate-pdf/{id}', [App\\Http\\Controllers\\SuratFSIController::class, 'generatePdf']);\n";
            echo "});\n\n";
        }
        
    } catch (\Exception $e) {
        echo "⚠️  Route check failed: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Quick barcode setup
    echo "\n3. Setting up barcode signature...\n";
    
    $barcodeCount = \App\Models\BarcodeSignature::where('is_active', true)->count();
    if ($barcodeCount === 0) {
        echo "Creating sample barcode...\n";
        
        \App\Models\BarcodeSignature::create([
            'fakultas_id' => null,
            'pejabat_nama' => 'AGUS KOMARUDIN, S.Kom., M.T.',
            'pejabat_nid' => '4121 758 78',
            'pejabat_jabatan' => 'WAKIL DEKAN III',
            'pejabat_pangkat' => 'PENATA MUDA TK.I – III/B',
            'barcode_path' => 'barcode-signatures/sample.png',
            'is_active' => true
        ]);
        
        echo "✅ Sample barcode signature created\n";
    } else {
        echo "✅ Barcode signatures available: {$barcodeCount}\n";
    }
    
    echo "\n===== QUICK SETUP COMPLETE =====\n";
    echo "🚀 READY TO TEST!\n\n";
    echo "Next steps:\n";
    echo "1. Add FSI routes to routes/web.php (if not shown above)\n";
    echo "2. Make sure SuratFSIController.php exists\n";
    echo "3. Create fakultas/surat/show.blade.php view\n";
    echo "4. Login as staff_fakultas and test the detail page\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}