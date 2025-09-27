<?php
/**
 * System Structure Analyzer
 * Script untuk menganalisis struktur sistem surat pengajuan mahasiswa
 * 
 * Usage: php analyze_system.php
 */

class SystemAnalyzer {
    private $basePath;
    private $output = [];
    
    public function __construct($basePath = null) {
        $this->basePath = $basePath ?: getcwd();
        $this->addLine("=== SISTEM SURAT PENGAJUAN MAHASISWA - STRUCTURE ANALYZER ===\n");
        $this->addLine("Base Path: " . $this->basePath . "\n");
        $this->addLine("Analysis Date: " . date('Y-m-d H:i:s') . "\n");
    }
    
    public function analyze() {
        $this->analyzeDatabaseStructure();
        $this->analyzeModels();
        $this->analyzeControllers();
        $this->analyzeViews();
        $this->analyzeRoutes();
        $this->analyzeMigrations();
        $this->analyzeSeeds();
        $this->generateSummary();
        
        return implode("\n", $this->output);
    }
    
    private function addLine($text, $indent = 0) {
        $this->output[] = str_repeat("  ", $indent) . $text;
    }
    
    private function addSection($title) {
        $this->addLine("\n" . str_repeat("=", 60));
        $this->addLine($title);
        $this->addLine(str_repeat("=", 60));
    }
    
    private function analyzeDatabaseStructure() {
        $this->addSection("DATABASE STRUCTURE");
        
        // Core tables for pengajuan mahasiswa
        $coreEntities = [
            'users' => [
                'purpose' => 'User authentication & roles (mahasiswa, staff_prodi, staff_fakultas, kaprodi)',
                'key_fields' => ['id', 'name', 'email', 'role', 'prodi_id', 'fakultas_id'],
                'relationships' => ['belongsTo: prodi', 'belongsTo: fakultas']
            ],
            'fakultas' => [
                'purpose' => 'Master data fakultas',
                'key_fields' => ['id', 'nama_fakultas', 'kode_fakultas'],
                'relationships' => ['hasMany: prodi', 'hasMany: users']
            ],
            'prodi' => [
                'purpose' => 'Master data program studi',
                'key_fields' => ['id', 'nama_prodi', 'kode_prodi', 'fakultas_id'],
                'relationships' => ['belongsTo: fakultas', 'hasMany: users', 'hasMany: pengajuan_surats']
            ],
            'jenis_surats' => [
                'purpose' => 'Master jenis surat (MA, KP, TA, SKM)',
                'key_fields' => ['id', 'nama_jenis', 'kode_surat', 'template_path'],
                'relationships' => ['hasMany: pengajuan_surats']
            ],
            'pengajuan_surats' => [
                'purpose' => 'CORE TABLE - Data pengajuan surat mahasiswa',
                'key_fields' => [
                    'id', 'tracking_token', 'nim', 'nama_mahasiswa', 'email',
                    'jenis_surat_id', 'prodi_id', 'keperluan', 'additional_data',
                    'status', 'approved_by_prodi', 'approved_at_prodi',
                    'rejected_by_prodi', 'rejection_reason_prodi',
                    'completed_by', 'completed_at', 'download_url'
                ],
                'relationships' => ['belongsTo: prodi', 'belongsTo: jenisSurat']
            ]
        ];
        
        foreach ($coreEntities as $table => $info) {
            $this->addLine("\n📋 Table: {$table}", 1);
            $this->addLine("Purpose: " . $info['purpose'], 2);
            $this->addLine("Key Fields: " . implode(', ', $info['key_fields']), 2);
            $this->addLine("Relationships: " . implode(', ', $info['relationships']), 2);
        }
        
        // Status flow
        $this->addLine("\n📊 STATUS FLOW UNTUK PENGAJUAN MAHASISWA:", 1);
        $statusFlow = [
            'pending' => 'Menunggu review dari staff prodi',
            'approved_prodi' => 'Disetujui prodi, diteruskan ke fakultas',
            'processed' => 'Sedang diproses di fakultas',
            'sedang_ditandatangani' => 'Menunggu tanda tangan fisik',
            'completed' => 'Surat selesai dan dapat didownload',
            'rejected_prodi' => 'Ditolak oleh prodi',
            'rejected_fakultas' => 'Ditolak oleh fakultas'
        ];
        
        foreach ($statusFlow as $status => $description) {
            $this->addLine("• {$status}: {$description}", 2);
        }
    }
    
