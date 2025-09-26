<?php
/**
 * CHECK CURRENT DATABASE STRUCTURE - SISTEMA SURAT
 * 
 * Script untuk menganalisis struktur database yang ada saat ini
 * dan memberikan rekomendasi perbaikan yang tepat
 * 
 * File: check_current_database_structure.php
 * 
 * CARA PAKAI:
 * php check_current_database_structure.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseStructureChecker
{
    private $output = [];
    
    public function __construct()
    {
        $this->log("=== DATABASE STRUCTURE ANALYSIS - SISTEMA SURAT ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("Database: " . config('database.connections.mysql.database'));
        $this->log("Host: " . config('database.connections.mysql.host'));
        $this->log("");
    }
    
    public function runAnalysis()
    {
        $this->log("🔍 PHASE 1: DATABASE CONNECTION TEST");
        $this->testDatabaseConnection();
        
        $this->log("\n📋 PHASE 2: LIST ALL EXISTING TABLES");
        $this->listAllTables();
        
        $this->log("\n🔍 PHASE 3: ANALYZE CRITICAL TABLES");
        $this->analyzeCriticalTables();
        
        $this->log("\n🔗 PHASE 4: CHECK TABLE RELATIONSHIPS");
        $this->checkTableRelationships();
        
        $this->log("\n📊 PHASE 5: DATA ANALYSIS");
        $this->analyzeTableData();
        
        $this->log("\n🎯 PHASE 6: RECOMMENDATIONS");
        $this->generateRecommendations();
        
        $this->displayResults();
    }
    
    /**
     * Test database connection
     */
    private function testDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            $this->log("✅ Database connection successful");
            
            $dbName = DB::connection()->getDatabaseName();
            $this->log("📁 Connected to database: " . $dbName);
            
        } catch (\Exception $e) {
            $this->log("❌ Database connection failed: " . $e->getMessage());
            return false;
        }
        
        return true;
    }
    
    /**
     * List all existing tables
     */
    private function listAllTables()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $this->log("📊 FOUND " . count($tables) . " TABLES:");
            $this->log(str_repeat("=", 40));
            
            $tableNames = [];
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                $tableNames[] = $tableName;
                
                // Get table info
                $tableInfo = DB::select("
                    SELECT 
                        TABLE_ROWS as row_count,
                        ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as size_mb,
                        ENGINE,
                        TABLE_COLLATION as collation
                    FROM information_schema.TABLES 
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                ", [config('database.connections.mysql.database'), $tableName]);
                
                $rows = $tableInfo[0]->row_count ?? 'Unknown';
                $size = $tableInfo[0]->size_mb ?? '0';
                $engine = $tableInfo[0]->ENGINE ?? 'Unknown';
                
                $this->log("  📋 {$tableName}");
                $this->log("     Rows: {$rows} | Size: {$size}MB | Engine: {$engine}");
            }
            
            return $tableNames;
            
        } catch (\Exception $e) {
            $this->log("❌ Error listing tables: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Analyze critical tables structure
     */
    private function analyzeCriticalTables()
    {
        $criticalTables = [
            'users' => 'User management',
            'prodi' => 'Program studi data', 
            'fakultas' => 'Fakultas data',
            'jenis_surat' => 'Jenis surat master',
            'jabatan' => 'Jabatan master',
            'pengajuan_surat' => 'Main pengajuan data',
            'surat' => 'Generated surat data',
            'surat_generated' => 'PDF file tracking',
            'status_surat' => 'Status workflow'
        ];
        
        $this->log("🔍 ANALYZING CRITICAL TABLES:");
        $this->log(str_repeat("=", 50));
        
        foreach ($criticalTables as $tableName => $description) {
            $this->analyzeTable($tableName, $description);
        }
    }
    
    /**
     * Analyze individual table
     */
    private function analyzeTable($tableName, $description)
    {
        try {
            if (!Schema::hasTable($tableName)) {
                $this->log("❌ {$tableName}: NOT EXISTS");
                $this->log("   Description: {$description}");
                $this->log("   Status: MISSING - NEEDS CREATION");
                $this->log("");
                return;
            }
            
            // Table exists - get details
            $count = DB::table($tableName)->count();
            $columns = DB::select("DESCRIBE {$tableName}");
            
            $this->log("✅ {$tableName}: EXISTS");
            $this->log("   Description: {$description}");
            $this->log("   Records: {$count}");
            $this->log("   Columns: " . count($columns));
            
            // Show important columns
            $importantColumns = [];
            foreach ($columns as $column) {
                if (in_array($column->Field, ['id', 'status', 'created_at', 'updated_at']) || 
                    str_contains($column->Field, '_id') || 
                    str_contains($column->Field, 'nama_') ||
                    str_contains($column->Field, 'kode_')) {
                    $importantColumns[] = $column->Field . '(' . $column->Type . ')';
                }
            }
            
            if (!empty($importantColumns)) {
                $this->log("   Key columns: " . implode(', ', array_slice($importantColumns, 0, 5)));
            }
            
            // Check for recent data
            if (in_array('created_at', array_column($columns, 'Field'))) {
                $latestRecord = DB::table($tableName)
                    ->whereNotNull('created_at')
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                if ($latestRecord) {
                    $this->log("   Latest record: " . $latestRecord->created_at);
                }
            }
            
            $this->log("");
            
        } catch (\Exception $e) {
            $this->log("❌ Error analyzing {$tableName}: " . $e->getMessage());
            $this->log("");
        }
    }
    
    /**
     * Check table relationships
     */
    private function checkTableRelationships()
    {
        $this->log("🔗 CHECKING TABLE RELATIONSHIPS:");
        $this->log(str_repeat("=", 40));
        
        try {
            // Get all foreign key constraints
            $foreignKeys = DB::select("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE CONSTRAINT_SCHEMA = ? 
                AND REFERENCED_TABLE_NAME IS NOT NULL
                ORDER BY TABLE_NAME, COLUMN_NAME
            ", [config('database.connections.mysql.database')]);
            
            if (empty($foreignKeys)) {
                $this->log("⚠️  No foreign key relationships found");
                $this->log("   This might indicate missing relationships");
            } else {
                $this->log("📊 FOUND " . count($foreignKeys) . " FOREIGN KEY RELATIONSHIPS:");
                
                foreach ($foreignKeys as $fk) {
                    $this->log("  🔗 {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}");
                }
            }
            
            // Check specific critical relationships
            $this->checkCriticalRelationships();
            
        } catch (\Exception $e) {
            $this->log("❌ Error checking relationships: " . $e->getMessage());
        }
    }
    
    /**
     * Check critical relationships that should exist
     */
    private function checkCriticalRelationships()
    {
        $this->log("\n🎯 CHECKING CRITICAL RELATIONSHIPS:");
        
        $criticalRelationships = [
            ['pengajuan_surat', 'prodi_id', 'prodi', 'id'],
            ['pengajuan_surat', 'jenis_surat_id', 'jenis_surat', 'id'], 
            ['surat_generated', 'pengajuan_id', 'pengajuan_surat', 'id'],
            ['users', 'prodi_id', 'prodi', 'id'],
            ['prodi', 'fakultas_id', 'fakultas', 'id']
        ];
        
        foreach ($criticalRelationships as $rel) {
            [$table, $column, $refTable, $refColumn] = $rel;
            
            if (!Schema::hasTable($table) || !Schema::hasTable($refTable)) {
                $this->log("  ❌ {$table}.{$column} -> {$refTable}.{$refColumn}: Tables missing");
                continue;
            }
            
            // Check if foreign key exists
            $fkExists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME = ?
            ", [config('database.connections.mysql.database'), $table, $column, $refTable]);
            
            if (empty($fkExists)) {
                $this->log("  ⚠️  {$table}.{$column} -> {$refTable}.{$refColumn}: No FK constraint");
            } else {
                $this->log("  ✅ {$table}.{$column} -> {$refTable}.{$refColumn}: FK exists");
            }
        }
    }
    
    /**
     * Analyze table data
     */
    private function analyzeTableData()
    {
        $this->log("📊 DATA ANALYSIS:");
        $this->log(str_repeat("=", 30));
        
        // Check if we have the minimum required data
        $requiredData = [
            'fakultas' => 'Should have at least 1 fakultas (FSI)',
            'prodi' => 'Should have at least 2-3 prodi',
            'jenis_surat' => 'Should have MA, KP, TA, KT types',
            'users' => 'Should have admin and test users',
        ];
        
        foreach ($requiredData as $table => $expectation) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->log("  📋 {$table}: {$count} records - {$expectation}");
                
                if ($table === 'jenis_surat' && $count > 0) {
                    $jenisTypes = DB::table($table)->pluck('kode_surat')->toArray();
                    $this->log("     Types: " . implode(', ', $jenisTypes));
                }
                
                if ($table === 'users' && $count > 0) {
                    $userRoles = DB::table($table)->distinct()->pluck('role')->toArray();
                    $this->log("     Roles: " . implode(', ', array_filter($userRoles)));
                }
            } else {
                $this->log("  ❌ {$table}: TABLE NOT EXISTS");
            }
        }
        
        // Check pengajuan_surat data if exists
        if (Schema::hasTable('pengajuan_surat')) {
            $this->analyzePengajuanData();
        }
        
        // Check surat_generated data if exists  
        if (Schema::hasTable('surat_generated')) {
            $this->analyzeSuratGeneratedData();
        }
    }
    
    /**
     * Analyze pengajuan_surat specific data
     */
    private function analyzePengajuanData()
    {
        try {
            $total = DB::table('pengajuan_surat')->count();
            $this->log("\n  🎯 PENGAJUAN_SURAT ANALYSIS:");
            $this->log("     Total records: {$total}");
            
            if ($total > 0) {
                // Status distribution
                $statusDist = DB::table('pengajuan_surat')
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get();
                
                $this->log("     Status distribution:");
                foreach ($statusDist as $status) {
                    $this->log("       {$status->status}: {$status->count}");
                }
                
                // Check for additional_data
                $withAdditionalData = DB::table('pengajuan_surat')
                    ->whereNotNull('additional_data')
                    ->count();
                $this->log("     With additional_data: {$withAdditionalData}");
                
                // Check date range
                $dateRange = DB::table('pengajuan_surat')
                    ->selectRaw('MIN(created_at) as oldest, MAX(created_at) as newest')
                    ->first();
                    
                if ($dateRange) {
                    $this->log("     Date range: {$dateRange->oldest} to {$dateRange->newest}");
                }
            }
            
        } catch (\Exception $e) {
            $this->log("     Error analyzing pengajuan data: " . $e->getMessage());
        }
    }
    
    /**
     * Analyze surat_generated specific data
     */
    private function analyzeSuratGeneratedData()
    {
        try {
            $total = DB::table('surat_generated')->count();
            $this->log("\n  📁 SURAT_GENERATED ANALYSIS:");
            $this->log("     Total records: {$total}");
            
            if ($total > 0) {
                // Check file paths
                $withFilePath = DB::table('surat_generated')
                    ->whereNotNull('file_path')
                    ->count();
                $this->log("     With file_path: {$withFilePath}");
                
                // Check if linked to pengajuan
                $linkedToPengajuan = DB::table('surat_generated as sg')
                    ->join('pengajuan_surat as ps', 'sg.pengajuan_id', '=', 'ps.id')
                    ->count();
                $this->log("     Linked to pengajuan: {$linkedToPengajuan}");
                
                // Check recent generation
                $recent = DB::table('surat_generated')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
                $this->log("     Generated in last 7 days: {$recent}");
            }
            
        } catch (\Exception $e) {
            $this->log("     Error analyzing surat_generated: " . $e->getMessage());
        }
    }
    
    /**
     * Generate recommendations based on analysis
     */
    private function generateRecommendations()
    {
        $this->log("🎯 RECOMMENDATIONS:");
        $this->log(str_repeat("=", 30));
        
        $recommendations = [];
        
        // Check for missing tables
        $missingTables = [];
        $criticalTables = ['pengajuan_surat', 'surat_generated', 'prodi', 'fakultas', 'jenis_surat'];
        
        foreach ($criticalTables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }
        
        if (!empty($missingTables)) {
            $recommendations[] = "🚨 HIGH PRIORITY: Create missing tables: " . implode(', ', $missingTables);
        }
        
        // Check for missing data
        if (Schema::hasTable('fakultas') && DB::table('fakultas')->count() == 0) {
            $recommendations[] = "📊 Insert master data for fakultas";
        }
        
        if (Schema::hasTable('prodi') && DB::table('prodi')->count() == 0) {
            $recommendations[] = "📊 Insert master data for prodi";
        }
        
        if (Schema::hasTable('jenis_surat') && DB::table('jenis_surat')->count() == 0) {
            $recommendations[] = "📊 Insert master data for jenis_surat";
        }
        
        // Check for relationship issues
        if (Schema::hasTable('pengajuan_surat') && Schema::hasTable('surat_generated')) {
            $orphanedGenerated = DB::table('surat_generated as sg')
                ->leftJoin('pengajuan_surat as ps', 'sg.pengajuan_id', '=', 'ps.id')
                ->whereNull('ps.id')
                ->count();
                
            if ($orphanedGenerated > 0) {
                $recommendations[] = "🔗 Fix {$orphanedGenerated} orphaned surat_generated records";
            }
        }
        
        // Check for status inconsistencies
        if (Schema::hasTable('pengajuan_surat')) {
            $completedWithoutGenerated = DB::table('pengajuan_surat as ps')
                ->leftJoin('surat_generated as sg', 'ps.id', '=', 'sg.pengajuan_id') 
                ->where('ps.status', 'completed')
                ->whereNull('sg.id')
                ->count();
                
            if ($completedWithoutGenerated > 0) {
                $recommendations[] = "🔄 Fix {$completedWithoutGenerated} completed pengajuan without surat_generated";
            }
        }
        
        if (empty($recommendations)) {
            $this->log("✅ Database structure looks good!");
            $this->log("   All critical tables exist with proper relationships");
        } else {
            foreach ($recommendations as $i => $rec) {
                $this->log(($i + 1) . ". {$rec}");
            }
        }
        
        $this->log("\n🛠️  SUGGESTED NEXT STEPS:");
        if (!empty($missingTables)) {
            $this->log("   1. Run: php fix_missing_database_tables.php");
        } else {
            $this->log("   1. Run: php complete_surat_system_fix.php");
        }
        $this->log("   2. Clear caches: php artisan cache:clear");
        $this->log("   3. Test application functionality");
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . PHP_EOL;
    }
    
    public function displayResults()
    {
        $this->log("\n" . str_repeat("=", 60));
        $this->log("📋 DATABASE STRUCTURE ANALYSIS COMPLETED");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        
        // Save detailed log
        $logFile = storage_path('logs/database_structure_check_' . date('Y-m-d_H-i-s') . '.log');
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, implode("\n", $this->output));
        $this->log("\n💾 Complete analysis saved to: {$logFile}");
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    echo "🔍 Starting Database Structure Analysis...\n\n";
    
    try {
        $checker = new DatabaseStructureChecker();
        $checker->runAnalysis();
        
    } catch (Exception $e) {
        echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "🔍 DATABASE STRUCTURE ANALYSIS (Web Mode)\n\n";
    
    try {
        $checker = new DatabaseStructureChecker();
        $checker->runAnalysis();
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>