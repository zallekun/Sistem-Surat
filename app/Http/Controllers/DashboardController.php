<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Surat;
use App\Models\StatusSurat;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $suratQuery = Surat::query();

        // Apply filtering based on user's role and associated IDs
        if ($user->hasRole('kaprodi')) {
            // Kaprodi should only see surats for their prodi that are in 'review_kaprodi' status
            $reviewKaprodiStatus = StatusSurat::where('kode_status', 'review_kaprodi')->first();
            if ($reviewKaprodiStatus) {
                $approvedKaprodiStatus = StatusSurat::where('kode_status', 'disetujui_kaprodi')->first();
            $rejectedKaprodiStatus = StatusSurat::where('kode_status', 'ditolak_kaprodi')->first();
            $approvedKaprodiStatus = StatusSurat::where('kode_status', 'disetujui_kaprodi')->first();
            $rejectedKaprodiStatus = StatusSurat::where('kode_status', 'ditolak_kaprodi')->first();
            $suratQuery->where('prodi_id', $user->prodi_id)
            
            ->whereIn('status_id', [$reviewKaprodiStatus->id, $approvedKaprodiStatus->id ?? 0, $rejectedKaprodiStatus->id ?? 0]);
            } else {
                // If 'review_kaprodi' status not found, show no surats
                $suratQuery->whereRaw('0 = 1');
            }
        } elseif ($user->prodi_id) {
            // Generic filter for users with prodi_id (e.g., staff_prodi)
            $suratQuery->where('prodi_id', $user->prodi_id);
        } elseif ($user->hasRole('staff_fakultas') && $user->fakultas_id) {
            // If user is staff_fakultas and has a fakultas_id, filter by fakultas
            $suratQuery->where('fakultas_id', $user->fakultas_id);
        }

        $stats = [
            'total_surat' => (clone $suratQuery)->count(),
            'surat_selesai' => (clone $suratQuery)->whereHas('currentStatus', fn($q) => $q->where('kode_status', 'selesai'))->count(),
            'surat_proses' => (clone $suratQuery)->whereHas('currentStatus', function ($q) {
                $q->whereNotIn('kode_status', ['selesai', 'arsip', 'draft']);
            })->count(),
            'surat_draft' => (clone $suratQuery)->whereHas('currentStatus', fn($q) => $q->where('kode_status', 'draft'))->count(),
        ];

    // ADD PENGAJUAN STATS - perbaikan
$pengajuan_stats = ['pending' => 0, 'processed' => 0];
if ($user->hasRole('staff_prodi') && $user->prodi_id) {
    $pengajuanQuery = \App\Models\PengajuanSurat::where('prodi_id', $user->prodi_id);
    $pengajuan_stats['pending'] = (clone $pengajuanQuery)->where('status', 'pending')->count();
    $pengajuan_stats['processed'] = (clone $pengajuanQuery)->where('status', 'processed')->count();
    $stats['pengajuan_pending'] = $pengajuan_stats['pending']; // backward compatibility
}

        $recent_surat = (clone $suratQuery)->with('currentStatus')->latest()->take(5)->get();

        // GET RECENT PENGAJUAN FOR STAFF
    $recent_pengajuan = null;
    if ($user->hasRole('staff_prodi') && $user->prodi_id) {
        $recent_pengajuan = \App\Models\PengajuanSurat::where('prodi_id', $user->prodi_id)
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
    }

        return view('dashboard.index', compact('stats', 'recent_surat', 'recent_pengajuan', 'pengajuan_stats'));
    }

    // Other dashboard methods can be added here if needed for other roles.
}