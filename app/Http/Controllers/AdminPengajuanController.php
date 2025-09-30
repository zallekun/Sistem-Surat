<?php
// app/Http/Controllers/AdminPengajuanController.php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\Prodi;
use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminPengajuanController extends Controller
{
    /**
     * Display listing of all pengajuan
     */
    public function index(Request $request)
    {
        $query = PengajuanSurat::with(['mahasiswa', 'prodi', 'jenisSurat']);
        
        // Filter by search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_token', 'like', "%{$request->search}%")
                  ->orWhereHas('mahasiswa', function($mq) use ($request) {
                      $mq->where('nim', 'like', "%{$request->search}%")
                         ->orWhere('nama', 'like', "%{$request->search}%");
                  });
            });
        }
        
        // Filter by prodi
        if ($request->prodi_id) {
            $query->where('prodi_id', $request->prodi_id);
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by jenis surat
        if ($request->jenis_surat_id) {
            $query->where('jenis_surat_id', $request->jenis_surat_id);
        }
        
        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filter stuck (optional checkbox)
        if ($request->show_stuck) {
            $query->whereIn('status', ['approved_prodi', 'approved_fakultas'])
                  ->where('updated_at', '<', now()->subDays(3));
        }
        
        // Include trashed if requested
        if ($request->show_deleted) {
            $query->withTrashed();
        }
        
        $pengajuans = $query->latest()->paginate(20);
        
        // Data for filters
        $prodis = Prodi::all();
        $jenisSurat = JenisSurat::all();
        
        // Status options
        $statuses = [
            'pending' => 'Pending',
            'approved_prodi' => 'Approved Prodi',
            'approved_fakultas' => 'Approved Fakultas',
            'completed' => 'Completed',
            'rejected_prodi' => 'Rejected Prodi',
            'rejected_fakultas' => 'Rejected Fakultas',
        ];
        
        return view('admin.pengajuan.index', compact(
            'pengajuans',
            'prodis',
            'jenisSurat',
            'statuses'
        ));
    }
    
    /**
     * Display single pengajuan detail
     */
    public function show($id)
    {
        $pengajuan = PengajuanSurat::withTrashed()
            ->with([
                'mahasiswa',
                'prodi.fakultas',
                'jenisSurat',
                'approvalHistories.performedBy',
                'trackingHistory'
            ])
            ->findOrFail($id);
        
        $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
        
        // Check if stuck
        $isStuck = in_array($pengajuan->status, ['approved_prodi', 'approved_fakultas']) 
                   && $pengajuan->updated_at->lt(now()->subDays(3));
        
        $stuckDays = $isStuck ? $pengajuan->updated_at->diffInDays(now()) : 0;
        
        return view('admin.pengajuan.show', compact(
            'pengajuan',
            'additionalData',
            'isStuck',
            'stuckDays'
        ));
    }
    
    /**
     * Soft delete pengajuan
     */
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        $pengajuan = PengajuanSurat::findOrFail($id);
        
        try {
            // Soft delete
            $pengajuan->delete();
            
            // Log the action (will implement audit trail later)
            Log::info('Admin deleted pengajuan', [
                'pengajuan_id' => $id,
                'admin_id' => Auth::id(),
                'reason' => $request->reason,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil dihapus',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting pengajuan', [
                'error' => $e->getMessage(),
                'pengajuan_id' => $id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengajuan',
            ], 500);
        }
    }
    
    /**
     * Restore soft deleted pengajuan
     */
    public function restore($id)
    {
        $pengajuan = PengajuanSurat::withTrashed()->findOrFail($id);
        
        try {
            $pengajuan->restore();
            
            Log::info('Admin restored pengajuan', [
                'pengajuan_id' => $id,
                'admin_id' => Auth::id(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil dipulihkan',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error restoring pengajuan', [
                'error' => $e->getMessage(),
                'pengajuan_id' => $id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan pengajuan',
            ], 500);
        }
    }
    
    /**
     * Parse additional data JSON
     */
    private function parseAdditionalData($data)
    {
        if (empty($data)) {
            return null;
        }
        
        if (is_array($data)) {
            return $data;
        }
        
        if (is_string($data)) {
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                Log::error('Error parsing additional_data', ['error' => $e->getMessage()]);
            }
            return ['data' => $data];
        }
        
        if (is_object($data)) {
            return (array) $data;
        }
        
        return null;
    }
}