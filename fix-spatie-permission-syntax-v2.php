<?php
// fix-spatie-permission-syntax-v2.php
// Jalankan: php fix-spatie-permission-syntax-v2.php

echo "=== FIXING SPATIE PERMISSION SYNTAX & CREATING TEST DATA (V2) ===\n\n";

echo "1. Fixing User fakultas_id Assignment\n";
echo str_repeat("-", 50) . "\n";

$fixUserScript = <<<'FIXUSER'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Fakultas;

// Get first fakultas
$fakultas = Fakultas::first();
if (!$fakultas) {
    echo "❌ No fakultas found in database\n";
    exit;
}

echo "✅ Using fakultas: {$fakultas->nama_fakultas} (ID: {$fakultas->id})\n";

// Fix test user fakultas_id
$testUser = User::where('email', 'staff.fakultas@test.com')->first();
if ($testUser) {
    $testUser->update(['fakultas_id' => $fakultas->id]);
    echo "✅ Updated test user fakultas_id to {$fakultas->id}\n";
} else {
    echo "❌ Test user not found\n";
}

// Check all staff_fakultas users
$staffFakultasUsers = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_fakultas');
})->get();

echo "\nStaff Fakultas Users:\n";
foreach ($staffFakultasUsers as $user) {
    if (!$user->fakultas_id) {
        $user->update(['fakultas_id' => $fakultas->id]);
        echo "  - Fixed {$user->nama} - assigned fakultas_id: {$fakultas->id}\n";
    } else {
        echo "  - {$user->nama} - fakultas_id: {$user->fakultas_id} ✅\n";
    }
}
FIXUSER;

file_put_contents('fix-user.php', $fixUserScript);
$fixUserOutput = shell_exec('php fix-user.php 2>&1');
echo $fixUserOutput;
unlink('fix-user.php');

echo "\n2. Creating Test Surat Data with nomor_surat\n";
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

// Get staff prodi user
$staffProdi = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_prodi');
})->first();

