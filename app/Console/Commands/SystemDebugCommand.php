<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class SystemDebugCommand extends Command
{
    protected $signature = 'debug:system';
    protected $description = 'Comprehensive system debug for Sistem Surat FSI';

    public function handle()
    {
        $this->info('=== SISTEM SURAT FSI - COMPREHENSIVE DEBUG ===');
        $this->newLine();

        // 1. Database Overview
        $this->debugDatabaseOverview();
        
        // 2. Table Structures
        $this->debugTableStructures();
        
        // 3. Sample Data
        $this->debugSampleData();
        
        // 4. File Structure
        $this->debugFileStructure();
        
        // 5. Laravel Environment
        $this->debugLaravelEnvironment();
        
        // 6. Missing Components
        $this->debugMissingComponents();

        $this->info('=== DEBUG COMPLETE ===');
        return 0;
    }

    private function debugDatabaseOverview()
    {
        $this->info('📊 DATABASE OVERVIEW');
        $this->line(str_repeat('-', 50));
        
        try {
            $tables = DB::select('SHOW TABLES');
            $this->info('Database: ' . DB::connection()->getDatabaseName());
            $this->info('Tables found: ' . count($tables));
            
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                $count = DB::table($tableName)->count();
                $this->line("  • {$tableName} ({$count} records)");
            }
        } catch (\Exception $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function debugTableStructures()
    {
        $this->info('🏗️  TABLE STRUCTURES');
        $this->line(str_repeat('-', 50));
        
        $coreTables = ['pengajuan_surat', 'jenis_surat', 'prodi', 'users', 'surat'];
        
        foreach ($coreTables as $table) {
            if (Schema::hasTable($table)) {
                $this->info("✅ Table: {$table}");
                $columns = DB::select("DESCRIBE {$table}");
                
                foreach ($columns as $column) {
                    $key = $column->Key ? " [{$column->Key}]" : '';
                    $null = $column->Null === 'NO' ? ' NOT NULL' : '';
                    $this->line("    {$column->Field}: {$column->Type}{$null}{$key}");
                }
                
                // Check for foreign keys
                $foreignKeys = DB::select("
                    SELECT 
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = '{$table}'
                      AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $this->line("    Foreign Keys:");
                    foreach ($foreignKeys as $fk) {
                        $this->line("      {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}");
                    }
                }
                
            } else {
                $this->error("❌ Table missing: {$table}");
            }
            $this->newLine();
        }
    }

    private function debugSampleData()
    {
        $this->info('📋 SAMPLE DATA ANALYSIS');
        $this->line(str_repeat('-', 50));
        
        // Pengajuan Surat Sample
        if (Schema::hasTable('pengajuan_surat')) {
            $pengajuanCount = DB::table('pengajuan_surat')->count();
            $this->info("Pengajuan Surat: {$pengajuanCount} records");
            
            if ($pengajuanCount > 0) {
                $sample = DB::table('pengajuan_surat')
                    ->select('id', 'tracking_token', 'status', 'nim', 'nama_mahasiswa', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();
                
                $this->table(
                    ['ID', 'Token', 'Status', 'NIM', 'Nama', 'Created At'],
                    $sample->map(function($item) {
                        return [
                            $item->id,
                            $item->tracking_token,
                            $item->status,
                            $item->nim,
                            substr($item->nama_mahasiswa, 0, 20) . '...',
                            $item->created_at
                        ];
                    })->toArray()
                );
                
                // Status distribution
                $statusStats = DB::table('pengajuan_surat')
                    ->select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get();
                
                $this->info('Status Distribution:');
                foreach ($statusStats as $stat) {
                    $this->line("  • {$stat->status}: {$stat->count}");
                }
            }
        }
        
        // Jenis Surat
        if (Schema::hasTable('jenis_surat')) {
            $jenisSurat = DB::table('jenis_surat')->get();
            $this->info("Jenis Surat: {$jenisSurat->count()} types");
            foreach ($jenisSurat as $jenis) {
                $this->line("  • {$jenis->kode_surat}: {$jenis->nama_jenis}");
            }
        }
        
        $this->newLine();
    }

    private function debugFileStructure()
    {
        $this->info('📁 FILE STRUCTURE ANALYSIS');
        $this->line(str_repeat('-', 50));
        
        // Controllers
        $this->checkFile('app/Http/Controllers/PublicSuratController.php', 'Public Surat Controller');
        $this->checkFile('app/Http/Controllers/StaffController.php', 'Staff Controller');
        
        // Models
        $this->checkFile('app/Models/PengajuanSurat.php', 'PengajuanSurat Model');
        $this->checkFile('app/Models/JenisSurat.php', 'JenisSurat Model');
        $this->checkFile('app/Models/Prodi.php', 'Prodi Model');
        $this->checkFile('app/Models/Surat.php', 'Surat Model');
        
        // Views
        $this->checkFile('resources/views/layouts/public.blade.php', 'Public Layout');
        $this->checkFile('resources/views/public/pengajuan/create.blade.php', 'Pengajuan Form');
        $this->checkFile('resources/views/public/pengajuan/form.blade.php', 'Enhanced Form');
        $this->checkFile('resources/views/public/tracking.blade.php', 'Tracking Page');
        
        // Routes
        $this->checkFile('routes/web.php', 'Web Routes');
        
        // Check for recent migrations
        $migrationPath = database_path('migrations');
        if (File::exists($migrationPath)) {
            $migrations = collect(File::files($migrationPath))
                ->map(fn($file) => $file->getFilename())
                ->sortDesc()
                ->take(5);
            
            $this->info('Recent Migrations:');
            foreach ($migrations as $migration) {
                $this->line("  • {$migration}");
            }
        }
        
        $this->newLine();
    }

    private function debugLaravelEnvironment()
    {
        $this->info('🔧 LARAVEL ENVIRONMENT');
        $this->line(str_repeat('-', 50));
        
        $this->info('Laravel Version: ' . app()->version());
        $this->info('PHP Version: ' . phpversion());
        $this->info('Environment: ' . app()->environment());
        
        // Check important configurations
        $this->info('Database: ' . config('database.default'));
        $this->info('Cache Driver: ' . config('cache.default'));
        $this->info('Session Driver: ' . config('session.driver'));
        
        // Check if key packages are installed
        $packages = [
            'laravel/framework',
            'laravel/tinker',
            'spatie/laravel-permission'
        ];
        
        $this->info('Key Packages:');
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        foreach ($packages as $package) {
            $version = $composer['require'][$package] ?? 'Not installed';
            $status = $version !== 'Not installed' ? '✅' : '❌';
            $this->line("  {$status} {$package}: {$version}");
        }
        
        $this->newLine();
    }

    private function debugMissingComponents()
    {
        $this->info('⚠️  MISSING COMPONENTS ANALYSIS');
        $this->line(str_repeat('-', 50));
        
        $missingItems = [];
        
        // Check for approval columns in pengajuan_surat
        if (Schema::hasTable('pengajuan_surat')) {
            $columns = Schema::getColumnListing('pengajuan_surat');
            
            $requiredColumns = ['approved_at', 'approved_by', 'token_description', 'dosen_wali_nama'];
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $columns)) {
                    $missingItems[] = "Column pengajuan_surat.{$col}";
                }
            }
        }
        
        // Check for surat table
        if (!Schema::hasTable('surat')) {
            $missingItems[] = "Table: surat";
        }
        
        // Check for key routes
        try {
            $routes = collect(\Route::getRoutes())->map(fn($route) => $route->getName())->filter();
            
            $requiredRoutes = [
                'public.pengajuan.store',
                'tracking.public',
                'staff.pengajuan.approve'
            ];
            
            foreach ($requiredRoutes as $routeName) {
                if (!$routes->contains($routeName)) {
                    $missingItems[] = "Route: {$routeName}";
                }
            }
        } catch (\Exception $e) {
            $missingItems[] = "Route analysis failed: " . $e->getMessage();
        }
        
        if (empty($missingItems)) {
            $this->info('✅ All components seem to be in place!');
        } else {
            $this->error('Missing Components:');
            foreach ($missingItems as $item) {
                $this->line("  ❌ {$item}");
            }
        }
        
        $this->newLine();
    }

    private function checkFile($path, $description)
    {
        $fullPath = base_path($path);
        $exists = File::exists($fullPath);
        $status = $exists ? '✅' : '❌';
        $size = $exists ? ' (' . human_filesize(File::size($fullPath)) . ')' : '';
        
        $this->line("{$status} {$description}: {$path}{$size}");
    }
}

// Helper function
if (!function_exists('human_filesize')) {
    function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . 'B';
    }
}