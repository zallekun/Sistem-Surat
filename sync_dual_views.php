<?php
/**
 * Sync Script untuk Sinkronisasi Data antara Dua View Terpisah
 * File: sync_dual_views.php
 * 
 * Menjaga dua view tetap terpisah tapi dengan data handling yang konsisten
 */

class DualViewSynchronizer {
    private $backupDir = 'storage/sync_backups';
    private $backupFiles = [];
    
    public function __construct() {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    public function run() {
        echo "\n===== DUAL VIEW SYNC SCRIPT =====\n\n";
        
        if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] === 'restore') {
            $this->restoreBackups();
            return;
        }
        
        try {
            $this->step1_BackupFiles();
            $this->step2_FixFakultasController();
            $this->step3_CreateDataHelper();
            $this->step4_UpdateFakultasView();
            $this->step5_UpdateProdiView();
            
            echo "\n✅ SELESAI! Kedua view tetap terpisah tapi data handling sudah konsisten.\n";
            echo "📁 Backup tersimpan di: {$this->backupDir}\n";
            
        } catch (Exception $e) {
            echo "\n❌ ERROR: " . $e->getMessage() . "\n";
            echo "🔄 Menjalankan restore...\n";
            $this->restoreBackups();
        }
    }
    
    private function step1_BackupFiles() {
        echo "STEP 1: Backup Files\n";
        echo "--------------------\n";
        
        $files = [
            'app/Http/Controllers/FakultasStaffController.php',
            'app/Http/Controllers/SuratController.php', 
            'resources/views/fakultas/surat/show.blade.php',
            'resources/views/staff/pengajuan/show.blade.php'
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $timestamp = date('Y-m-d_H-i-s');
                $backupPath = $this->backupDir . '/' . basename($file) . '.' . $timestamp . '.backup';
                if (copy($file, $backupPath)) {
                    $this->backupFiles[$file] = $backupPath;
                    echo "✅ Backup: " . basename($file) . "\n";
                }
            }
        }
        echo "\n";
    }
    
    private function step2_FixFakultasController() {
        echo "STEP 2: Fix FakultasStaffController\n";
        echo "------------------------------------\n";
        
        $controllerPath = 'app/Http/Controllers/FakultasStaffController.php';
        
        if (!file_exists($controllerPath)) {
            echo "⚠️ File tidak ditemukan, skip...\n\n";
            return;
        }
        
        $content = file_get_contents($controllerPath);
        
        // Cari method show() dan perbaiki data handling
        $improvedShow = <<<'PHP'
    public function show($id)
    {
        $user = Auth::user();
        $user->load('prodi.fakultas');
        
        $fakultasId = $user->prodi?->fakultas_id;
        if (!$fakultasId) {
            return redirect()->route('fakultas.surat.index')
                           ->with('error', 'Anda tidak memiliki akses ke fakultas manapun');
        }
        
        // Cari di surat dulu
        $surat = Surat::with([
            'jenisSurat', 
            'currentStatus', 
            'createdBy.jabatan', 
            'tujuanJabatan', 
            'prodi.fakultas',
            'statusHistories.user',
            'statusHistories.status'
        ])->find($id);
        
        if ($surat) {
            if ($surat->prodi->fakultas_id !== $fakultasId) {
                return redirect()->route('fakultas.surat.index')
                               ->with('error', 'Anda tidak memiliki akses ke surat ini');
            }
            
            $surat->type = 'surat';
            
            // CONSISTENT DATA HANDLING - decode additional_data if exists
            if (isset($surat->additional_data)) {
                $surat->additional_data = $this->parseAdditionalData($surat->additional_data);
            }
            
            return view('fakultas.surat.show', compact('surat'));
        }
        
        // Cari di pengajuan
        $pengajuan = PengajuanSurat::with([
            'jenisSurat', 
            'prodi.fakultas'
        ])->find($id);
        
        if ($pengajuan) {
            if ($pengajuan->prodi->fakultas_id !== $fakultasId) {
                return redirect()->route('fakultas.surat.index')
                               ->with('error', 'Anda tidak memiliki akses ke pengajuan ini');
            }
            
            // CRITICAL FIX: Ensure additional_data is properly decoded
            $pengajuan->additional_data = $this->parseAdditionalData($pengajuan->additional_data);
            
            // Transform pengajuan ke format yang sama dengan staff prodi
            // Tapi tetap pakai surat wrapper untuk backward compatibility
            $surat = new \stdClass();
            $surat->id = $pengajuan->id;
            $surat->type = 'pengajuan';
            $surat->pengajuan = $pengajuan; // Use same name as staff prodi
            
            // Also keep original_pengajuan for backward compatibility
            $surat->original_pengajuan = $pengajuan;
            
            return view('fakultas.surat.show', compact('surat', 'pengajuan'));
        }
        
        return redirect()->route('fakultas.surat.index')
                       ->with('error', 'Data tidak ditemukan');
    }
    
    private function parseAdditionalData($data)
    {
        if (empty($data)) {
            return null;
        }
        
        if (is_array($data)) {
            return $data;
        }
        
        if (is_string($data)) {
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                // Log error if needed
            }
            return ['data' => $data];
        }
        
        if (is_object($data)) {
            return (array) $data;
        }
        
        return null;
    }
