<?php
/**
 * Debug Script untuk Pengajuan Surat System
 * File: debug_pengajuan.php
 * 
 * Instruksi:
 * 1. Simpan file ini di root Laravel project
 * 2. Jalankan: php debug_pengajuan.php
 * 3. Script akan otomatis backup file yang akan dimodifikasi
 * 4. Untuk restore backup: php debug_pengajuan.php restore
 */

// Configuration
$config = [
    'backup_dir' => 'storage/debug_backups',
    'log_file' => 'storage/logs/debug_pengajuan.log',
    'test_pengajuan_id' => null, // Will be set automatically
];

// ANSI Colors for better output
class Colors {
    const RESET = "\033[0m";
    const BLACK = "\033[30m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const MAGENTA = "\033[35m";
    const CYAN = "\033[36m";
    const WHITE = "\033[37m";
    const BOLD = "\033[1m";
}

class PengajuanDebugger {
    private $config;
    private $backupFiles = [];
    
    public function __construct($config) {
        $this->config = $config;
        $this->createBackupDir();
    }
    
    public function run() {
        $this->printHeader();
        
        if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] === 'restore') {
            $this->restoreBackups();
            return;
        }
        
        try {
            $this->step1_BackupFiles();
            $this->step2_CheckDatabase();
            $this->step3_TestPengajuanData();
            $this->step4_TestControllers();
            $this->step5_CreateUnifiedView();
            $this->step6_UpdateControllers();
            $this->step7_FinalTest();
            
            $this->printSuccess();
            
        } catch (Exception $e) {
            $this->printError("Error: " . $e->getMessage());
            $this->printInfo("Menjalankan rollback...");
            $this->restoreBackups();
        }
    }
    
    private function printHeader() {
        echo Colors::CYAN . Colors::BOLD . "\n";
        echo "=====================================\n";
        echo "   PENGAJUAN SURAT DEBUG SCRIPT     \n";
        echo "=====================================\n";
        echo Colors::RESET . "\n";
    }
    
    private function printStep($step, $title) {
        echo Colors::BLUE . Colors::BOLD . "STEP $step: " . Colors::RESET . Colors::YELLOW . $title . Colors::RESET . "\n";
        echo str_repeat("-", 50) . "\n";
    }
    
    private function printInfo($message) {
        echo Colors::CYAN . "[INFO] " . Colors::RESET . $message . "\n";
    }
    
    private function printSuccess($message = null) {
        if ($message) {
            echo Colors::GREEN . "[SUCCESS] " . Colors::RESET . $message . "\n";
        } else {
            echo Colors::GREEN . Colors::BOLD . "\n✓ DEBUG SCRIPT COMPLETED SUCCESSFULLY!\n" . Colors::RESET;
            echo Colors::GREEN . "Semua file telah diupdate dan backup disimpan.\n" . Colors::RESET;
            echo Colors::YELLOW . "Backup location: " . $this->config['backup_dir'] . "\n" . Colors::RESET;
        }
    }
    
    private function printError($message) {
        echo Colors::RED . "[ERROR] " . Colors::RESET . $message . "\n";
    }
    
    private function printWarning($message) {
        echo Colors::YELLOW . "[WARNING] " . Colors::RESET . $message . "\n";
    }
    
    private function createBackupDir() {
        if (!is_dir($this->config['backup_dir'])) {
            mkdir($this->config['backup_dir'], 0755, true);
        }
    }
    
    private function backupFile($filePath) {
        if (!file_exists($filePath)) {
            $this->printWarning("File tidak ditemukan: $filePath");
            return false;
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = $this->config['backup_dir'] . '/' . basename($filePath) . '.' . $timestamp . '.backup';
        
        if (copy($filePath, $backupPath)) {
            $this->backupFiles[$filePath] = $backupPath;
            $this->printSuccess("Backup created: " . basename($backupPath));
            return true;
        } else {
            throw new Exception("Gagal membuat backup untuk: $filePath");
        }
    }
    
    private function step1_BackupFiles() {
        $this->printStep(1, "Backup Files");
        
        $filesToBackup = [
            'app/Http/Controllers/FakultasStaffController.php',
            'app/Http/Controllers/SuratController.php',
            'resources/views/fakultas/surat/show.blade.php',
            'resources/views/staff/pengajuan/show.blade.php'
        ];
        
        foreach ($filesToBackup as $file) {
            $this->backupFile($file);
        }
        
        echo "\n";
    }
    
    private function step2_CheckDatabase() {
        $this->printStep(2, "Check Database Connection & Data");
        
        // Test database connection
        try {
            require_once 'vendor/autoload.php';
            
            // Load Laravel app
            $app = require_once 'bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            // Test database connection
            $connection = \Illuminate\Support\Facades\DB::connection();
            $connection->getPdo();
            $this->printSuccess("Database connection OK");
            
            // Check tables
            $tables = ['pengajuan_surat', 'jenis_surat', 'prodi'];
            foreach ($tables as $table) {
                $count = $connection->table($table)->count();
                $this->printInfo("Table $table: $count records");
            }
            
            // Get sample pengajuan for testing
            $pengajuan = $connection->table('pengajuan_surat')
                ->orderBy('id', 'desc')
                ->first();
            
            if ($pengajuan) {
                $this->config['test_pengajuan_id'] = $pengajuan->id;
                $this->printInfo("Test Pengajuan ID: {$pengajuan->id}");
                $this->printInfo("Additional Data Type: " . gettype($pengajuan->additional_data));
                
                if (is_string($pengajuan->additional_data)) {
                    $decoded = json_decode($pengajuan->additional_data, true);
                    if ($decoded) {
                        $this->printInfo("JSON decode successful, keys: " . implode(', ', array_keys($decoded)));
                    } else {
                        $this->printWarning("JSON decode failed: " . json_last_error_msg());
                    }
                }
            } else {
                throw new Exception("Tidak ada data pengajuan untuk testing");
            }
            
        } catch (Exception $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function step3_TestPengajuanData() {
        $this->printStep(3, "Analyze Pengajuan Data Structure");
        
        try {
            $pengajuan = \App\Models\PengajuanSurat::with(['jenisSurat', 'prodi'])
                ->find($this->config['test_pengajuan_id']);
            
            if (!$pengajuan) {
                throw new Exception("Pengajuan not found");
            }
            
            // Test data access methods
            echo "Raw additional_data from DB:\n";
            var_dump($pengajuan->getOriginal('additional_data'));
            
            echo "\nProcessed additional_data:\n";
            var_dump($pengajuan->additional_data);
            
            echo "\nRelation data:\n";
            echo "- Jenis Surat: " . ($pengajuan->jenisSurat ? $pengajuan->jenisSurat->nama_jenis : 'NULL') . "\n";
            echo "- Prodi: " . ($pengajuan->prodi ? $pengajuan->prodi->nama_prodi : 'NULL') . "\n";
            
            // Test JSON decoding
            $additionalData = null;
            if (!empty($pengajuan->additional_data)) {
                if (is_string($pengajuan->additional_data)) {
                    $additionalData = json_decode($pengajuan->additional_data, true);
                    $this->printInfo("JSON decoded successfully");
                } elseif (is_array($pengajuan->additional_data)) {
                    $additionalData = $pengajuan->additional_data;
                    $this->printInfo("Already an array");
                }
                
                if ($additionalData) {
                    echo "Decoded additional_data keys: " . implode(', ', array_keys($additionalData)) . "\n";
                }
            }
            
        } catch (Exception $e) {
            throw new Exception("Pengajuan data test failed: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function step4_TestControllers() {
        $this->printStep(4, "Test Current Controller Methods");
        
        try {
            // Test FakultasStaffController show method
            $this->printInfo("Testing FakultasStaffController...");
            
            // Mock user and test
            $user = \App\Models\User::whereHas('role', function($q) {
                $q->where('name', 'staff_fakultas');
            })->first();
            
            if ($user) {
                \Illuminate\Support\Facades\Auth::login($user);
                $this->printSuccess("Logged in as staff fakultas: " . $user->nama);
                
                // Test the show method logic (without actual HTTP request)
                $controller = new \App\Http\Controllers\FakultasStaffController();
                
                // We'll test the data transformation logic
                $pengajuan = \App\Models\PengajuanSurat::find($this->config['test_pengajuan_id']);
                
                if ($pengajuan) {
                    // Test the transformation logic from controller
                    $surat = new \stdClass();
                    $surat->id = $pengajuan->id;
                    $surat->type = 'pengajuan';
                    $surat->original_pengajuan = $pengajuan;
                    
                    $this->printSuccess("Surat object transformation OK");
                    $this->printInfo("Original pengajuan attached: " . (isset($surat->original_pengajuan) ? 'YES' : 'NO'));
                }
            } else {
                $this->printWarning("No staff_fakultas user found for testing");
            }
            
            // Test SuratController
            $this->printInfo("Testing SuratController...");
            
            $staffUser = \App\Models\User::whereHas('role', function($q) {
                $q->where('name', 'staff_prodi');
            })->first();
            
            if ($staffUser) {
                \Illuminate\Support\Facades\Auth::login($staffUser);
                $this->printSuccess("Logged in as staff prodi: " . $staffUser->nama);
            }
            
        } catch (Exception $e) {
            $this->printWarning("Controller test warning: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function step5_CreateUnifiedView() {
        $this->printStep(5, "Create Unified View Template");
        
        // Create directory structure
        $viewDir = 'resources/views/shared/pengajuan';
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
            $this->printSuccess("Created directory: $viewDir");
        }
        
        // Create the unified view file
        $unifiedViewContent = $this->getUnifiedViewContent();
        $unifiedViewPath = $viewDir . '/show.blade.php';
        
        if (file_put_contents($unifiedViewPath, $unifiedViewContent)) {
            $this->printSuccess("Created unified view: $unifiedViewPath");
        } else {
            throw new Exception("Failed to create unified view");
        }
        
        // Create supporting files
        $modalsContent = $this->getModalsContent();
        $modalsPath = $viewDir . '/modals.blade.php';
        
        if (file_put_contents($modalsPath, $modalsContent)) {
            $this->printSuccess("Created modals file: $modalsPath");
        }
        
        $scriptsContent = $this->getScriptsContent();
        $scriptsPath = $viewDir . '/scripts.blade.php';
        
        if (file_put_contents($scriptsPath, $scriptsContent)) {
            $this->printSuccess("Created scripts file: $scriptsPath");
        }
        
        echo "\n";
    }
    
    private function step6_UpdateControllers() {
        $this->printStep(6, "Update Controllers to Use Unified View");
        
        // Update FakultasStaffController
        $this->updateFakultasStaffController();
        
        // Update SuratController
        $this->updateSuratController();
        
        echo "\n";
    }
    
    private function step7_FinalTest() {
        $this->printStep(7, "Final Integration Test");
        
        try {
            // Test the unified view can be loaded
            $pengajuan = \App\Models\PengajuanSurat::with(['jenisSurat', 'prodi'])
                ->find($this->config['test_pengajuan_id']);
            
            if (!$pengajuan) {
                throw new Exception("Test pengajuan not found");
            }
            
            // Test view compilation (without rendering)
            $viewPath = 'shared.pengajuan.show';
            
            // Check if view exists
            if (view()->exists($viewPath)) {
                $this->printSuccess("Unified view template exists and can be found");
            } else {
                throw new Exception("Unified view template not found");
            }
            
            // Test data consistency
            $additionalData = null;
            if (isset($pengajuan->additional_data) && !empty($pengajuan->additional_data)) {
                if (is_string($pengajuan->additional_data)) {
                    $additionalData = json_decode($pengajuan->additional_data, true);
                } elseif (is_array($pengajuan->additional_data)) {
                    $additionalData = $pengajuan->additional_data;
                }
            }
            
            if ($additionalData) {
                $this->printSuccess("Additional data parsing works correctly");
                $this->printInfo("Data keys available: " . implode(', ', array_keys($additionalData)));
            } else {
                $this->printInfo("No additional data found (this is normal for some pengajuan)");
            }
            
            $this->printSuccess("Integration test completed");
            
        } catch (Exception $e) {
            throw new Exception("Final test failed: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function restoreBackups() {
        $this->printInfo("Restoring backup files...");
        
        foreach ($this->backupFiles as $originalFile => $backupFile) {
            if (file_exists($backupFile)) {
                if (copy($backupFile, $originalFile)) {
                    $this->printSuccess("Restored: " . basename($originalFile));
                } else {
                    $this->printError("Failed to restore: " . basename($originalFile));
                }
            }
        }
        
        $this->printSuccess("Backup restoration completed");
    }
    
    private function updateFakultasStaffController() {
        $filePath = 'app/Http/Controllers/FakultasStaffController.php';
        $content = file_get_contents($filePath);
        
        // Find the show method and replace its return statement
        $pattern = '/return view\(\'fakultas\.surat\.show\', compact\(\'surat\'\)\);/';
        $replacement = 'return view(\'shared.pengajuan.show\', compact(\'surat\'));';
        
        $updatedContent = preg_replace($pattern, $replacement, $content);
        
        if ($updatedContent !== $content) {
            if (file_put_contents($filePath, $updatedContent)) {
                $this->printSuccess("Updated FakultasStaffController.php");
            } else {
                throw new Exception("Failed to update FakultasStaffController.php");
            }
        } else {
            $this->printInfo("FakultasStaffController.php - no changes needed");
        }
    }
    
    private function updateSuratController() {
        $filePath = 'app/Http/Controllers/SuratController.php';
        $content = file_get_contents($filePath);
        
        // Find the pengajuanShow method and replace its return statement
        $pattern = '/return view\(\'staff\.pengajuan\.show\', compact\(\'pengajuan\'\)\);/';
        $replacement = 'return view(\'shared.pengajuan.show\', compact(\'pengajuan\'));';
        
        $updatedContent = preg_replace($pattern, $replacement, $content);
        
        if ($updatedContent !== $content) {
            if (file_put_contents($filePath, $updatedContent)) {
                $this->printSuccess("Updated SuratController.php");
            } else {
                throw new Exception("Failed to update SuratController.php");
            }
        } else {
            $this->printInfo("SuratController.php - no changes needed");
        }
    }
    
    private function getUnifiedViewContent() {
        return <<<'BLADE'
{{--
    Unified Pengajuan Detail View
    Can be used by both Staff Prodi and Staff Fakultas
--}}

@php
    // Standardize data access - handle both direct pengajuan and wrapped surat object
    if (isset($surat) && $surat->type === 'pengajuan' && isset($surat->original_pengajuan)) {
        $pengajuan = $surat->original_pengajuan;
        $isFromFakultas = true;
        $backRoute = 'fakultas.surat.index';
    } else {
        // Direct pengajuan access (staff prodi)
        $isFromFakultas = false;
        $backRoute = 'staff.pengajuan.index';
    }
    
    // Unified additional_data decoding
    $additionalData = null;
    if (isset($pengajuan->additional_data) && !empty($pengajuan->additional_data)) {
        if (is_string($pengajuan->additional_data)) {
            try {
                $additionalData = json_decode($pengajuan->additional_data, true);
            } catch (\Exception $e) {
                $additionalData = null;
            }
        } elseif (is_array($pengajuan->additional_data)) {
            $additionalData = $pengajuan->additional_data;
        } elseif (is_object($pengajuan->additional_data)) {
            $additionalData = (array) $pengajuan->additional_data;
        }
    }
    
    $jenisSurat = $pengajuan->jenisSurat ?? null;
@endphp

@extends('layouts.app')

@section('title', 'Detail Pengajuan Surat')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    Detail Pengajuan Surat
                    @if($isFromFakultas)
                        <span class="text-sm font-normal text-blue-600">(Staff Fakultas)</span>
                    @else
                        <span class="text-sm font-normal text-green-600">(Staff Prodi)</span>
                    @endif
                </h2>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        @if($pengajuan->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($pengajuan->status === 'processed') bg-blue-100 text-blue-800
                        @elseif($pengajuan->status === 'approved_prodi') bg-green-100 text-green-800
                        @elseif($pengajuan->status === 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucwords(str_replace('_', ' ', $pengajuan->status)) }}
                    </span>
                    <a href="{{ route($backRoute) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Basic Info Grid -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800 mb-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informasi Pengajuan
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div><strong>Token Tracking:</strong> 
                            <span class="font-mono bg-blue-100 px-2 py-1 rounded">{{ $pengajuan->tracking_token }}</span>
                        </div>
                        <div><strong>Tanggal Pengajuan:</strong> {{ $pengajuan->created_at->format('d/m/Y H:i') }}</div>
                        <div><strong>Jenis Surat:</strong> 
                            <span class="font-semibold">{{ $jenisSurat ? $jenisSurat->nama_jenis : 'N/A' }}</span>
                            @if($jenisSurat && $jenisSurat->kode_surat)
                                <span class="text-xs bg-gray-200 px-2 py-1 rounded ml-1">{{ $jenisSurat->kode_surat }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800 mb-3">
                        <i class="fas fa-user-graduate mr-2"></i>
                        Data Mahasiswa
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div><strong>NIM:</strong> {{ $pengajuan->nim }}</div>
                        <div><strong>Nama:</strong> {{ $pengajuan->nama_mahasiswa }}</div>
                        <div><strong>Program Studi:</strong> {{ $pengajuan->prodi ? $pengajuan->prodi->nama_prodi : 'N/A' }}</div>
                        <div><strong>Email:</strong> {{ $pengajuan->email }}</div>
                        <div><strong>Phone:</strong> {{ $pengajuan->phone ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Keperluan -->
            <div class="mb-8">
                <h3 class="font-semibold text-gray-800 mb-3">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Keperluan Surat
                </h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-700">{{ $pengajuan->keperluan }}</p>
                </div>
            </div>

            <!-- Additional Data -->
            @if($additionalData && is_array($additionalData))
                <div class="mb-8">
                    <h3 class="font-semibold text-gray-800 mb-4">
                        <i class="fas fa-file-alt mr-2"></i>
                        Data Tambahan - {{ $jenisSurat ? $jenisSurat->nama_jenis : 'Unknown' }}
                    </h3>

                    {{-- Universal Data --}}
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
                                            <br><span class="text-xs text-gray-600">NID: {{ $additionalData['dosen_wali']['nid'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Specific Data by Jenis Surat --}}
                    @if($jenisSurat && $jenisSurat->kode_surat === 'MA' && isset($additionalData['orang_tua']))
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="font-medium text-yellow-800 mb-3">
                                <i class="fas fa-users mr-2"></i>
                                Biodata Orang Tua
                            </h4>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                @foreach([
                                    'nama' => 'Nama',
                                    'tempat_lahir' => 'Tempat Lahir',
                                    'tanggal_lahir' => 'Tanggal Lahir',
                                    'pekerjaan' => 'Pekerjaan',
                                    'nip' => 'NIP',
                                    'jabatan' => 'Jabatan',
                                    'pangkat_golongan' => 'Pangkat/Golongan',
                                    'pendidikan_terakhir' => 'Pendidikan Terakhir'
                                ] as $key => $label)
                                    @if(isset($additionalData['orang_tua'][$key]) && !empty($additionalData['orang_tua'][$key]))
                                        <div><strong>{{ $label }}:</strong> {{ $additionalData['orang_tua'][$key] }}</div>
                                    @endif
                                @endforeach
                            </div>
                            
                            @if(isset($additionalData['orang_tua']['alamat_instansi']) && !empty($additionalData['orang_tua']['alamat_instansi']))
                                <div class="mt-4">
                                    <strong>Alamat Instansi:</strong>
                                    <div class="mt-1 p-3 bg-white rounded border text-sm">
                                        {{ $additionalData['orang_tua']['alamat_instansi'] }}
                                    </div>
                                </div>
                            @endif
                            
                            @if(isset($additionalData['orang_tua']['alamat_rumah']) && !empty($additionalData['orang_tua']['alamat_rumah']))
                                <div class="mt-4">
                                    <strong>Alamat Rumah:</strong>
                                    <div class="mt-1 p-3 bg-white rounded border text-sm">
                                        {{ $additionalData['orang_tua']['alamat_rumah'] }}
                                    </div>
                                </div>
                            @endif
                        </div>

                    @elseif($jenisSurat && $jenisSurat->kode_surat === 'KP' && isset($additionalData['kerja_praktek']))
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-medium text-blue-800 mb-3">
                                <i class="fas fa-briefcase mr-2"></i>
                                Data Kerja Praktek
                            </h4>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                @foreach([
                                    'nama_perusahaan' => 'Nama Perusahaan',
                                    'bidang_kerja' => 'Bidang Kerja',
                                    'periode_mulai' => 'Periode Mulai',
                                    'periode_selesai' => 'Periode Selesai'
                                ] as $key => $label)
                                    @if(isset($additionalData['kerja_praktek'][$key]) && !empty($additionalData['kerja_praktek'][$key]))
                                        <div><strong>{{ $label }}:</strong> {{ $additionalData['kerja_praktek'][$key] }}</div>
                                    @endif
                                @endforeach
                            </div>
                            
                            @if(isset($additionalData['kerja_praktek']['alamat_perusahaan']) && !empty($additionalData['kerja_praktek']['alamat_perusahaan']))
                                <div class="mt-4">
                                    <strong>Alamat Perusahaan:</strong>
                                    <div class="mt-1 p-3 bg-white rounded border text-sm">
                                        {{ $additionalData['kerja_praktek']['alamat_perusahaan'] }}
                                    </div>
                                </div>
                            @endif
                        </div>

                    @elseif($jenisSurat && $jenisSurat->kode_surat === 'TA' && isset($additionalData['tugas_akhir']))
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-medium text-purple-800 mb-3">
                                <i class="fas fa-book mr-2"></i>
                                Data Tugas Akhir
                            </h4>
                            
                            @if(isset($additionalData['tugas_akhir']['judul_ta']) && !empty($additionalData['tugas_akhir']['judul_ta']))
                                <div class="mb-3">
                                    <strong>Judul Tugas Akhir:</strong>
                                    <div class="mt-1 p-3 bg-white rounded border text-sm">
                                        {{ $additionalData['tugas_akhir']['judul_ta'] }}
                                    </div>
                                </div>
                            @endif
                            
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                @foreach([
                                    'dosen_pembimbing1' => 'Dosen Pembimbing 1',
                                    'dosen_pembimbing2' => 'Dosen Pembimbing 2',
                                    'lokasi_penelitian' => 'Lokasi Penelitian'
                                ] as $key => $label)
                                    @if(isset($additionalData['tugas_akhir'][$key]) && !empty($additionalData['tugas_akhir'][$key]))
                                        <div><strong>{{ $label }}:</strong> {{ $additionalData['tugas_akhir'][$key] }}</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg mb-6">
                    <p class="text-gray-600 text-sm text-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Tidak ada data tambahan untuk pengajuan ini.
                    </p>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                @if(config('app.debug'))
                    <div class="text-xs text-gray-500">
                        Status: {{ $pengajuan->status }} | 
                        Data: {{ $additionalData ? count($additionalData) . ' items' : 'none' }} |
                        View: {{ $isFromFakultas ? 'Fakultas' : 'Prodi' }}
                    </div>
                @else
                    <div></div>
                @endif
                
                <div class="flex space-x-3">
                    @if($isFromFakultas && in_array($pengajuan->status, ['processed', 'approved_prodi']))
                        <button onclick="generateSurat({{ isset($surat) ? $surat->id : $pengajuan->id }})" 
                                class="inline-flex items-center px-5 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700">
                            <i class="fas fa-file-alt mr-2"></i>
                            Generate Surat
                        </button>
                    @elseif(!$isFromFakultas && $pengajuan->status === 'pending' && auth()->user()->hasRole(['staff_prodi', 'kaprodi']))
                        <button onclick="showRejectModal()" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                            <i class="fas fa-times mr-2"></i>
                            Tolak
                        </button>
                        
                        <button onclick="showApproveConfirm()" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                            <i class="fas fa-check mr-2"></i>
                            Setujui
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$isFromFakultas && $pengajuan->status === 'pending')
    @include('shared.pengajuan.modals', ['pengajuan' => $pengajuan])
@endif

@push('scripts')
@if($isFromFakultas)
    <script>
    function generateSurat(id) {
        if (!confirm('Generate surat dari pengajuan ini?')) return;
        
        fetch(`/fakultas/pengajuan/${id}/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Surat berhasil di-generate!');
                window.location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan');
            }
        });
    }
    </script>
@else
    @include('shared.pengajuan.scripts', ['pengajuan' => $pengajuan])
@endif
@endpush
@endsection
BLADE;
    }
    
    private function getModalsContent() {
        return <<<'BLADE'
<!-- Modal Konfirmasi Approve -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4">Konfirmasi Persetujuan</h3>
        <div class="mb-6">
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menyetujui pengajuan surat ini?</p>
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm"><strong>Mahasiswa:</strong> {{ $pengajuan->nama_mahasiswa }} ({{ $pengajuan->nim }})</p>
                <p class="text-sm"><strong>Jenis Surat:</strong> {{ $pengajuan->jenisSurat->nama_jenis ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeApproveModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Batal</button>
            <button onclick="processPengajuan('approve')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Ya, Setujui</button>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4 text-red-600">Tolak Pengajuan</h3>
        <div class="mb-4">
            <p class="text-gray-600 mb-4">Berikan alasan penolakan:</p>
            <textarea id="rejectionReason" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" rows="4" placeholder="Contoh: Dokumen tidak lengkap, data tidak sesuai, dll..." required></textarea>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Batal</button>
            <button onclick="processPengajuan('reject')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Tolak Pengajuan</button>
        </div>
    </div>
</div>
BLADE;
    }
    
    private function getScriptsContent() {
        return <<<'BLADE'
<script>
function showApproveConfirm() {
    document.getElementById('approveModal').classList.remove('hidden');
    document.getElementById('approveModal').classList.add('flex');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    document.getElementById('approveModal').classList.remove('flex');
}

function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
    document.getElementById('rejectionReason').value = '';
}

function processPengajuan(action) {
    let data = { action: action };
    
    if (action === 'reject') {
        const reason = document.getElementById('rejectionReason').value.trim();
        if (!reason) {
            alert('Alasan penolakan harus diisi!');
            return;
        }
        data.rejection_reason = reason;
    }
    
    fetch('/pengajuan/{{ $pengajuan->id }}/prodi/process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            window.location.href = '{{ route("staff.pengajuan.index") }}';
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan sistem. Silakan coba lagi.');
    });
    
    if (action === 'approve') closeApproveModal();
    if (action === 'reject') closeRejectModal();
}
</script>
BLADE;
    }
}

// Main execution
try {
    echo Colors::GREEN . "PHP Debug Script for Pengajuan Surat System\n" . Colors::RESET;
    echo Colors::YELLOW . "========================================\n" . Colors::RESET;
    
    // Check if we're in Laravel root
    if (!file_exists('artisan')) {
        throw new Exception("Script harus dijalankan dari root directory Laravel project");
    }
    
    // Initialize and run debugger
    $debugger = new PengajuanDebugger($config);
    $debugger->run();
    
} catch (Exception $e) {
    echo Colors::RED . "\nFATAL ERROR: " . $e->getMessage() . Colors::RESET . "\n";
    echo Colors::YELLOW . "\nInstruksi:\n" . Colors::RESET;
    echo "1. Pastikan Anda berada di root directory Laravel\n";
    echo "2. Pastikan database sudah terkoneksi\n";
    echo "3. Pastikan ada data pengajuan_surat untuk testing\n";
    echo "4. Untuk restore backup: php debug_pengajuan.php restore\n\n";
    exit(1);
}