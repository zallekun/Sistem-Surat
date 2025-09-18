<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

echo "=== LARAVEL ROUTE DEBUG ===\n";

try {
    // 1. Bootstrap Laravel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();
    echo "1. Laravel Bootstrap: OK\n";

    // 2. Test database connection
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "2. Database Connection: OK\n";
    } catch (Exception $e) {
        echo "2. Database Connection: FAILED - " . $e->getMessage() . "\n";
        exit;
    }

    // 3. List all routes with fakultas
    echo "3. Routes Check:\n";
    $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(function ($route) {
        return str_contains($route->uri(), 'fakultas');
    });
    
    echo "   Found " . $routes->count() . " fakultas routes:\n";
    foreach ($routes as $route) {
        $methods = implode('|', $route->methods());
        $uri = $route->uri();
        $action = $route->getActionName();
        echo "   - $methods $uri => $action\n";
    }

    // 4. Test specific route matching
    echo "\n4. Route Matching Test:\n";
    $testRoutes = [
        'GET /fakultas/surat',
        'GET /fakultas/surat/1', 
        'GET /fakultas/1'
    ];

    foreach ($testRoutes as $testRoute) {
        [$method, $path] = explode(' ', $testRoute, 2);
        
        try {
            $request = \Illuminate\Http\Request::create($path, $method);
            $route = \Illuminate\Support\Facades\Route::getRoutes()->match($request);
            $action = $route->getActionName();
            $name = $route->getName();
            echo "   $testRoute => $action" . ($name ? " (Route: $name)" : "") . "\n";
        } catch (Exception $e) {
            echo "   $testRoute => ERROR: " . $e->getMessage() . "\n";
        }
    }

    // 5. Determine the correct role column name
    echo "\n5. Role Table Structure Check:\n";
    try {
        $roleColumns = collect(\Illuminate\Support\Facades\DB::select('DESCRIBE roles'))
            ->pluck('Field')->toArray();
        echo "   Role table columns: " . implode(', ', $roleColumns) . "\n";
        
        // Determine which column to use for role name
        $roleNameColumn = null;
        if (in_array('nama_role', $roleColumns)) {
            $roleNameColumn = 'nama_role';
        } elseif (in_array('name', $roleColumns)) {
            $roleNameColumn = 'name';
        } else {
            echo "   ❌ No recognizable role name column found\n";
        }
        
        if ($roleNameColumn) {
            echo "   ✅ Using role column: $roleNameColumn\n";
            
            // Show all roles
            $roles = \Illuminate\Support\Facades\DB::table('roles')->get();
            echo "   Available roles:\n";
            foreach ($roles as $role) {
                $name = $role->{$roleNameColumn} ?? 'unnamed';
                echo "   - ID: {$role->id}, Name: {$name}\n";
            }
            
            // Try to find staff_fakultas role
            $staffRole = \Illuminate\Support\Facades\DB::table('roles')
                ->where($roleNameColumn, 'staff_fakultas')
                ->first();
                
            if ($staffRole) {
                echo "   ✅ Found staff_fakultas role with ID: {$staffRole->id}\n";
                
                // Get users with this role
                $users = \Illuminate\Support\Facades\DB::table('users')
                    ->where('role_id', $staffRole->id)
                    ->whereNull('deleted_at')
                    ->limit(3)
                    ->get();
                    
                echo "   Found " . $users->count() . " staff_fakultas users:\n";
                foreach ($users as $user) {
                    $name = $user->nama ?? $user->name ?? 'unnamed';
                    echo "   - User: {$name} (ID: {$user->id})\n";
                }
            } else {
                echo "   ❌ staff_fakultas role not found. Available role names:\n";
                foreach ($roles as $role) {
                    echo "     - " . ($role->{$roleNameColumn} ?? 'unnamed') . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "   Error checking roles: " . $e->getMessage() . "\n";
    }

    // 6. Test middleware
    echo "\n6. Middleware Test:\n";
    try {
        $middleware = app('router')->getMiddleware();
        
        if (isset($middleware['check.role'])) {
            echo "   ✅ 'check.role' middleware: FOUND\n";
        } else {
            echo "   ❌ 'check.role' middleware: NOT FOUND\n";
        }
        
        if (isset($middleware['role'])) {
            echo "   ✅ 'role' middleware: FOUND\n";
        } else {
            echo "   ❌ 'role' middleware: NOT FOUND\n";
        }
        
        echo "   Available middleware: " . implode(', ', array_keys($middleware)) . "\n";
    } catch (Exception $e) {
        echo "   Middleware check failed: " . $e->getMessage() . "\n";
    }

    // 7. Test view existence
    echo "\n7. View Files Check:\n";
    $viewPaths = [
        'fakultas.surat.show',
        'fakultas.surat.index', 
        'fakultas.show'
    ];
    
    foreach ($viewPaths as $view) {
        if (view()->exists($view)) {
            echo "   ✅ $view: EXISTS\n";
        } else {
            echo "   ❌ $view: NOT FOUND\n";
        }
    }

    // 8. Check FakultasStaffController
    echo "\n8. Controller Check:\n";
    if (class_exists('App\Http\Controllers\FakultasStaffController')) {
        echo "   ✅ FakultasStaffController: EXISTS\n";
        
        $controller = new ReflectionClass('App\Http\Controllers\FakultasStaffController');
        $methods = $controller->getMethods(ReflectionMethod::IS_PUBLIC);
        echo "   Methods: ";
        foreach ($methods as $method) {
            if ($method->class === 'App\Http\Controllers\FakultasStaffController') {
                echo $method->name . ' ';
            }
        }
        echo "\n";
        
        // Check middleware in constructor
        try {
            $source = file_get_contents($controller->getFileName());
            if (strpos($source, "middleware('role:") !== false) {
                echo "   ✅ Controller uses 'role:' middleware\n";
            } elseif (strpos($source, "middleware('check.role:") !== false) {
                echo "   ✅ Controller uses 'check.role:' middleware\n";
            } else {
                echo "   ❌ Controller middleware not detected\n";
            }
        } catch (Exception $e) {
            echo "   Could not check controller middleware\n";
        }
    } else {
        echo "   ❌ FakultasStaffController: NOT FOUND\n";
    }

    // 9. Test User-Role relationship
    echo "\n9. User-Role Relationship Test:\n";
    try {
        if (class_exists('App\Models\User')) {
            echo "   ✅ User model: EXISTS\n";
            
            // Get a user with role
            $userWithRole = \App\Models\User::with('role')->first();
            if ($userWithRole) {
                echo "   ✅ Found user with ID: {$userWithRole->id}\n";
                if ($userWithRole->role) {
                    // Try both possible column names
                    $roleName = $userWithRole->role->nama_role ?? $userWithRole->role->name ?? 'unnamed';
                    echo "   ✅ User role: {$roleName}\n";
                } else {
                    echo "   ❌ User has no role assigned\n";
                }
            } else {
                echo "   ❌ No users found\n";
            }
        } else {
            echo "   ❌ User model: NOT FOUND\n";
        }
    } catch (Exception $e) {
        echo "   User-Role relationship test failed: " . $e->getMessage() . "\n";
    }

    // 10. Final recommendations
    echo "\n10. Recommendations:\n";
    
    // Check which middleware should be used
    $middleware = app('router')->getMiddleware();
    if (isset($middleware['role']) && !isset($middleware['check.role'])) {
        echo "   ➤ Use 'role:staff_fakultas' middleware in controller\n";
    } elseif (isset($middleware['check.role'])) {
        echo "   ➤ Use 'check.role:staff_fakultas' middleware in controller\n";
    }
    
    // Check if views are ready
    $viewsReady = view()->exists('fakultas.surat.show') && view()->exists('fakultas.surat.index');
    if ($viewsReady) {
        echo "   ✅ Views are ready\n";
    } else {
        echo "   ❌ Some views are missing\n";
    }
    
    echo "   ➤ Try accessing: http://your-domain/fakultas/surat/1\n";

} catch (Exception $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETED ===\n";