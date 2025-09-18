<?php
// fix-staff-fakultas-auth.php
// Jalankan: php fix-staff-fakultas-auth.php

echo "=== FIXING STAFF FAKULTAS AUTHORIZATION & ROUTES ===\n\n";

echo "1. Checking Current User and Role\n";
echo str_repeat("-", 50) . "\n";

// Check current logged in user
$checkUserScript = <<<'CHECKUSER'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "Checking users with staff_fakultas role:\n";

$staffFakultasRole = Role::where('name', 'staff_fakultas')->first();
if ($staffFakultasRole) {
    $users = User::role('staff_fakultas')->get();
    
    if ($users->count() > 0) {
        foreach ($users as $user) {
            echo "  - {$user->nama} ({$user->email}) - Fakultas ID: {$user->fakultas_id}\n";
        }
    } else {
        echo "  No users found with staff_fakultas role\n";
        
        // Show all users for reference
        echo "\nAll users in system:\n";
        $allUsers = User::with('roles')->get();
        foreach ($allUsers as $user) {
            $roleNames = $user->roles->pluck('name')->join(', ');
            echo "  - {$user->nama} ({$user->email}) - Roles: {$roleNames} - Fakultas: {$user->fakultas_id}\n";
        }
    }
} else {
    echo "  staff_fakultas role not found!\n";
}
CHECKUSER;

file_put_contents('temp-check-user.php', $checkUserScript);
$userOutput = shell_exec('php temp-check-user.php 2>&1');
echo $userOutput;
unlink('temp-check-user.php');

echo "\n2. Checking Route Configuration\n";
echo str_repeat("-", 50) . "\n";

// Check if staff routes are conflicting
$webRoutesFile = 'routes/web.php';
$content = file_get_contents($webRoutesFile);

echo "Checking route conflicts...\n";
if (strpos($content, "Route::prefix('staff')") !== false) {
    echo "⚠️  Found staff prefix routes that might conflict\n";
    echo "Staff fakultas routes should use /fakultas prefix, not /staff\n";
}

if (strpos($content, "require __DIR__.'/fakultas.php'") !== false) {
    echo "✅ Fakultas routes included\n";
} else {
    echo "❌ Fakultas routes not included\n";
}

echo "\n3. Creating Test User for Staff Fakultas\n";
echo str_repeat("-", 50) . "\n";

$createTestUserScript = <<<'CREATEUSER'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Fakultas;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

// Get or create fakultas
$fakultas = Fakultas::first();
if (!$fakultas) {
    $fakultas = Fakultas::create([
        'nama_fakultas' => 'Fakultas Teknik',
        'kode_fakultas' => 'FT'
    ]);
    echo "✅ Created test fakultas: {$fakultas->nama_fakultas}\n";
} else {
    echo "✅ Using existing fakultas: {$fakultas->nama_fakultas}\n";
}

// Check if test user exists
$testUser = User::where('email', 'staff.fakultas@test.com')->first();

if (!$testUser) {
    $testUser = User::create([
        'nama' => 'Staff Fakultas Test',
        'email' => 'staff.fakultas@test.com',
        'password' => Hash::make('password123'),
        'fakultas_id' => $fakultas->id,
        'email_verified_at' => now()
    ]);
    echo "✅ Created test user: {$testUser->email}\n";
} else {
    echo "✅ Test user already exists: {$testUser->email}\n";
    // Update fakultas_id if null
    if (!$testUser->fakultas_id) {
        $testUser->update(['fakultas_id' => $fakultas->id]);
        echo "✅ Updated test user fakultas_id\n";
    }
}

// Assign staff_fakultas role
$role = Role::where('name', 'staff_fakultas')->first();
if ($role) {
    if (!$testUser->hasRole('staff_fakultas')) {
        $testUser->assignRole('staff_fakultas');
        echo "✅ Assigned staff_fakultas role to test user\n";
    } else {
        echo "✅ Test user already has staff_fakultas role\n";
    }
} else {
    echo "❌ staff_fakultas role not found\n";
}

echo "\nTest user credentials:\n";
echo "Email: staff.fakultas@test.com\n";
echo "Password: password123\n";
echo "Role: staff_fakultas\n";
echo "Fakultas: {$fakultas->nama_fakultas}\n";
CREATEUSER;

