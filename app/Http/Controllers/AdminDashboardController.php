<?php
// app/Http/Controllers/AdminDashboardController.php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AuditTrail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AuditTrailExport;

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

    public function auditTrail(Request $request)
{
    $query = AuditTrail::with('user');
    
    if ($request->search) {
        $query->where(function($q) use ($request) {
            $q->where('reason', 'like', "%{$request->search}%")
              ->orWhere('model_id', $request->search);
        });
    }
    
    if ($request->action) {
        $query->where('action', $request->action);
    }
    
    if ($request->user_id) {
        $query->where('user_id', $request->user_id);
    }
    
    if ($request->date) {
        $query->whereDate('created_at', $request->date);
    }
    
    $logs = $query->latest()->paginate(20);
    $users = User::whereHas('roles', function($q) {
        $q->where('name', 'admin');
    })->get();
    
    return view('admin.audit-trail.index', compact('logs', 'users'));
}

public function auditTrailShow($id)
{
    $log = AuditTrail::with('user')->findOrFail($id);
    
    return response()->json([
        'action' => ucfirst(str_replace('_', ' ', $log->action)),
        'user_name' => $log->user->nama ?? 'Unknown',
        'created_at' => $log->created_at->format('d F Y, H:i:s'),
        'ip_address' => $log->ip_address,
        'reason' => $log->reason,
        'old_data' => $log->old_data,
        'new_data' => $log->new_data,
    ]);
}

public function auditTrailExport(Request $request)
{
    $filters = $request->only(['action', 'user_id', 'date']);
    
    $filename = 'audit_trail_' . date('Y-m-d_His') . '.xlsx';
    
    return Excel::download(new AuditTrailExport($filters), $filename);
}
}