    private function analyzeModels() {
        $this->addSection("MODELS STRUCTURE");
        
        $modelPath = $this->basePath . '/app/Models';
        
        $coreModels = [
            'User.php' => [
                'extends' => 'Authenticatable',
                'traits' => ['HasRoles', 'Notifiable'],
                'relationships' => ['belongsTo: prodi', 'belongsTo: fakultas'],
                'methods' => ['hasRole()', 'isStaffProdi()', 'isStaffFakultas()']
            ],
            'PengajuanSurat.php' => [
                'extends' => 'Model',
                'fillable' => ['tracking_token', 'nim', 'nama_mahasiswa', 'email', 'keperluan', 'additional_data', 'status'],
                'relationships' => ['belongsTo: prodi', 'belongsTo: jenisSurat', 'belongsTo: user'],
                'scopes' => ['scopePending()', 'scopeApproved()', 'scopeCompleted()'],
                'methods' => ['generateToken()', 'canBeProcessed()', 'markAsPrinted()']
            ],
            'JenisSurat.php' => [
                'extends' => 'Model',
                'fillable' => ['nama_jenis', 'kode_surat', 'template_path', 'is_active'],
                'relationships' => ['hasMany: pengajuan_surats'],
                'methods' => ['getTemplatePathAttribute()']
            ],
            'Prodi.php' => [
                'extends' => 'Model',
                'fillable' => ['nama_prodi', 'kode_prodi', 'fakultas_id'],
                'relationships' => ['belongsTo: fakultas', 'hasMany: users', 'hasMany: pengajuan_surats']
            ],
            'Fakultas.php' => [
                'extends' => 'Model',
                'fillable' => ['nama_fakultas', 'kode_fakultas'],
                'relationships' => ['hasMany: prodi', 'hasMany: users']
            ]
        ];
        
        foreach ($coreModels as $model => $details) {
            $this->addLine("\n🏗️ Model: {$model}", 1);
            $modelFile = $modelPath . '/' . $model;
            
            if (file_exists($modelFile)) {
                $this->addLine("✅ EXISTS", 2);
                $content = file_get_contents($modelFile);
                
                // Check for relationships
                if (isset($details['relationships'])) {
                    $this->addLine("Relationships:", 2);
                    foreach ($details['relationships'] as $rel) {
                        $found = $this->checkMethodInFile($content, explode(':', $rel)[1]);
                        $status = $found ? "✅" : "❌";
                        $this->addLine("{$status} {$rel}", 3);
                    }
                }
                
                // Check for important methods
                if (isset($details['methods'])) {
                    $this->addLine("Key Methods:", 2);
                    foreach ($details['methods'] as $method) {
                        $methodName = str_replace(['()', 'scope'], '', $method);
                        $found = $this->checkMethodInFile($content, $methodName);
                        $status = $found ? "✅" : "❌";
                        $this->addLine("{$status} {$method}", 3);
                    }
                }
            } else {
                $this->addLine("❌ NOT FOUND", 2);
            }
        }
    }
    
    private function analyzeControllers() {
        $this->addSection("CONTROLLERS STRUCTURE");
        
        $controllerPath = $this->basePath . '/app/Http/Controllers';
        
        $coreControllers = [
            'PengajuanSuratController.php' => [
                'purpose' => 'Handle pengajuan dari mahasiswa (public)',
                'methods' => ['index', 'create', 'store', 'show', 'track'],
                'middleware' => ['guest atau auth mahasiswa']
            ],
            'StaffPengajuanController.php' => [
                'purpose' => 'Handle review pengajuan oleh staff prodi',
                'methods' => ['index', 'show', 'processPengajuan', 'getStatistics'],
                'middleware' => ['auth', 'role:staff_prodi,kaprodi']
            ],
            'FakultasStaffController.php' => [
                'purpose' => 'Handle surat yang sudah disetujui prodi',
                'methods' => ['index', 'show', 'processPengajuanFromProdi'],
                'middleware' => ['auth', 'role:staff_fakultas']
            ],
            'SuratFSIController.php' => [
                'purpose' => 'Generate dan manage surat FSI',
                'methods' => ['preview', 'generatePDF', 'printSurat', 'uploadSigned'],
                'middleware' => ['auth', 'role:staff_fakultas']
            ]
        ];
        
        foreach ($coreControllers as $controller => $details) {
            $this->addLine("\n🎮 Controller: {$controller}", 1);
            $controllerFile = $controllerPath . '/' . $controller;
            
            $this->addLine("Purpose: " . $details['purpose'], 2);
            $this->addLine("Middleware: " . $details['middleware'], 2);
            
            if (file_exists($controllerFile)) {
                $this->addLine("✅ EXISTS", 2);
                $content = file_get_contents($controllerFile);
                
                $this->addLine("Methods:", 2);
                foreach ($details['methods'] as $method) {
                    $found = $this->checkMethodInFile($content, $method);
                    $status = $found ? "✅" : "❌";
                    $this->addLine("{$status} {$method}()", 3);
                }
            } else {
                $this->addLine("❌ NOT FOUND", 2);
            }
        }
    }
    
