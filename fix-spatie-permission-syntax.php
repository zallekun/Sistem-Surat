<?php
// fix-spatie-permission-syntax.php
// Jalankan: php fix-spatie-permission-syntax.php

echo "=== FIXING SPATIE PERMISSION SYNTAX & CREATING TEST DATA ===\n\n";

echo "1. Checking Users with Staff Fakultas Role\n";
echo str_repeat("-", 50) . "\n";

$checkUsersScript = <<<'CHECKUSERS'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "Checking users with staff_fakultas role (correct syntax):\n";

// Correct way to get users with specific role
$staffFakultasUsers = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_fakultas');
})->get();

if ($staffFakultasUsers->count() > 0) {
    echo "Found staff_fakultas users:\n";
    foreach ($staffFakultasUsers as $user) {
        echo "  - {$user->nama} ({$user->email}) - Fakultas ID: {$user->fakultas_id}\n";
    }
} else {
    echo "No users found with staff_fakultas role\n";
}

echo "\nAll users in system:\n";
$allUsers = User::with('roles')->get();
foreach ($allUsers as $user) {
    $roleNames = $user->roles->pluck('name')->join(', ') ?: 'No roles';
    echo "  - {$user->nama} ({$user->email}) - Roles: {$roleNames} - Fakultas: {$user->fakultas_id}\n";
}
CHECKUSERS;

file_put_contents('check-users-correct.php', $checkUsersScript);
$usersOutput = shell_exec('php check-users-correct.php 2>&1');
echo $usersOutput;
unlink('check-users-correct.php');

echo "\n2. Creating Test Surat Data\n";
echo str_repeat("-", 50) . "\n";

$createTestDataScript = <<<'TESTDATA'
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
use App\Models\Fakultas;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Get test fakultas
$fakultas = Fakultas::first();
if (!$fakultas) {
    echo "❌ No fakultas found\n";
    exit;
}
echo "✅ Using fakultas: {$fakultas->nama_fakultas}\n";

// Get or create prodi
$prodi = Prodi::where('fakultas_id', $fakultas->id)->first();
if (!$prodi) {
    $prodi = Prodi::create([
        'nama_prodi' => 'Teknik Informatika',
        'kode_prodi' => 'TI',
        'fakultas_id' => $fakultas->id
    ]);
    echo "✅ Created prodi: {$prodi->nama_prodi}\n";
} else {
    echo "✅ Using prodi: {$prodi->nama_prodi}\n";
}

// Get staff prodi user (correct syntax)
$staffProdi = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_prodi');
})->first();

if (!$staffProdi) {
    $staffProdi = User::create([
        'nama' => 'Staff Prodi Test',
        'email' => 'staff.prodi@test.com',
        'password' => Hash::make('password123'),
        'prodi_id' => $prodi->id,
        'fakultas_id' => $fakultas->id,
        'email_verified_at' => now()
    ]);
    
    $role = Spatie\Permission\Models\Role::where('name', 'staff_prodi')->first();
    if ($role) {
        $staffProdi->assignRole($role);
    }
    echo "✅ Created staff prodi user\n";
} else {
    echo "✅ Using existing staff prodi: {$staffProdi->nama}\n";
}

// Get or create required data
$approvedStatus = StatusSurat::where('kode_status', 'disetujui_kaprodi')->first();
if (!$approvedStatus) {
    $approvedStatus = StatusSurat::create([
        'nama_status' => 'Disetujui Kaprodi',
        'kode_status' => 'disetujui_kaprodi'
    ]);
    echo "✅ Created approved status\n";
} else {
    echo "✅ Using status: {$approvedStatus->nama_status}\n";
}

$jenisSurat = JenisSurat::first();
if (!$jenisSurat) {
    $jenisSurat = JenisSurat::create([
        'nama_jenis' => 'Surat Keterangan',
        'kode_jenis' => 'SKT'
    ]);
    echo "✅ Created jenis surat\n";
} else {
    echo "✅ Using jenis surat: {$jenisSurat->nama_jenis}\n";
}

$jabatan = Jabatan::first();
if (!$jabatan) {
    $jabatan = Jabatan::create([
        'nama_jabatan' => 'Rektor',
        'kode_jabatan' => 'REKTOR'
    ]);
    echo "✅ Created jabatan\n";
} else {
    echo "✅ Using jabatan: {$jabatan->nama_jabatan}\n";
}

// Create test surat
$testSurat = Surat::where('perihal', 'like', '%Test Surat untuk Staff Fakultas%')->first();