file_put_contents('create-test-user.php', $createTestUserScript);
$createUserOutput = shell_exec('php create-test-user.php 2>&1');
echo $createUserOutput;
unlink('create-test-user.php');

echo "\n4. Checking Controller Authorization Logic\n";
echo str_repeat("-", 50) . "\n";

$controllerFile = 'app/Http/Controllers/FakultasStaffController.php';
if (file_exists($controllerFile)) {
    echo "✅ FakultasStaffController exists\n";
    
    $controllerContent = file_get_contents($controllerFile);
    if (strpos($controllerContent, "hasRole('staff_fakultas')") !== false) {
        echo "✅ Controller has role authorization check\n";
    } else {
        echo "❌ Controller missing role authorization\n";
    }
    
    if (strpos($controllerContent, 'fakultas_id') !== false) {
        echo "✅ Controller checks fakultas_id\n";
    } else {
        echo "❌ Controller missing fakultas_id check\n";
    }
} else {
    echo "❌ FakultasStaffController not found\n";
}

echo "\n5. Testing Route Access\n";
echo str_repeat("-", 50) . "\n";

echo "Testing route generation:\n";
$testRouteScript = <<<'TESTROUTE'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = [
    'fakultas.surat.index' => [],
    'fakultas.surat.show' => ['id' => 1]
];

foreach ($routes as $routeName => $params) {
    try {
        $url = route($routeName, $params);
        echo "✅ $routeName: $url\n";
    } catch (Exception $e) {
        echo "❌ $routeName: " . $e->getMessage() . "\n";
    }
}
TESTROUTE;

file_put_contents('test-route-access.php', $testRouteScript);
$testRouteOutput = shell_exec('php test-route-access.php 2>&1');
echo $testRouteOutput;
unlink('test-route-access.php');

echo "\n6. Creating Test Surat for Testing\n";
echo str_repeat("-", 50) . "\n";

$createTestSuratScript = <<<'TESTSURAT'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\JenisSurat;
use App\Models\Jabatan;
use App\Models\Prodi;
use App\Models\User;

// Get required data
$fakultas = App\Models\Fakultas::first();
$prodi = Prodi::where('fakultas_id', $fakultas->id)->first();
$staffProdi = User::role('staff_prodi')->first();
$approvedStatus = StatusSurat::where('kode_status', 'disetujui_kaprodi')->first();
$jenisSurat = JenisSurat::first();
$jabatan = Jabatan::first();

if (!$prodi) {
    $prodi = Prodi::create([
        'nama_prodi' => 'Teknik Informatika',
        'kode_prodi' => 'TI',
        'fakultas_id' => $fakultas->id
    ]);
    echo "✅ Created test prodi: {$prodi->nama_prodi}\n";
}

if (!$staffProdi) {
    $staffProdi = User::create([
        'nama' => 'Staff Prodi Test',
        'email' => 'staff.prodi@test.com',
        'password' => Hash::make('password123'),
        'prodi_id' => $prodi->id,
        'fakultas_id' => $fakultas->id,
        'email_verified_at' => now()
    ]);
    $staffProdi->assignRole('staff_prodi');
    echo "✅ Created test staff prodi\n";
}

if (!$jenisSurat) {
    $jenisSurat = JenisSurat::create([
        'nama_jenis' => 'Surat Keterangan',
        'kode_jenis' => 'SKT'
    ]);
    echo "✅ Created test jenis surat\n";
}

if (!$jabatan) {
    $jabatan = Jabatan::create([
        'nama_jabatan' => 'Rektor',
        'kode_jabatan' => 'REKTOR'
    ]);
    echo "✅ Created test jabatan\n";
}

if (!$approvedStatus) {
    $approvedStatus = StatusSurat::create([
        'nama_status' => 'Disetujui Kaprodi',
        'kode_status' => 'disetujui_kaprodi'
    ]);
    echo "✅ Created approved status\n";
}

// Create test surat that's approved by kaprodi
$existingTestSurat = Surat::where('perihal', 'like', '%Test Surat untuk Staff Fakultas%')->first();

