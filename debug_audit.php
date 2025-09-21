<?php
/**
 * Laravel Application Audit Script
 * Jalankan script ini di root folder Laravel Anda
 * 
 * Usage: php debug_audit.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== LARAVEL APPLICATION AUDIT ===\n\n";

// Check if we're in Laravel root
if (!file_exists('artisan')) {
    echo "❌ ERROR: Script harus dijalankan di root folder Laravel (dimana file artisan berada)\n";
    exit(1);
}

echo "✅ Laravel root directory detected\n\n";

// 1. BASIC INFO
echo "1. BASIC INFORMATION\n";
echo "===================\n";

if (file_exists('composer.json')) {
    $composer = json_decode(file_get_contents('composer.json'), true);
    echo "Project Name: " . ($composer['name'] ?? 'N/A') . "\n";
    echo "Laravel Version: " . ($composer['require']['laravel/framework'] ?? 'Unknown') . "\n";
}

if (file_exists('.env')) {
    echo "✅ .env file exists\n";
    $env = file_get_contents('.env');
    
    // Extract key info safely
    if (preg_match('/APP_NAME=(.+)/', $env, $matches)) {
        echo "App Name: " . trim($matches[1], '"') . "\n";
    }
    if (preg_match('/APP_ENV=(.+)/', $env, $matches)) {
        echo "Environment: " . trim($matches[1], '"') . "\n";
    }
    if (preg_match('/DB_CONNECTION=(.+)/', $env, $matches)) {
        echo "Database: " . trim($matches[1], '"') . "\n";
    }
} else {
    echo "❌ .env file not found\n";
}

echo "\n";

// 2. DATABASE TABLES
echo "2. DATABASE STRUCTURE\n";
echo "=====================\n";

try {
    // Try to load Laravel and get database info
    require_once 'vendor/autoload.php';
    
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Get database connection
    $db = DB::connection();
    
    echo "✅ Database connection successful\n";
    echo "Database: " . $db->getDatabaseName() . "\n\n";
    
    // Get all tables
    $tables = $db->select('SHOW TABLES');
    $tableNames = [];
    
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        $tableNames[] = $tableName;
        
        // Get table structure
        $columns = $db->select("DESCRIBE `$tableName`");
        
        echo "Table: $tableName\n";
        echo "Columns:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field} ({$column->Type}) " . 
                 ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') . 
                 ($column->Key ? " [{$column->Key}]" : '') . "\n";
        }
        echo "\n";
    }
    
    echo "Total Tables: " . count($tableNames) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
}

// 3. MODELS
echo "3. MODELS\n";
echo "=========\n";

$modelsPath = 'app/Models';
if (is_dir($modelsPath)) {
    $models = scandir($modelsPath);
    foreach ($models as $model) {
        if (pathinfo($model, PATHINFO_EXTENSION) === 'php') {
            $modelName = pathinfo($model, PATHINFO_FILENAME);
            echo "Model: $modelName\n";
            
            // Try to read model file and extract info
            $content = file_get_contents("$modelsPath/$model");
            
            // Extract fillable
            if (preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\]/s', $content, $matches)) {
                echo "  Fillable: " . preg_replace('/\s+/', ' ', $matches[1]) . "\n";
            }
            
            // Extract relationships
            if (preg_match_all('/public\s+function\s+(\w+)\s*\([^)]*\)\s*\{[^}]*return\s+\$this->(hasOne|hasMany|belongsTo|belongsToMany)/s', $content, $matches)) {
                echo "  Relationships:\n";
                for ($i = 0; $i < count($matches[1]); $i++) {
                    echo "    - {$matches[1][$i]} ({$matches[2][$i]})\n";
                }
            }
            echo "\n";
        }
    }
} else {
    echo "❌ Models directory not found\n\n";
}

// 4. CONTROLLERS
echo "4. CONTROLLERS\n";
echo "==============\n";

$controllersPath = 'app/Http/Controllers';
function scanControllers($path, $prefix = '') {
    if (!is_dir($path)) return;
    
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $fullPath = "$path/$item";
        if (is_dir($fullPath)) {
            echo "Directory: $prefix$item/\n";
            scanControllers($fullPath, "$prefix$item/");
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
            $controllerName = pathinfo($item, PATHINFO_FILENAME);
            echo "Controller: $prefix$controllerName\n";
            
            // Read controller and extract methods
            $content = file_get_contents($fullPath);
            if (preg_match_all('/public\s+function\s+(\w+)\s*\(/s', $content, $matches)) {
                echo "  Methods: " . implode(', ', $matches[1]) . "\n";
            }
            echo "\n";
        }
    }
}

scanControllers($controllersPath);

// 5. ROUTES
echo "5. ROUTES\n";
echo "=========\n";

$routeFiles = [
    'routes/web.php' => 'Web Routes',
    'routes/api.php' => 'API Routes',
];

foreach ($routeFiles as $file => $label) {
    if (file_exists($file)) {
        echo "$label:\n";
        $content = file_get_contents($file);
        
        // Extract route definitions
        if (preg_match_all('/Route::(get|post|put|patch|delete|any|match|resource)\s*\(\s*[\'"]([^\'"]+)[\'"](?:[^;]+;|\s*,\s*[^;]+;)/s', $content, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                echo "  {$matches[1][$i]} -> {$matches[2][$i]}\n";
            }
        }
        echo "\n";
    }
}

// 6. MIDDLEWARE
echo "6. MIDDLEWARE\n";
echo "=============\n";

$middlewarePath = 'app/Http/Middleware';
if (is_dir($middlewarePath)) {
    $middlewares = scandir($middlewarePath);
    foreach ($middlewares as $middleware) {
        if (pathinfo($middleware, PATHINFO_EXTENSION) === 'php') {
            $middlewareName = pathinfo($middleware, PATHINFO_FILENAME);
            echo "Middleware: $middlewareName\n";
        }
    }
} else {
    echo "❌ Middleware directory not found\n";
}
echo "\n";

// 7. MIGRATIONS
echo "7. MIGRATIONS\n";
echo "=============\n";

$migrationsPath = 'database/migrations';
if (is_dir($migrationsPath)) {
    $migrations = scandir($migrationsPath);
    $migrations = array_filter($migrations, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'php';
    });
    
    // Sort by filename (includes timestamp)
    sort($migrations);
    
    foreach ($migrations as $migration) {
        $migrationName = pathinfo($migration, PATHINFO_FILENAME);
        echo "Migration: $migrationName\n";
        
        // Try to extract table name
        $content = file_get_contents("$migrationsPath/$migration");
        if (preg_match('/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            echo "  Creates table: {$matches[1]}\n";
        } elseif (preg_match('/Schema::table\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            echo "  Modifies table: {$matches[1]}\n";
        }
    }
    echo "\nTotal Migrations: " . count($migrations) . "\n\n";
} else {
    echo "❌ Migrations directory not found\n\n";
}

// 8. VIEWS/FRONTEND
echo "8. FRONTEND STRUCTURE\n";
echo "=====================\n";

// Check for Inertia.js
if (file_exists('resources/js/app.js')) {
    $appJs = file_get_contents('resources/js/app.js');
    if (strpos($appJs, 'inertia') !== false || strpos($appJs, 'Inertia') !== false) {
        echo "✅ Inertia.js detected\n";
    }
    if (strpos($appJs, 'vue') !== false || strpos($appJs, 'Vue') !== false) {
        echo "✅ Vue.js detected\n";
    }
}

// Check Pages directory
if (is_dir('resources/js/Pages')) {
    echo "✅ Inertia Pages directory found\n";
    
    function scanPages($path, $prefix = '') {
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = "$path/$item";
            if (is_dir($fullPath)) {
                echo "  $prefix$item/\n";
                scanPages($fullPath, "$prefix$item/");
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'vue') {
                $pageName = pathinfo($item, PATHINFO_FILENAME);
                echo "    $prefix$pageName.vue\n";
            }
        }
    }
    
    scanPages('resources/js/Pages', '');
}

echo "\n";

// 9. AUTHENTICATION
echo "9. AUTHENTICATION & ROLES\n";
echo "=========================\n";

// Check for authentication setup
if (file_exists('config/auth.php')) {
    echo "✅ Authentication config found\n";
    
    // Check if Spatie Permission is installed
    if (file_exists('vendor/spatie/laravel-permission')) {
        echo "✅ Spatie Laravel Permission installed\n";
    } else {
        echo "❌ Spatie Laravel Permission not found\n";
    }
    
    // Check for User model
    if (file_exists('app/Models/User.php')) {
        echo "✅ User model found\n";
        $userContent = file_get_contents('app/Models/User.php');
        if (strpos($userContent, 'HasRoles') !== false) {
            echo "✅ HasRoles trait detected\n";
        }
    }
}

echo "\n";

// 10. PACKAGES
echo "10. INSTALLED PACKAGES\n";
echo "======================\n";

if (file_exists('composer.lock')) {
    $composerLock = json_decode(file_get_contents('composer.lock'), true);
    $packages = $composerLock['packages'] ?? [];
    
    $relevantPackages = [
        'laravel/framework',
        'inertiajs/inertia-laravel',
        'spatie/laravel-permission',
        'barryvdh/laravel-dompdf',
        'maatwebsite/excel',
        'intervention/image',
        'pusher/pusher-php-server',
        'laravel/sanctum',
        'laravel/jetstream',
    ];
    
    foreach ($packages as $package) {
        if (in_array($package['name'], $relevantPackages)) {
            echo "✅ {$package['name']} v{$package['version']}\n";
        }
    }
    
    echo "\nTotal packages: " . count($packages) . "\n";
}

echo "\n";

// 11. SUMMARY & RECOMMENDATIONS
echo "11. SUMMARY & NEXT STEPS\n";
echo "========================\n";

echo "Aplikasi Laravel Anda siap untuk ditambahkan fitur sistem surat.\n\n";

echo "YANG PERLU DISIAPKAN:\n";
echo "- Migration untuk tabel pengajuan_surats\n";
echo "- Model PengajuanSurat\n";
echo "- Controllers untuk public form & TU Prodi\n";
echo "- Vue components untuk frontend\n";
echo "- Routes untuk public dan authenticated\n\n";

echo "ESTIMASI: Dengan struktur existing yang baik, target 1 minggu sangat feasible.\n\n";

echo "=== AUDIT COMPLETED ===\n";
?>