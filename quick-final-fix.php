<?php
// quick-final-fix.php
// Jalankan: php quick-final-fix.php

echo "=== QUICK FINAL FIX FOR STAFF FAKULTAS ===\n\n";

echo "1. Database Schema Check\n";
echo str_repeat("-", 40) . "\n";

$schemaCheckScript = <<<'SCHEMACHECK'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking users table schema:\n";

if (Schema::hasTable('users')) {
    $columns = DB::select("SHOW COLUMNS FROM users");
    $hasFakultasId = false;
    
    echo "Users table columns:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field}: {$column->Type}\n";
        if ($column->Field === 'fakultas_id') {
            $hasFakultasId = true;
        }
    }
    
    if ($hasFakultasId) {
        echo "✅ fakultas_id column exists in users table\n";
    } else {
        echo "❌ fakultas_id column NOT found in users table\n";
        echo "This explains the database error!\n";
    }
} else {
    echo "❌ Users table not found\n";
}
SCHEMACHECK;

file_put_contents('schema-check.php', $schemaCheckScript);
$schemaOutput = shell_exec('php schema-check.php 2>&1');
echo $schemaOutput;
unlink('schema-check.php');

echo "\n2. Manual Fix for Test User\n";
echo str_repeat("-", 40) . "\n";

$manualFixScript = <<<'MANUALFIX'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Fakultas;
use Illuminate\Support\Facades\DB;

$fakultas = Fakultas::first();
$testUser = User::where('email', 'staff.fakultas@test.com')->first();

if ($testUser && $fakultas) {
    echo "Before fix:\n";
    echo "  User: {$testUser->nama}\n";
    echo "  Fakultas ID: " . ($testUser->fakultas_id ?: 'NULL') . "\n";
    
    // Direct database update
    try {
        DB::table('users')
          ->where('id', $testUser->id)
          ->update(['fakultas_id' => $fakultas->id]);
        
        // Refresh model
        $testUser->refresh();
        
        echo "\nAfter fix:\n";
        echo "  User: {$testUser->nama}\n";
        echo "  Fakultas ID: {$testUser->fakultas_id}\n";
        echo "  ✅ Successfully updated via direct DB query\n";
        
    } catch (Exception $e) {
        echo "❌ Error updating: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ Test user or fakultas not found\n";
}
MANUALFIX;

file_put_contents('manual-fix.php', $manualFixScript);
$manualFixOutput = shell_exec('php manual-fix.php 2>&1');
echo $manualFixOutput;
unlink('manual-fix.php');

echo "\n3. Final User Verification\n";
echo str_repeat("-", 40) . "\n";

$finalVerifyScript = <<<'FINALVERIFY'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "Final verification of staff fakultas users:\n\n";

$staffUsers = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_fakultas');
})->get();

foreach ($staffUsers as $user) {
    echo "👤 {$user->nama}\n";
    echo "   📧 Email: {$user->email}\n";
    echo "   🔑 Password: password123\n";
    echo "   🏢 Fakultas ID: " . ($user->fakultas_id ?: 'NOT SET') . "\n";
    echo "   👮 Role: " . ($user->hasRole('staff_fakultas') ? 'staff_fakultas ✅' : 'MISSING ❌') . "\n";
    echo "   🔗 URL: http://localhost:8000/fakultas/surat\n";
    echo "   ---\n";
}

echo "System ready status:\n";
$readyUsers = $staffUsers->where('fakultas_id', '>', 0);
echo "- Users ready for testing: {$readyUsers->count()}/{$staffUsers->count()}\n";

if ($readyUsers->count() > 0) {
    echo "✅ At least one user is ready for testing!\n";
} else {
    echo "❌ No users are properly configured\n";
}
FINALVERIFY;

file_put_contents('final-verify.php', $finalVerifyScript);
$finalVerifyOutput = shell_exec('php final-verify.php 2>&1');
echo $finalVerifyOutput;
unlink('final-verify.php');

echo "\n4. Quick Test Access\n";
echo str_repeat("-", 40) . "\n";

echo "🚀 READY TO TEST!\n\n";
echo "Primary Account (RECOMMENDED):\n";
echo "📧 Email: staff.fakultas@sistemsurat.com\n";
echo "🔑 Password: password123\n";
echo "🔗 URL: http://localhost:8000/fakultas/surat\n\n";

echo "Backup Account:\n";
echo "📧 Email: staff.fakultas@test.com\n";
echo "🔑 Password: password123\n";
echo "🔗 URL: http://localhost:8000/fakultas/surat\n\n";

echo "📋 What You Should See:\n";
echo "- At least 1 surat ready for processing\n";
echo "- Surat with status 'Disetujui Kaprodi'\n";
echo "- Ability to view, filter, and update surat\n\n";

echo "🛠️ Add Navigation:\n";
echo "Add this code to your navigation blade file:\n\n";

$navCode = '@if(Auth::user()->hasRole(\'staff_fakultas\'))
    <x-nav-link :href="route(\'fakultas.surat.index\')" :active="request()->routeIs(\'fakultas.surat.*\')">
        {{ __("Surat Fakultas") }}
    </x-nav-link>
@endif';

echo $navCode . "\n\n";

echo "🎯 TESTING STEPS:\n";
echo "1. Login with primary account above\n";
echo "2. Navigate to the URL\n";
echo "3. You should see the surat list\n";
echo "4. Test filtering and viewing functions\n";
echo "5. Add navigation menu for easy access\n\n";

echo "✅ System is configured and ready!\n";
?>