    private function analyzeViews() {
        $this->addSection("VIEWS STRUCTURE");
        
        $viewPath = $this->basePath . '/resources/views';
        
        $coreViews = [
            'pengajuan/' => [
                'purpose' => 'Views untuk mahasiswa mengajukan surat',
                'files' => ['index.blade.php', 'create.blade.php', 'show.blade.php', 'track.blade.php']
            ],
            'staff/pengajuan/' => [
                'purpose' => 'Views untuk staff prodi review pengajuan',
                'files' => ['index.blade.php', 'show.blade.php']
            ],
            'fakultas/surat/' => [
                'purpose' => 'Views untuk staff fakultas manage surat',
                'files' => ['index.blade.php', 'show.blade.php']
            ],
            'surat/fsi/' => [
                'purpose' => 'Views untuk preview dan edit surat',
                'files' => ['preview-editable.blade.php']
            ],
            'surat/pdf/' => [
                'purpose' => 'Templates PDF untuk print surat',
                'files' => ['surat-ma.blade.php', 'surat-kp.blade.php', 'surat-ta.blade.php']
            ]
        ];
        
        foreach ($coreViews as $directory => $details) {
            $this->addLine("\n📄 View Directory: {$directory}", 1);
            $this->addLine("Purpose: " . $details['purpose'], 2);
            
            $dirPath = $viewPath . '/' . rtrim($directory, '/');
            if (is_dir($dirPath)) {
                $this->addLine("✅ DIRECTORY EXISTS", 2);
                
                foreach ($details['files'] as $file) {
                    $filePath = $dirPath . '/' . $file;
                    $status = file_exists($filePath) ? "✅" : "❌";
                    $this->addLine("{$status} {$file}", 3);
                }
            } else {
                $this->addLine("❌ DIRECTORY NOT FOUND", 2);
            }
        }
    }
    
    private function analyzeRoutes() {
        $this->addSection("ROUTES STRUCTURE");
        
        $routeFile = $this->basePath . '/routes/web.php';
        
        if (file_exists($routeFile)) {
            $this->addLine("✅ Route file exists: web.php", 1);
            
            $expectedRoutes = [
                'Public Routes (Mahasiswa)' => [
                    'GET /pengajuan' => 'PengajuanSuratController@index',
                    'GET /pengajuan/create' => 'PengajuanSuratController@create',
                    'POST /pengajuan' => 'PengajuanSuratController@store',
                    'GET /pengajuan/track' => 'PengajuanSuratController@track'
                ],
                'Staff Prodi Routes' => [
                    'GET /staff/pengajuan' => 'StaffPengajuanController@index',
                    'GET /staff/pengajuan/{id}' => 'StaffPengajuanController@show',
                    'POST /staff/pengajuan/{id}/process' => 'StaffPengajuanController@processPengajuan'
                ],
                'Staff Fakultas Routes' => [
                    'GET /fakultas/surat' => 'FakultasStaffController@index',
                    'GET /fakultas/surat/{id}' => 'FakultasStaffController@show',
                    'GET /fakultas/surat/fsi/preview/{id}' => 'SuratFSIController@preview',
                    'POST /fakultas/surat/fsi/print/{id}' => 'SuratFSIController@printSurat'
                ]
            ];
            
            $content = file_get_contents($routeFile);
            
            foreach ($expectedRoutes as $group => $routes) {
                $this->addLine("\n{$group}:", 2);
                foreach ($routes as $route => $handler) {
                    // Simple check for route existence
                    $routePath = explode(' ', $route)[1];
                    $found = strpos($content, $routePath) !== false;
                    $status = $found ? "✅" : "❌";
                    $this->addLine("{$status} {$route}", 3);
                }
            }
        } else {
            $this->addLine("❌ Route file not found", 1);
        }
    }
    
