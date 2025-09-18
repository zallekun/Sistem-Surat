<?php
// debug_staff_controller.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG STAFF SURAT CONTROLLER ===\n\n";

// 1. Check if controller exists
$controllerClasses = [
    'App\Http\Controllers\StaffController',
    'App\Http\Controllers\StaffSuratController', 
    'App\Http\Controllers\Staff\SuratController',
    'App\Http\Controllers\Kaprodi\SuratController',
    'App\Http\Controllers\KaprodiController',
    'App\Http\Controllers\SuratController'
];

echo "1. Checking for Staff/Kaprodi Controllers:\n";
foreach ($controllerClasses as $class) {
    if (class_exists($class)) {
        echo "   ✓ Found: $class\n";
        
        // Check methods
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        echo "     Methods: ";
        foreach ($methods as $method) {
            if ($method->class === $class) {
                echo $method->name . " ";
            }
        }
        echo "\n";
        
        // Check if index method exists and what it returns
        if ($reflection->hasMethod('index')) {
            $sourceFile = $reflection->getFileName();
            $source = file_get_contents($sourceFile);
            
            // Check for view being returned
            if (preg_match('/return\s+view\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches)) {
                echo "     Returns view: " . $matches[1] . "\n";
                
                // Check what variables are passed to view
                if (preg_match('/compact\s*\(\s*([^\)]+)\)/', $source, $varMatches)) {
                    echo "     Variables passed: " . $varMatches[1] . "\n";
                }
            }
        }
    }
}

// 2. Check routes for staff/kaprodi surat
echo "\n2. Routes for Staff/Kaprodi:\n";
$routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(function ($route) {
    $uri = $route->uri();
    return str_contains($uri, 'staff') || str_contains($uri, 'kaprodi') || str_contains($uri, 'surat');
});

foreach ($routes as $route) {
    if (str_contains($route->uri(), 'surat')) {
        $methods = implode('|', $route->methods());
        $uri = $route->uri();
        $action = $route->getActionName();
        $name = $route->getName();
        echo "   $methods $uri => $action" . ($name ? " (name: $name)" : "") . "\n";
    }
}

// 3. Check view files
echo "\n3. View Files Check:\n";
$viewsToCheck = [
    'staff.surat.index',
    'kaprodi.surat.index',
    'surat.index',
    'staff.index'
];

foreach ($viewsToCheck as $view) {
    if (view()->exists($view)) {
        echo "   ✓ $view exists\n";
        
        // Check what the view expects
        $viewPath = resource_path('views/' . str_replace('.', '/', $view) . '.blade.php');
        if (file_exists($viewPath)) {
            $content = file_get_contents($viewPath);
            
            // Check for $surats usage
            if (str_contains($content, '$surats')) {
                echo "     ⚠️ Uses \$surats variable\n";
                
                // Count occurrences
                preg_match_all('/\$surats/', $content, $matches);
                echo "     Found " . count($matches[0]) . " references to \$surats\n";
            }
            
            // Check for other variables
            preg_match_all('/\$([a-zA-Z_]+)/', $content, $varMatches);
            $uniqueVars = array_unique($varMatches[1]);
            $filteredVars = array_filter($uniqueVars, function($var) {
                return !in_array($var, ['loop', 'errors', 'app', 'config', 'request', 'session']);
            });
            if (!empty($filteredVars)) {
                echo "     Other variables used: " . implode(', ', $filteredVars) . "\n";
            }
        }
    }
}

// 4. Check middleware for kaprodi role
echo "\n4. Middleware Check:\n";
$middleware = app('router')->getMiddleware();
if (isset($middleware['role'])) {
    echo "   ✓ Role middleware exists\n";
    
    // Check CheckRole middleware
    if (class_exists('App\Http\Middleware\CheckRole')) {
        $checkRole = new ReflectionClass('App\Http\Middleware\CheckRole');
        $source = file_get_contents($checkRole->getFileName());
        
        // Check which field it uses for role checking
        if (str_contains($source, '->role->name')) {
            echo "   ✓ CheckRole uses: user->role->name\n";
        } elseif (str_contains($source, '->role->nama_role')) {
            echo "   ⚠️ CheckRole uses: user->role->nama_role (but DB has 'name' field)\n";
        }
    }
}

// 5. Database check
echo "\n5. Database Roles Check:\n";
try {
    $roles = \Illuminate\Support\Facades\DB::table('roles')->get();
    foreach ($roles as $role) {
        if (str_contains(strtolower($role->name ?? ''), 'kaprodi') || str_contains(strtolower($role->name ?? ''), 'staff')) {
            echo "   - ID: {$role->id}, Name: {$role->name}\n";
        }
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// 6. Find the actual controller handling the route
echo "\n6. Route Resolution Test:\n";
$testPaths = ['/staff/surat', '/kaprodi/surat'];
foreach ($testPaths as $path) {
    try {
        $request = \Illuminate\Http\Request::create($path, 'GET');
        $route = \Illuminate\Support\Facades\Route::getRoutes()->match($request);
        if ($route) {
            echo "   $path resolves to: " . $route->getActionName() . "\n";
        }
    } catch (Exception $e) {
        echo "   $path: Route not found\n";
    }
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "The error 'Undefined variable \$surats' means the controller is not passing the variable.\n";
echo "Check the controller's index() method and ensure it includes:\n";
echo "  \$surats = Surat::...->paginate(10);\n";
echo "  return view('staff.surat.index', compact('surats'));\n";

echo "\n=== END DEBUG ===\n";