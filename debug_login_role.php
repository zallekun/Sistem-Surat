<?php
// debug_login_role.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG USER ROLES ===\n\n";

// Get all users with role kaprodi or staff_prodi
$users = \App\Models\User::with('role', 'prodi', 'jabatan')->get();

echo "1. Users with their roles:\n";
echo str_pad("ID", 5) . str_pad("Name", 25) . str_pad("Email", 30) . str_pad("Role", 15) . str_pad("Prodi", 20) . "\n";
echo str_repeat("-", 95) . "\n";

foreach ($users as $user) {
    $roleName = $user->role->name ?? 'N/A';
    if (in_array($roleName, ['kaprodi', 'staff_prodi', 'staff_fakultas'])) {
        echo str_pad($user->id, 5) . 
             str_pad($user->nama ?? $user->name ?? 'N/A', 25) . 
             str_pad($user->email, 30) . 
             str_pad($roleName, 15) . 
             str_pad($user->prodi->nama_prodi ?? 'N/A', 20) . "\n";
    }
}

echo "\n2. Check hasRole method:\n";
// Test hasRole method for a kaprodi user
$kaprodiUser = \App\Models\User::whereHas('role', function($q) {
    $q->where('name', 'kaprodi');
})->first();

if ($kaprodiUser) {
    echo "Testing user: " . ($kaprodiUser->nama ?? $kaprodiUser->name) . " (ID: {$kaprodiUser->id})\n";
    echo "Role name from DB: " . ($kaprodiUser->role->name ?? 'N/A') . "\n";
    
    // Test hasRole method
    if (method_exists($kaprodiUser, 'hasRole')) {
        echo "hasRole('kaprodi'): " . ($kaprodiUser->hasRole('kaprodi') ? 'true' : 'false') . "\n";
        echo "hasRole('Kaprodi'): " . ($kaprodiUser->hasRole('Kaprodi') ? 'true' : 'false') . "\n";
        echo "hasRole('staff_prodi'): " . ($kaprodiUser->hasRole('staff_prodi') ? 'true' : 'false') . "\n";
    } else {
        echo "⚠️ hasRole method not found in User model\n";
    }
}

echo "\n3. Check User model hasRole implementation:\n";
$userModelFile = app_path('Models/User.php');
if (file_exists($userModelFile)) {
    $content = file_get_contents($userModelFile);
    
    // Check for hasRole method
    if (preg_match('/public\s+function\s+hasRole.*?\{.*?\}/s', $content, $match)) {
        echo "hasRole method found:\n";
        echo substr($match[0], 0, 500) . "...\n";
    } else {
        echo "⚠️ hasRole method not found in User model\n";
        echo "Checking for Spatie Laravel Permission trait...\n";
        if (str_contains($content, 'HasRoles')) {
            echo "✓ Uses Spatie\\Permission\\Traits\\HasRoles\n";
        } else {
            echo "⚠️ Does not use Spatie HasRoles trait\n";
        }
    }
}

echo "\n4. Test route access:\n";
// Simulate accessing the route
$testRoutes = [
    '/staff/surat' => 'staff_prodi',
    '/kaprodi/surat/approval' => 'kaprodi'
];

foreach ($testRoutes as $route => $expectedRole) {
    echo "Route: $route (expects role: $expectedRole)\n";
    
    // Find a user with this role
    $testUser = \App\Models\User::whereHas('role', function($q) use ($expectedRole) {
        $q->where('name', $expectedRole);
    })->first();
    
    if ($testUser) {
        echo "  Test user: " . ($testUser->nama ?? $testUser->name) . "\n";
        echo "  User role: " . ($testUser->role->name ?? 'N/A') . "\n";
        
        // Test if they would pass the role check
        if (method_exists($testUser, 'hasRole')) {
            $passesCheck = $testUser->hasRole($expectedRole);
            echo "  Would pass role check: " . ($passesCheck ? '✓ Yes' : '✗ No') . "\n";
            
            // Also test with capitalized version
            $capitalizedRole = ucfirst($expectedRole);
            $passesCapCheck = $testUser->hasRole($capitalizedRole);
            if (!$passesCheck && $passesCapCheck) {
                echo "  ⚠️ Role check is case-sensitive! Use '$capitalizedRole' instead of '$expectedRole'\n";
            }
        }
    } else {
        echo "  No user found with role: $expectedRole\n";
    }
}

echo "\n5. Database role names check:\n";
$roles = \Illuminate\Support\Facades\DB::table('roles')->get();
foreach ($roles as $role) {
    echo "  ID: {$role->id}, Name: '{$role->name}' (exact case)\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Check if role names in code match exactly with database (case-sensitive)\n";
echo "2. Ensure User model has proper hasRole implementation\n";
echo "3. Verify the logged-in user has the correct role assigned\n";

echo "\n=== END DEBUG ===\n";