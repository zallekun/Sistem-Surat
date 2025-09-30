<?php
// app/Http/Controllers/AdminPengajuanController.php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\Prodi;
use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\AuditTrail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengajuanExport;

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

    /**
     * Force complete pengajuan yang stuck
     */
    public function forceComplete(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        $pengajuan = PengajuanSurat::withTrashed()->findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            $oldData = [
                'status' => $pengajuan->status,
                'completed_at' => $pengajuan->completed_at,
                'completed_by' => $pengajuan->completed_by,
            ];
            
            // Force complete
            $pengajuan->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => Auth::id(),
            ]);
            
            // Log to approval_histories
            $pengajuan->logApproval(
                action: 'admin_force_complete',
                notes: 'Admin force complete: ' . $request->reason
            );
            
            // Log to audit trail
            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'force_complete',
                'model_type' => PengajuanSurat::class,
                'model_id' => $pengajuan->id,
                'old_data' => $oldData,
                'new_data' => [
                    'status' => 'completed',
                    'completed_at' => $pengajuan->completed_at,
                    'completed_by' => $pengajuan->completed_by,
                ],
                'reason' => $request->reason,
                'ip_address' => $request->ip(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil di-force complete',
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error force completing pengajuan', [
                'error' => $e->getMessage(),
                'pengajuan_id' => $id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal force complete pengajuan',
            ], 500);
        }
    }

    /**
     * Reopen pengajuan yang rejected
     */
    public function reopen(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'reset_to' => 'required|in:pending,approved_prodi',
        ]);
        
        $pengajuan = PengajuanSurat::withTrashed()->findOrFail($id);
        
        // Cek apakah bisa direopen
        if (!in_array($pengajuan->status, ['rejected_prodi', 'rejected_fakultas'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan yang rejected yang bisa direopen',
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            $oldData = [
                'status' => $pengajuan->status,
                'rejected_by_prodi' => $pengajuan->rejected_by_prodi,
                'rejected_at_prodi' => $pengajuan->rejected_at_prodi,
                'rejection_reason_prodi' => $pengajuan->rejection_reason_prodi,
            ];
            
            // Reset status
            $pengajuan->update([
                'status' => $request->reset_to,
                'rejected_by_prodi' => null,
                'rejected_at_prodi' => null,
                'rejection_reason_prodi' => null,
            ]);
            
            // Log to approval_histories
            $pengajuan->logApproval(
                action: 'admin_reopen',
                notes: 'Admin reopen to ' . $request->reset_to . ': ' . $request->reason
            );
            
            // Log to audit trail
            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'reopen',
                'model_type' => PengajuanSurat::class,
                'model_id' => $pengajuan->id,
                'old_data' => $oldData,
                'new_data' => [
                    'status' => $request->reset_to,
                ],
                'reason' => $request->reason,
                'ip_address' => $request->ip(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil direopen',
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error reopening pengajuan', [
                'error' => $e->getMessage(),
                'pengajuan_id' => $id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal reopen pengajuan',
            ], 500);
        }
    }

    /**
     * Change status manually
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'new_status' => 'required|in:pending,approved_prodi,approved_fakultas,completed,rejected_prodi,rejected_fakultas',
        ]);
        
        $pengajuan = PengajuanSurat::withTrashed()->findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            $oldStatus = $pengajuan->status;
            
            $pengajuan->update([
                'status' => $request->new_status,
            ]);
            
            // Log to approval_histories
            $pengajuan->logApproval(
                action: 'admin_change_status',
                notes: "Admin change status from {$oldStatus} to {$request->new_status}: {$request->reason}"
            );
            
            // Log to audit trail
            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'change_status',
                'model_type' => PengajuanSurat::class,
                'model_id' => $pengajuan->id,
                'old_data' => ['status' => $oldStatus],
                'new_data' => ['status' => $request->new_status],
                'reason' => $request->reason,
                'ip_address' => $request->ip(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error changing status', [
                'error' => $e->getMessage(),
                'pengajuan_id' => $id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status',
            ], 500);
        }
    }

    public function export(Request $request)
{
    $filters = $request->only(['search', 'prodi_id', 'status', 'jenis_surat_id', 'date_from', 'date_to']);
    
    $filename = 'pengajuan_' . date('Y-m-d_His') . '.xlsx';
    
    return Excel::download(new PengajuanExport($filters), $filename);
}
}