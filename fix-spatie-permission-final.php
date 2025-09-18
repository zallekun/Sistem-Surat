<?php
// fix-spatie-permission-final.php
// Jalankan: php fix-spatie-permission-final.php

echo "=== FIXING SPATIE PERMISSION SYNTAX & CREATING TEST DATA (FINAL) ===\n\n";

echo "1. Checking and Fixing All Staff Fakultas Users\n";
echo str_repeat("-", 50) . "\n";

$fixAllUsersScript = <<<'FIXALLUSERS'
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

// Fix ALL staff_fakultas users
$staffFakultasUsers = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_fakultas');
})->get();

echo "\nFixing Staff Fakultas Users:\n";
foreach ($staffFakultasUsers as $user) {
    if (!$user->fakultas_id) {
        $user->update(['fakultas_id' => $fakultas->id]);
        echo "  ✅ Fixed {$user->nama} ({$user->email}) - assigned fakultas_id: {$fakultas->id}\n";
    } else {
        echo "  ✅ {$user->nama} ({$user->email}) - fakultas_id: {$user->fakultas_id} already set\n";
    }
}

// Verify fix
echo "\nVerification - All Staff Fakultas Users:\n";
$verifyUsers = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_fakultas');
})->get();

foreach ($verifyUsers as $user) {
    $hasRole = $user->hasRole('staff_fakultas') ? '✅' : '❌';
    $hasFakultas = $user->fakultas_id ? '✅' : '❌';
    echo "  - {$user->nama} ({$user->email})\n";
    echo "    Role: {$hasRole} | Fakultas ID: {$user->fakultas_id} {$hasFakultas}\n";
}
FIXALLUSERS;

file_put_contents('fix-all-users.php', $fixAllUsersScript);
$fixUsersOutput = shell_exec('php fix-all-users.php 2>&1');
echo $fixUsersOutput;
unlink('fix-all-users.php');

echo "\n2. Creating Test Surat Data with ALL Required Fields\n";
echo str_repeat("-", 50) . "\n";

$createCompleteDataScript = <<<'COMPLETEDATA'
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

// Get required data
$fakultas = Fakultas::first();
$prodi = Prodi::where('fakultas_id', $fakultas->id)->first();
$staffProdi = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_prodi');
})->where('fakultas_id', $fakultas->id)->first();

$approvedStatus = StatusSurat::where('kode_status', 'disetujui_kaprodi')->first();
$jenisSurat = JenisSurat::first();
$jabatan = Jabatan::first();

echo "Using data:\n";
echo "- Fakultas: {$fakultas->nama_fakultas}\n";
echo "- Prodi: {$prodi->nama_prodi}\n";  
echo "- Staff: {$staffProdi->nama}\n";
echo "- Status: {$approvedStatus->nama_status}\n";
echo "- Jenis: {$jenisSurat->nama_jenis}\n";
echo "- Jabatan: {$jabatan->nama_jabatan}\n\n";

// Generate nomor surat function
function generateNomorSurat($jenisSurat, $prodi, $increment = null) {
    $tahun = date('Y');
    $bulan = date('m');
    
    if ($increment === null) {
        $count = Surat::whereYear('created_at', $tahun)
                      ->whereMonth('created_at', $bulan)
                      ->count() + 1;
    } else {
        $count = $increment;
    }
    
    $nomorUrut = str_pad($count, 3, '0', STR_PAD_LEFT);
    $kodeJenis = $jenisSurat->kode_jenis ? $jenisSurat->kode_jenis : 'SRT';
    $kodeProdi = $prodi->kode_prodi ? $prodi->kode_prodi : 'PRD';
    
    return "{$nomorUrut}/{$kodeJenis}/{$kodeProdi}/{$bulan}/{$tahun}";
}

// Clean up old test data
Surat::where('perihal', 'like', '%Test Surat%')->delete();
echo "✅ Cleaned up old test data\n";

