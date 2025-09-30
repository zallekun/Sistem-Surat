#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PengajuanSurat;
use App\Models\Prodi;
use App\Models\User;
use App\Models\TrackingHistory;
use Illuminate\Support\Facades\DB;

class SuratDebugger {
    
    public function run() {
        echo "\n╔══════════════════════════════════════════════════════╗\n";
        echo "║           DEBUG SISTEM SURAT KP/TA                  ║\n";
        echo "╚══════════════════════════════════════════════════════╝\n\n";
        
        $this->checkPengajuanStatus();
        $this->checkProdiAndFakultas();
        $this->testFakultasQuery();
        $this->analyzeSpecificCase();
        $this->checkDatabaseIntegrity();
        $this->suggestFix();
    }
    
    private function checkPengajuanStatus() {
        echo "📋 STATUS PENGAJUAN KP/TA\n";
        echo str_repeat("─", 60) . "\n";
        
        $stats = PengajuanSurat::whereHas('jenisSurat', function($q) {
            $q->whereIn('kode_surat', ['KP', 'TA']);
        })
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->get();
        
        foreach($stats as $stat) {
            printf("  %-25s : %d\n", $stat->status, $stat->total);
        }
        
        // List approved_prodi
        $approvedProdi = PengajuanSurat::whereHas('jenisSurat', function($q) {
            $q->whereIn('kode_surat', ['KP', 'TA']);
        })->where('status', 'approved_prodi')->get();
        
        if ($approvedProdi->count() > 0) {
            echo "\n  ⚠️  KP/TA dengan status approved_prodi:\n";
            foreach($approvedProdi as $p) {
                echo "     - ID: {$p->id} | Token: {$p->tracking_token} | {$p->nama_mahasiswa}\n";
                echo "       Surat Pengantar: " . ($p->hasSuratPengantar() ? '✅ ADA' : '❌ TIDAK ADA') . "\n";
            }
        }
        echo "\n";
    }
    
    private function checkProdiAndFakultas() {
        echo "🏢 DATA FAKULTAS & PRODI\n";
        echo str_repeat("─", 60) . "\n";
        
        $fakultasData = DB::table('fakultas')
            ->leftJoin('prodi', 'fakultas.id', '=', 'prodi.fakultas_id')
            ->select('fakultas.id as fak_id', 'fakultas.nama_fakultas', 
                    DB::raw('COUNT(prodi.id) as jumlah_prodi'))
            ->groupBy('fakultas.id', 'fakultas.nama_fakultas')
            ->get();
        
        foreach($fakultasData as $fak) {
            echo "  Fakultas: {$fak->nama_fakultas} (ID: {$fak->fak_id})\n";
            echo "  Jumlah Prodi: {$fak->jumlah_prodi}\n\n";
        }
    }
    
    private function testFakultasQuery() {
    echo "🔍 TEST QUERY FAKULTAS\n";
    echo str_repeat("─", 60) . "\n";
    
    $fakultasId = 1;
    echo "  Testing dengan Fakultas ID: {$fakultasId}\n\n";
    
    // Query yang SEKARANG dipakai (setelah perbaikan)
    echo "  📌 Query ACTUAL (yang sekarang di controller):\n";
    $actualQuery = PengajuanSurat::whereHas('prodi', function($q) use ($fakultasId) {
        $q->where('fakultas_id', $fakultasId);
    })
    ->where(function($q) use ($fakultasId) {
        // MA: approved_prodi → langsung fakultas
        $q->where(function($subQ) {
            $subQ->where('status', 'approved_prodi')
                 ->whereHas('jenisSurat', function($js) {
                     $js->where('kode_surat', 'MA');
                 });
        })
        // KP/TA: HANYA yang sudah ada surat pengantar
        ->orWhere(function($subQ) {
            $subQ->where('status', 'pengantar_generated')
                 ->whereHas('jenisSurat', function($js) {
                     $js->whereIn('kode_surat', ['KP', 'TA']);
                 });
        })
        // Status lanjutan yang sudah di fakultas
        ->orWhereIn('status', ['sedang_ditandatangani', 'completed', 'rejected_fakultas']);
    });
    
    $actualResults = $actualQuery->get();
    echo "     Total: {$actualResults->count()} pengajuan\n";
    
    // Check apakah ada KP approved_prodi
    $kpApproved = $actualResults->filter(function($item) {
        return $item->jenisSurat->kode_surat === 'KP' && $item->status === 'approved_prodi';
    });
    
    if ($kpApproved->count() > 0) {
        echo "     ⚠️  MASIH ADA {$kpApproved->count()} KP dengan approved_prodi!\n";
        foreach($kpApproved as $kp) {
            echo "        - ID: {$kp->id} | {$kp->nama_mahasiswa}\n";
        }
    } else {
        echo "     ✅ Tidak ada KP approved_prodi (BENAR)\n";
    }
    
    echo "\n";
}
    