PHP;
        
        // Check if parseAdditionalData method exists
        if (strpos($content, 'parseAdditionalData') === false) {
            // Add the method before the last closing brace
            $lastBrace = strrpos($content, '}');
            $content = substr($content, 0, $lastBrace) . $improvedShow . "\n" . substr($content, $lastBrace);
        }
        
        file_put_contents($controllerPath, $content);
        echo "✅ FakultasStaffController updated\n\n";
    }
    
    private function step3_CreateDataHelper() {
        echo "STEP 3: Create Data Helper\n";
        echo "--------------------------\n";
        
        $helperDir = 'app/Helpers';
        if (!is_dir($helperDir)) {
            mkdir($helperDir, 0755, true);
        }
        
        $helperContent = <<<'PHP'
<?php

namespace App\Helpers;

class PengajuanDataHelper
{
    /**
     * Parse additional_data dengan konsisten
     */
    public static function parseAdditionalData($data)
    {
        if (empty($data)) {
            return null;
        }
        
        if (is_array($data)) {
            return $data;
        }
        
        if (is_string($data)) {
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                \Log::error('JSON decode error: ' . $e->getMessage());
            }
            return ['data' => $data];
        }
        
        if (is_object($data)) {
            return (array) $data;
        }
        
        return null;
    }
    
    /**
     * Get status color class
     */
    public static function getStatusColor($status)
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processed' => 'bg-blue-100 text-blue-800',
            'approved_prodi' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'rejected_prodi' => 'bg-red-100 text-red-800',
            'surat_generated' => 'bg-purple-100 text-purple-800'
        ];
        
        return $colors[$status] ?? 'bg-gray-100 text-gray-800';
    }
}
PHP;
        
        file_put_contents($helperDir . '/PengajuanDataHelper.php', $helperContent);
        echo "✅ Created PengajuanDataHelper\n\n";
    }
    
    private function step4_UpdateFakultasView() {
        echo "STEP 4: Update Fakultas View\n";
        echo "-----------------------------\n";
        
        $viewPath = 'resources/views/fakultas/surat/show.blade.php';
        
        // Create improved fakultas view that handles data consistently
        $viewContent = <<<'BLADE'
@extends('layouts.app')

@section('title', 'Detail Surat - Staff Fakultas')

@php
    use App\Helpers\PengajuanDataHelper;
    
    // Handle both surat and pengajuan types
    if (isset($surat) && $surat->type === 'pengajuan') {
        // This is a pengajuan - use consistent data access
        $pengajuan = isset($pengajuan) ? $pengajuan : $surat->original_pengajuan;
        $isFromPengajuan = true;
    } else {
        // This is a regular surat
        $isFromPengajuan = false;
    }
    
    // Parse additional_data consistently
    if ($isFromPengajuan && isset($pengajuan->additional_data)) {
        $additionalData = PengajuanDataHelper::parseAdditionalData($pengajuan->additional_data);
    } else {
        $additionalData = null;
    }
    
    // Get jenis surat
    $jenisSurat = $isFromPengajuan ? ($pengajuan->jenisSurat ?? null) : ($surat->jenisSurat ?? null);
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    Detail {{ $isFromPengajuan ? 'Pengajuan' : 'Surat' }}
                    <span class="text-sm font-normal text-blue-600">(Staff Fakultas)</span>
                </h2>
                <a href="{{ route('fakultas.surat.index') }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>

        <div class="p-6">
            @if($isFromPengajuan)
                <!-- Pengajuan Content -->
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <!-- Info Pengajuan -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-800 mb-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informasi Pengajuan
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Token:</strong> {{ $pengajuan->tracking_token }}</div>
                            <div><strong>Tanggal:</strong> {{ $pengajuan->created_at->format('d/m/Y H:i') }}</div>
                            <div><strong>Jenis Surat:</strong> {{ $jenisSurat ? $jenisSurat->nama_jenis : 'N/A' }}</div>
                            <div><strong>Status:</strong> 
                                <span class="px-2 py-1 rounded text-xs {{ PengajuanDataHelper::getStatusColor($pengajuan->status) }}">
                                    {{ ucwords(str_replace('_', ' ', $pengajuan->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Mahasiswa -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-green-800 mb-3">
                            <i class="fas fa-user-graduate mr-2"></i>
                            Data Mahasiswa
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>NIM:</strong> {{ $pengajuan->nim }}</div>
                            <div><strong>Nama:</strong> {{ $pengajuan->nama_mahasiswa }}</div>
                            <div><strong>Prodi:</strong> {{ $pengajuan->prodi ? $pengajuan->prodi->nama_prodi : 'N/A' }}</div>
                            <div><strong>Email:</strong> {{ $pengajuan->email }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Keperluan -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Keperluan
                    </h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        {{ $pengajuan->keperluan }}
                    </div>
                </div>
                
                <!-- Additional Data dengan format yang sama seperti staff prodi -->
                @if($additionalData && is_array($additionalData))
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-800 mb-3">
                            <i class="fas fa-file-alt mr-2"></i>
                            Data Tambahan
                        </h3>
                        
                        @include('partials.pengajuan.additional_data', [
                            'additionalData' => $additionalData,
                            'jenisSurat' => $jenisSurat
                        ])
                    </div>
                @endif
                
            @else
                <!-- Regular Surat Content -->
                <div class="mb-6">
                    <h3 class="font-semibold">Detail Surat</h3>
                    <!-- Your existing surat display code here -->
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
BLADE;
        
        file_put_contents($viewPath, $viewContent);
        echo "✅ Updated fakultas view\n\n";
    }
    
    private function step5_UpdateProdiView() {
        echo "STEP 5: Create Shared Partial for Additional Data\n";
        echo "---------------------------------------------------\n";
        
        // Create partials directory
        $partialsDir = 'resources/views/partials/pengajuan';
        if (!is_dir($partialsDir)) {
            mkdir($partialsDir, 0755, true);
        }
        
        // Create shared partial for additional data display
        $partialContent = <<<'BLADE'
{{-- Shared partial for displaying additional data --}}
{{-- Used by both staff prodi and staff fakultas views --}}

@if($additionalData && is_array($additionalData))
    
    {{-- Universal Academic Data --}}
    @if(isset($additionalData['semester']) || isset($additionalData['tahun_akademik']) || isset($additionalData['dosen_wali']))
        <div class="bg-indigo-50 p-4 rounded-lg mb-4">
            <h4 class="font-medium text-indigo-800 mb-3">
                <i class="fas fa-graduation-cap mr-2"></i>
                Data Akademik
            </h4>
            <div class="grid md:grid-cols-3 gap-4 text-sm">
                @if(isset($additionalData['semester']))
                    <div><strong>Semester:</strong> {{ $additionalData['semester'] }}</div>
                @endif
                @if(isset($additionalData['tahun_akademik']))
                    <div><strong>Tahun Akademik:</strong> {{ $additionalData['tahun_akademik'] }}</div>
                @endif
                @if(isset($additionalData['dosen_wali']) && is_array($additionalData['dosen_wali']))
                    <div>
                        <strong>Dosen Wali:</strong> {{ $additionalData['dosen_wali']['nama'] ?? '-' }}
                        @if(isset($additionalData['dosen_wali']['nid']))
                            <br><span class="text-xs">NID: {{ $additionalData['dosen_wali']['nid'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Surat Mahasiswa Aktif (MA) --}}
    @if($jenisSurat && isset($jenisSurat->kode_surat) && $jenisSurat->kode_surat === 'MA' && isset($additionalData['orang_tua']))
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h4 class="font-medium text-yellow-800 mb-3">
                <i class="fas fa-users mr-2"></i>
                Biodata Orang Tua
            </h4>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                @foreach(['nama', 'tempat_lahir', 'tanggal_lahir', 'pekerjaan', 'nip', 'jabatan', 'pangkat_golongan', 'pendidikan_terakhir'] as $field)
                    @if(isset($additionalData['orang_tua'][$field]) && !empty($additionalData['orang_tua'][$field]))
                        <div>
                            <strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong> 
                            {{ $additionalData['orang_tua'][$field] }}
                        </div>
                    @endif
                @endforeach
            </div>
            
            @foreach(['alamat_instansi', 'alamat_rumah'] as $alamat)
                @if(isset($additionalData['orang_tua'][$alamat]) && !empty($additionalData['orang_tua'][$alamat]))
                    <div class="mt-3">
                        <strong>{{ ucwords(str_replace('_', ' ', $alamat)) }}:</strong>
                        <div class="mt-1 p-2 bg-white rounded border text-sm">
                            {{ $additionalData['orang_tua'][$alamat] }}
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        
    {{-- Surat Kerja Praktek (KP) --}}
    @elseif($jenisSurat && isset($jenisSurat->kode_surat) && $jenisSurat->kode_surat === 'KP' && isset($additionalData['kerja_praktek']))
        <div class="bg-blue-50 p-4 rounded-lg">
            <h4 class="font-medium text-blue-800 mb-3">
                <i class="fas fa-briefcase mr-2"></i>
                Data Kerja Praktek
            </h4>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                @foreach(['nama_perusahaan', 'bidang_kerja', 'periode_mulai', 'periode_selesai'] as $field)
                    @if(isset($additionalData['kerja_praktek'][$field]))
                        <div>
                            <strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong>
                            {{ $additionalData['kerja_praktek'][$field] }}
                        </div>
                    @endif
                @endforeach
            </div>
            
            @if(isset($additionalData['kerja_praktek']['alamat_perusahaan']))
                <div class="mt-3">
                    <strong>Alamat Perusahaan:</strong>
                    <div class="mt-1 p-2 bg-white rounded border text-sm">
                        {{ $additionalData['kerja_praktek']['alamat_perusahaan'] }}
                    </div>
                </div>
            @endif
        </div>
        
    {{-- Generic display for other types --}}
    @else
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-800 mb-3">
                <i class="fas fa-list mr-2"></i>
                Data Lainnya
            </h4>
            <div class="space-y-2 text-sm">
                @foreach($additionalData as $key => $value)
                    @if(!in_array($key, ['semester', 'tahun_akademik', 'dosen_wali']) && !is_array($value))
                        <div>
                            <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                            {{ $value }}
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
    
@else
    <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg text-center">
        <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
        <p class="text-gray-600 text-sm">
            Tidak ada data tambahan untuk pengajuan ini.
        </p>
    </div>
@endif
BLADE;
        
        file_put_contents($partialsDir . '/additional_data.blade.php', $partialContent);
        echo "✅ Created shared partial for additional data\n\n";
    }
    
    private function restoreBackups() {
        echo "\n🔄 Restoring backups...\n";
        
        foreach ($this->backupFiles as $originalFile => $backupFile) {
            if (file_exists($backupFile)) {
                if (copy($backupFile, $originalFile)) {
                    echo "✅ Restored: " . basename($originalFile) . "\n";
                }
            }
        }
        
        echo "\n✅ Restore completed\n";
    }
}

// Run the script
try {
    if (!file_exists('artisan')) {
        throw new Exception("Script harus dijalankan dari root Laravel project!");
    }
    
    $syncer = new DualViewSynchronizer();
    $syncer->run();
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nPastikan:\n";
    echo "1. Script dijalankan dari root Laravel project\n";
    echo "2. File permissions memadai\n";
    echo "3. Struktur folder sesuai Laravel standard\n\n";
}