// Create test surats with ALL required fields
$testSurats = [
    [
        'perihal' => 'Test Surat untuk Staff Fakultas - Surat Keterangan Mahasiswa',
        'isi_surat' => 'Dengan hormat, surat ini dibuat sebagai test untuk sistem staff fakultas. Surat ini berisi permohonan keterangan untuk mahasiswa yang telah disetujui oleh Kaprodi.',
        'increment' => 10
    ],
    [
        'perihal' => 'Test Surat Rekomendasi Penelitian',
        'isi_surat' => 'Surat rekomendasi untuk penelitian mahasiswa yang telah melewati review kaprodi dan siap diproses staff fakultas.',
        'increment' => 11
    ],
    [
        'perihal' => 'Test Surat Izin Kegiatan Akademik',
        'isi_surat' => 'Permohonan izin untuk kegiatan akademik yang memerlukan persetujuan tingkat fakultas.',
        'increment' => 12
    ],
    [
        'perihal' => 'Test Surat Pengantar Magang',
        'isi_surat' => 'Surat pengantar untuk mahasiswa yang akan melaksanakan program magang di industri.',
        'increment' => 13
    ]
];

$createdSurats = [];

foreach ($testSurats as $index => $testData) {
    $nomorSurat = generateNomorSurat($jenisSurat, $prodi, $testData['increment']);
    
    $surat = Surat::create([
        'nomor_surat' => $nomorSurat,
        'tanggal_surat' => now()->subDays($index)->format('Y-m-d'),
        'perihal' => $testData['perihal'],
        'isi_surat' => $testData['isi_surat'],
        'tipe_surat' => 'keluar',
        'sifat_surat' => 'biasa',
        'jenis_id' => $jenisSurat->id,
        'tujuan_jabatan_id' => $jabatan->id,
        'prodi_id' => $prodi->id,
        'fakultas_id' => $fakultas->id,
        'created_by' => $staffProdi->id,
        'status_id' => $approvedStatus->id,
        'status' => 'approved',
        'created_at' => now()->subDays($index),
        'updated_at' => now()->subDays($index)
    ]);
    
    $createdSurats[] = $surat;
    echo "✅ Created surat ID: {$surat->id} - {$nomorSurat}\n";
    echo "   Perihal: {$surat->perihal}\n";
}

echo "\n📊 Test Data Summary:\n";
echo "- Total surats created: " . count($createdSurats) . "\n";
echo "- All surats have status: {$approvedStatus->nama_status}\n";
echo "- All surats belong to fakultas: {$fakultas->nama_fakultas}\n";
echo "- Ready for staff fakultas processing ✅\n";
COMPLETEDATA;

file_put_contents('create-complete-data.php', $createCompleteDataScript);
$completeDataOutput = shell_exec('php create-complete-data.php 2>&1');
echo $completeDataOutput;
unlink('create-complete-data.php');

echo "\n3. Final Verification and Query Test\n";
echo str_repeat("-", 50) . "\n";

$finalVerificationScript = <<<'FINALVERIFY'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Surat;
use App\Models\User;

// Get staff fakultas users
$staffFakultasUsers = User::whereHas('roles', function($query) {
    $query->where('name', 'staff_fakultas');
})->get();