    private function analyzeSpecificCase() {
    echo "📊 ANALISIS KASUS SPESIFIK\n";
    echo str_repeat("─", 60) . "\n";
    
    $kpApproved = PengajuanSurat::whereHas('jenisSurat', function($q) {
        $q->where('kode_surat', 'KP');
    })->where('status', 'approved_prodi')->first();
    
    if ($kpApproved) {
        echo "  Pengajuan KP ID: {$kpApproved->id}\n";
        echo "  Token: {$kpApproved->tracking_token}\n";
        echo "  Mahasiswa: {$kpApproved->nama_mahasiswa}\n";
        echo "  Status: {$kpApproved->status}\n";
        echo "  Prodi: {$kpApproved->prodi->nama_prodi}\n";
        echo "  Fakultas ID: {$kpApproved->prodi->fakultas_id}\n";
        echo "  Needs Surat Pengantar: " . ($kpApproved->needsSuratPengantar() ? 'YES' : 'NO') . "\n";
        echo "  Has Surat Pengantar: " . ($kpApproved->hasSuratPengantar() ? 'YES' : 'NO') . "\n";
        
        // Test dengan query yang BENAR
        $fakultasId = $kpApproved->prodi->fakultas_id;
        $appearsInFakultas = PengajuanSurat::whereHas('prodi', function($q) use ($fakultasId) {
            $q->where('fakultas_id', $fakultasId);
        })
        ->where(function($q) use ($fakultasId) {
            $q->where(function($subQ) {
                $subQ->where('status', 'approved_prodi')
                     ->whereHas('jenisSurat', function($js) {
                         $js->where('kode_surat', 'MA');
                     });
            })
            ->orWhere(function($subQ) {
                $subQ->where('status', 'pengantar_generated')
                     ->whereHas('jenisSurat', function($js) {
                         $js->whereIn('kode_surat', ['KP', 'TA']);
                     });
            })
            ->orWhereIn('status', ['sedang_ditandatangani', 'completed', 'rejected_fakultas']);
        })
        ->where('id', $kpApproved->id)
        ->exists();
        
        echo "  Muncul di Fakultas: " . ($appearsInFakultas ? '⚠️ YES (HARUSNYA TIDAK!)' : '✅ NO (BENAR)') . "\n";
    }
    echo "\n";
}
    
private function checkDatabaseIntegrity() {
    echo "🔧 CEK INTEGRITAS DATABASE\n";
    echo str_repeat("─", 60) . "\n";
    
    // Skip query yang error, cek yang lain saja
    $invalidStatus = PengajuanSurat::whereNotIn('status', [
        'pending', 'approved_prodi', 'pengantar_generated', 
        'processed', 'sedang_ditandatangani', 'completed',
        'rejected_prodi', 'rejected_fakultas'
    ])->count();
    
    echo "  Pengajuan dengan status invalid: {$invalidStatus}\n";
    
    // Cek konsistensi status
    $kpWithWrongStatus = PengajuanSurat::whereHas('jenisSurat', function($q) {
        $q->whereIn('kode_surat', ['KP', 'TA']);
    })
    ->where('status', 'approved_prodi')
    ->whereNotNull('surat_pengantar_url')
    ->count();
    
    echo "  KP/TA yang harusnya pengantar_generated: {$kpWithWrongStatus}\n";
    echo "\n";
}
    
    private function suggestFix() {
        echo "💡 REKOMENDASI PERBAIKAN\n";
        echo str_repeat("─", 60) . "\n";
        
        $needsFix = PengajuanSurat::whereHas('jenisSurat', function($q) {
            $q->whereIn('kode_surat', ['KP', 'TA']);
        })->where('status', 'approved_prodi')->count();
        
        if ($needsFix > 0) {
            echo "  ⚠️  Ada {$needsFix} KP/TA yang perlu diperbaiki\n\n";
            echo "  Masalah utama:\n";
            echo "  1. Query fakultas menggunakan orWhereIn yang menangkap semua status\n";
            echo "  2. KP/TA dengan status approved_prodi langsung masuk fakultas\n\n";
            echo "  Solusi:\n";
            echo "  1. Perbaiki query di FakultasStaffController\n";
            echo "  2. Hapus orWhereIn status umum\n";
            echo "  3. Pastikan KP/TA hanya masuk fakultas jika status = pengantar_generated\n\n";
            echo "  Script fix (jalankan di tinker):\n";
            echo "  // Tidak ada yang perlu direset, cukup perbaiki query controller\n";
        } else {
            echo "  ✅ Tidak ada masalah status yang perlu diperbaiki\n";
        }
        
        echo "\n";
    }
}

// Run debugger
$debugger = new SuratDebugger();
$debugger->run();

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║                  DEBUG SELESAI                      ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";