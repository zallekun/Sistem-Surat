<?php
/**
 * Script untuk test dan update status pengajuan
 * File: test_pengajuan_status.php
 * 
 * Jalankan: php test_pengajuan_status.php
 */

echo "===== TEST & UPDATE PENGAJUAN STATUS =====\n\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    // Test 1: Cari pengajuan MA
    echo "1. Mencari pengajuan Surat Mahasiswa Aktif (MA)...\n";
    
    $maPengajuan = \App\Models\PengajuanSurat::with(['jenisSurat', 'prodi.fakultas'])
        ->whereHas('jenisSurat', function($q) {
            $q->where('kode_surat', 'MA');
        })
        ->orderBy('id', 'desc')
        ->get();
    
    if ($maPengajuan->count() > 0) {
        echo "✅ Ditemukan {$maPengajuan->count()} pengajuan MA\n\n";
        
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
            
            // Additional data check
            if ($pengajuan->additional_data) {
                $additionalData = json_decode($pengajuan->additional_data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "   Additional Data: " . implode(', ', array_keys($additionalData)) . "\n";
                    
                    if (isset($additionalData['orang_tua']['nama'])) {
                        echo "   Nama Ortu: {$additionalData['orang_tua']['nama']}\n";
                    }
                } else {
                    echo "   Additional Data: JSON parsing failed\n";
                }
            } else {
                echo "   Additional Data: None\n";
            }
            
            echo "\n";
        }
        
        // Test 2: Update status untuk testing
        echo "2. Mau update status untuk testing? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($line) === 'y') {
            echo "\nPilih pengajuan mana yang akan diupdate (1-{$maPengajuan->count()}): ";
            $handle = fopen("php://stdin", "r");
            $choice = trim(fgets($handle));
            fclose($handle);
            
            $index = intval($choice) - 1;
            if (isset($maPengajuan[$index])) {
                $selectedPengajuan = $maPengajuan[$index];
                
                echo "Status yang tersedia:\n";
                echo "1. pending - Menunggu review prodi\n";
                echo "2. processed - Sudah disetujui prodi (ini yang dibutuhkan FSI)\n";
                echo "3. approved_prodi - Disetujui prodi (alternatif FSI)\n";
                echo "4. completed - Sudah selesai\n";
                echo "5. rejected - Ditolak\n";
                
                echo "\nPilih status (1-5): ";
                $handle = fopen("php://stdin", "r");
                $statusChoice = trim(fgets($handle));
                fclose($handle);
                
                $statusMap = [
                    '1' => 'pending',
                    '2' => 'processed',
                    '3' => 'approved_prodi', 
                    '4' => 'completed',
                    '5' => 'rejected'
                ];
                
                if (isset($statusMap[$statusChoice])) {
                    $newStatus = $statusMap[$statusChoice];
                    
                    $selectedPengajuan->update(['status' => $newStatus]);
                    
                    echo "✅ Status berhasil diupdate ke: {$newStatus}\n";
                    echo "🔗 URL Test: /fakultas/surat/{$selectedPengajuan->id}\n";
                    
                    // Test URL generation
                    $baseUrl = config('app.url', 'http://localhost');
                    echo "🌐 Full URL: {$baseUrl}/fakultas/surat/{$selectedPengajuan->id}\n";
                } else {
                    echo "❌ Pilihan status tidak valid\n";
                }
            } else {
                echo "❌ Pilihan pengajuan tidak valid\n";
            }
        }
        
    } else {
        echo "❌ Tidak ditemukan pengajuan MA\n";
        echo "📝 Membuat sample pengajuan MA...\n";
        
        // Cari jenis surat MA
        $jenisSuratMA = \App\Models\JenisSurat::where('kode_surat', 'MA')->first();
        if (!$jenisSuratMA) {
            echo "❌ Jenis surat MA tidak ditemukan di database\n";
            echo "🔧 Buat jenis surat MA terlebih dahulu\n";
            return;
        }
        
        // Cari prodi pertama
        $prodi = \App\Models\Prodi::with('fakultas')->first();
        if (!$prodi) {
            echo "❌ Tidak ada prodi di database\n";
            return;
        }
        
        // Sample additional data
        $sampleAdditionalData = [
            'semester' => 'Ganjil',
            'tahun_akademik' => '2024/2025',
            'orang_tua' => [
                'nama' => 'Budi Santoso',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1970-05-15',
                'pekerjaan' => 'PNS',
                'nip' => '197005151990011001',
                'alamat_rumah' => 'Jl. Sudirman No. 123, Jakarta Selatan'
            ]
        ];
        
        // Create sample pengajuan
        $samplePengajuan = \App\Models\PengajuanSurat::create([
            'nim' => '2250231999',
            'nama_mahasiswa' => 'Test Student FSI',
            'email' => 'test@student.com',
            'phone' => '081234567890',
            'prodi_id' => $prodi->id,
            'jenis_surat_id' => $jenisSuratMA->id,
            'keperluan' => 'Testing FSI System - Pengajuan Surat Mahasiswa Aktif',
            'additional_data' => json_encode($sampleAdditionalData),
            'tracking_token' => 'TRK-' . strtoupper(Str::random(8)),
            'status' => 'processed' // Ready for FSI testing
        ]);
        
        echo "✅ Sample pengajuan MA berhasil dibuat:\n";
        echo "   ID: {$samplePengajuan->id}\n";
        echo "   NIM: {$samplePengajuan->nim}\n";
        echo "   Nama: {$samplePengajuan->nama_mahasiswa}\n";
        echo "   Status: {$samplePengajuan->status}\n";
        echo "   Token: {$samplePengajuan->tracking_token}\n";
        echo "🔗 URL Test: /fakultas/surat/{$samplePengajuan->id}\n";
    }
    
    // Test 3: Check barcode signatures
    echo "\n3. Checking barcode signatures...\n";
    
    $barcodeCount = \App\Models\BarcodeSignature::where('is_active', true)->count();
    echo "📊 Active barcode signatures: {$barcodeCount}\n";
    
    if ($barcodeCount === 0) {
        echo "⚠️  No barcode signatures found\n";
        echo "🔧 Buat sample barcode...\n";
        
        // Create sample barcode
        $sampleBarcode = \App\Models\BarcodeSignature::create([
            'fakultas_id' => null, // General use
            'pejabat_nama' => 'AGUS KOMARUDIN, S.Kom., M.T.',
            'pejabat_nid' => '4121 758 78',
            'pejabat_jabatan' => 'WAKIL DEKAN III',
            'pejabat_pangkat' => 'PENATA MUDA TK.I – III/B',
            'barcode_path' => 'barcode-signatures/sample_barcode.png',
            'is_active' => true,
            'description' => 'Sample barcode for FSI testing'
        ]);
        
        echo "✅ Sample barcode signature created\n";
        
        // Create dummy barcode file
        $barcodeDir = storage_path('app/public/barcode-signatures');
        if (!is_dir($barcodeDir)) {
            mkdir($barcodeDir, 0755, true);
        }
        
        $barcodePath = $barcodeDir . '/sample_barcode.png';
        if (!file_exists($barcodePath) && extension_loaded('gd')) {
            $img = imagecreate(200, 80);
            $bg = imagecolorallocate($img, 255, 255, 255);
            $text_color = imagecolorallocate($img, 0, 0, 0);
            imagestring($img, 5, 10, 30, 'SAMPLE BARCODE', $text_color);
            
            // Draw barcode-like lines
            for ($i = 20; $i < 180; $i += 3) {
                imageline($img, $i, 10, $i, 25, $text_color);
            }
            
            imagepng($img, $barcodePath);
            imagedestroy($img);
            echo "✅ Sample barcode image created\n";
        }
    } else {
        echo "✅ Barcode signatures available\n";
        
        $barcodes = \App\Models\BarcodeSignature::where('is_active', true)->get();
        foreach ($barcodes as $barcode) {
            echo "   • {$barcode->pejabat_nama} - {$barcode->pejabat_jabatan}\n";
        }
    }
    
    echo "\n===== TEST COMPLETE =====\n";
    echo "🚀 Ready to test FSI system!\n";
    echo "👉 Login sebagai staff_fakultas\n";
    echo "👉 Akses pengajuan dengan status 'processed' dan jenis 'MA'\n";
    echo "👉 Button FSI harus muncul\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}