    private function analyzeMigrations() {
        $this->addSection("MIGRATIONS");
        
        $migrationPath = $this->basePath . '/database/migrations';
        
        if (is_dir($migrationPath)) {
            $this->addLine("✅ Migration directory exists", 1);
            
            $expectedMigrations = [
                'create_users_table',
                'create_fakultas_table',
                'create_prodi_table',
                'create_jenis_surats_table',
                'create_pengajuan_surats_table'
            ];
            
            $files = scandir($migrationPath);
            
            foreach ($expectedMigrations as $migration) {
                $found = false;
                foreach ($files as $file) {
                    if (strpos($file, $migration) !== false) {
                        $found = true;
                        break;
                    }
                }
                $status = $found ? "✅" : "❌";
                $this->addLine("{$status} {$migration}", 2);
            }
        } else {
            $this->addLine("❌ Migration directory not found", 1);
        }
    }
    
    private function analyzeSeeds() {
        $this->addSection("SEEDERS");
        
        $seederPath = $this->basePath . '/database/seeders';
        
        if (is_dir($seederPath)) {
            $this->addLine("✅ Seeder directory exists", 1);
            
            $expectedSeeders = [
                'RolePermissionSeeder.php',
                'FakultasSeeder.php',
                'ProdiSeeder.php',
                'JenisSuratSeeder.php',
                'UserSeeder.php'
            ];
            
            foreach ($expectedSeeders as $seeder) {
                $seederFile = $seederPath . '/' . $seeder;
                $status = file_exists($seederFile) ? "✅" : "❌";
                $this->addLine("{$status} {$seeder}", 2);
            }
        } else {
            $this->addLine("❌ Seeder directory not found", 1);
        }
    }
    
    private function generateSummary() {
        $this->addSection("SYSTEM SUMMARY");
        
        $this->addLine("🎯 FOCUS: Sistem Pengajuan Surat Mahasiswa", 1);
        $this->addLine("📋 Core Feature: Mahasiswa dapat mengajukan surat secara online", 1);
        $this->addLine("👥 User Roles:", 1);
        $this->addLine("   • Mahasiswa: Mengajukan surat", 2);
        $this->addLine("   • Staff Prodi: Review dan approve pengajuan", 2);
        $this->addLine("   • Staff Fakultas: Generate dan manage surat", 2);
        $this->addLine("   • Kaprodi: Approve pengajuan (sama seperti staff prodi)", 2);
        
        $this->addLine("\n📊 Workflow:", 1);
        $this->addLine("1. Mahasiswa submit pengajuan surat online", 2);
        $this->addLine("2. Staff Prodi/Kaprodi review dan approve/reject", 2);
        $this->addLine("3. Staff Fakultas generate surat dan print untuk TTD", 2);
        $this->addLine("4. Setelah TTD, upload link surat signed", 2);
        $this->addLine("5. Status completed, surat dapat didownload mahasiswa", 2);
        
        $this->addLine("\n🔧 Tech Stack:", 1);
        $this->addLine("• Laravel (Backend)", 2);
        $this->addLine("• Blade Templates (Views)", 2);
        $this->addLine("• Tailwind CSS (Styling)", 2);
        $this->addLine("• DomPDF (PDF Generation)", 2);
        $this->addLine("• Spatie Roles & Permissions", 2);
        
        $this->addLine("\n📄 Supported Letter Types:", 1);
        $this->addLine("• MA: Surat Mahasiswa Aktif", 2);
        $this->addLine("• KP: Surat Kerja Praktek", 2);
        $this->addLine("• TA: Surat Tugas Akhir", 2);
        $this->addLine("• SKM: Surat Keterangan Mahasiswa", 2);
    }
    
    private function checkMethodInFile($content, $methodName) {
        $patterns = [
            "function {$methodName}(",
            "public function {$methodName}(",
            "private function {$methodName}(",
            "protected function {$methodName}("
        ];
        
        foreach ($patterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $basePath = isset($argv[1]) ? $argv[1] : getcwd();
    
    echo "Starting System Analysis...\n";
    echo "Base Path: {$basePath}\n\n";
    
    $analyzer = new SystemAnalyzer($basePath);
    $report = $analyzer->analyze();
    
    // Output to console
    echo $report;
    
    // Save to file
    $reportFile = 'system_analysis_' . date('Y-m-d_H-i-s') . '.txt';
    file_put_contents($reportFile, $report);
    
    echo "\n\n📄 Report saved to: {$reportFile}\n";
    echo "✅ Analysis completed!\n";
} else {
    echo "This script should be run from command line.\n";
    echo "Usage: php analyze_system.php [project_path]\n";
}