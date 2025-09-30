<?php
// app/Http/Controllers/AdminDashboardController.php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Statistics Cards
        $stats = [
            'total_pengajuan' => PengajuanSurat::count(),
            'pengajuan_pending' => PengajuanSurat::whereIn('status', ['pending', 'approved_prodi'])->count(),
            'pengajuan_completed' => PengajuanSurat::where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'pengajuan_rejected' => PengajuanSurat::whereIn('status', ['rejected_prodi', 'rejected_fakultas'])
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'pengajuan_stuck' => PengajuanSurat::whereIn('status', ['approved_prodi', 'approved_fakultas'])
                ->where('updated_at', '<', now()->subDays(3))
                ->count(),
            'total_users' => User::count(),
        ];
        
        // Average Processing Time (completed pengajuan)
        $avgProcessingTime = PengajuanSurat::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get()
            ->avg(function($pengajuan) {
                return $pengajuan->created_at->diffInHours($pengajuan->completed_at);
            });
        
        $stats['avg_processing_hours'] = round($avgProcessingTime, 1);
        
        // Chart Data: Trend 30 hari terakhir
        $trendData = PengajuanSurat::whereBetween('created_at', [now()->subDays(30), now()])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        $chartTrend = [
            'labels' => $trendData->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray(),
            'data' => $trendData->pluck('count')->toArray(),
        ];
        
        // Chart Data: Per Prodi
        $prodiData = PengajuanSurat::with('prodi')
            ->select('prodi_id', DB::raw('COUNT(*) as count'))
            ->groupBy('prodi_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        $chartProdi = [
            'labels' => $prodiData->map(fn($p) => $p->prodi->nama_prodi ?? 'N/A')->toArray(),
            'data' => $prodiData->pluck('count')->toArray(),
        ];
        
        // Chart Data: Status Distribution
        $statusData = PengajuanSurat::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
        
        $statusLabels = [
            'pending' => 'Pending',
            'approved_prodi' => 'Approved Prodi',
            'approved_fakultas' => 'Approved Fakultas',
            'completed' => 'Completed',
            'rejected_prodi' => 'Rejected Prodi',
            'rejected_fakultas' => 'Rejected Fakultas',
        ];
        
        $chartStatus = [
            'labels' => $statusData->map(fn($s) => $statusLabels[$s->status] ?? ucfirst($s->status))->toArray(),
            'data' => $statusData->pluck('count')->toArray(),
        ];
        
        // Recent Activity
        $recentPengajuan = PengajuanSurat::with(['mahasiswa', 'prodi', 'jenisSurat'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Stuck Pengajuan (alert)
        $stuckPengajuan = PengajuanSurat::with(['mahasiswa', 'prodi'])
            ->whereIn('status', ['approved_prodi', 'approved_fakultas'])
            ->where('updated_at', '<', now()->subDays(3))
            ->limit(5)
            ->get();
        
        return view('admin.dashboard', compact(
            'stats',
            'chartTrend',
            'chartProdi',
            'chartStatus',
            'recentPengajuan',
            'stuckPengajuan'
        ));
    }
}