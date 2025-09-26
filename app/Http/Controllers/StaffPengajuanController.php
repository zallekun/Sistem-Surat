<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffPengajuanController extends Controller
{
/**
     * Display pengajuan list for staff with filters
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Authorization check
        if (!$user->hasRole(['staff_prodi', 'kaprodi'])) {
            abort(403, 'Unauthorized - Only staff prodi and kaprodi can access this page');
        }
        
        // Build query for pengajuan
        $query = PengajuanSurat::with(['prodi', 'jenisSurat']);
        
        // Filter by prodi
        if ($user->prodi_id) {
            $query->where('prodi_id', $user->prodi_id);
        }
        
        // SEARCH FILTER - Token, NIM, atau Nama
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('tracking_token', 'like', "%{$searchTerm}%")
                  ->orWhere('nim', 'like', "%{$searchTerm}%")
                  ->orWhere('nama_mahasiswa', 'like', "%{$searchTerm}%");
            });
        }
        
        // STATUS FILTER
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: Show semua status yang relevan untuk staff prodi
            $relevantStatuses = [
                'pending',               // Baru masuk, perlu review
                'processed',             // Sudah diproses staff prodi
                'approved_prodi',        // Sudah disetujui prodi
                'sedang_ditandatangani', // Sedang proses TTD di fakultas
                'completed',             // Selesai
                'rejected_prodi',        // Ditolak prodi
                'rejected_fakultas'      // Ditolak fakultas (untuk info)
            ];
            
            $query->whereIn('status', $relevantStatuses);
        }
        
        // JENIS SURAT FILTER
        if ($request->filled('jenis_surat')) {
            $query->where('jenis_surat_id', $request->jenis_surat);
        }
        
        // DATE RANGE FILTER
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // SORTING
        $allowedSortFields = ['tracking_token', 'nama_mahasiswa', 'status', 'created_at'];
        $sortField = in_array($request->get('sort'), $allowedSortFields) ? $request->get('sort') : 'created_at';
        $sortDirection = in_array($request->get('direction'), ['asc', 'desc']) ? $request->get('direction') : 'desc';
        
        $query->orderBy($sortField, $sortDirection);
        
        // Get paginated results
        $pengajuans = $query->paginate(15)->withQueryString();
        
        // Get jenis surat for dropdown
        $jenisSurat = JenisSurat::orderBy('nama_jenis')->get();
        
        // Calculate status counts for quick filters
        $baseCountQuery = PengajuanSurat::query();
        if ($user->prodi_id) {
            $baseCountQuery->where('prodi_id', $user->prodi_id);
        }
        
        $pendingCount = $baseCountQuery->clone()->where('status', 'pending')->count();
        $approvedCount = $baseCountQuery->clone()->where('status', 'approved_prodi')->count();
        $completedCount = $baseCountQuery->clone()->where('status', 'completed')->count();
        
        // Additional counts for dashboard
        $statusCounts = [
            'pending' => $pendingCount,
            'processed' => $baseCountQuery->clone()->where('status', 'processed')->count(),
            'approved_prodi' => $approvedCount,
            'sedang_ditandatangani' => $baseCountQuery->clone()->where('status', 'sedang_ditandatangani')->count(),
            'completed' => $completedCount,
            'rejected' => $baseCountQuery->clone()->whereIn('status', ['rejected_prodi', 'rejected_fakultas'])->count(),
        ];
        
        return view('staff.pengajuan.index', compact(
            'pengajuans', 
            'jenisSurat', 
            'statusCounts',
            'pendingCount',
            'approvedCount', 
            'completedCount'
        ));
    }
    
    /**
     * Show pengajuan detail
     */
    public function show($id)
    {

        
        $user = Auth::user();
        
        if (!$user->hasRole(['staff_prodi', 'kaprodi'])) {
            abort(403, 'Unauthorized');
        }
        
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat', 'trackingHistory' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        // Check if user can view this pengajuan
        if ($user->prodi_id && $pengajuan->prodi_id !== $user->prodi_id) {
            abort(403, 'Unauthorized - Pengajuan dari prodi lain');
        }
        
        // ✅ TAMBAH: Parse additional data untuk display
        $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
        
        return view('staff.pengajuan.show', compact('pengajuan', 'additionalData'));
    }
    
    /**
     * ✅ TAMBAH: Helper method untuk parse additional data
     */
    private function parseAdditionalData($data)
    {
        if (empty($data)) return [];
        if (is_array($data)) return $data;
        if (is_string($data)) {
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to parse additional_data", ['error' => $e->getMessage()]);
            }
        }
        if (is_object($data)) return (array) $data;
        return [];
    }
}