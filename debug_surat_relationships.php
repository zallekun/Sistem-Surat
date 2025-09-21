<?php
/**
 * Debug Surat Model Relationships
 * Run: php debug_surat_relationships.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG SURAT MODEL RELATIONSHIPS ===\n\n";

if (!file_exists('artisan')) {
    die("ERROR: Run from Laravel root!\n");
}

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// 1. CHECK SURAT MODEL EXISTS
echo "1. CHECKING SURAT MODEL\n";
echo "========================\n";

if (!class_exists('App\Models\Surat')) {
    echo "ERROR: Surat model not found!\n";
    exit(1);
}

$surat = new App\Models\Surat();
echo "✓ Surat model found\n";

// 2. CHECK SURAT TABLE STRUCTURE
echo "\n2. SURAT TABLE STRUCTURE\n";
echo "=========================\n";

try {
    $columns = Schema::getColumnListing('surats');
    echo "Table 'surats' columns:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }
} catch (Exception $e) {
    echo "ERROR: Cannot access surats table: " . $e->getMessage() . "\n";
}

// 3. CHECK AVAILABLE RELATIONSHIPS IN SURAT MODEL
echo "\n3. CHECKING SURAT MODEL RELATIONSHIPS\n";
echo "======================================\n";

$reflection = new ReflectionClass('App\Models\Surat');
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

$relationships = [];
$potentialRelationships = [];

foreach ($methods as $method) {
    $name = $method->getName();
    
    // Skip magic methods and common methods
    if (in_array($name, ['__construct', '__call', '__destruct', '__get', '__set', 'save', 'delete', 'update', 'create', 'find', 'where', 'orderBy', 'paginate', 'get', 'first', 'toArray', 'toJson'])) {
        continue;
    }
    
    // Skip getters/setters
    if (strpos($name, 'get') === 0 && strpos($name, 'Attribute') !== false) {
        continue;
    }
    
    if (strpos($name, 'set') === 0 && strpos($name, 'Attribute') !== false) {
        continue;
    }
    
    // Check if method could be a relationship
    try {
        // Try to get method source code to check for relationship keywords
        $methodSource = $method->getDocComment() . "\n";
        if ($method->getFileName()) {
            $file = file($method->getFileName());
            $startLine = $method->getStartLine() - 1;
            $endLine = $method->getEndLine();
            $methodCode = implode('', array_slice($file, $startLine, $endLine - $startLine));
            
            if (preg_match('/(belongsTo|hasMany|hasOne|belongsToMany|morphMany|morphTo|morphOne)/', $methodCode)) {
                $relationships[] = $name;
            } else if (!in_array($name, ['toArray', 'toJson', 'jsonSerialize', 'offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset', 'getIterator', 'count'])) {
                $potentialRelationships[] = $name;
            }
        }
    } catch (Exception $e) {
        // If we can't analyze, just add to potential
        if (!in_array($name, ['toArray', 'toJson', 'jsonSerialize', 'offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset', 'getIterator', 'count'])) {
            $potentialRelationships[] = $name;
        }
    }
}

echo "Confirmed relationships (contains Eloquent relationship methods):\n";
foreach ($relationships as $rel) {
    echo "  ✓ $rel()\n";
}

echo "\nPotential relationships (public methods that could be relationships):\n";
foreach ($potentialRelationships as $rel) {
    echo "  ? $rel()\n";
}

// 4. CHECK SPECIFIC RELATIONSHIP THAT FAILED
echo "\n4. CHECKING SPECIFIC FAILED RELATIONSHIP\n";
echo "==========================================\n";

$failedRelationships = ['pengirim', 'tujuan_jabatan', 'status', 'fakultas', 'prodi'];

foreach ($failedRelationships as $relName) {
    if (method_exists($surat, $relName)) {
        echo "✓ $relName() method EXISTS\n";
        
        // Try to call the relationship to see what it returns
        try {
            $relationship = $surat->$relName();
            $relationshipType = get_class($relationship);
            echo "  Type: $relationshipType\n";
            
            if (method_exists($relationship, 'getForeignKeyName')) {
                echo "  Foreign Key: " . $relationship->getForeignKeyName() . "\n";
            }
            if (method_exists($relationship, 'getOwnerKeyName')) {
                echo "  Owner Key: " . $relationship->getOwnerKeyName() . "\n";
            }
            if (method_exists($relationship, 'getRelated')) {
                $related = $relationship->getRelated();
                echo "  Related Model: " . get_class($related) . "\n";
                echo "  Related Table: " . $related->getTable() . "\n";
            }
        } catch (Exception $e) {
            echo "  ERROR calling relationship: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ $relName() method NOT FOUND\n";
    }
}

// 5. SUGGEST COLUMN-BASED RELATIONSHIPS
echo "\n5. SUGGESTED RELATIONSHIPS BASED ON COLUMNS\n";
echo "=============================================\n";

if (isset($columns)) {
    $foreignKeyColumns = array_filter($columns, function($col) {
        return strpos($col, '_id') !== false;
    });
    
    echo "Foreign key columns found:\n";
    foreach ($foreignKeyColumns as $fk) {
        $relationshipName = str_replace('_id', '', $fk);
        echo "  $fk -> suggested relationship: $relationshipName()\n";
        
        // Check if suggested relationship exists
        if (method_exists($surat, $relationshipName)) {
            echo "    ✓ $relationshipName() method exists\n";
        } else {
            echo "    ✗ $relationshipName() method missing\n";
            
            // Suggest relationship code
            $modelName = ucfirst(str_replace('_', '', $relationshipName));
            echo "    Suggested code:\n";
            echo "    public function $relationshipName() {\n";
            echo "        return \$this->belongsTo(App\\Models\\$modelName::class, '$fk');\n";
            echo "    }\n";
        }
    }
}

// 6. CHECK RELATED MODELS
echo "\n6. CHECKING RELATED MODELS\n";
echo "===========================\n";

$possibleModels = ['User', 'Jabatan', 'Status', 'Fakultas', 'Prodi', 'StatusSurat'];

foreach ($possibleModels as $model) {
    $modelClass = "App\\Models\\$model";
    if (class_exists($modelClass)) {
        echo "✓ $model model exists\n";
        
        // Check table name
        try {
            $instance = new $modelClass();
            echo "  Table: " . $instance->getTable() . "\n";
        } catch (Exception $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ $model model not found\n";
    }
}

// 7. GENERATE FIXED QUERY
echo "\n7. SUGGESTED FIX FOR STAFFINDEX METHOD\n";
echo "=======================================\n";

echo "Replace the problematic with() call with existing relationships:\n\n";

$workingRelationships = [];
foreach ($failedRelationships as $rel) {
    if (method_exists($surat, $rel)) {
        $workingRelationships[] = $rel;
    }
}

if (count($workingRelationships) > 0) {
    echo "Working relationships: " . implode(', ', $workingRelationships) . "\n";
    echo "\nFixed code:\n";
    echo "\$query = Surat::with(['" . implode("', '", $workingRelationships) . "']);\n";
} else {
    echo "No working relationships found. Use simple query:\n";
    echo "\$query = Surat::query();\n";
}

echo "\nAlternatively, remove with() entirely for now:\n";
echo "\$query = Surat::orderBy('created_at', 'desc');\n";

// 8. CREATE QUICK FIX FILE
echo "\n8. CREATING QUICK FIX\n";
echo "=====================\n";

$quickFix = '<?php
// Quick fix for staffIndex method
// Replace the problematic line in SuratController.php

// FROM:
// $query = Surat::with([\'pengirim\', \'tujuan_jabatan\', \'status\', \'fakultas\', \'prodi\']);

// TO (temporary fix - remove relationships):
// $query = Surat::query();

// OR if some relationships work:
';

if (count($workingRelationships) > 0) {
    $quickFix .= "// \$query = Surat::with(['" . implode("', '", $workingRelationships) . "']);\n";
}

$quickFix .= '
// After fixing relationships in the model, you can add them back
';

file_put_contents('quick_fix_staffindex.txt', $quickFix);
echo "Saved quick fix to: quick_fix_staffindex.txt\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 50) . "\n";

echo "PROBLEM: Relationship 'pengirim' not found in Surat model\n\n";

echo "IMMEDIATE FIX:\n";
echo "1. Open app/Http/Controllers/SuratController.php\n";
echo "2. In staffIndex() method, change:\n";
echo "   FROM: \$query = Surat::with(['pengirim', 'tujuan_jabatan', 'status', 'fakultas', 'prodi']);\n";
echo "   TO:   \$query = Surat::query();\n";
echo "3. Test the page - it should work without relationships\n";
echo "4. Then add proper relationships to Surat model\n\n";

echo "LONG-term FIX:\n";
echo "Add missing relationship methods to app/Models/Surat.php\n";

echo "\n=== DEBUG COMPLETED ===\n";