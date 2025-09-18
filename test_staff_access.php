<?php
// test_staff_access.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== TEST STAFF ACCESS ===\n\n";

// Test users
$testUsers = [
    'kaprodi.if@sistemsurat.com' => 'kaprodi',
    'staff.if@sistemsurat.com' => 'staff_prodi'
];

foreach ($testUsers as $email => $expectedRole) {
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        echo "User not found: $email\n";
        continue;
    }
    
    echo "Testing user: {$user->nama} ({$email})\n";
    echo "Expected role: $expectedRole\n";
    echo "Actual role: " . ($user->role->name ?? 'NO ROLE') . "\n";
    
    // Test hasRole with different cases
    $testRoles = [$expectedRole, ucfirst($expectedRole), strtoupper($expectedRole)];
    foreach ($testRoles as $testRole) {
        $result = $user->hasRole($testRole);
        echo "  hasRole('$testRole'): " . ($result ? '✓' : '✗') . "\n";
    }
    
    // Test if they can access staffIndex
    $canAccessStaff = $user->hasRole('staff_prodi') || $user->hasRole('kaprodi');
    echo "  Can access /staff/surat: " . ($canAccessStaff ? '✓ YES' : '✗ NO') . "\n";
    
    // Test what surat they would see
    if ($user->prodi_id) {
        $suratCount = \App\Models\Surat::where('prodi_id', $user->prodi_id)->count();
        echo "  Surat in their prodi: $suratCount\n";
        
        if ($user->hasRole('staff_prodi')) {
            $ownSurat = \App\Models\Surat::where('created_by', $user->id)->count();
            echo "  Surat they created: $ownSurat\n";
        }
    }
    
    echo "\n";
}

echo "=== TEST CONTROLLER METHOD ===\n";

// Test if the controller method exists
if (class_exists('App\Http\Controllers\SuratController')) {
    $controller = new \App\Http\Controllers\SuratController();
    
    if (method_exists($controller, 'staffIndex')) {
        echo "✓ staffIndex method exists\n";
        
        // Check what the method returns by inspecting the code
        $reflection = new ReflectionMethod($controller, 'staffIndex');
        $filename = $reflection->getFileName();
        $start_line = $reflection->getStartLine();
        $end_line = $reflection->getEndLine();
        $length = $end_line - $start_line;
        
        $source = file($filename);
        $method_code = implode("", array_slice($source, $start_line, $length));
        
        // Check what variables are passed to view
        if (preg_match('/compact\s*\(\s*([^\)]+)\)/', $method_code, $matches)) {
            $variables = str_replace(["'", '"', ' '], '', $matches[1]);
            $varArray = explode(',', $variables);
            echo "Variables passed to view: " . implode(', ', $varArray) . "\n";
            
            if (in_array('surats', $varArray)) {
                echo "✓ Passes 'surats' variable\n";
            } else {
                echo "✗ Does NOT pass 'surats' variable\n";
            }
        }
    } else {
        echo "✗ staffIndex method NOT found\n";
    }
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Update the staffIndex method in SuratController with the fixed code\n";
echo "2. Make sure to use lowercase role names: 'kaprodi', not 'Kaprodi'\n";
echo "3. Clear cache after updating: php artisan cache:clear\n";
echo "4. Test login with both kaprodi and staff_prodi users\n";

echo "\n=== END TEST ===\n";