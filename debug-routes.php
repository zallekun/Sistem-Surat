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

    // 5. Test users with role_id (FIXED)
    echo "\n5. User Authentication Test:\n";
    try {
        // First, check if roles table exists
        $rolesExist = \Illuminate\Support\Facades\Schema::hasTable('roles');
        if (!$rolesExist) {
            echo "   Roles table does not exist\n";
        } else {
            echo "   Roles table: EXISTS\n";
            
            // Get role_id for staff_fakultas
            $role = \Illuminate\Support\Facades\DB::table('roles')
                ->where('nama_role', 'staff_fakultas')
                ->first();
                
            if (!$role) {
                echo "   Role 'staff_fakultas' not found in roles table\n";
                
                // Show available roles
                $roles = \Illuminate\Support\Facades\DB::table('roles')->get();
                echo "   Available roles:\n";
                foreach ($roles as $r) {
                    $name = $r->nama_role ?? $r->name ?? 'unnamed';
                    echo "   - ID: {$r->id}, Name: {$name}\n";
                }
            } else {
                echo "   Found staff_fakultas role with ID: {$role->id}\n";
                
                // Test users with this role_id
                $users = \Illuminate\Support\Facades\DB::table('users')
                    ->where('role_id', $role->id)
                    ->whereNull('deleted_at')
                    ->limit(3)
                    ->get();
                    
                echo "   Found " . $users->count() . " staff_fakultas users\n";
                
                foreach ($users as $user) {
                    $name = $user->nama ?? $user->name ?? 'unnamed';
                    echo "   - User: {$name} (ID: {$user->id})\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "   FATAL ERROR: " . $e->getMessage() . "\n";
        
        // Show users table structure
        echo "   \nUsers table structure:\n";
        try {
            $columns = \Illuminate\Support\Facades\DB::select('DESCRIBE users');
            foreach ($columns as $column) {
                echo "   - {$column->Field}: {$column->Type}\n";
            }
        } catch (Exception $e2) {
            echo "   Could not describe users table: " . $e2->getMessage() . "\n";
        }
    }

    // 6. Test middleware and auth
    echo "\n6. Middleware Test:\n";
    try {
        // Check if role middleware exists
        $middleware = app('router')->getMiddleware();
        if (isset($middleware['check.role'])) {
            echo "   'check.role' middleware: FOUND\n";
        } else {
            echo "   'check.role' middleware: NOT FOUND\n";
        }
        
        if (isset($middleware['role'])) {
            echo "   'role' middleware: FOUND\n";
        } else {
            echo "   'role' middleware: NOT FOUND\n";
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
    } else {
        echo "   ❌ FakultasStaffController: NOT FOUND\n";
    }

    // 9. Test Role Model
    echo "\n9. Role Model Test:\n";
    try {
        if (class_exists('App\Models\Role')) {
            echo "   ✅ Role model: EXISTS\n";
            $roleCount = \App\Models\Role::count();
            echo "   Total roles: $roleCount\n";
            
            $staffRole = \App\Models\Role::where('nama_role', 'staff_fakultas')->first();
            if ($staffRole) {
                echo "   ✅ staff_fakultas role found with ID: {$staffRole->id}\n";
            } else {
                echo "   ❌ staff_fakultas role not found\n";
            }
        } else {
            echo "   ❌ Role model: NOT FOUND\n";
        }
    } catch (Exception $e) {
        echo "   Role model test failed: " . $e->getMessage() . "\n";
    }

    // 10. Test User Model with Role Relationship
    echo "\n10. User-Role Relationship Test:\n";
    try {
        if (class_exists('App\Models\User')) {
            echo "   ✅ User model: EXISTS\n";
            
            // Get a user with role
            $userWithRole = \App\Models\User::with('role')->first();
            if ($userWithRole) {
                echo "   ✅ Found user with ID: {$userWithRole->id}\n";
                if ($userWithRole->role) {
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

} catch (Exception $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETED ===\n";