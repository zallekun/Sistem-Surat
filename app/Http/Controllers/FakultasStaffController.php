<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FakultasStaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:staff_fakultas'); // Updated to use check.role middleware
    }

    /**
     * Display the specified surat with detailed debugging
     */
    public function show($id)
    {
        // Add debugging
        Log::info('FakultasStaffController::show called', [
            'id' => $id,
            'url' => request()->fullUrl(),
            'method' => request()->method()
        ]);
        
        try {
            $surat = Surat::findOrFail($id);
            Log::info('Surat found', ['surat_id' => $surat->id]);
            
        } catch (\Exception $e) {
            Log::error('Surat not found', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('fakultas.surat.index')
                           ->with('error', 'Surat tidak ditemukan');
        }
        
        $user = Auth::user();
        
        // Load user role relationship
        $user->load('role', 'prodi.fakultas');
        
        Log::info('Current user', [
            'user_id' => $user->id,
            'role_name' => $user->role->nama_role ?? 'N/A', // Use nama_role
            'role_id' => $user->role_id
        ]);
        
        // Check if user has access to this surat's fakultas
        $fakultasId = null;
        
        if ($user->prodi && $user->prodi->fakultas_id) {
            $fakultasId = $user->prodi->fakultas_id;
            Log::info('User fakultas', ['fakultas_id' => $fakultasId]);
        } else {
            Log::warning('User has no fakultas access', ['user_id' => $user->id]);
        }
        
        if (!$fakultasId) {
            return redirect()->route('fakultas.surat.index')
                           ->with('error', 'Anda tidak memiliki akses ke fakultas manapun');
        }
        
        // Check surat fakultas access
        try {
            $surat->load('prodi.fakultas');
            
            if ($surat->prodi->fakultas_id !== $fakultasId) {
                Log::warning('Access denied - different fakultas', [
                    'user_fakultas' => $fakultasId,
                    'surat_fakultas' => $surat->prodi->fakultas_id
                ]);
                
                return redirect()->route('fakultas.surat.index')
                               ->with('error', 'Anda tidak memiliki akses ke surat ini');
            }
        } catch (\Exception $e) {
            Log::error('Error checking fakultas access', ['error' => $e->getMessage()]);
            return redirect()->route('fakultas.surat.index')
                           ->with('error', 'Error mengakses data surat');
        }
        
        // Load relationships
        try {
            $surat->load([
                'jenisSurat', 
                'currentStatus', 
                'createdBy.jabatan', 
                'tujuanJabatan', 
                'prodi.fakultas',
                'statusHistories.user',
                'statusHistories.status'
            ]);
            
            Log::info('Surat relationships loaded successfully');
            
        } catch (\Exception $e) {
            Log::error('Error loading surat relationships', ['error' => $e->getMessage()]);
            // Continue without some relationships
        }
        
        // Debug view path
        $viewPath = 'fakultas.surat.show';
        Log::info('Attempting to load view', ['view' => $viewPath]);
        
        // Check if view exists
        if (!view()->exists($viewPath)) {
            Log::error('View does not exist', ['view' => $viewPath]);
            
            // Try to find alternative views
            $alternativeViews = [
                'fakultas.surat.detail',
                'fakultas.show',
                'surat.show'
            ];
            
            foreach ($alternativeViews as $altView) {
                if (view()->exists($altView)) {
                    Log::info('Alternative view found', ['view' => $altView]);
                    return view($altView, compact('surat'));
                }
            }
            
            // If no view found, return error
            return redirect()->route('fakultas.surat.index')
                           ->with('error', 'View template tidak ditemukan');
        }
        
        Log::info('Returning view successfully');
        return view($viewPath, compact('surat'));
    }

    /**
     * Display a listing of surats for fakultas staff
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->load('prodi.fakultas', 'role');
        
        // Get fakultas through user's prodi relationship
        $fakultasId = null;
        if ($user->prodi && $user->prodi->fakultas_id) {
            $fakultasId = $user->prodi->fakultas_id;
        }
        
        if (!$fakultasId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke fakultas manapun');
        }
        
        // Get surats for this fakultas that are approved by kaprodi
        $query = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan', 'prodi.fakultas'])
                      ->whereHas('prodi', function($q) use ($fakultasId) {
                          $q->where('fakultas_id', $fakultasId);
                      })
                      ->whereHas('currentStatus', function($q) {
                          $q->where('kode_status', 'disetujui_kaprodi');
                      });
        
        // Add search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('perihal', 'like', '%' . $request->search . '%')
                  ->orWhere('nomor_surat', 'like', '%' . $request->search . '%');
            });
        }
        
        // Add prodi filter
        if ($request->has('prodi_id') && $request->prodi_id) {
            $query->where('prodi_id', $request->prodi_id);
        }
        
        // Add date filter
        if ($request->has('tanggal_dari') && $request->tanggal_dari) {
            $query->where('created_at', '>=', $request->tanggal_dari);
        }
        
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai) {
            $query->where('created_at', '<=', $request->tanggal_sampai . ' 23:59:59');
        }
        
        // Add status filter
        if ($request->has('status_id') && $request->status_id) {
            $query->where('status_id', $request->status_id);
        }
        
        $surats = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get filter options
        $prodis = \App\Models\Prodi::where('fakultas_id', $fakultasId)->get();
        $statuses = StatusSurat::whereIn('kode_status', [
            'disetujui_kaprodi', 
            'diproses_fakultas', 
            'disetujui_fakultas',
            'ditolak_fakultas'
        ])->get();
        
        return view('fakultas.surat.index', compact('surats', 'prodis', 'statuses'));
    }

    /**
     * Update surat status (process, approve, or reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);
        $user = Auth::user();
        $user->load('prodi.fakultas');
        
        // Check access
        $fakultasId = $user->prodi ? $user->prodi->fakultas_id : null;
        if (!$fakultasId || $surat->prodi->fakultas_id !== $fakultasId) {
            return redirect()->back()->with('error', 'Akses ditolak');
        }
        
        $request->validate([
            'status' => 'required|in:diproses_fakultas,disetujui_fakultas,ditolak_fakultas',
            'catatan' => 'nullable|string|max:1000'
        ]);
        
        // Get status
        $status = StatusSurat::where('kode_status', $request->status)->first();
        
        if (!$status) {
            return redirect()->back()->with('error', 'Status tidak valid');
        }
        
        DB::beginTransaction();
        try {
            // Update surat
            $surat->update([
                'status_id' => $status->id,
                'updated_by' => $user->id
            ]);
            
            // Add to status history if model exists
            if (class_exists('App\Models\StatusHistory')) {
                StatusHistory::create([
                    'surat_id' => $surat->id,
                    'status_id' => $status->id,
                    'user_id' => $user->id,
                    'keterangan' => $request->catatan
                ]);
            }
            
            // Log the action
            Log::info('Surat status updated by fakultas staff', [
                'surat_id' => $surat->id,
                'new_status' => $request->status,
                'user_id' => $user->id,
                'catatan' => $request->catatan
            ]);
            
            DB::commit();
            
            $message = match($request->status) {
                'diproses_fakultas' => 'Surat sedang diproses fakultas',
                'disetujui_fakultas' => 'Surat telah disetujui fakultas',
                'ditolak_fakultas' => 'Surat ditolak fakultas',
                default => 'Status surat diperbarui'
            };
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update surat status', [
                'surat_id' => $surat->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Gagal mengupdate status: ' . $e->getMessage());
        }
    }

    /**
     * Approve surat and forward to destination
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $user->load('prodi.fakultas');
        $surat = Surat::findOrFail($id);
        
        // Check access
        $fakultasId = $user->prodi ? $user->prodi->fakultas_id : null;
        if (!$fakultasId || $surat->prodi->fakultas_id !== $fakultasId) {
            return redirect()->back()->with('error', 'Akses ditolak');
        }
        
        // Get approved status
        $approvedStatus = StatusSurat::where('kode_status', 'disetujui_fakultas')->first();
        
        if (!$approvedStatus) {
            return redirect()->back()->with('error', 'Status persetujuan tidak ditemukan');
        }
        
        DB::beginTransaction();
        try {
            // Update surat status
            $surat->update([
                'status_id' => $approvedStatus->id,
                'updated_by' => $user->id
            ]);
            
            // Add to status history if model exists
            if (class_exists('App\Models\StatusHistory')) {
                StatusHistory::create([
                    'surat_id' => $surat->id,
                    'status_id' => $approvedStatus->id,
                    'user_id' => $user->id,
                    'keterangan' => 'Surat disetujui dan diteruskan ke ' . ($surat->tujuanJabatan->nama_jabatan ?? 'tujuan')
                ]);
            }
            
            // Log the action
            Log::info('Surat approved by fakultas staff', [
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'tujuan' => $surat->tujuanJabatan->nama_jabatan ?? 'N/A'
            ]);
            
            DB::commit();
            
            return redirect()->route('fakultas.surat.index')
                           ->with('success', 'Surat berhasil disetujui dan diteruskan ke ' . ($surat->tujuanJabatan->nama_jabatan ?? 'tujuan'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve surat', [
                'surat_id' => $surat->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Gagal menyetujui surat: ' . $e->getMessage());
        }
    }

    /**
     * Reject surat with reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'required|string|max:1000'
        ]);
        
        $user = Auth::user();
        $user->load('prodi.fakultas');
        $surat = Surat::findOrFail($id);
        
        // Check access
        $fakultasId = $user->prodi ? $user->prodi->fakultas_id : null;
        if (!$fakultasId || $surat->prodi->fakultas_id !== $fakultasId) {
            return redirect()->back()->with('error', 'Akses ditolak');
        }
        
        // Get rejected status
        $rejectedStatus = StatusSurat::where('kode_status', 'ditolak_fakultas')->first();
        
        if (!$rejectedStatus) {
            return redirect()->back()->with('error', 'Status penolakan tidak ditemukan');
        }
        
        DB::beginTransaction();
        try {
            // Update surat status
            $surat->update([
                'status_id' => $rejectedStatus->id,
                'updated_by' => $user->id
            ]);
            
            // Add to status history if model exists
            if (class_exists('App\Models\StatusHistory')) {
                StatusHistory::create([
                    'surat_id' => $surat->id,
                    'status_id' => $rejectedStatus->id,
                    'user_id' => $user->id,
                    'keterangan' => $request->keterangan
                ]);
            }
            
            // Log the action
            Log::info('Surat rejected by fakultas staff', [
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'reason' => $request->keterangan
            ]);
            
            DB::commit();
            
            return redirect()->route('fakultas.surat.index')
                           ->with('success', 'Surat telah ditolak dengan alasan: ' . $request->keterangan);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject surat', [
                'surat_id' => $surat->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Gagal menolak surat: ' . $e->getMessage());
        }
    }
}