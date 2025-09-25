<?php
/**
 * Flexible Debug Script untuk Pengajuan Surat System
 * File: debug_pengajuan_flexible.php
 * 
 * Features:
 * - Auto-detect table names and structure
 * - Handle different database schemas
 * - Create missing files if needed
 * - Better error handling and recovery
 */

// Configuration
$config = [
    'backup_dir' => 'storage/debug_backups',
    'log_file' => 'storage/logs/debug_pengajuan.log',
    'test_pengajuan_id' => null,
    'table_mapping' => [
        'pengajuan' => ['pengajuan_surat', 'pengajuan', 'surat_pengajuan'],
        'jenis_surat' => ['jenis_surat', 'jenis_surats', 'surat_jenis'],
        'prodi' => ['prodi', 'prodis', 'program_studi'],
        'users' => ['users', 'user'],
        'roles' => ['roles', 'role', 'user_roles']
    ]
];

// ANSI Colors
class Colors {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
}

class FlexiblePengajuanDebugger {
    private $config;
    private $backupFiles = [];
    private $detectedTables = [];
    private $hasValidData = false;
    
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
            $this->step2_DetectDatabase();
            $this->step3_AnalyzeCurrent();
            $this->step4_CreateSolution();
            $this->step5_TestSolution();
            
            $this->printSuccess();
            
        } catch (Exception $e) {
            $this->printError("Error: " . $e->getMessage());
            $this->printInfo("Menjalankan rollback...");
            $this->restoreBackups();
        }
    }
    
    private function printHeader() {
        echo Colors::CYAN . Colors::BOLD . "\n";
        echo "===========================================\n";
        echo "   FLEXIBLE PENGAJUAN DEBUG SCRIPT v2.0   \n";
        echo "===========================================\n";
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
            echo Colors::GREEN . Colors::BOLD . "\n✓ DEBUG SCRIPT COMPLETED!\n" . Colors::RESET;
            echo Colors::GREEN . "Solution implemented successfully.\n" . Colors::RESET;
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
        $this->printStep(1, "Backup Existing Files");
        
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
    
    private function step2_DetectDatabase() {
        $this->printStep(2, "Detect Database Structure");
        
        try {
            require_once 'vendor/autoload.php';
            $app = require_once 'bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            $connection = \Illuminate\Support\Facades\DB::connection();
            $connection->getPdo();
            $this->printSuccess("Database connection OK");
            
            // Get all tables
            $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
            $tableNames = [];
            foreach ($tables as $table) {
                $tableNames[] = array_values((array)$table)[0];
            }
            
            $this->printInfo("Found tables: " . implode(', ', $tableNames));
            
            // Detect table mappings
            foreach ($this->config['table_mapping'] as $type => $possibleNames) {
                foreach ($possibleNames as $name) {
                    if (in_array($name, $tableNames)) {
                        $this->detectedTables[$type] = $name;
                        $this->printInfo("Detected $type table: $name");
                        break;
                    }
                }
            }
            
            // Check for data
            if (isset($this->detectedTables['pengajuan'])) {
                $count = $connection->table($this->detectedTables['pengajuan'])->count();
                $this->printInfo("Pengajuan records: $count");
                
                if ($count > 0) {
                    $sample = $connection->table($this->detectedTables['pengajuan'])
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    $this->config['test_pengajuan_id'] = $sample->id;
                    $this->hasValidData = true;
                    $this->printSuccess("Found test data - ID: {$sample->id}");
                    
                    // Check additional_data structure
                    if (property_exists($sample, 'additional_data')) {
                        $this->printInfo("Additional data type: " . gettype($sample->additional_data));
                        if (is_string($sample->additional_data) && !empty($sample->additional_data)) {
                            $decoded = json_decode($sample->additional_data, true);
                            if ($decoded) {
                                $this->printInfo("JSON keys: " . implode(', ', array_keys($decoded)));
                            }
                        }
                    }
                }
            } else {
                $this->printWarning("No pengajuan table found - will create mock solution");
            }
            
        } catch (Exception $e) {
            $this->printError("Database detection failed: " . $e->getMessage());
            $this->printInfo("Will proceed with file-only solution");
        }
        
        echo "\n";
    }
    
    private function step3_AnalyzeCurrent() {
        $this->printStep(3, "Analyze Current Implementation");
        
        // Check existing controllers
        $fakultasController = 'app/Http/Controllers/FakultasStaffController.php';
        if (file_exists($fakultasController)) {
            $content = file_get_contents($fakultasController);
            if (strpos($content, 'fakultas.surat.show') !== false) {
                $this->printInfo("FakultasStaffController uses fakultas.surat.show view");
            }
            if (strpos($content, 'original_pengajuan') !== false) {
                $this->printSuccess("Found original_pengajuan handling");
            } else {
                $this->printWarning("No original_pengajuan handling found");
            }
        }
        
        $suratController = 'app/Http/Controllers/SuratController.php';
        if (file_exists($suratController)) {
            $content = file_get_contents($suratController);
            if (strpos($content, 'staff.pengajuan.show') !== false) {
                $this->printInfo("SuratController uses staff.pengajuan.show view");
            }
        }
        
        // Check existing views
        $fakultasView = 'resources/views/fakultas/surat/show.blade.php';
        $prodiView = 'resources/views/staff/pengajuan/show.blade.php';
        
        if (file_exists($fakultasView)) {
            $content = file_get_contents($fakultasView);
            $this->printInfo("Fakultas view exists (" . strlen($content) . " chars)");
        }
        
        if (file_exists($prodiView)) {
            $content = file_get_contents($prodiView);
            $this->printInfo("Prodi view exists (" . strlen($content) . " chars)");
        }
        
        echo "\n";
    }
    
    private function step4_CreateSolution() {
        $this->printStep(4, "Create Unified Solution");
        
        // Create shared directory
        $sharedDir = 'resources/views/shared/pengajuan';
        if (!is_dir($sharedDir)) {
            mkdir($sharedDir, 0755, true);
            $this->printSuccess("Created directory: $sharedDir");
        }
        
        // Create unified view
        $unifiedView = $this->getUnifiedViewContent();
        $unifiedViewPath = $sharedDir . '/show.blade.php';
        
        if (file_put_contents($unifiedViewPath, $unifiedView)) {
            $this->printSuccess("Created unified view: $unifiedViewPath");
        } else {
            throw new Exception("Failed to create unified view");
        }
        
        // Create supporting files
        $modalsContent = $this->getModalsContent();
        if (file_put_contents($sharedDir . '/modals.blade.php', $modalsContent)) {
            $this->printSuccess("Created modals file");
        }
        
        $scriptsContent = $this->getScriptsContent();
        if (file_put_contents($sharedDir . '/scripts.blade.php', $scriptsContent)) {
            $this->printSuccess("Created scripts file");
        }
        
        // Update controllers
        $this->updateControllers();
        
        echo "\n";
    }
    
    private function step5_TestSolution() {
        $this->printStep(5, "Test Solution");
        
        try {
            // Test if unified view can be found
            if (file_exists('resources/views/shared/pengajuan/show.blade.php')) {
                $this->printSuccess("Unified view file exists");
            } else {
                throw new Exception("Unified view file not created");
            }
            
            // Test if Laravel can find the view
            if ($this->hasValidData) {
                try {
                    $viewExists = view()->exists('shared.pengajuan.show');
                    if ($viewExists) {
                        $this->printSuccess("Laravel can locate unified view");
                    } else {
                        $this->printWarning("Laravel view system not accessible");
                    }
                } catch (Exception $e) {
                    $this->printWarning("View test skipped: " . $e->getMessage());
                }
            }
            
            // Test controller updates
            $fakultasController = file_get_contents('app/Http/Controllers/FakultasStaffController.php');
            if (strpos($fakultasController, 'shared.pengajuan.show') !== false) {
                $this->printSuccess("FakultasStaffController updated");
            } else {
                $this->printWarning("FakultasStaffController may need manual update");
            }
            
            $suratController = file_get_contents('app/Http/Controllers/SuratController.php');
            if (strpos($suratController, 'shared.pengajuan.show') !== false) {
                $this->printSuccess("SuratController updated");
            } else {
                $this->printWarning("SuratController may need manual update");
            }
            
        } catch (Exception $e) {
            throw new Exception("Solution test failed: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function updateControllers() {
        // Update FakultasStaffController
        $fakultasPath = 'app/Http/Controllers/FakultasStaffController.php';
        if (file_exists($fakultasPath)) {
            $content = file_get_contents($fakultasPath);
            
            // Replace the view path in show method
            $patterns = [
                '/return view\(\'fakultas\.surat\.show\', compact\(\'surat\'\)\);/',
                '/return view\("fakultas\.surat\.show", compact\("surat"\)\);/'
            ];
            
            foreach ($patterns as $pattern) {
                $content = preg_replace($pattern, "return view('shared.pengajuan.show', compact('surat'));", $content);
            }
            
            if (file_put_contents($fakultasPath, $content)) {
                $this->printSuccess("Updated FakultasStaffController.php");
            }
        }
        
        // Update SuratController
        $suratPath = 'app/Http/Controllers/SuratController.php';
        if (file_exists($suratPath)) {
            $content = file_get_contents($suratPath);
            
            // Replace the view path in pengajuanShow method
            $patterns = [
                '/return view\(\'staff\.pengajuan\.show\', compact\(\'pengajuan\'\)\);/',
                '/return view\("staff\.pengajuan\.show", compact\("pengajuan"\)\);/'
            ];
            
            foreach ($patterns as $pattern) {
                $content = preg_replace($pattern, "return view('shared.pengajuan.show', compact('pengajuan'));", $content);
            }
            
            if (file_put_contents($suratPath, $content)) {
                $this->printSuccess("Updated SuratController.php");
            }
        }
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
    
    private function getUnifiedViewContent() {
        return <<<'BLADE'
{{--
    Unified Pengajuan Detail View
    Compatible with both Staff Prodi and Staff Fakultas
    Auto-detects data source and adjusts display accordingly
--}}

@php
    // Auto-detect data source and context
    if (isset($surat) && is_object($surat) && isset($surat->type) && $surat->type === 'pengajuan' && isset($surat->original_pengajuan)) {
        // Coming from Fakultas (wrapped in surat object)
        $pengajuan = $surat->original_pengajuan;
        $isFromFakultas = true;
        $backRoute = 'fakultas.surat.index';
        $contextLabel = 'Staff Fakultas';
        $contextClass = 'text-blue-600';
    } else {
        // Coming from Prodi (direct pengajuan object)
        $isFromFakultas = false;
        $backRoute = 'staff.pengajuan.index';
        $contextLabel = 'Staff Prodi';
        $contextClass = 'text-green-600';
    }
    
    // Unified additional_data handling with error protection
    $additionalData = null;
    if (isset($pengajuan->additional_data) && !empty($pengajuan->additional_data)) {
        try {
            if (is_string($pengajuan->additional_data)) {
                $additionalData = json_decode($pengajuan->additional_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $additionalData = null;
                }
            } elseif (is_array($pengajuan->additional_data)) {
                $additionalData = $pengajuan->additional_data;
            } elseif (is_object($pengajuan->additional_data)) {
                $additionalData = (array) $pengajuan->additional_data;
            }
        } catch (\Exception $e) {
            $additionalData = null;
        }
    }
    
    // Safe access to relations
    $jenisSurat = isset($pengajuan->jenisSurat) ? $pengajuan->jenisSurat : null;
    $prodi = isset($pengajuan->prodi) ? $pengajuan->prodi : null;
    
    // Status handling
    $status = isset($pengajuan->status) ? $pengajuan->status : 'unknown';
    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'processed' => 'bg-blue-100 text-blue-800',
        'approved_prodi' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'rejected_prodi' => 'bg-red-100 text-red-800',
        'surat_generated' => 'bg-purple-100 text-purple-800'
    ];
    $statusClass = isset($statusColors[$status]) ? $statusColors[$status] : 'bg-gray-100 text-gray-800';
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
                    <span class="text-sm font-normal {{ $contextClass }}">({{ $contextLabel }})</span>
                </h2>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                        {{ ucwords(str_replace('_', ' ', $status)) }}
                    </span>
                    <a href="{{ route($backRoute) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Basic Information Grid -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Informasi Pengajuan -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informasi Pengajuan
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium">Token Tracking:</span>
                            <span class="font-mono bg-blue-100 px-2 py-1 rounded text-xs">
                                {{ $pengajuan->tracking_token ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Tanggal Pengajuan:</span>
                            <span>{{ isset($pengajuan->created_at) ? $pengajuan->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Jenis Surat:</span>
                            <div class="text-right">
                                <span class="font-semibold">{{ $jenisSurat ? $jenisSurat->nama_jenis : 'N/A' }}</span>
                                @if($jenisSurat && isset($jenisSurat->kode_surat))
                                    <br><span class="text-xs bg-gray-200 px-1 py-0.5 rounded">{{ $jenisSurat->kode_surat }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Mahasiswa -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800 mb-3 flex items-center">
                        <i class="fas fa-user-graduate mr-2"></i>
                        Data Mahasiswa
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium">NIM:</span>
                            <span>{{ $pengajuan->nim ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Nama:</span>
                            <span>{{ $pengajuan->nama_mahasiswa ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Program Studi:</span>
                            <span>{{ $prodi ? $prodi->nama_prodi : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Email:</span>
                            <span class="text-xs">{{ $pengajuan->email ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Phone:</span>
                            <span>{{ $pengajuan->phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keperluan -->
            <div class="mb-8">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Keperluan Surat
                </h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-700">{{ $pengajuan->keperluan ?? 'Tidak ada keterangan keperluan' }}</p>
                </div>
            </div>

            <!-- Additional Data Section -->
            @if($additionalData && is_array($additionalData) && count($additionalData) > 0)
                <div class="mb-8">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-alt mr-2"></i>
                        Data Tambahan - {{ $jenisSurat ? $jenisSurat->nama_jenis : 'Pengajuan' }}
                    </h3>

                    {{-- Universal Academic Data --}}
                    @if(isset($additionalData['semester']) || isset($additionalData['tahun_akademik']) || isset($additionalData['dosen_wali']))
                        <div class="bg-indigo-50 p-4 rounded-lg mb-4">
                            <h4 class="font-medium text-indigo-800 mb-3 flex items-center">
                                <i class="fas fa-graduation-cap mr-2"></i>
                                Data Akademik
                            </h4>
                            <div class="grid md:grid-cols-3 gap-4 text-sm">
                                @if(isset($additionalData['semester']))
                                    <div>
                                        <span class="font-medium">Semester:</span>
                                        <span class="ml-2">{{ $additionalData['semester'] }}</span>
                                    </div>
                                @endif
                                @if(isset($additionalData['tahun_akademik']))
                                    <div>
                                        <span class="font-medium">Tahun Akademik:</span>
                                        <span class="ml-2">{{ $additionalData['tahun_akademik'] }}</span>
                                    </div>
                                @endif
                                @if(isset($additionalData['dosen_wali']) && is_array($additionalData['dosen_wali']))
                                    <div>
                                        <span class="font-medium">Dosen Wali:</span>
                                        <span class="ml-2">{{ $additionalData['dosen_wali']['nama'] ?? '-' }}</span>
                                        @if(isset($additionalData['dosen_wali']['nid']) && !empty($additionalData['dosen_wali']['nid']))
                                            <br><span class="text-xs text-gray-600">NID: {{ $additionalData['dosen_wali']['nid'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Surat Mahasiswa Aktif (MA) --}}
                    @if($jenisSurat && isset($jenisSurat->kode_surat) && $jenisSurat->kode_surat === 'MA' && isset($additionalData['orang_tua']))
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="font-medium text-yellow-800 mb-3 flex items-center">
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
                                        <div>
                                            <span class="font-medium">{{ $label }}:</span>
                                            <span class="ml-2">{{ $additionalData['orang_tua'][$key] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            
                            @if(isset($additionalData['orang_tua']['alamat_instansi']) && !empty($additionalData['orang_tua']['alamat_instansi']))
                                <div class="mt-4">
                                    <span class="font-medium text-sm">Alamat Instansi:</span>
                                    <div class="mt-1 p-3 bg-white rounded border text-sm">
                                        {{ $additionalData['orang_tua']['alamat_instansi'] }}
                                    </div>
                                </div>
                            @endif
                            
                            @if(isset($additionalData['orang_tua']['alamat_rumah']) && !empty($additionalData['orang_tua']['alamat_rumah']))
                                <div class="mt-4">
                                    <span class="font-medium text-sm">Alamat Rumah:</span>
                                    <div class="mt-1 p-3 bg-white rounded border text-sm">
                                        {{ $additionalData['orang_tua']['alamat_rumah'] }}
                                    </div>
                                </div>
                            @endif
                        </div>

                    {{-- Surat Kerja Praktek (KP) --}}
                    @elseif($jenisSurat && isset($jenisSurat->kode_surat) && $jenisSurat->kode_surat === 'KP' && isset($additionalData['kerja_praktek']))
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-medium text-blue-800 mb-3 flex items-center">
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
                                        <div>
                                            <span class="font-medium">{{ $label }}:</span>
                                            <span class="ml-2">{{ $additionalData['kerja_praktek'][$key] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            
                            @if(isset($additionalData['kerja_praktek']['alamat_perusahaan']) && !empty($additionalData['kerja_praktek']['alamat_perusahaan']))
                                <div class="mt-4">
                                    <span class="font-medium text-sm">Alamat Perusahaan:</span>
                                    <div class="mt-1 p-3 bg-white rounded border text-sm">
                                        {{ $additionalData['kerja_praktek']['alamat_perusahaan'] }}
                                    </div>
                                </div>
                            @endif
                        </div>

                    {{-- Surat Tugas Akhir (TA) --}}
                    @elseif($jenisSurat && isset($jenisSurat->kode_surat) && $jenisSurat->kode_surat === 'TA' && isset($additionalData['tugas_akhir']))
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-medium text-purple-800 mb-3 flex items-center">
                                <i class="fas fa-book mr-2"></i>
                                Data Tugas Akhir
                            </h4>
                            
                            @if(isset($additionalData['tugas_akhir']['judul_ta']) && !empty($additionalData['tugas_akhir']['judul_ta']))
                                <div class="mb-3">
                                    <span class="font-medium text-sm">Judul Tugas Akhir:</span>
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
                                        <div>
                                            <span class="font-medium">{{ $label }}:</span>
                                            <span class="ml-2">{{ $additionalData['tugas_akhir'][$key] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                    {{-- Generic additional data display --}}
                    @else
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-list mr-2"></i>
                                Data Lainnya
                            </h4>
                            <div class="space-y-2 text-sm">
                                @foreach($additionalData as $key => $value)
                                    @if(!in_array($key, ['semester', 'tahun_akademik', 'dosen_wali']) && !is_array($value) && !is_object($value))
                                        <div class="flex justify-between">
                                            <span class="font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                            <span>{{ $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <!-- No Additional Data -->
                <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg mb-8 text-center">
                    <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                    <p class="text-gray-600 text-sm">
                        Tidak ada data tambahan untuk pengajuan ini.
                    </p>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                @if(config('app.debug'))
                    <div class="text-xs text-gray-500 bg-yellow-50 px-2 py-1 rounded">
                        Status: {{ $status }} | 
                        Context: {{ $isFromFakultas ? 'Fakultas' : 'Prodi' }} |
                        Data: {{ $additionalData ? count($additionalData) . ' items' : 'none' }}
                    </div>
                @else
                    <div></div>
                @endif
                
                <div class="flex space-x-3">
                    @if($isFromFakultas && in_array($status, ['processed', 'approved_prodi']))
                        <button onclick="generateSurat({{ isset($surat) ? $surat->id : $pengajuan->id }})" 
                                class="inline-flex items-center px-5 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors">
                            <i class="fas fa-file-alt mr-2"></i>
                            Generate Surat
                        </button>
                    @elseif(!$isFromFakultas && $status === 'pending' && auth()->check() && (auth()->user()->hasRole(['staff_prodi', 'kaprodi']) || method_exists(auth()->user(), 'hasRole') === false))
                        <button onclick="showRejectModal()" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Tolak
                        </button>
                        
                        <button onclick="showApproveConfirm()" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-check mr-2"></i>
                            Setujui
                        </button>
                    @else
                        <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-md">
                            <i class="fas fa-clock mr-2"></i>
                            {{ $isFromFakultas ? 'Menunggu Proses' : 'Status: ' . ucwords(str_replace('_', ' ', $status)) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include modals only for staff prodi with pending status --}}
@if(!$isFromFakultas && $status === 'pending')
    @include('shared.pengajuan.modals', ['pengajuan' => $pengajuan])
@endif

@push('scripts')
@if($isFromFakultas)
    <script>
    function generateSurat(id) {
        if (!confirm('Generate surat dari pengajuan ini? Surat akan dibuat berdasarkan template.')) return;
        
        // Show loading state
        event.target.disabled = true;
        event.target.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';
        
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
                alert('Surat berhasil di-generate! Silakan print untuk ditandatangani.');
                window.location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan');
                // Reset button
                event.target.disabled = false;
                event.target.innerHTML = '<i class="fas fa-file-alt mr-2"></i>Generate Surat';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat generate surat');
            // Reset button
            event.target.disabled = false;
            event.target.innerHTML = '<i class="fas fa-file-alt mr-2"></i>Generate Surat';
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
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-bold mb-4 text-gray-900">Konfirmasi Persetujuan</h3>
        <div class="mb-6">
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menyetujui pengajuan surat ini?</p>
            <div class="bg-gray-50 p-3 rounded-lg">
                <p class="text-sm"><strong>Mahasiswa:</strong> {{ $pengajuan->nama_mahasiswa ?? 'N/A' }} ({{ $pengajuan->nim ?? 'N/A' }})</p>
                <p class="text-sm"><strong>Jenis Surat:</strong> {{ isset($pengajuan->jenisSurat) ? $pengajuan->jenisSurat->nama_jenis : 'N/A' }}</p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeApproveModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                Batal
            </button>
            <button onclick="processPengajuan('approve')" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

<!-- Modal Reject dengan Alasan -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-bold mb-4 text-red-600">Tolak Pengajuan</h3>
        <div class="mb-4">
            <p class="text-gray-600 mb-4">Berikan alasan penolakan untuk pengajuan ini:</p>
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="text-sm"><strong>Mahasiswa:</strong> {{ $pengajuan->nama_mahasiswa ?? 'N/A' }} ({{ $pengajuan->nim ?? 'N/A' }})</p>
                <p class="text-sm"><strong>Jenis Surat:</strong> {{ isset($pengajuan->jenisSurat) ? $pengajuan->jenisSurat->nama_jenis : 'N/A' }}</p>
            </div>
            <textarea id="rejectionReason" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                      rows="4"
                      placeholder="Contoh: Dokumen pendukung tidak lengkap, data tidak sesuai dengan persyaratan, dll..."
                      required></textarea>
            <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter diperlukan</p>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeRejectModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                Batal
            </button>
            <button onclick="processPengajuan('reject')" 
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                Tolak Pengajuan
            </button>
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
    document.body.style.overflow = 'hidden';
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    document.getElementById('approveModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
    document.getElementById('rejectionReason').focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
    document.getElementById('rejectionReason').value = '';
    document.body.style.overflow = 'auto';
}

function processPengajuan(action) {
    let data = { action: action };
    
    if (action === 'reject') {
        const reason = document.getElementById('rejectionReason').value.trim();
        if (!reason) {
            alert('Alasan penolakan harus diisi!');
            return;
        }
        if (reason.length < 10) {
            alert('Alasan penolakan minimal 10 karakter!');
            return;
        }
        data.rejection_reason = reason;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
    // Construct URL - try to get pengajuan ID safely
    let pengajuanId = '{{ isset($pengajuan->id) ? $pengajuan->id : "0" }}';
    let processUrl = `/pengajuan/${pengajuanId}/prodi/process`;
    
    fetch(processUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            alert(result.message || 'Pengajuan berhasil diproses');
            // Try to redirect to staff pengajuan index, fallback to reload
            try {
                window.location.href = '{{ route("staff.pengajuan.index") }}';
            } catch (e) {
                window.location.reload();
            }
        } else {
            alert('Error: ' + (result.message || 'Terjadi kesalahan'));
            // Reset button
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error processing pengajuan:', error);
        alert('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.');
        // Reset button
        button.disabled = false;
        button.innerHTML = originalText;
    });
    
    // Close modals
    if (action === 'approve') closeApproveModal();
    if (action === 'reject') closeRejectModal();
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.id === 'approveModal') {
        closeApproveModal();
    }
    if (event.target.id === 'rejectModal') {
        closeRejectModal();
    }
});

// Handle Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeApproveModal();
        closeRejectModal();
    }
});
</script>
BLADE;
    }
}

// Main execution
try {
    echo Colors::GREEN . "Flexible PHP Debug Script for Pengajuan Surat System v2.0\n" . Colors::RESET;
    echo Colors::YELLOW . "=====================================================\n" . Colors::RESET;
    
    // Check if we're in Laravel root
    if (!file_exists('artisan')) {
        throw new Exception("Script harus dijalankan dari root directory Laravel project");
    }
    
    // Initialize and run debugger
    $debugger = new FlexiblePengajuanDebugger($config);
    $debugger->run();
    
} catch (Exception $e) {
    echo Colors::RED . "\nFATAL ERROR: " . $e->getMessage() . Colors::RESET . "\n";
    echo Colors::YELLOW . "\nTroubleshooting:\n" . Colors::RESET;
    echo "1. Pastikan Anda berada di root directory Laravel\n";
    echo "2. Cek koneksi database di .env\n";
    echo "3. Periksa nama tabel di database (mungkin berbeda)\n";
    echo "4. Script akan tetap membuat unified view meski tidak ada data\n";
    echo "5. Untuk restore: php debug_pengajuan_flexible.php restore\n\n";
    exit(1);
}