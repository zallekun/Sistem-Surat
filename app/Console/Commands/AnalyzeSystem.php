<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class AnalyzeSystem extends Command
{
    protected $signature = 'system:analyze {--output= : Output file path}';
    protected $description = 'Analyze system structure for Surat Pengajuan Mahasiswa';
    
    private $output_lines = [];

    public function handle()
    {
        $this->line("==========================================================");
        $this->line("SISTEM SURAT PENGAJUAN MAHASISWA - STRUCTURE ANALYZER");
        $this->line("==========================================================\n");
        
        $this->analyzeDatabaseStructure();
        $this->analyzeModels();
        $this->analyzeControllers();
        $this->analyzeViews();
        $this->analyzeRoutes();
        $this->generateSummary();
        
        // Save to file if output option provided
        if ($this->option('output')) {
            File::put($this->option('output'), implode("\n", $this->output_lines));
            $this->info("\nReport saved to: " . $this->option('output'));
        }
        
        return 0;
    }
    
    private function addLine($text, $indent = 0)
    {
        $line = str_repeat("  ", $indent) . $text;
        $this->line($line);
        $this->output_lines[] = $line;
    }
    
    private function addSection($title)
    {
        $this->addLine("\n" . str_repeat("=", 60));
        $this->addLine($title);
        $this->addLine(str_repeat("=", 60));
    }
    
    private function analyzeDatabaseStructure()
    {
        $this->addSection("DATABASE STRUCTURE");
        
        $tables = [
            'users',
            'fakultas', 
            'prodis',
            'jenis_surats',
            'pengajuan_surats'
        ];
        
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->addLine("\n❌ Table '{$table}' NOT FOUND", 1);
                continue;
            }
            
            $this->addLine("\n✅ Table: {$table}", 1);
            
            // Get columns
            $columns = Schema::getColumnListing($table);
            $this->addLine("Columns: " . implode(', ', $columns), 2);
            
            // Get column details
            $columnDetails = [];
            foreach ($columns as $column) {
                $type = Schema::getColumnType($table, $column);
                $columnDetails[] = "{$column} ({$type})";
            }
            $this->addLine("Details:", 2);
            foreach ($columnDetails as $detail) {
                $this->addLine("• {$detail}", 3);
            }
            
            // Get foreign keys (MySQL specific)
            try {
                $foreignKeys = DB::select("
                    SELECT 
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = ? 
                    AND TABLE_NAME = ?
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [env('DB_DATABASE'), $table]);
                
                if (!empty($foreignKeys)) {
                    $this->addLine("Foreign Keys:", 2);
                    foreach ($foreignKeys as $fk) {
                        $this->addLine("• {$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}({$fk->REFERENCED_COLUMN_NAME})", 3);
                    }
                }
            } catch (\Exception $e) {
                $this->addLine("Could not fetch foreign keys: " . $e->getMessage(), 3);
            }
            
            // Get indexes
            try {
                $indexes = DB::select("SHOW INDEX FROM {$table}");
                $uniqueIndexes = array_filter($indexes, fn($idx) => $idx->Key_name !== 'PRIMARY' && $idx->Non_unique == 0);
                
                if (!empty($uniqueIndexes)) {
                    $this->addLine("Unique Indexes:", 2);
                    foreach ($uniqueIndexes as $idx) {
                        $this->addLine("• {$idx->Key_name} on {$idx->Column_name}", 3);
                    }
                }
            } catch (\Exception $e) {
                $this->addLine("Could not fetch indexes", 3);
            }
        }
        
        // Status enum values for pengajuan_surats
        if (Schema::hasTable('pengajuan_surats')) {
            $this->addLine("\n📊 Status Flow (pengajuan_surats):", 1);
            try {
                $enumValues = DB::select("
                    SELECT COLUMN_TYPE 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = ? 
                    AND TABLE_NAME = 'pengajuan_surats' 
                    AND COLUMN_NAME = 'status'
                ", [env('DB_DATABASE')]);
                
                if (!empty($enumValues)) {
                    preg_match("/^enum\(\'(.*)\'\)$/", $enumValues[0]->COLUMN_TYPE, $matches);
                    if (isset($matches[1])) {
                        $statuses = explode("','", $matches[1]);
                        foreach ($statuses as $status) {
                            $this->addLine("• {$status}", 2);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->addLine("Could not fetch status enum values", 2);
            }
        }
    }
    
    private function analyzeModels()
    {
        $this->addSection("MODELS STRUCTURE");
        
        $models = [
            'User' => 'App\\Models\\User',
            'Fakultas' => 'App\\Models\\Fakultas',
            'Prodi' => 'App\\Models\\Prodi',
            'JenisSurat' => 'App\\Models\\JenisSurat',
            'PengajuanSurat' => 'App\\Models\\PengajuanSurat'
        ];
        
        foreach ($models as $name => $class) {
            if (!class_exists($class)) {
                $this->addLine("\n❌ Model '{$name}' NOT FOUND", 1);
                continue;
            }
            
            $this->addLine("\n✅ Model: {$name}", 1);
            
            $reflection = new \ReflectionClass($class);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            
            // Check for relationships
            $relationships = [];
            foreach ($methods as $method) {
                if ($method->class !== $class) continue;
                
                $methodName = $method->getName();
                if (in_array($methodName, ['belongsTo', 'hasMany', 'hasOne', 'belongsToMany'])) {
                    continue;
                }
                
                // Check if method returns relationship
                try {
                    $source = file_get_contents($method->getFileName());
                    if (preg_match('/function\s+' . $methodName . '\s*\(.*?\)\s*{[^}]*return\s+\$this->(belongsTo|hasMany|hasOne|belongsToMany)/s', $source)) {
                        $relationships[] = $methodName;
                    }
                } catch (\Exception $e) {
                    // Skip
                }
            }
            
            if (!empty($relationships)) {
                $this->addLine("Relationships:", 2);
                foreach ($relationships as $rel) {
                    $this->addLine("• {$rel}()", 3);
                }
            }
            
            // Check for fillable
            try {
                $instance = new $class;
                $fillable = $instance->getFillable();
                if (!empty($fillable)) {
                    $this->addLine("Fillable: " . implode(', ', $fillable), 2);
                }
            } catch (\Exception $e) {
                $this->addLine("Could not get fillable attributes", 2);
            }
            
            // Check for custom methods
            $customMethods = array_filter($methods, function($method) use ($class) {
                return $method->class === $class 
                    && !in_array($method->getName(), ['__construct', 'getFillable', 'getTable'])
                    && !str_starts_with($method->getName(), 'get')
                    && !str_starts_with($method->getName(), 'set')
                    && !str_starts_with($method->getName(), 'scope');
            });
            
            if (!empty($customMethods)) {
                $this->addLine("Custom Methods:", 2);
                foreach ($customMethods as $method) {
                    if (count(array_filter($customMethods)) > 10) break; // Limit output
                    $this->addLine("• {$method->getName()}()", 3);
                }
            }
        }
    }
    
    private function analyzeControllers()
    {
        $this->addSection("CONTROLLERS STRUCTURE");
        
        $controllers = [
            'PengajuanSuratController' => 'App\\Http\\Controllers\\PengajuanSuratController',
            'StaffPengajuanController' => 'App\\Http\\Controllers\\StaffPengajuanController',
            'FakultasStaffController' => 'App\\Http\\Controllers\\FakultasStaffController',
            'SuratFSIController' => 'App\\Http\\Controllers\\SuratFSIController'
        ];
        
        foreach ($controllers as $name => $class) {
            if (!class_exists($class)) {
                $this->addLine("\n❌ Controller '{$name}' NOT FOUND", 1);
                continue;
            }
            
            $this->addLine("\n✅ Controller: {$name}", 1);
            
            $reflection = new \ReflectionClass($class);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            
            $publicMethods = array_filter($methods, function($method) use ($class) {
                return $method->class === $class 
                    && !in_array($method->getName(), ['__construct', 'middleware']);
            });
            
            $this->addLine("Methods:", 2);
            foreach ($publicMethods as $method) {
                $params = array_map(fn($p) => '$' . $p->getName(), $method->getParameters());
                $this->addLine("• {$method->getName()}(" . implode(', ', $params) . ")", 3);
            }
        }
    }
    
    private function analyzeViews()
    {
        $this->addSection("VIEWS STRUCTURE");
        
        $viewDirs = [
            'pengajuan' => 'Views untuk mahasiswa mengajukan surat',
            'staff/pengajuan' => 'Views untuk staff prodi review pengajuan',
            'fakultas/surat' => 'Views untuk staff fakultas manage surat',
            'surat/fsi' => 'Views untuk preview dan edit surat',
            'surat/pdf' => 'Templates PDF untuk print surat'
        ];
        
        foreach ($viewDirs as $dir => $purpose) {
            $path = resource_path('views/' . $dir);
            
            if (!is_dir($path)) {
                $this->addLine("\n❌ View Directory '{$dir}' NOT FOUND", 1);
                continue;
            }
            
            $this->addLine("\n✅ View Directory: {$dir}", 1);
            $this->addLine("Purpose: {$purpose}", 2);
            
            $files = File::files($path);
            if (!empty($files)) {
                $this->addLine("Files:", 2);
                foreach ($files as $file) {
                    $this->addLine("• {$file->getFilename()}", 3);
                }
            }
        }
    }
    
    private function analyzeRoutes()
    {
        $this->addSection("ROUTES STRUCTURE");
        
        $routes = \Route::getRoutes();
        
        $groupedRoutes = [
            'Mahasiswa (Public)' => [],
            'Staff Prodi' => [],
            'Staff Fakultas' => []
        ];
        
        foreach ($routes->getRoutes() as $route) {
            $uri = $route->uri();
            $name = $route->getName();
            $action = $route->getActionName();
            
            if (str_starts_with($uri, 'pengajuan')) {
                $groupedRoutes['Mahasiswa (Public)'][] = [
                    'method' => implode('|', $route->methods()),
                    'uri' => $uri,
                    'name' => $name,
                    'action' => $action
                ];
            } elseif (str_starts_with($uri, 'staff/pengajuan')) {
                $groupedRoutes['Staff Prodi'][] = [
                    'method' => implode('|', $route->methods()),
                    'uri' => $uri,
                    'name' => $name,
                    'action' => $action
                ];
            } elseif (str_starts_with($uri, 'fakultas')) {
                $groupedRoutes['Staff Fakultas'][] = [
                    'method' => implode('|', $route->methods()),
                    'uri' => $uri,
                    'name' => $name,
                    'action' => $action
                ];
            }
        }
        
        foreach ($groupedRoutes as $group => $routes) {
            if (empty($routes)) continue;
            
            $this->addLine("\n{$group}:", 1);
            foreach ($routes as $route) {
                $this->addLine("• {$route['method']} /{$route['uri']}", 2);
                if ($route['name']) {
                    $this->addLine("  Name: {$route['name']}", 3);
                }
            }
        }
    }
    
    private function generateSummary()
    {
        $this->addSection("SYSTEM SUMMARY");
        
        $this->addLine("🎯 FOCUS: Sistem Pengajuan Surat Mahasiswa", 1);
        $this->addLine("📋 Core Feature: Mahasiswa dapat mengajukan surat secara online", 1);
        
        $this->addLine("\n💥 PERUBAHAN ALUR BARU (KP & TA):", 1);
        $this->addLine("• Staff Prodi generate Surat Pengantar + TTD Kaprodi", 2);
        $this->addLine("• Staff Fakultas terima Pengantar + generate Surat Final", 2);
        $this->addLine("• Surat MA tetap flow lama (approve → fakultas)", 2);
        
        $this->addLine("\n📊 Workflow KP/TA:", 1);
        $this->addLine("1. Mahasiswa submit pengajuan", 2);
        $this->addLine("2. Staff Prodi review → Generate Surat Pengantar", 2);
        $this->addLine("3. Staff Prodi insert TTD Kaprodi → Teruskan", 2);
        $this->addLine("4. Staff Fakultas terima Pengantar → Generate Surat Final", 2);
        $this->addLine("5. Print TTD → Upload → Selesai", 2);
        
        $this->addLine("\n🔧 Tech Stack:", 1);
        $this->addLine("• Laravel " . app()->version(), 2);
        $this->addLine("• Database: " . env('DB_CONNECTION'), 2);
        $this->addLine("• PHP " . PHP_VERSION, 2);
    }
}