<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseDebugERD extends Command
{
    protected $signature = 'debug:erd';
    protected $description = 'Generate database structure for ERD creation';

    public function handle()
    {
        $this->info('=== DATABASE STRUCTURE FOR ERD ===');
        
        // Get all tables
        $tables = $this->getAllTables();
        
        $dbStructure = [
            'tables' => [],
            'relationships' => [],
            'summary' => []
        ];
        
        foreach ($tables as $table) {
            $this->info("\n--- Processing Table: $table ---");
            
            // Get table structure
            $columns = $this->getTableColumns($table);
            $indexes = $this->getTableIndexes($table);
            $foreignKeys = $this->getForeignKeys($table);
            
            $dbStructure['tables'][$table] = [
                'columns' => $columns,
                'indexes' => $indexes,
                'foreign_keys' => $foreignKeys
            ];
            
            // Display table info
            $this->displayTableInfo($table, $columns, $indexes, $foreignKeys);
        }
        
        // Get all foreign key relationships
        $this->info("\n=== FOREIGN KEY RELATIONSHIPS ===");
        $relationships = $this->getAllRelationships();
        $dbStructure['relationships'] = $relationships;
        
        foreach ($relationships as $rel) {
            $this->info("$rel->table_name.$rel->column_name -> $rel->referenced_table_name.$rel->referenced_column_name");
        }
        
        // Generate summary
        $this->info("\n=== DATABASE SUMMARY ===");
        $summary = $this->generateSummary($dbStructure);
        
        // Save to file
        $this->saveToFile($dbStructure);
        
        $this->info("\nDebug completed! Check storage/app/database_structure.json for detailed output.");
    }
    
    private function getAllTables()
    {
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];
        
        foreach ($tables as $table) {
            $tableNames[] = array_values((array)$table)[0];
        }
        
        return $tableNames;
    }
    
    private function getTableColumns($tableName)
    {
        $columns = DB::select("DESCRIBE $tableName");
        $columnData = [];
        
        foreach ($columns as $column) {
            $columnData[] = [
                'name' => $column->Field,
                'type' => $column->Type,
                'null' => $column->Null,
                'key' => $column->Key,
                'default' => $column->Default,
                'extra' => $column->Extra
            ];
        }
        
        return $columnData;
    }
    
    private function getTableIndexes($tableName)
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM $tableName");
            $indexData = [];
            
            foreach ($indexes as $index) {
                $indexData[] = [
                    'name' => $index->Key_name,
                    'column' => $index->Column_name,
                    'unique' => !$index->Non_unique,
                    'type' => $index->Index_type
                ];
            }
            
            return $indexData;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function getForeignKeys($tableName)
    {
        $foreignKeys = DB::select("
            SELECT 
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME,
                CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '$tableName'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        return $foreignKeys;
    }
    
    private function getAllRelationships()
    {
        return DB::select("
            SELECT 
                TABLE_NAME as table_name,
                COLUMN_NAME as column_name,
                REFERENCED_TABLE_NAME as referenced_table_name,
                REFERENCED_COLUMN_NAME as referenced_column_name,
                CONSTRAINT_NAME as constraint_name
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY TABLE_NAME, COLUMN_NAME
        ");
    }
    
    private function displayTableInfo($tableName, $columns, $indexes, $foreignKeys)
    {
        // Display columns
        $this->info("Columns:");
        foreach ($columns as $col) {
            $keyInfo = $col['key'] ? " [{$col['key']}]" : '';
            $nullInfo = $col['null'] === 'NO' ? ' NOT NULL' : ' NULL';
            $this->line("  - {$col['name']}: {$col['type']}{$nullInfo}{$keyInfo}");
        }
        
        // Display foreign keys
        if (!empty($foreignKeys)) {
            $this->info("Foreign Keys:");
            foreach ($foreignKeys as $fk) {
                $this->line("  - {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}");
            }
        }
    }
    
    private function generateSummary($dbStructure)
    {
        $totalTables = count($dbStructure['tables']);
        $totalRelationships = count($dbStructure['relationships']);
        
        $this->info("Total Tables: $totalTables");
        $this->info("Total Relationships: $totalRelationships");
        
        // Core entities
        $coreEntities = [
            'users', 'pengajuan_surats', 'surat', 'jenis_surat', 
            'prodi', 'fakultas', 'status_surat'
        ];
        
        $this->info("\nCore Entities Found:");
        foreach ($coreEntities as $entity) {
            if (isset($dbStructure['tables'][$entity])) {
                $columnCount = count($dbStructure['tables'][$entity]['columns']);
                $this->line("  ✓ $entity ($columnCount columns)");
            } else {
                $this->error("  ✗ $entity (NOT FOUND)");
            }
        }
        
        return [
            'total_tables' => $totalTables,
            'total_relationships' => $totalRelationships,
            'core_entities_found' => array_intersect($coreEntities, array_keys($dbStructure['tables']))
        ];
    }
    
    private function saveToFile($dbStructure)
    {
        $jsonOutput = json_encode($dbStructure, JSON_PRETTY_PRINT);
        
        // Save to storage
        $path = storage_path('app/database_structure.json');
        file_put_contents($path, $jsonOutput);
        
        // Also create a simplified ERD format
        $erdFormat = $this->convertToERDFormat($dbStructure);
        $erdPath = storage_path('app/database_erd_format.txt');
        file_put_contents($erdPath, $erdFormat);
        
        $this->info("Files saved:");
        $this->line("  - $path (detailed JSON)");
        $this->line("  - $erdPath (ERD format)");
    }
    
    private function convertToERDFormat($dbStructure)
    {
        $erdText = "DATABASE ERD FORMAT\n";
        $erdText .= "==================\n\n";
        
        // Tables with their columns
        foreach ($dbStructure['tables'] as $tableName => $tableData) {
            $erdText .= "TABLE: $tableName\n";
            $erdText .= str_repeat("-", strlen($tableName) + 7) . "\n";
            
            foreach ($tableData['columns'] as $column) {
                $pk = $column['key'] === 'PRI' ? ' [PK]' : '';
                $fk = $column['key'] === 'MUL' ? ' [FK]' : '';
                $type = $column['type'];
                $null = $column['null'] === 'NO' ? ' NOT NULL' : '';
                
                $erdText .= "  {$column['name']}: $type$null$pk$fk\n";
            }
            
            $erdText .= "\n";
        }
        
        // Relationships
        $erdText .= "RELATIONSHIPS\n";
        $erdText .= "=============\n";
        
        foreach ($dbStructure['relationships'] as $rel) {
            $erdText .= "{$rel->table_name}.{$rel->column_name} -> {$rel->referenced_table_name}.{$rel->referenced_column_name}\n";
        }
        
        return $erdText;
    }
}