echo "Staff Fakultas Users Ready for Testing:\n";
foreach ($staffFakultasUsers as $user) {
    echo "\n👤 User: {$user->nama} ({$user->email})\n";
    echo "   Password: password123\n";
    echo "   Fakultas ID: {$user->fakultas_id}\n";
    echo "   Role Check: " . ($user->hasRole('staff_fakultas') ? '✅' : '❌') . "\n";
    
    if ($user->fakultas_id) {
        // Test query for this user
        $surats = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan', 'prodi'])
                      ->whereHas('prodi', function($query) use ($user) {
                          $query->where('fakultas_id', $user->fakultas_id);
                      })
                      ->whereHas('currentStatus', function($query) {
                          $query->where('kode_status', 'disetujui_kaprodi');
                      })
                      ->get();
        
        echo "   📋 Available surats: {$surats->count()}\n";
        
        if ($surats->count() > 0) {
            echo "   📄 Sample surats:\n";
            foreach ($surats->take(2) as $surat) {
                echo "      - ID {$surat->id}: {$surat->perihal}\n";
                echo "        Nomor: {$surat->nomor_surat}\n";
                echo "        Prodi: " . ($surat->prodi ? $surat->prodi->nama_prodi : 'N/A') . "\n";
            }
        }
    }
    echo "   🔗 URL: http://localhost:8000/fakultas/surat\n";
}

// Overall system check
echo "\n🔍 System Status Check:\n";
$totalSurats = Surat::count();
$approvedSurats = Surat::whereHas('currentStatus', function($query) {
    $query->where('kode_status', 'disetujui_kaprodi');
})->count();

echo "- Total surats in system: {$totalSurats}\n";
echo "- Surats ready for staff fakultas: {$approvedSurats}\n";
echo "- Staff fakultas users: " . $staffFakultasUsers->count() . "\n";

if ($staffFakultasUsers->count() > 0 && $approvedSurats > 0) {
    echo "\n🎉 System is ready for testing!\n";
} else {
    echo "\n⚠️  System may have issues - check data above\n";
}
FINALVERIFY;

file_put_contents('final-verification.php', $finalVerificationScript);
$verifyOutput = shell_exec('php final-verification.php 2>&1');
echo $verifyOutput;
unlink('final-verification.php');

echo "\n4. Navigation Code for Manual Addition\n";
echo str_repeat("-", 50) . "\n";

$navigationCode = <<<'NAVCODE'
{{-- 📝 ADD THIS TO YOUR NAVIGATION FILE --}}
{{-- File: resources/views/layouts/navigation.blade.php --}}
{{-- Add this block where other role-based navigation items are --}}

@if(Auth::user()->hasRole('staff_fakultas'))
    <x-nav-link :href="route('fakultas.surat.index')" :active="request()->routeIs('fakultas.surat.*')">
        {{ __('Surat Fakultas') }}
    </x-nav-link>
@endif

{{-- Alternative for regular HTML/Bootstrap navigation: --}}
@if(Auth::user()->hasRole('staff_fakultas'))
    <a href="{{ route('fakultas.surat.index') }}" 
       class="nav-link {{ request()->routeIs('fakultas.surat.*') ? 'active' : '' }}">
        <i class="fas fa-envelope me-2"></i>
        Surat Fakultas
    </a>
@endif

{{-- For AdminLTE sidebar: --}}
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

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 SISTEM STAFF FAKULTAS SIAP DIGUNAKAN!\n";
echo str_repeat("=", 60) . "\n\n";

echo "✅ TESTING CHECKLIST:\n";
echo "1. Login dengan salah satu akun:\n";
echo "   📧 staff.fakultas@sistemsurat.com\n";
echo "   📧 staff.fakultas@test.com\n";
echo "   🔑 Password: password123\n\n";

echo "2. Akses URL: http://localhost:8000/fakultas/surat\n\n";

echo "3. Tambahkan navigasi secara manual menggunakan kode di atas\n\n";

echo "4. Fitur yang dapat ditest:\n";
echo "   - Melihat daftar surat yang sudah disetujui kaprodi\n";
echo "   - Filter dan pencarian surat\n";
echo "   - Update status surat\n";
echo "   - View detail surat\n\n";

echo "🔧 Jika ada error, periksa:\n";
echo "   - Role 'staff_fakultas' sudah ada di database\n";
echo "   - User memiliki fakultas_id\n";
echo "   - Routes sudah di-register\n";
echo "   - Controller sudah dibuat\n\n";

echo "📱 Happy Testing! 🚀\n";
?>