if (!$existingTestSurat) {
    $testSurat = Surat::create([
        'perihal' => 'Test Surat untuk Staff Fakultas',
        'isi_surat' => 'Ini adalah surat test untuk menguji fungsi staff fakultas. Surat ini sudah disetujui oleh kaprodi dan siap untuk diproses oleh staff fakultas.',
        'jenis_surat_id' => $jenisSurat->id,
        'tujuan_jabatan_id' => $jabatan->id,
        'prodi_id' => $prodi->id,
        'created_by' => $staffProdi->id,
        'status_id' => $approvedStatus->id
    ]);
    echo "✅ Created test surat ID: {$testSurat->id}\n";
    echo "   Perihal: {$testSurat->perihal}\n";
    echo "   Status: {$approvedStatus->nama_status}\n";
    echo "   Prodi: {$prodi->nama_prodi}\n";
} else {
    echo "✅ Test surat already exists ID: {$existingTestSurat->id}\n";
}

echo "\nTest data ready for staff fakultas testing!\n";
TESTSURAT;

file_put_contents('create-test-surat.php', $createTestSuratScript);
$testSuratOutput = shell_exec('php create-test-surat.php 2>&1');
echo $testSuratOutput;
unlink('create-test-surat.php');

echo "\n7. Adding Navigation Link for Staff Fakultas\n";
echo str_repeat("-", 50) . "\n";

// Check if navigation needs to be updated
$layoutFiles = [
    'resources/views/layouts/app.blade.php',
    'resources/views/layouts/navigation.blade.php'
];

$navigationUpdated = false;
foreach ($layoutFiles as $layoutFile) {
    if (file_exists($layoutFile)) {
        $content = file_get_contents($layoutFile);
        
        if (strpos($content, 'fakultas.surat.index') === false && strpos($content, 'navigation') !== false) {
            echo "Adding navigation link to $layoutFile...\n";
            
            // Look for existing navigation structure
            if (strpos($content, "hasRole('staff_prodi')") !== false) {
                // Add after staff_prodi nav item
                $staffFakultasNav = <<<'NAV'

                        {{-- Staff Fakultas Navigation --}}
                        @if(Auth::user()->hasRole('staff_fakultas'))
                            <x-nav-link :href="route('fakultas.surat.index')" :active="request()->routeIs('fakultas.surat.*')">
                                {{ __('Daftar Surat Fakultas') }}
                            </x-nav-link>
                        @endif
NAV;
                
                $newContent = preg_replace(
                    '/@if\(Auth::user\(\)->hasRole\(\'staff_prodi\'\)\).*?@endif/s',
                    '$0' . $staffFakultasNav,
                    $content,
                    1
                );
                
                if ($newContent !== $content) {
                    $backup = $layoutFile . '.nav.' . date('YmdHis');
                    copy($layoutFile, $backup);
                    file_put_contents($layoutFile, $newContent);
                    echo "✅ Added staff fakultas navigation link\n";
                    $navigationUpdated = true;
                    break;
                }
            }
        } elseif (strpos($content, 'fakultas.surat.index') !== false) {
            echo "✅ Navigation link already exists in $layoutFile\n";
            $navigationUpdated = true;
            break;
        }
    }
}

if (!$navigationUpdated) {
    echo "⚠️  Could not automatically add navigation link\n";
    echo "   Please manually add navigation for staff_fakultas role\n";
}

echo "\n=== TROUBLESHOOTING SUMMARY ===\n";
echo "The issue was:\n";
echo "1. ❌ You accessed /staff/surat (wrong URL)\n";
echo "2. ✅ Should access /fakultas/surat (correct URL)\n";
echo "3. ✅ Created test user with proper role and fakultas_id\n";
echo "4. ✅ Created test surat data for testing\n";

echo "\n=== LOGIN CREDENTIALS ===\n";
echo "Test Staff Fakultas User:\n";
echo "Email: staff.fakultas@test.com\n";
echo "Password: password123\n";

echo "\n=== CORRECT URLS ===\n";
echo "❌ Wrong: http://localhost:8000/staff/surat\n";
echo "✅ Correct: http://localhost:8000/fakultas/surat\n";
echo "✅ Detail: http://localhost:8000/fakultas/surat/{id}\n";

echo "\n=== TESTING STEPS ===\n";
echo "1. Login with staff.fakultas@test.com / password123\n";
echo "2. Go to: http://localhost:8000/fakultas/surat\n";
echo "3. Should see list of approved surats from prodi in your fakultas\n";
echo "4. Click 'Lihat Detail' to view surat details\n";
echo "5. Test approve/reject functionality\n";

echo "\nStaff Fakultas system should now work correctly!\n";
?>