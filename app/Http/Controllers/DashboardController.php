<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\PengajuanSurat;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $suratQuery = Surat::query();

        // Apply filtering based on user's role and associated IDs
        if ($user->hasRole('kaprodi')) {
            $reviewKaprodiStatus = StatusSurat::where('kode_status', 'review_kaprodi')->first();
            if ($reviewKaprodiStatus) {
                $approvedKaprodiStatus = StatusSurat::where('kode_status', 'disetujui_kaprodi')->first();
                $rejectedKaprodiStatus = StatusSurat::where('kode_status', 'ditolak_kaprodi')->first();
                
                $suratQuery->where('prodi_id', $user->prodi_id)
                    ->whereIn('status_id', [
                        $reviewKaprodiStatus->id, 
                        $approvedKaprodiStatus->id ?? 0, 
                        $rejectedKaprodiStatus->id ?? 0
                    ]);
            } else {
                $suratQuery->whereRaw('0 = 1');
            }
        } elseif ($user->hasRole('staff_fakultas') && $user->prodi?->fakultas_id) {
            // Staff fakultas sees surats from their fakultas
            $suratQuery->whereHas('prodi', function($q) use ($user) {
                $q->where('fakultas_id', $user->prodi->fakultas_id);
            });
        } elseif ($user->prodi_id) {
            $suratQuery->where('prodi_id', $user->prodi_id);
        }

        $stats = [
            'total_surat' => (clone $suratQuery)->count(),
            'surat_selesai' => (clone $suratQuery)->whereHas('currentStatus', fn($q) => $q->where('kode_status', 'selesai'))->count(),
            'surat_proses' => (clone $suratQuery)->whereHas('currentStatus', function ($q) {
                $q->whereNotIn('kode_status', ['selesai', 'arsip', 'draft']);
            })->count(),
            'surat_draft' => (clone $suratQuery)->whereHas('currentStatus', fn($q) => $q->where('kode_status', 'draft'))->count(),
        ];

        // STATS UNTUK STAFF PRODI - Pengajuan dari mahasiswa
        $recent_pengajuan = null;
        if ($user->hasRole('staff_prodi') && $user->prodi_id) {
            $pengajuanQuery = PengajuanSurat::where('prodi_id', $user->prodi_id);
            $stats['pengajuan_pending'] = (clone $pengajuanQuery)->where('status', 'pending')->count();
            
            $recent_pengajuan = (clone $pengajuanQuery)
                ->where('status', 'pending')
                ->with('jenisSurat')
                ->latest()
                ->take(5)
                ->get();
        }

        // STATS UNTUK STAFF FAKULTAS - Pengajuan dari prodi
        $recent_pengajuan_fakultas = null;
        if ($user->hasRole('staff_fakultas') && $user->prodi?->fakultas_id) {
            $fakultasId = $user->prodi->fakultas_id;
            
            $pengajuanFakultasQuery = PengajuanSurat::whereHas('prodi', function($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            });
            
            $stats['pengajuan_fakultas_pending'] = (clone $pengajuanFakultasQuery)
                ->whereIn('status', ['approved_prodi', 'approved_prodi_direct_fakultas'])
                ->count();
            
            $recent_pengajuan_fakultas = (clone $pengajuanFakultasQuery)
                ->whereIn('status', ['approved_prodi', 'approved_prodi_direct_fakultas'])
                ->with(['prodi', 'jenisSurat'])
                ->latest()
                ->take(5)
                ->get();
        }

        $recent_surat = (clone $suratQuery)->with('currentStatus')->latest()->take(5)->get();

        // PERBAIKAN: Gunakan path view yang benar
        return view('dashboard.index', compact(
            'stats', 
            'recent_surat', 
            'recent_pengajuan', 
            'recent_pengajuan_fakultas'
        ));
    }
}