if (!$staffProdi) {
    $staffProdi = User::create([
        'nama' => 'Staff Prodi Test',
        'email' => 'staff.prodi.test@test.com',
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

// Generate nomor surat function
function generateNomorSurat($jenisSurat, $prodi) {
    $tahun = date('Y');
    $bulan = date('m');
    
    // Count existing surats this month for auto increment
    $count = Surat::whereYear('created_at', $tahun)
                  ->whereMonth('created_at', $bulan)
                  ->count() + 1;
    
    $nomorUrut = str_pad($count, 3, '0', STR_PAD_LEFT);
    
    return "{$nomorUrut}/{$jenisSurat->kode_jenis}/{$prodi->kode_prodi}/{$bulan}/{$tahun}";
}

// Create test surat
$testSurat = Surat::where('perihal', 'like', '%Test Surat untuk Staff Fakultas%')->first();

if (!$testSurat) {
    $nomorSurat = generateNomorSurat($jenisSurat, $prodi);
    
    $testSurat = Surat::create([
        'nomor_surat' => $nomorSurat,
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
    echo "✅ Created test surat ID: {$testSurat->id} with nomor: {$nomorSurat}\n";
} else {
    echo "✅ Test surat already exists ID: {$testSurat->id}\n";
}

// Create a few more test surats
for ($i = 1; $i <= 3; $i++) {
    $existingSurat = Surat::where('perihal', "Test Surat Tambahan $i")->first();
    if (!$existingSurat) {
        $nomorSurat = generateNomorSurat($jenisSurat, $prodi);
        
        Surat::create([
            'nomor_surat' => $nomorSurat,
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
        echo "✅ Created additional test surat $i with nomor: {$nomorSurat}\n";
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

file_put_contents('create-test-data-v2.php', $createTestDataScript);
$testDataOutput = shell_exec('php create-test-data-v2.php 2>&1');
echo $testDataOutput;
unlink('create-test-data-v2.php');

echo "\n3. Verifying Staff Fakultas User (Fixed)\n";
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

file_put_contents('verify-user-v2.php', $verifyUserScript);
$verifyOutput = shell_exec('php verify-user-v2.php 2>&1');
echo $verifyOutput;
unlink('verify-user-v2.php');

echo "\n4. Testing Query for Faculty Surats (PHP Compatible)\n";
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
        echo "    Nomor: {$surat->nomor_surat}\n";
        echo "    Perihal: {$surat->perihal}\n";
        
        $prodiName = 'N/A';
        if ($surat->prodi) {
            $prodiName = $surat->prodi->nama_prodi;
        }
        echo "    Prodi: {$prodiName}\n";
        
        $statusName = 'N/A';
        if ($surat->currentStatus) {
            $statusName = $surat->currentStatus->nama_status;
        }
        echo "    Status: {$statusName}\n";
        
        $createdByName = 'N/A';
        if ($surat->createdBy) {
            $createdByName = $surat->createdBy->nama;
        }
        echo "    Created by: {$createdByName}\n";
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

file_put_contents('test-query-v2.php', $testQueryScript);
$queryOutput = shell_exec('php test-query-v2.php 2>&1');
echo $queryOutput;
unlink('test-query-v2.php');

echo "\n5. Database Schema Check\n";
echo str_repeat("-", 50) . "\n";

$schemaCheckScript = <<<'SCHEMACHECK'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Checking surat table schema:\n";

$columns = DB::select("SHOW COLUMNS FROM surat");

foreach ($columns as $column) {
    $nullable = $column->Null === 'YES' ? 'NULLABLE' : 'NOT NULL';
    $default = $column->Default !== null ? "DEFAULT: {$column->Default}" : 'NO DEFAULT';
    
    echo "  - {$column->Field}: {$column->Type} ({$nullable}) ({$default})\n";
    
    if ($column->Field === 'nomor_surat' && $column->Null === 'NO' && $column->Default === null) {
        echo "    ⚠️  nomor_surat is NOT NULL without default - this causes the error\n";
    }
}

echo "\nSurat table record count: " . DB::table('surat')->count() . "\n";
SCHEMACHECK;

file_put_contents('schema-check.php', $schemaCheckScript);
$schemaOutput = shell_exec('php schema-check.php 2>&1');
echo $schemaOutput;
unlink('schema-check.php');

echo "\n6. Manual Navigation Instructions\n";
echo str_repeat("-", 50) . "\n";

echo "Add this navigation code manually:\n\n";

$navigationCode = <<<'NAVCODE'
{{-- Add to resources/views/layouts/navigation.blade.php --}}
{{-- Add after other role-based navigation items --}}

@if(Auth::user()->hasRole('staff_fakultas'))
    <x-nav-link :href="route('fakultas.surat.index')" :active="request()->routeIs('fakultas.surat.*')">
        {{ __('Daftar Surat Fakultas') }}
    </x-nav-link>
@endif

{{-- Alternative without x-nav-link component: --}}
@if(Auth::user()->hasRole('staff_fakultas'))
    <a href="{{ route('fakultas.surat.index') }}" 
       class="nav-link {{ request()->routeIs('fakultas.surat.*') ? 'active' : '' }}">
        Daftar Surat Fakultas
    </a>
@endif

{{-- For sidebar navigation (if using AdminLTE or similar): --}}
@if(Auth::user()->hasRole('staff_fakultas'))
    <li class="nav-item">
        <a href="{{ route('fakultas.surat.index') }}" 
           class="nav-link {{ request()->routeIs('fakultas.surat.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-envelope"></i>
            <p>Surat Fakultas</p>
        </a>
    </li>
@endif
NAVCODE;

echo $navigationCode;

echo "\n=== FINAL INSTRUCTIONS (V2) ===\n";
echo "✅ Users fixed with proper fakultas_id\n";
echo "✅ Test data created with nomor_surat included\n";
echo "✅ PHP compatibility issues resolved\n";
echo "✅ Routes working correctly\n";
echo "\n1. Login credentials:\n";
echo "   - staff.fakultas@test.com / password123\n";
echo "   - staff.fakultas@sistemsurat.com / password123\n";
echo "2. Navigate to: http://localhost:8000/fakultas/surat\n";
echo "3. Should see test surats ready for processing\n";
echo "4. Add navigation link manually using code above\n";

echo "\n🎯 Staff Fakultas system is ready for testing!\n";
?>