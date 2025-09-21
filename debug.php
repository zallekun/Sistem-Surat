<?php

/**
 * Laravel Website Structure Debug Script
 * Simpan sebagai: debug.php di folder root project Laravel
 * Akses via: http://localhost:8000/debug.php
 */

// Autoload Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Laravel Website Structure Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        pre { background: #f8f9fa; padding: 10px; overflow-x: auto; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; font-weight: bold; }
        .highlight { background: #fff3cd !important; }
        .code { font-family: monospace; background: #f1f1f1; padding: 2px 4px; }
        h1 { color: #2c3e50; }
        h2 { color: #34495e; margin-bottom: 10px; }
    </style>
</head>
<body>";

echo "<h1>🔍 Laravel Website Structure Debug</h1>";

// 1. Basic Laravel Info
echo "<div class='section info'>
    <h2>📋 Basic Laravel Info</h2>
    <p><strong>Laravel Version:</strong> " . app()->version() . "</p>
    <p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>
    <p><strong>Environment:</strong> " . app()->environment() . "</p>
    <p><strong>Debug Mode:</strong> " . (config('app.debug') ? 'ON' : 'OFF') . "</p>
    <p><strong>Database Connection:</strong> " . config('database.default') . "</p>
</div>";

// 2. Database Structure Analysis
echo "<div class='section info'>
    <h2>🗄️ Database Structure</h2>";

try {
    $connection = DB::connection();
    echo "<p class='success'>✅ Database connected successfully</p>";
    
    // Get all tables
    $tables = DB::select('SHOW TABLES');
    $tableKey = 'Tables_in_' . env('DB_DATABASE');
    
    echo "<h3>📊 Available Tables:</h3>";
    echo "<table>";
    echo "<tr><th>Table Name</th><th>Row Count</th><th>Columns</th></tr>";
    
    foreach ($tables as $table) {
        $tableName = $table->$tableKey;
        try {
            $count = DB::table($tableName)->count();
            $columns = DB::select("DESCRIBE $tableName");
            $columnNames = array_column($columns, 'Field');
            
            $highlight = (strpos($tableName, 'pengajuan') !== false || 
                         strpos($tableName, 'jenis_surat') !== false || 
                         strpos($tableName, 'prodi') !== false) ? 'class="highlight"' : '';
            
            echo "<tr $highlight>";
            echo "<td><strong>$tableName</strong></td>";
            echo "<td>$count</td>";
            echo "<td>" . implode(', ', $columnNames) . "</td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr><td>$tableName</td><td colspan='2' class='error'>Error: " . $e->getMessage() . "</td></tr>";
        }
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 3. Models Check
echo "<div class='section info'>
    <h2>📦 Models Analysis</h2>";

$models = ['PengajuanSurat', 'Prodi', 'JenisSurat', 'User'];

foreach ($models as $model) {
    try {
        $modelClass = "\\App\\Models\\$model";
        if (class_exists($modelClass)) {
            $instance = new $modelClass();
            $table = $instance->getTable();
            $fillable = $instance->getFillable();
            $count = $modelClass::count();
            
            echo "<div class='success'>";
            echo "<h4>✅ $model Model</h4>";
            echo "<p><strong>Table:</strong> $table</p>";
            echo "<p><strong>Records:</strong> $count</p>";
            echo "<p><strong>Fillable Fields:</strong> " . implode(', ', $fillable) . "</p>";
            echo "</div>";
        } else {
            echo "<p class='error'>❌ <strong>$model</strong>: Model not found</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ <strong>$model</strong>: " . $e->getMessage() . "</p>";
    }
}

echo "</div>";

// 4. Routes Analysis (Focus on Pengajuan)
echo "<div class='section info'>
    <h2>🛣️ Routes Analysis</h2>";

try {
    $routes = Route::getRoutes();
    $pengajuanRoutes = [];
    
    foreach ($routes as $route) {
        $name = $route->getName();
        $uri = $route->uri();
        $methods = implode(', ', $route->methods());
        $action = $route->getActionName();
        
        if (strpos($name, 'pengajuan') !== false || strpos($uri, 'pengajuan') !== false || 
            strpos($name, 'surat') !== false || strpos($uri, 'surat') !== false) {
            $pengajuanRoutes[] = [
                'name' => $name ?: '-',
                'uri' => $uri,
                'methods' => $methods,
                'action' => $action
            ];
        }
    }
    
    echo "<h3>🎯 Pengajuan/Surat Related Routes:</h3>";
    echo "<table>";
    echo "<tr><th>Route Name</th><th>URI</th><th>Methods</th><th>Controller</th></tr>";
    
    foreach ($pengajuanRoutes as $route) {
        echo "<tr class='highlight'>";
        echo "<td><strong>{$route['name']}</strong></td>";
        echo "<td>{$route['uri']}</td>";
        echo "<td>{$route['methods']}</td>";
        echo "<td>{$route['action']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error loading routes: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 5. Controllers Analysis
echo "<div class='section info'>
    <h2>🎮 Controllers Analysis</h2>";

$controllers = ['PublicSuratController', 'DashboardController'];

foreach ($controllers as $controllerName) {
    try {
        $controllerClass = "\\App\\Http\\Controllers\\$controllerName";
        if (class_exists($controllerClass)) {
            $methods = get_class_methods($controllerClass);
            $publicMethods = array_filter($methods, function($method) {
                return !in_array($method, ['__construct', '__call', '__callStatic']);
            });
            
            echo "<div class='success'>";
            echo "<h4>✅ $controllerName</h4>";
            echo "<p><strong>Available Methods:</strong> " . implode(', ', $publicMethods) . "</p>";
            echo "</div>";
        } else {
            echo "<p class='error'>❌ <strong>$controllerName</strong>: Controller not found</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ <strong>$controllerName</strong>: " . $e->getMessage() . "</p>";
    }
}

echo "</div>";

// 6. Views Structure
echo "<div class='section info'>
    <h2>👁️ Views Structure</h2>";

$viewPaths = [
    'layouts/app.blade.php',
    'layouts/public.blade.php',
    'dashboard/index.blade.php',
    'public/pengajuan/form.blade.php',
    'public/pengajuan/list.blade.php',
    'public/pengajuan/show.blade.php'
];

echo "<table>";
echo "<tr><th>View Path</th><th>Status</th><th>Size</th></tr>";

foreach ($viewPaths as $viewPath) {
    $fullPath = resource_path("views/$viewPath");
    if (file_exists($fullPath)) {
        $size = round(filesize($fullPath) / 1024, 2) . ' KB';
        echo "<tr class='success'><td>$viewPath</td><td>✅ EXISTS</td><td>$size</td></tr>";
    } else {
        echo "<tr class='error'><td>$viewPath</td><td>❌ NOT FOUND</td><td>-</td></tr>";
    }
}

echo "</table></div>";

// 7. Sample Data Analysis
echo "<div class='section info'>
    <h2>📊 Sample Data Analysis</h2>";

try {
    // Jenis Surat Data
    if (class_exists('\\App\\Models\\JenisSurat')) {
        $jenisSurat = \App\Models\JenisSurat::all();
        echo "<h4>📋 Jenis Surat Data:</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nama Jenis</th><th>Kode Surat</th></tr>";
        foreach ($jenisSurat as $jenis) {
            echo "<tr>";
            echo "<td>{$jenis->id}</td>";
            echo "<td>{$jenis->nama_jenis}</td>";
            echo "<td>{$jenis->kode_surat}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Program Studi Data
    if (class_exists('\\App\\Models\\Prodi')) {
        $prodis = \App\Models\Prodi::all();
        echo "<h4>🎓 Program Studi Data:</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nama Prodi</th><th>Kode Prodi</th></tr>";
        foreach ($prodis as $prodi) {
            echo "<tr>";
            echo "<td>{$prodi->id}</td>";
            echo "<td>{$prodi->nama_prodi}</td>";
            echo "<td>{$prodi->kode_prodi}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Recent Pengajuan Data
    if (class_exists('\\App\\Models\\PengajuanSurat')) {
        $pengajuan = \App\Models\PengajuanSurat::latest()->take(5)->get();
        echo "<h4>📝 Recent Pengajuan (Last 5):</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Jenis Surat</th><th>Status</th><th>Created</th></tr>";
        foreach ($pengajuan as $p) {
            echo "<tr>";
            echo "<td>{$p->id}</td>";
            echo "<td>{$p->nim}</td>";
            echo "<td>{$p->nama_mahasiswa}</td>";
            echo "<td>{$p->jenis_surat_id}</td>";
            echo "<td>{$p->status}</td>";
            echo "<td>{$p->created_at}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error loading sample data: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 8. Current Form Field Analysis
echo "<div class='section warning'>
    <h2>📝 Current Form Analysis</h2>";

try {
    $formPath = resource_path('views/public/pengajuan/form.blade.php');
    if (file_exists($formPath)) {
        $formContent = file_get_contents($formPath);
        
        // Extract form fields
        preg_match_all('/name=["\']([^"\']+)["\']/', $formContent, $matches);
        $formFields = array_unique($matches[1]);
        
        echo "<p class='success'>✅ Form file exists</p>";
        echo "<h4>🔍 Current Form Fields:</h4>";
        echo "<div class='code'>" . implode(', ', $formFields) . "</div>";
        
        // Check for dynamic behavior
        if (strpos($formContent, 'additional-fields') !== false) {
            echo "<p class='success'>✅ Dynamic fields container detected</p>";
        } else {
            echo "<p class='warning'>⚠️ No dynamic fields container found</p>";
        }
        
        // Check for JavaScript
        if (strpos($formContent, 'jenis_surat_id') !== false && strpos($formContent, 'addEventListener') !== false) {
            echo "<p class='success'>✅ JavaScript form handling detected</p>";
        } else {
            echo "<p class='warning'>⚠️ Limited JavaScript form handling</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Form file not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error analyzing form: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 9. Recommendations
echo "<div class='section info'>
    <h2>💡 Recommendations for Dynamic Form</h2>
    <h4>📋 To implement Surat Mahasiswa Aktif form:</h4>
    <ol>
        <li><strong>Database:</strong> Add columns to <code>pengajuan_surat</code> table for orang tua data</li>
        <li><strong>Model:</strong> Update <code>PengajuanSurat</code> fillable fields</li>
        <li><strong>Form:</strong> Add dynamic fields based on jenis_surat selection</li>
        <li><strong>Validation:</strong> Add conditional validation rules in controller</li>
        <li><strong>PDF Template:</strong> Create surat template for PDF generation</li>
    </ol>
    
    <h4>🔧 Immediate Next Steps:</h4>
    <ul>
        <li>Verify current <code>pengajuan_surat</code> table structure</li>
        <li>Check which fields are missing for biodata orang tua</li>
        <li>Update form.blade.php with dynamic sections</li>
        <li>Test current form submission</li>
    </ul>
</div>";

echo "</body></html>";
?>