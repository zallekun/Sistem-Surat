<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyzeDatabase extends Command
{
    protected $signature = 'db:analyze {--output=console : Output format (console|json|file)}';
    
    protected $description = 'Analyze database structure for normalization';

    public function handle()
    {
        $this->info('🔍 Analyzing Database Structure...');
        $this->newLine();

        $tables = $this->getAllTables();
        $analysis = [];

        foreach ($tables as $table) {
            $this->info("📋 Analyzing table: {$table}");
            
            $tableAnalysis = [
                'name' => $table,
                'columns' => $this->getTableColumns($table),
                'indexes' => $this->getTableIndexes($table),
                'foreign_keys' => $this->getForeignKeys($table),
                'row_count' => DB::table($table)->count(),
            ];
            
            $analysis[$table] = $tableAnalysis;
            
            // Display column info
            $this->table(
                ['Column', 'Type', 'Nullable', 'Default', 'Key'],
                collect($tableAnalysis['columns'])->map(function($col) {
                    return [
                        $col['name'],
                        $col['type'],
                        $col['nullable'] ? 'YES' : 'NO',
                        $col['default'] ?? 'NULL',
                        $col['key'] ?? '-'
                    ];
                })
            );
            
            // Display foreign keys
            if (!empty($tableAnalysis['foreign_keys'])) {
                $this->warn('  Foreign Keys:');
                foreach ($tableAnalysis['foreign_keys'] as $fk) {
                    $this->line("    - {$fk['column']} → {$fk['referenced_table']}.{$fk['referenced_column']}");
                }
            }
            
            $this->line("  Row Count: {$tableAnalysis['row_count']}");
            $this->newLine();
        }

        // Check for redundancy patterns
        $this->info('🔎 Checking for Potential Redundancy Issues...');
        $this->newLine();
        $this->detectRedundancy($analysis);

        // Output options
        $output = $this->option('output');
        if ($output === 'json') {
            $this->saveAsJson($analysis);
        } elseif ($output === 'file') {
            $this->saveAsFile($analysis);
        }

        $this->info('✅ Analysis Complete!');
        return 0;
    }

    private function getAllTables(): array
    {
        $database = DB::getDatabaseName();
        
        $tables = DB::select("
            SELECT TABLE_NAME 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ", [$database]);

        return array_map(fn($table) => $table->TABLE_NAME, $tables);
    }

    private function getTableColumns(string $table): array
    {
        $columns = DB::select("
            SELECT 
                COLUMN_NAME as name,
                COLUMN_TYPE as type,
                IS_NULLABLE as nullable,
                COLUMN_DEFAULT as `default`,
                COLUMN_KEY as `key`,
                EXTRA as extra
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [DB::getDatabaseName(), $table]);

        return array_map(function($col) {
            return [
                'name' => $col->name,
                'type' => $col->type,
                'nullable' => $col->nullable === 'YES',
                'default' => $col->default,
                'key' => $col->key,
                'extra' => $col->extra,
            ];
        }, $columns);
    }

    private function getTableIndexes(string $table): array
    {
        $indexes = DB::select("
            SELECT 
                INDEX_NAME as name,
                COLUMN_NAME as column_name,
                NON_UNIQUE as non_unique,
                INDEX_TYPE as type
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY INDEX_NAME, SEQ_IN_INDEX
        ", [DB::getDatabaseName(), $table]);

        return array_map(function($idx) {
            return [
                'name' => $idx->name,
                'column' => $idx->column_name,
                'unique' => !$idx->non_unique,
                'type' => $idx->type,
            ];
        }, $indexes);
    }

    private function getForeignKeys(string $table): array
    {
        $fks = DB::select("
            SELECT 
                COLUMN_NAME as column_name,
                REFERENCED_TABLE_NAME as referenced_table,
                REFERENCED_COLUMN_NAME as referenced_column,
                CONSTRAINT_NAME as constraint_name
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [DB::getDatabaseName(), $table]);

        return array_map(function($fk) {
            return [
                'column' => $fk->column_name,
                'referenced_table' => $fk->referenced_table,
                'referenced_column' => $fk->referenced_column,
                'constraint' => $fk->constraint_name,
            ];
        }, $fks);
    }

    private function detectRedundancy(array $analysis): void
    {
        $issues = [];

        foreach ($analysis as $tableName => $tableInfo) {
            // Check for non-atomic columns (comma-separated values)
            foreach ($tableInfo['columns'] as $column) {
                if (in_array($column['name'], ['tags', 'categories', 'items']) && 
                    str_contains($column['type'], 'text')) {
                    $issues[] = "⚠️  Table '{$tableName}' column '{$column['name']}' might contain non-atomic data (violates 1NF)";
                }
            }

            // Check for repeated column patterns (like address1, address2, address3)
            $columnNames = array_column($tableInfo['columns'], 'name');
            $patterns = [];
            foreach ($columnNames as $colName) {
                if (preg_match('/^(.+?)(\d+)$/', $colName, $matches)) {
                    $base = $matches[1];
                    $patterns[$base] = ($patterns[$base] ?? 0) + 1;
                }
            }
            foreach ($patterns as $base => $count) {
                if ($count > 1) {
                    $issues[] = "⚠️  Table '{$tableName}' has repeated columns '{$base}1, {$base}2...' (violates 1NF - consider separate table)";
                }
            }

            // Check for columns that might be derived data
            $derivedPatterns = ['total_', 'sum_', 'count_', 'avg_'];
            foreach ($tableInfo['columns'] as $column) {
                foreach ($derivedPatterns as $pattern) {
                    if (str_starts_with($column['name'], $pattern)) {
                        $issues[] = "⚠️  Table '{$tableName}' column '{$column['name']}' might be derived data (consider calculating on-the-fly)";
                    }
                }
            }

            // Check for JSON columns that might need normalization
            foreach ($tableInfo['columns'] as $column) {
                if (str_contains($column['type'], 'json') && 
                    !in_array($column['name'], ['metadata', 'settings', 'options'])) {
                    $issues[] = "💡 Table '{$tableName}' column '{$column['name']}' is JSON - verify if it needs separate table";
                }
            }

            // Check for tables without foreign keys (potential orphaned data)
            if (empty($tableInfo['foreign_keys']) && 
                !in_array($tableName, ['migrations', 'failed_jobs', 'password_resets', 'personal_access_tokens'])) {
                $issues[] = "💡 Table '{$tableName}' has no foreign keys - verify relationships";
            }
        }

        // Check for missing junction tables
        $this->checkManyToManyRelationships($analysis, $issues);

        // Display issues
        if (!empty($issues)) {
            $this->warn('Found potential normalization issues:');
            foreach ($issues as $issue) {
                $this->line("  {$issue}");
            }
        } else {
            $this->info('✅ No obvious normalization issues detected!');
        }
    }

    private function checkManyToManyRelationships(array $analysis, array &$issues): void
    {
        // Logic to detect potential many-to-many relationships that need junction tables
        $tablePairs = [];
        
        foreach ($analysis as $tableName => $tableInfo) {
            foreach ($tableInfo['foreign_keys'] as $fk) {
                $pair = [$tableName, $fk['referenced_table']];
                sort($pair);
                $key = implode('_', $pair);
                
                if (!isset($tablePairs[$key])) {
                    $tablePairs[$key] = [];
                }
                $tablePairs[$key][] = $tableName;
            }
        }
    }

    private function saveAsJson(array $analysis): void
    {
        $filename = storage_path('app/database_analysis_' . date('Y-m-d_His') . '.json');
        file_put_contents($filename, json_encode($analysis, JSON_PRETTY_PRINT));
        $this->info("📄 JSON saved to: {$filename}");
    }

    private function saveAsFile(array $analysis): void
    {
        $filename = storage_path('app/database_analysis_' . date('Y-m-d_His') . '.txt');
        
        $content = "DATABASE STRUCTURE ANALYSIS\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= str_repeat('=', 80) . "\n\n";
        
        foreach ($analysis as $tableName => $tableInfo) {
            $content .= "TABLE: {$tableName}\n";
            $content .= "Rows: {$tableInfo['row_count']}\n";
            $content .= str_repeat('-', 80) . "\n";
            
            $content .= "\nCOLUMNS:\n";
            foreach ($tableInfo['columns'] as $col) {
                $content .= sprintf("  %-30s %-20s %s\n", 
                    $col['name'], 
                    $col['type'], 
                    $col['nullable'] ? 'NULL' : 'NOT NULL'
                );
            }
            
            if (!empty($tableInfo['foreign_keys'])) {
                $content .= "\nFOREIGN KEYS:\n";
                foreach ($tableInfo['foreign_keys'] as $fk) {
                    $content .= "  {$fk['column']} → {$fk['referenced_table']}.{$fk['referenced_column']}\n";
                }
            }
            
            $content .= "\n" . str_repeat('=', 80) . "\n\n";
        }
        
        file_put_contents($filename, $content);
        $this->info("📄 Analysis saved to: {$filename}");
    }
}