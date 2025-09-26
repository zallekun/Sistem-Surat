<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffPengajuanController extends Controller
{
    /**
     * Display pengajuan list for staff
     */
    public function index()
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
        
        // Filter by status
        $status = request('status');
        if ($status) {
            $query->where('status', $status);
        } else {
            // ✅ PERBAIKAN: Show semua status yang relevan untuk staff prodi
            $relevantStatuses = [
                'pending',              // Baru masuk, perlu review
                'processed',            // Sudah diproses staff prodi
                'approved_prodi',       // Sudah disetujui prodi
                'sedang_ditandatangani', // Sedang proses TTD di fakultas
                'completed',            // Selesai
                'rejected',             // Ditolak (untuk review)
                'rejected_fakultas'     // Ditolak fakultas (untuk info)
            ];
            
            $query->whereIn('status', $relevantStatuses);
        }
        
        $pengajuans = $query->orderBy('created_at', 'desc')->paginate(10);
        $jenisSurats = JenisSurat::all();
        
        // ✅ TAMBAH: Hitung statistik status untuk dashboard info
        $statusCounts = [];
        if ($user->prodi_id) {
            $baseQuery = PengajuanSurat::where('prodi_id', $user->prodi_id);
            $statusCounts = [
                'pending' => $baseQuery->clone()->where('status', 'pending')->count(),
                'processed' => $baseQuery->clone()->where('status', 'processed')->count(),
                'approved_prodi' => $baseQuery->clone()->where('status', 'approved_prodi')->count(),
                'sedang_ditandatangani' => $baseQuery->clone()->where('status', 'sedang_ditandatangani')->count(),
                'completed' => $baseQuery->clone()->where('status', 'completed')->count(),
                'rejected' => $baseQuery->clone()->whereIn('status', ['rejected', 'rejected_fakultas'])->count(),
            ];
        }
        
        return view('staff.pengajuan.index', compact('pengajuans', 'jenisSurats', 'statusCounts'));
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