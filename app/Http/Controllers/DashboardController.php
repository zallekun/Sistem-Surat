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
        
        // ✅ REDIRECT ADMIN KE ADMIN DASHBOARD
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        // Stats untuk staff prodi/fakultas
        $stats = [];
        
        if ($user->hasRole('staff_fakultas')) {
            $fakultasId = $user->prodi?->fakultas_id;
            
            $stats = [
                'total_surat' => PengajuanSurat::whereHas('prodi', function($q) use ($fakultasId) {
                    $q->where('fakultas_id', $fakultasId);
                })->count(),
                'surat_selesai' => PengajuanSurat::whereHas('prodi', function($q) use ($fakultasId) {
                    $q->where('fakultas_id', $fakultasId);
                })->where('status', 'completed')->count(),
                'surat_proses' => PengajuanSurat::whereHas('prodi', function($q) use ($fakultasId) {
                    $q->where('fakultas_id', $fakultasId);
                })->whereIn('status', ['approved_prodi', 'approved_fakultas'])->count(),
                'surat_draft' => 0,
            ];
        } 
        elseif ($user->hasRole(['staff_prodi', 'kaprodi'])) {
            $stats = [
                'total_surat' => PengajuanSurat::where('prodi_id', $user->prodi_id)->count(),
                'surat_selesai' => PengajuanSurat::where('prodi_id', $user->prodi_id)
                    ->where('status', 'completed')->count(),
                'surat_proses' => PengajuanSurat::where('prodi_id', $user->prodi_id)
                    ->whereIn('status', ['pending', 'approved_prodi'])->count(),
                'surat_draft' => 0,
                'pengajuan_pending' => PengajuanSurat::where('prodi_id', $user->prodi_id)
                    ->where('status', 'pending')->count(),
            ];
            
            // Recent pengajuan untuk staff prodi
            $recent_pengajuan = PengajuanSurat::where('prodi_id', $user->prodi_id)
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get();
        }
        else {
            // Default stats untuk role lain
            $stats = [
                'total_surat' => 0,
                'surat_selesai' => 0,
                'surat_proses' => 0,
                'surat_draft' => 0,
            ];
        }
        
        return view('dashboard.index', compact('stats', 'recent_pengajuan'));
    }
}