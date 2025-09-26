<?php
// debug_system_structure.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SystemStructureDebugger
{
    private $results = [];
    
    public function __construct()
    {
        echo "\n🔍 SYSTEM STRUCTURE DEBUGGER\n";
        echo "=============================\n\n";
    }
    
    public function debug()
    {
        $this->checkDatabaseTables();
        $this->checkPengajuanSuratColumns();
        $this->checkSuratGeneratedColumns();
        $this->checkTrackingHistoryTable();
        $this->checkRoutes();
        $this->showResults();
    }
    
    private function checkDatabaseTables()
    {
        echo "📊 Checking Database Tables...\n";
        
        $requiredTables = [
            'pengajuan_surat',
            'surat_generated',
            'tracking_histories',
            'barcode_signatures',
            'status_histories'
        ];
        
        foreach ($requiredTables as $table) {
            if (Schema::hasTable($table)) {
                $this->results['tables'][$table] = '✅ EXISTS';
                echo "  ✅ Table '$table' exists\n";
            } else {
                $this->results['tables'][$table] = '❌ MISSING';
                echo "  ❌ Table '$table' is missing\n";
            }
        }
    }
    
    private function checkPengajuanSuratColumns()
    {
        echo "\n📋 Checking pengajuan_surat columns...\n";
        
        if (!Schema::hasTable('pengajuan_surat')) {
            echo "  ❌ Table pengajuan_surat doesn't exist\n";
            return;
        }
        
        $columns = Schema::getColumnListing('pengajuan_surat');
        
        $requiredColumns = [
            'id',
            'tracking_token',
            'nim',
            'nama_mahasiswa',
            'email',
            'phone',
            'prodi_id',
            'jenis_surat_id',
            'keperluan',
            'additional_data',
            'surat_data',        // New column for edited data
            'status',
            'printed_at',        // New column
            'printed_by',        // New column
            'surat_generated_id',
            'completed_at',
            'completed_by'
        ];
        
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columns)) {
                echo "  ✅ Column '$column' exists\n";
                $this->results['pengajuan_columns'][$column] = '✅';
            } else {
                echo "  ❌ Column '$column' is MISSING\n";
                $this->results['pengajuan_columns'][$column] = '❌';
            }
        }
        
        // Show column types
        echo "\n  Column Details:\n";
        $columnDetails = DB::select("SHOW COLUMNS FROM pengajuan_surat");
        foreach ($columnDetails as $col) {
            if (in_array($col->Field, ['surat_data', 'additional_data', 'printed_at', 'printed_by'])) {
                echo "    - {$col->Field}: {$col->Type} " . ($col->Null == 'YES' ? '(nullable)' : '(not null)') . "\n";
            }
        }
    }
    
    private function checkSuratGeneratedColumns()
    {
        echo "\n📄 Checking surat_generated columns...\n";
        
        if (!Schema::hasTable('surat_generated')) {
            echo "  ❌ Table surat_generated doesn't exist\n";
            return;
        }
        
        $columns = Schema::getColumnListing('surat_generated');
        
        $requiredColumns = [
            'id',
            'pengajuan_id',
            'nomor_surat',
            'file_path',
            'signed_url',        // New column for signed document link
            'signed_by',
            'signed_at',
            'generated_by',
            'status',
            'notes'              // New column for notes
        ];
        
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columns)) {
                echo "  ✅ Column '$column' exists\n";
                $this->results['surat_generated_columns'][$column] = '✅';
            } else {
                echo "  ❌ Column '$column' is MISSING\n";
                $this->results['surat_generated_columns'][$column] = '❌';
            }
        }
    }
    
    private function checkTrackingHistoryTable()
    {
        echo "\n📝 Checking tracking_histories table...\n";
        
        if (!Schema::hasTable('tracking_histories')) {
            echo "  ❌ Table tracking_histories doesn't exist - NEED TO CREATE\n";
            $this->results['tracking_histories'] = '❌ NEED TO CREATE';
            
            echo "\n  Migration needed:\n";
            echo "  ```sql\n";
            echo "  CREATE TABLE tracking_histories (\n";
            echo "    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n";
            echo "    pengajuan_id BIGINT UNSIGNED NOT NULL,\n";
            echo "    status VARCHAR(50) NOT NULL,\n";
            echo "    description TEXT,\n";
            echo "    created_by BIGINT UNSIGNED NULL,\n";
            echo "    created_at TIMESTAMP NULL,\n";
            echo "    updated_at TIMESTAMP NULL,\n";
            echo "    FOREIGN KEY (pengajuan_id) REFERENCES pengajuan_surat(id) ON DELETE CASCADE,\n";
            echo "    FOREIGN KEY (created_by) REFERENCES users(id),\n";
            echo "    INDEX idx_pengajuan_created (pengajuan_id, created_at)\n";
            echo "  );\n";
            echo "  ```\n";
        } else {
            $columns = Schema::getColumnListing('tracking_histories');
            echo "  ✅ Table exists with columns: " . implode(', ', $columns) . "\n";
            $this->results['tracking_histories'] = '✅ EXISTS';
        }
    }
    
    private function checkRoutes()
    {
        echo "\n🛣️ Checking Routes...\n";
        
        $routes = Route::getRoutes();
        
        $requiredRoutes = [
            'fakultas.surat.index' => 'GET',
            'fakultas.surat.fsi.preview' => 'GET',
            'fakultas.surat.fsi.save-edits' => 'POST',
            'fakultas.surat.fsi.print' => 'GET',
            'fakultas.surat.fsi.upload-signed' => 'POST',
            'fakultas.surat.fsi.reject' => 'POST',
            'tracking.show' => 'GET',
            'tracking.api' => 'POST',
            'tracking.download' => 'GET'
        ];
        
        foreach ($requiredRoutes as $name => $method) {
            $exists = false;
            foreach ($routes as $route) {
                if ($route->getName() == $name) {
                    $exists = true;
                    echo "  ✅ Route '$name' exists [{$method}]\n";
                    break;
                }
            }
            if (!$exists) {
                echo "  ❌ Route '$name' is MISSING [{$method}]\n";
                $this->results['routes'][$name] = '❌ MISSING';
            } else {
                $this->results['routes'][$name] = '✅';
            }
        }
        
        // Check for conflicting routes
        echo "\n  Checking for FSI route conflicts...\n";
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (str_contains($uri, 'fakultas/surat/fsi')) {
                echo "    Found FSI route: {$route->methods()[0]} /{$uri} -> " . ($route->getName() ?? 'unnamed') . "\n";
            }
        }
    }
    
    private function showResults()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 SUMMARY\n";
        echo str_repeat("=", 50) . "\n\n";
        
        // Count issues
        $issues = [];
        
        // Check tables
        foreach ($this->results['tables'] ?? [] as $table => $status) {
            if (str_contains($status, '❌')) {
                $issues[] = "Missing table: $table";
            }
        }
        
        // Check columns
        foreach (['pengajuan_columns', 'surat_generated_columns'] as $key) {
            foreach ($this->results[$key] ?? [] as $column => $status) {
                if ($status == '❌') {
                    $issues[] = "Missing column: $column in " . str_replace('_columns', '', $key);
                }
            }
        }
        
        // Check routes
        foreach ($this->results['routes'] ?? [] as $route => $status) {
            if (str_contains($status, '❌')) {
                $issues[] = "Missing route: $route";
            }
        }
        
        if (empty($issues)) {
            echo "✅ All system structures are in place!\n";
        } else {
            echo "❌ Found " . count($issues) . " issues:\n\n";
            foreach ($issues as $issue) {
                echo "  • $issue\n";
            }
            
            echo "\n📝 MIGRATIONS NEEDED:\n";
            echo "-------------------\n";
            $this->generateMigrations();
        }
    }
    
    private function generateMigrations()
    {
        // Check if we need to add columns to pengajuan_surat
        $missingPengajuanColumns = [];
        foreach ($this->results['pengajuan_columns'] ?? [] as $column => $status) {
            if ($status == '❌') {
                $missingPengajuanColumns[] = $column;
            }
        }
        
        if (!empty($missingPengajuanColumns)) {
            echo "\n1. Add columns to pengajuan_surat:\n";
            echo "```php\n";
            echo "Schema::table('pengajuan_surat', function (Blueprint \$table) {\n";
            
            foreach ($missingPengajuanColumns as $column) {
                switch ($column) {
                    case 'surat_data':
                        echo "    \$table->json('surat_data')->nullable()->after('additional_data');\n";
                        break;
                    case 'printed_at':
                        echo "    \$table->timestamp('printed_at')->nullable();\n";
                        break;
                    case 'printed_by':
                        echo "    \$table->unsignedBigInteger('printed_by')->nullable();\n";
                        echo "    \$table->foreign('printed_by')->references('id')->on('users');\n";
                        break;
                }
            }
            
            echo "});\n";
            echo "```\n";
        }
        
        // Check if we need to add columns to surat_generated
        $missingSuratGeneratedColumns = [];
        foreach ($this->results['surat_generated_columns'] ?? [] as $column => $status) {
            if ($status == '❌') {
                $missingSuratGeneratedColumns[] = $column;
            }
        }
        
        if (!empty($missingSuratGeneratedColumns)) {
            echo "\n2. Add columns to surat_generated:\n";
            echo "```php\n";
            echo "Schema::table('surat_generated', function (Blueprint \$table) {\n";
            
            foreach ($missingSuratGeneratedColumns as $column) {
                switch ($column) {
                    case 'signed_url':
                        echo "    \$table->string('signed_url', 500)->nullable()->after('file_path');\n";
                        break;
                    case 'notes':
                        echo "    \$table->text('notes')->nullable();\n";
                        break;
                }
            }
            
            echo "});\n";
            echo "```\n";
        }
        
        // Check if tracking_histories table is needed
        if (($this->results['tracking_histories'] ?? '') == '❌ NEED TO CREATE') {
            echo "\n3. Create tracking_histories table - see SQL above\n";
        }
    }
}

// Execute
try {
    $debugger = new SystemStructureDebugger();
    $debugger->debug();
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>