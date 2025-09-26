<?php
/**
 * Comprehensive Debug Script for Tracking System
 * Place this file in Laravel root directory
 * Run: php debug_tracking.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n" . str_repeat("=", 80) . "\n";
echo "TRACKING SYSTEM COMPREHENSIVE DEBUG\n";
echo str_repeat("=", 80) . "\n\n";

// 1. CHECK ENVIRONMENT
echo "1. ENVIRONMENT CHECK\n";
echo str_repeat("-", 40) . "\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Environment: " . app()->environment() . "\n";
echo "Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF') . "\n\n";

// 2. CHECK DATABASE CONNECTION
echo "2. DATABASE CHECK\n";
echo str_repeat("-", 40) . "\n";
try {
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n";
    
    // Check tables
    $tables = ['pengajuan_surat', 'tracking_history', 'users', 'prodi', 'jenis_surat'];
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "✓ Table '$table' exists (Records: $count)\n";
        } else {
            echo "✗ Table '$table' NOT FOUND\n";
        }
    }
    
    // Check for test data
    $testData = DB::table('pengajuan_surat')
        ->select('id', 'tracking_token', 'nim', 'nama_mahasiswa', 'status', 'created_at')
        ->limit(3)
        ->get();
    
    echo "\nSample Pengajuan Data:\n";
    if ($testData->count() > 0) {
        foreach ($testData as $data) {
            echo "  - Token: {$data->tracking_token} | NIM: {$data->nim} | Status: {$data->status}\n";
        }
    } else {
        echo "  ✗ No pengajuan data found\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. CHECK CONTROLLERS
echo "3. CONTROLLER CHECK\n";
echo str_repeat("-", 40) . "\n";

$controllers = [
    'App\Http\Controllers\PublicSuratController',
    'App\Http\Controllers\TrackingController',
    'App\Http\Controllers\SuratController',
];

foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "✓ Controller exists: $controller\n";
        
        // Check methods for PublicSuratController
        if ($controller == 'App\Http\Controllers\PublicSuratController') {
            $methods = ['trackingIndex', 'trackingShow', 'trackingSearch', 'trackingApi'];
            foreach ($methods as $method) {
                if (method_exists($controller, $method)) {
                    echo "  ✓ Method '$method' exists\n";
                } else {
                    echo "  ✗ Method '$method' NOT FOUND\n";
                }
            }
        }
    } else {
        echo "✗ Controller NOT FOUND: $controller\n";
    }
}
echo "\n";

// 4. CHECK ROUTES
echo "4. ROUTE CHECK\n";
echo str_repeat("-", 40) . "\n";

$routes = Route::getRoutes();
$trackingRoutes = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'tracking') !== false) {
        $action = $route->getActionName();
        $methods = implode('|', $route->methods());
        $name = $route->getName() ?? 'unnamed';
        
        $trackingRoutes[] = [
            'uri' => $uri,
            'methods' => $methods,
            'action' => $action,
            'name' => $name
        ];
    }
}

if (count($trackingRoutes) > 0) {
    echo "Found " . count($trackingRoutes) . " tracking routes:\n\n";
    foreach ($trackingRoutes as $route) {
        echo "  [{$route['methods']}] {$route['uri']}\n";
        echo "    Action: {$route['action']}\n";
        echo "    Name: {$route['name']}\n\n";
    }
} else {
    echo "✗ No tracking routes found!\n";
}

// 5. CHECK VIEWS
echo "5. VIEW CHECK\n";
echo str_repeat("-", 40) . "\n";

$viewPaths = [
    'public.tracking.index',
    'public.tracking.show',
    'layouts.public',
];

foreach ($viewPaths as $viewPath) {
    $fullPath = resource_path('views/' . str_replace('.', '/', $viewPath) . '.blade.php');
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        echo "✓ View exists: $viewPath (Size: $size bytes)\n";
        
        // Check for common issues in tracking index
        if ($viewPath == 'public.tracking.index') {
            $content = file_get_contents($fullPath);
            
            // Check for form
            if (strpos($content, '<form') !== false) {
                echo "  ✓ Form tag found\n";
            } else {
                echo "  ✗ No form tag found\n";
            }
            
            // Check for route
            if (strpos($content, 'route(\'tracking.search\')') !== false) {
                echo "  ✓ Route 'tracking.search' referenced\n";
            } else {
                echo "  ! Warning: 'tracking.search' route not found in view\n";
            }
            
            // Check for CSRF
            if (strpos($content, '@csrf') !== false) {
                echo "  ✓ CSRF token present\n";
            } else {
                echo "  ✗ No CSRF token found\n";
            }
        }
    } else {
        echo "✗ View NOT FOUND: $viewPath\n";
        echo "  Expected at: $fullPath\n";
    }
}
echo "\n";

// 6. CHECK MODELS
echo "6. MODEL CHECK\n";
echo str_repeat("-", 40) . "\n";

$models = [
    'App\Models\PengajuanSurat',
    'App\Models\TrackingHistory',
    'App\Models\SuratGenerated',
];

foreach ($models as $model) {
    if (class_exists($model)) {
        echo "✓ Model exists: $model\n";
        
        // Check fillable for PengajuanSurat
        if ($model == 'App\Models\PengajuanSurat') {
            $instance = new $model();
            $fillable = $instance->getFillable();
            echo "  Fillable fields: " . count($fillable) . "\n";
            if (in_array('tracking_token', $fillable)) {
                echo "  ✓ 'tracking_token' is fillable\n";
            } else {
                echo "  ✗ 'tracking_token' is NOT fillable\n";
            }
        }
    } else {
        echo "✗ Model NOT FOUND: $model\n";
    }
}
echo "\n";

// 7. SIMULATE REQUEST
echo "7. REQUEST SIMULATION\n";
echo str_repeat("-", 40) . "\n";

try {
    // Test route resolution
    $url = '/tracking';
    $request = Request::create($url, 'GET');
    $route = Route::getRoutes()->match($request);
    
    if ($route) {
        echo "✓ Route '/tracking' resolves to:\n";
        echo "  Action: " . $route->getActionName() . "\n";
        echo "  Controller: " . $route->getControllerClass() . "\n";
        echo "  Method: " . $route->getActionMethod() . "\n";
        
        // Check if controller method is callable
        $controller = $route->getControllerClass();
        $method = $route->getActionMethod();
        
        if (class_exists($controller) && method_exists($controller, $method)) {
            echo "  ✓ Controller method is callable\n";
            
            // Try to get method parameters
            $reflection = new ReflectionMethod($controller, $method);
            $params = $reflection->getParameters();
            echo "  Method parameters: " . count($params) . "\n";
            
            if (count($params) > 0) {
                foreach ($params as $param) {
                    echo "    - \${$param->getName()}";
                    if ($param->hasType()) {
                        echo " (" . $param->getType() . ")";
                    }
                    if ($param->isDefaultValueAvailable()) {
                        echo " = " . json_encode($param->getDefaultValue());
                    }
                    echo "\n";
                }
            }
        } else {
            echo "  ✗ Controller method NOT callable\n";
        }
    } else {
        echo "✗ Route '/tracking' NOT resolved\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error simulating request: " . $e->getMessage() . "\n";
}
echo "\n";

// 8. CHECK POTENTIAL ISSUES
echo "8. POTENTIAL ISSUES CHECK\n";
echo str_repeat("-", 40) . "\n";

// Check for duplicate route names
$routeNames = [];
foreach (Route::getRoutes() as $route) {
    $name = $route->getName();
    if ($name) {
        if (!isset($routeNames[$name])) {
            $routeNames[$name] = [];
        }
        $routeNames[$name][] = $route->uri();
    }
}

$duplicates = array_filter($routeNames, function($uris) {
    return count($uris) > 1;
});

if (count($duplicates) > 0) {
    echo "✗ Found duplicate route names:\n";
    foreach ($duplicates as $name => $uris) {
        echo "  '$name' used by: " . implode(', ', $uris) . "\n";
    }
} else {
    echo "✓ No duplicate route names found\n";
}

// Check middleware
$trackingMiddleware = [];
foreach ($routes as $route) {
    if (strpos($route->uri(), 'tracking') !== false) {
        $middleware = $route->middleware();
        if (count($middleware) > 0) {
            $trackingMiddleware[$route->uri()] = $middleware;
        }
    }
}

if (count($trackingMiddleware) > 0) {
    echo "\nMiddleware on tracking routes:\n";
    foreach ($trackingMiddleware as $uri => $middleware) {
        echo "  $uri: " . implode(', ', $middleware) . "\n";
    }
}

// Check for cached routes
$cachedRoutesFile = base_path('bootstrap/cache/routes-v7.php');
if (file_exists($cachedRoutesFile)) {
    $modified = date('Y-m-d H:i:s', filemtime($cachedRoutesFile));
    echo "\n⚠ Routes are CACHED (Modified: $modified)\n";
    echo "  Run: php artisan route:clear\n";
} else {
    echo "\n✓ Routes are not cached\n";
}

// Check for cached config
$cachedConfigFile = base_path('bootstrap/cache/config.php');
if (file_exists($cachedConfigFile)) {
    $modified = date('Y-m-d H:i:s', filemtime($cachedConfigFile));
    echo "⚠ Config is CACHED (Modified: $modified)\n";
    echo "  Run: php artisan config:clear\n";
} else {
    echo "✓ Config is not cached\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "DEBUG COMPLETE\n";
echo str_repeat("=", 80) . "\n";

// 9. RECOMMENDATIONS
echo "\nRECOMMENDATIONS:\n";
echo str_repeat("-", 40) . "\n";

$issues = [];

// Check if TrackingController exists
if (class_exists('App\Http\Controllers\TrackingController')) {
    $issues[] = "Remove or rename TrackingController.php to avoid conflicts";
}

// Check if tracking index view exists
if (!file_exists(resource_path('views/public/tracking/index.blade.php'))) {
    $issues[] = "Create view file: resources/views/public/tracking/index.blade.php";
}

// Check if routes are cached
if (file_exists(base_path('bootstrap/cache/routes-v7.php'))) {
    $issues[] = "Clear route cache: php artisan route:clear";
}

if (count($issues) > 0) {
    echo "Found " . count($issues) . " issue(s) to fix:\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". $issue\n";
    }
} else {
    echo "✓ No critical issues found\n";
}

echo "\n";