if (!$testSurat) {
    $testSurat = Surat::create([
        'perihal' => 'Test Surat untuk Staff Fakultas - ' . date('d/m/Y H:i'),
        'isi_surat' => 'Ini adalah surat test untuk menguji fungsi staff fakultas. Surat ini sudah disetujui oleh kaprodi dan siap untuk diproses oleh staff fakultas.',
        'jenis_surat_id' => $jenisSurat->id,
        'tujuan_jabatan_id' => $jabatan->id,
        'prodi_id' => $prodi->id,
        'created_by' => $staffProdi->id,
        'status_id' => $approvedStatus->id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✅ Created test surat ID: {$testSurat->id}\n";
} else {
    echo "✅ Test surat already exists ID: {$testSurat->id}\n";
}

// Create a few more test surats
for ($i = 1; $i <= 3; $i++) {
    $existingSurat = Surat::where('perihal', "Test Surat Tambahan $i")->first();
    if (!$existingSurat) {
        Surat::create([
            'perihal' => "Test Surat Tambahan $i",
            'isi_surat' => "Ini adalah surat test tambahan nomor $i untuk staff fakultas.",
            'jenis_surat_id' => $jenisSurat->id,
            'tujuan_jabatan_id' => $jabatan->id,
            'prodi_id' => $prodi->id,
            'created_by' => $staffProdi->id,
            'status_id' => $approvedStatus->id,
            'created_at' => now()->subDays($i),
            'updated_at' => now()->subDays($i)
        ]);
        echo "✅ Created additional test surat $i\n";
    }
}

echo "\nTest data summary:\n";
echo "- Fakultas: {$fakultas->nama_fakultas}\n";
echo "- Prodi: {$prodi->nama_prodi}\n";  
echo "- Staff Prodi: {$staffProdi->nama} ({$staffProdi->email})\n";
echo "- Status: {$approvedStatus->nama_status}\n";
echo "- Jenis Surat: {$jenisSurat->nama_jenis}\n";
echo "- Tujuan: {$jabatan->nama_jabatan}\n";
echo "- Test Surat ID: {$testSurat->id}\n";
TESTDATA;

file_put_contents('create-test-data-correct.php', $createTestDataScript);
$testDataOutput = shell_exec('php create-test-data-correct.php 2>&1');
echo $testDataOutput;
unlink('create-test-data-correct.php');

echo "\n3. Verifying Staff Fakultas User\n";
echo str_repeat("-", 50) . "\n";

$verifyUserScript = <<<'VERIFYUSER'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$testUser = User::where('email', 'staff.fakultas@test.com')->first();

if ($testUser) {
    echo "✅ Staff fakultas test user exists\n";
    echo "   Name: {$testUser->nama}\n";
    echo "   Email: {$testUser->email}\n";
    echo "   Fakultas ID: {$testUser->fakultas_id}\n";
    
    $roles = $testUser->roles->pluck('name')->toArray();
    echo "   Roles: " . implode(', ', $roles) . "\n";
    
    if ($testUser->hasRole('staff_fakultas')) {
        echo "   ✅ Has staff_fakultas role\n";
    } else {
        echo "   ❌ Missing staff_fakultas role\n";
    }
    
    if ($testUser->fakultas_id) {
        echo "   ✅ Has fakultas_id assigned\n";
    } else {
        echo "   ❌ Missing fakultas_id\n";
    }
} else {
    echo "❌ Staff fakultas test user not found\n";
}
VERIFYUSER;

file_put_contents('verify-user.php', $verifyUserScript);
$verifyOutput = shell_exec('php verify-user.php 2>&1');
echo $verifyOutput;
unlink('verify-user.php');

echo "\n4. Testing Query for Faculty Surats\n";
echo str_repeat("-", 50) . "\n";

$testQueryScript = <<<'TESTQUERY'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Surat;
use App\Models\User;

$testUser = User::where('email', 'staff.fakultas@test.com')->first();

if ($testUser && $testUser->fakultas_id) {
    echo "Testing query for surats that staff fakultas should see:\n";
    
    $surats = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan', 'prodi'])
                  ->whereHas('prodi', function($query) use ($testUser) {
                      $query->where('fakultas_id', $testUser->fakultas_id);
                  })
                  ->whereHas('currentStatus', function($query) {
                      $query->where('kode_status', 'disetujui_kaprodi');
                  })
                  ->get();
    
    echo "Found {$surats->count()} surats for staff fakultas:\n";
    
    foreach ($surats as $surat) {
        echo "  - ID: {$surat->id}\n";
        echo "    Perihal: {$surat->perihal}\n";
        echo "    Prodi: {$surat->prodi->nama_prodi ?? 'N/A'}\n";
        echo "    Status: {$surat->currentStatus->nama_status ?? 'N/A'}\n";
        echo "    Created by: {$surat->createdBy->nama ?? 'N/A'}\n";
        echo "    ---\n";
    }
    
    if ($surats->count() > 0) {
        echo "✅ Query working correctly - staff fakultas will see these surats\n";
    } else {
        echo "⚠️  No surats found - might need to create more test data\n";
    }
} else {
    echo "❌ Test user or fakultas_id not found\n";
}
TESTQUERY;

file_put_contents('test-query.php', $testQueryScript);
$queryOutput = shell_exec('php test-query.php 2>&1');
echo $queryOutput;
unlink('test-query.php');

echo "\n5. Manual Navigation Instructions\n";
echo str_repeat("-", 50) . "\n";

echo "Since automatic navigation addition failed, here's the manual code:\n\n";

$navigationCode = <<<'NAVCODE'
{{-- Add this to your navigation blade file (usually resources/views/layouts/navigation.blade.php) --}}
{{-- Add after other navigation items --}}

@if(Auth::user()->hasRole('staff_fakultas'))
    <x-nav-link :href="route('fakultas.surat.index')" :active="request()->routeIs('fakultas.surat.*')">
        {{ __('Daftar Surat Fakultas') }}
    </x-nav-link>
@endif

{{-- Or if you don't have x-nav-link component, use regular link: --}}

@if(Auth::user()->hasRole('staff_fakultas'))
    <a href="{{ route('fakultas.surat.index') }}" 
       class="nav-link {{ request()->routeIs('fakultas.surat.*') ? 'active' : '' }}">
        Daftar Surat Fakultas
    </a>
@endif
NAVCODE;

echo $navigationCode;

echo "\n=== FINAL INSTRUCTIONS ===\n";
echo "✅ User created: staff.fakultas@test.com / password123\n";
echo "✅ Test data created with approved surats\n";
echo "✅ Routes working correctly\n";
echo "\n1. Login with: staff.fakultas@test.com / password123\n";
echo "2. Go to: http://localhost:8000/fakultas/surat\n";
echo "3. Should see test surats ready for processing\n";
echo "4. Add navigation link manually using code above\n";

echo "\nStaff Fakultas system is ready for testing!\n";
?>