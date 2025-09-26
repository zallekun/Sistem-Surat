<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\Log;
use App\Models\Surat;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\JenisSurat;
use App\Models\StatusSurat;
use App\Models\Jabatan;
use App\Models\Tracking;
use App\Models\PengajuanSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SuratController extends Controller
{
    /**
     * Display a listing of surat for staff/kaprodi
     */
    public function index()
    {
        return $this->staffIndex();
    }

    /**
     * Display surat index for staff and kaprodi
     */
    public function staffIndex()
    {
        $user = Auth::user();
        $user->load('role', 'prodi');
        
        // Allow staff_prodi and kaprodi
        if (!$user->hasRole('staff_prodi') && !$user->hasRole('kaprodi')) {
            abort(403, 'Unauthorized - Only staff prodi and kaprodi can access this page');
        }
        
        // Build query
        $query = Surat::with(['jenisSurat', 'currentStatus', 'tujuanJabatan', 'prodi']);
        
        if ($user->prodi_id) {
            if ($user->hasRole('kaprodi')) {
                // Kaprodi sees all surat from their prodi
                $query->where('prodi_id', $user->prodi_id);
            } else {
                // Staff sees surat they created or from their prodi
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('prodi_id', $user->prodi_id);
                });
            }
        }
        
        $surats = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get filter data
        $allStatuses = StatusSurat::all();
        $draftStatusId = StatusSurat::where('kode_status', 'draft')->first()->id ?? null;
        $needsRevisionStatusId = StatusSurat::where('kode_status', 'ditolak_umum')->first()->id ?? null;
        $jenis = JenisSurat::all();
        
        return view('staff.surat.index', compact('surats', 'allStatuses', 'draftStatusId', 'needsRevisionStatusId', 'jenis'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('staff.surat.create');
    }

    /**
     * Show surat detail
     */
    public function show($id)
    {
        $user = Auth::user();
        $user->load('role', 'jabatan', 'prodi');
        
        $surat = Surat::with([
            'jenisSurat', 
            'currentStatus', 
            'createdBy.jabatan',
            'createdBy.prodi',
            'tujuanJabatan',
            'prodi',
            'tracking'
        ])->findOrFail($id);
        
        // Authorization check
        $canView = false;
        
        if ($user->hasRole(['admin', 'super_admin'])) {
            $canView = true;
        } elseif ($user->hasRole('staff_prodi')) {
            $canView = ($surat->created_by === $user->id || $surat->prodi_id === $user->prodi_id);
        } elseif ($user->hasRole('kaprodi')) {
            $canView = ($surat->prodi_id === $user->prodi_id);
        } elseif ($user->hasRole('staff_fakultas')) {
            $canView = ($surat->fakultas_id === $user->fakultas_id);
        } elseif ($user->jabatan) {
            $allowedJabatans = ['Dekan', 'Wakil Dekan Bidang Akademik', 'Wakil Dekan Bidang Keuangan', 
                               'Wakil Dekan Bidang Kemahasiswaan', 'Kepala Bagian TU'];
            $canView = in_array($user->jabatan->nama_jabatan, $allowedJabatans);
        }
        
        if (!$canView) {
            abort(403, 'Anda tidak memiliki akses untuk melihat surat ini.');
        }
        
        $allStatuses = StatusSurat::orderBy('urutan')->get();
        $draftStatusId = StatusSurat::where('kode_status', 'draft')->first()->id ?? null;
        $needsRevisionStatusId = StatusSurat::where('kode_status', 'needs_revision')->first()->id ?? null;
        
        return view('staff.surat.show', compact('surat', 'allStatuses', 'draftStatusId', 'needsRevisionStatusId'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $surat = Surat::findOrFail($id);
        $user = Auth::user()->load('jabatan', 'prodi.fakultas');
        
        $prodis = Prodi::all();
        $fakultas = Fakultas::all();
        $sifatSuratOptions = ['Biasa', 'Segera', 'Rahasia'];
        $tujuanJabatanOptions = Jabatan::whereIn('nama_jabatan', [
            'Dekan', 'Wakil Dekan Bidang Akademik', 'Wakil Dekan Bidang Keuangan', 
            'Wakil Dekan Bidang Kemahasiswaan', 'Kepala Bagian TU'
        ])->get();

        $selectedProdiId = null;
        $selectedFakultasId = null;

        if ($user->jabatan?->nama_jabatan === 'Staff Program Studi' && $user->prodi) {
            $selectedProdiId = $user->prodi->id;
            $selectedFakultasId = $user->prodi->fakultas_id;
        } elseif ($user->jabatan?->nama_jabatan === 'Staff Fakultas' && $user->prodi?->fakultas) {
            $selectedFakultasId = $user->prodi->fakultas->id;
        }

        return view('staff.surat.edit', compact('surat', 'prodis', 'fakultas', 'sifatSuratOptions', 
                                                'tujuanJabatanOptions', 'selectedProdiId', 'selectedFakultasId'));
    }

    /**
     * Update surat
     */
    public function update(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);
        $user = Auth::user();

        $request->validate([
            'perihal' => 'required|string|max:255',
            'tujuan_jabatan_id' => 'required|exists:jabatan,id',
            'lampiran' => 'nullable|string|max:255',
            'prodi_id' => 'required|exists:prodi,id',
            'fakultas_id' => 'required|exists:fakultas,id',
            'tanggal_surat' => 'required|date',
            'sifat_surat' => 'required|in:Biasa,Segera,Rahasia',
            'file_surat' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $filePath = $surat->file_surat;
            if ($request->hasFile('file_surat')) {
                if ($filePath) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $request->file('file_surat')->store('surat_pdfs', 'public');
            }

            $nomorSurat = $surat->nomor_surat;
            $ditolakUmumStatus = StatusSurat::where('kode_status', 'ditolak_umum')->first();

            if (!$ditolakUmumStatus || $surat->status_id !== $ditolakUmumStatus->id) {
                $needsRevision = ($surat->perihal !== $request->perihal || 
                                 $surat->tujuan_jabatan_id !== (int)$request->tujuan_jabatan_id || 
                                 $surat->sifat_surat !== $request->sifat_surat ||
                                 $surat->tanggal_surat->format('Y-m-d') !== $request->tanggal_surat ||
                                 ($request->hasFile('file_surat') && $request->file('file_surat')->isValid()));

                if ($needsRevision) {
                    $suratModel = new Surat();
                    $nomorSurat = $suratModel->generateNomorSurat($request->fakultas_id, $request->prodi_id, 
                                                                   $surat->tanggal_surat->year, $surat->nomor_surat, true);
                }
            }

            $surat->update([
                'nomor_surat' => $nomorSurat,
                'perihal' => $request->perihal,
                'tujuan_jabatan_id' => $request->tujuan_jabatan_id,
                'lampiran' => $request->lampiran,
                'prodi_id' => $request->prodi_id,
                'fakultas_id' => $request->fakultas_id,
                'tanggal_surat' => $request->tanggal_surat,
                'sifat_surat' => $request->sifat_surat,
                'file_surat' => $filePath,
                'updated_by' => $user->id,
            ]);

            DB::commit();
            
            $actionType = $request->input('action_type', 'draft');
            
            if ($actionType === 'submit') {
                $reviewStatus = StatusSurat::where('kode_status', 'review_kaprodi')
                    ->orWhere('kode_status', 'diajukan')
                    ->first();
                
                if ($reviewStatus) {
                    $surat->update(['status_id' => $reviewStatus->id, 'updated_by' => Auth::id()]);
                }
                
                return redirect()->route('surat.show', $surat->id)
                    ->with('success', 'Surat berhasil dikirim ke Kaprodi untuk review.');
            }
            
            return redirect()->route('surat.show', $surat->id)
                ->with('success', 'Surat berhasil disimpan sebagai draft.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->hasFile('file_surat') && $filePath && $filePath !== $surat->file_surat) {
                Storage::disk('public')->delete($filePath);
            }
            return back()->withInput()->with('error', 'Gagal memperbarui surat: ' . $e->getMessage());
        }
    }

    /**
     * Submit surat for review
     */
    public function submit(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);
        $user = Auth::user();
        
        $reviewKaprodiStatus = StatusSurat::where('kode_status', 'review_kaprodi')->firstOrFail();
        $draftStatus = StatusSurat::where('kode_status', 'draft')->firstOrFail();
        $revisiOpsionalStatus = StatusSurat::where('kode_status', 'revisi_opsional')->firstOrFail();

        if (!in_array($surat->status_id, [$draftStatus->id, $revisiOpsionalStatus->id])) {
            return back()->with('error', 'Surat tidak dapat disubmit karena statusnya bukan draft atau perlu revisi.');
        }

        DB::beginTransaction();
        try {
            $surat->update([
                'status_id' => $reviewKaprodiStatus->id,
                'updated_by' => $user->id,
            ]);

            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $user->id,
                'action' => 'submitted',
                'keterangan' => 'Surat disubmit untuk review Kaprodi oleh ' . $user->nama,
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            return back()->with('success', 'Surat berhasil disubmit untuk review Kaprodi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal submit surat: ' . $e->getMessage());
        }
    }

    /**
     * Display approval list for kaprodi
     */
    public function approvalList()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('kaprodi')) {
            abort(403, 'Unauthorized action.');
        }

        $reviewKaprodiStatus = StatusSurat::where('kode_status', 'review_kaprodi')->firstOrFail();
        $surats = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan'])
            ->where('status_id', $reviewKaprodiStatus->id)
            ->where('prodi_id', $user->prodi_id)
            ->latest()
            ->paginate(10);

        return view('kaprodi.surat.approval', compact('surats'));
    }

    /**
     * Approve surat
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $surat = Surat::findOrFail($id);
        
        if (!$user->hasRole('kaprodi') || $surat->prodi_id !== $user->prodi_id) {
            abort(403, 'Unauthorized');
        }
        
        $approvedStatus = StatusSurat::where('kode_status', 'disetujui_kaprodi')->first();
        
        if (!$approvedStatus) {
            return back()->with('error', 'Status tidak ditemukan');
        }
        
        $surat->update(['status_id' => $approvedStatus->id]);
        
        return back()->with('success', 'Surat berhasil disetujui');
    }

    /**
     * Reject surat
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['keterangan' => 'required|string|max:500']);
        
        $user = Auth::user();
        $surat = Surat::findOrFail($id);
        
        if (!$user->hasRole('kaprodi') || $surat->prodi_id !== $user->prodi_id) {
            abort(403, 'Unauthorized');
        }
        
        $rejectedStatus = StatusSurat::where('kode_status', 'ditolak_kaprodi')
            ->orWhere('kode_status', 'ditolak')
            ->first();
        
        if (!$rejectedStatus) {
            return back()->with('error', 'Status penolakan tidak ditemukan');
        }
        
        try {
            DB::transaction(function () use ($surat, $rejectedStatus, $user, $request) {
                $surat->update(['status_id' => $rejectedStatus->id]);
                
                if (class_exists('App\Models\StatusHistory')) {
                    StatusHistory::create([
                        'surat_id' => $surat->id,
                        'status_id' => $rejectedStatus->id,
                        'user_id' => $user->id,
                        'keterangan' => $request->input('keterangan')
                    ]);
                }
            });
            
            return back()->with('success', 'Surat berhasil ditolak');
        } catch (Exception $e) {
            Log::error('Failed to reject surat', ['surat_id' => $surat->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menolak surat: ' . $e->getMessage());
        }
    }

    /**
 * Process pengajuan at prodi level (approve/reject) - WORKFLOW BARU
 */
/**
     * Process pengajuan at prodi level (approve/reject)
     * FIXED: Status konsisten dengan FakultasStaffController
     */
    public function processProdiPengajuan(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
        ]);
        
        $user = Auth::user();
        
        if (!$user->hasRole(['staff_prodi', 'kaprodi'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $pengajuan = PengajuanSurat::where('prodi_id', $user->prodi_id)
            ->where('id', $id)
            ->first();
        
        if (!$pengajuan) {
            return response()->json([
                'success' => false, 
                'message' => 'Pengajuan tidak ditemukan'
            ], 404);
        }
        
        if ($pengajuan->status !== 'pending') {
            return response()->json([
                'success' => false, 
                'message' => 'Pengajuan sudah diproses sebelumnya'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            if ($request->action === 'approve') {
                // IMPORTANT: Use 'approved_prodi' so it appears in fakultas
                $pengajuan->update([
                    'status' => 'processed', // <-- KONSISTEN dengan FakultasStaffController
                    'approved_by_prodi' => $user->id,
                    'approved_at_prodi' => now()
                ]);
                
                $message = 'Pengajuan berhasil disetujui dan diteruskan ke fakultas';
                
            } else {
                $pengajuan->update([
                    'status' => 'rejected_prodi',
                    'rejected_by_prodi' => $user->id,
                    'rejected_at_prodi' => now(),
                    'rejection_reason_prodi' => $request->rejection_reason
                ]);
                
                $message = 'Pengajuan berhasil ditolak';
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error processing prodi pengajuan', [
                'error' => $e->getMessage(),
                'pengajuan_id' => $id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Display pengajuan list
     */
    // Di SuratController.php
public function pengajuanIndex(Request $request)
{
    $user = Auth::user();
    
    if (!$user->hasRole(['staff_prodi', 'kaprodi'])) {
        abort(403);
    }
    
    $query = PengajuanSurat::with(['prodi', 'jenisSurat']);
    
    if ($user->prodi_id) {
        $query->where('prodi_id', $user->prodi_id);
    }
    
    // Apply filters if any
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('nim', 'like', "%{$request->search}%")
              ->orWhere('nama_mahasiswa', 'like', "%{$request->search}%")
              ->orWhere('tracking_token', 'like', "%{$request->search}%");
        });
    }
    
    $pengajuans = $query->latest()->paginate(15);
    
    // PERBAIKAN: Pastikan variabel dikirim dengan nama yang benar
    return view('staff.pengajuan.index', compact('pengajuans'));
}

    /**
     * Show pengajuan detail
     */
    public function pengajuanShow($id)
    {
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat'])->findOrFail($id);
        
        $user = Auth::user();
        if ($user->prodi_id && $pengajuan->prodi_id !== $user->prodi_id) {
            abort(403);
        }
        
        return view('staff.pengajuan.show', compact('pengajuan'));
    }

    /**
     * Approve pengajuan - CONSOLIDATED AND ENHANCED
     */
    public function approvePengajuan(Request $request, $id)
    {
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat'])->findOrFail($id);
        
        if ($pengajuan->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        }
        
        DB::beginTransaction();
        try {
            $draftStatus = StatusSurat::where('kode_status', 'draft')->first();
            if (!$draftStatus) {
                throw new \Exception('Status draft tidak ditemukan di database');
            }
            
            // Generate nomor surat
            $nomorSurat = $this->generateEnhancedNomorSurat($pengajuan);
            
            // Create surat
            $surat = Surat::create([
                'nomor_surat' => $nomorSurat,
                'tanggal_surat' => now(),
                'perihal' => $this->generatePerihal($pengajuan),
                'isi_surat' => $this->generateContentFromPengajuan($pengajuan),
                'tipe_surat' => 'keluar',
                'sifat_surat' => 'biasa',
                'jenis_id' => $pengajuan->jenis_surat_id,
                'status_id' => $draftStatus->id,
                'created_by' => Auth::id(),
                'prodi_id' => $pengajuan->prodi_id,
                'fakultas_id' => $pengajuan->prodi->fakultas_id ?? 1,
            ]);
            
            // Update pengajuan
            $pengajuan->update([
                'status' => 'processed',
                'surat_id' => $surat->id,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);
            
            // Create tracking
            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => Auth::id(),
                'action' => 'created_from_pengajuan',
                'keterangan' => "Surat dibuat dari pengajuan {$pengajuan->tracking_token}",
                'data_after' => $surat->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            DB::commit();
            
            return redirect()->route('surat.edit', $surat->id)
                ->with('success', 'Pengajuan berhasil di-approve! Silakan edit draft surat.');
                        
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to approve pengajuan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal approve pengajuan: ' . $e->getMessage());
        }
    }

    /**
     * Reject pengajuan
     */
    public function rejectPengajuan(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|min:10']);
        
        $pengajuan = PengajuanSurat::findOrFail($id);
        
        if ($pengajuan->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        }
        
        $pengajuan->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->reason
        ]);
        
        return redirect()->route('surat.pengajuan.index')
            ->with('success', 'Pengajuan telah ditolak.');
    }

    /**
     * Generate content from pengajuan
     */
    private function generateContentFromPengajuan($pengajuan)
    {
        $additionalData = is_array($pengajuan->additional_data) 
            ? $pengajuan->additional_data 
            : (is_string($pengajuan->additional_data) ? json_decode($pengajuan->additional_data, true) : []);
        
        $content = "<h3>SURAT " . strtoupper($pengajuan->jenisSurat->nama_jenis) . "</h3>";
        $content .= "<p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>";
        $content .= "<table style='width: 100%; margin: 20px 0;'>";
        $content .= "<tr><td width='30%'>Nama</td><td>: " . $pengajuan->nama_mahasiswa . "</td></tr>";
        $content .= "<tr><td>NIM</td><td>: " . $pengajuan->nim . "</td></tr>";
        $content .= "<tr><td>Program Studi</td><td>: " . $pengajuan->prodi->nama_prodi . "</td></tr>";
        $content .= "<tr><td>Keperluan</td><td>: " . $pengajuan->keperluan . "</td></tr>";
        
        // Add semester and tahun akademik if available
        if (isset($additionalData['semester'])) {
            $content .= "<tr><td>Semester</td><td>: " . $additionalData['semester'] . "</td></tr>";
        }
        if (isset($additionalData['tahun_akademik'])) {
            $content .= "<tr><td>Tahun Akademik</td><td>: " . $additionalData['tahun_akademik'] . "</td></tr>";
        }
        
        $content .= "</table>";
        
        // Add parent data for MA surat
        if ($pengajuan->jenisSurat->kode_surat == 'MA' && !empty($additionalData)) {
            $content .= "<p><strong>Data Orang Tua/Wali:</strong></p>";
            $content .= "<table style='width: 100%; margin: 20px 0;'>";
            
            if (isset($additionalData['nama_orang_tua'])) {
                $content .= "<tr><td width='30%'>Nama</td><td>: " . $additionalData['nama_orang_tua'] . "</td></tr>";
            }
            if (isset($additionalData['tempat_lahir_ortu']) && isset($additionalData['tanggal_lahir_ortu'])) {
                $content .= "<tr><td>Tempat/Tgl Lahir</td><td>: " . $additionalData['tempat_lahir_ortu'] . ", " . $additionalData['tanggal_lahir_ortu'] . "</td></tr>";
            }
            if (isset($additionalData['pekerjaan_ortu'])) {
                $content .= "<tr><td>Pekerjaan</td><td>: " . $additionalData['pekerjaan_ortu'] . "</td></tr>";
            }
            if (isset($additionalData['alamat_rumah_ortu'])) {
                $content .= "<tr><td>Alamat</td><td>: " . $additionalData['alamat_rumah_ortu'] . "</td></tr>";
            }
            
            $content .= "</table>";
        }
        
        $content .= "<p>Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>";
        
        return $content;
    }

    /**
     * Generate enhanced nomor surat
     */
    private function generateEnhancedNomorSurat($pengajuan)
    {
        $jenisKode = $pengajuan->jenisSurat->kode_surat;
        $tahun = date('Y');
        $bulan = $this->getRomanMonth(date('n'));
        
        $lastNumber = Surat::whereHas('jenisSurat', function($q) use ($jenisKode) {
            $q->where('kode_surat', $jenisKode);
        })
        ->where('prodi_id', $pengajuan->prodi_id)
        ->whereYear('tanggal_surat', $tahun)
        ->count();
        
        $nomorUrut = $lastNumber + 1;
        
        return sprintf('%03d/%s/%s/%s/%s', 
            $nomorUrut, 
            $jenisKode, 
            $pengajuan->prodi->kode_prodi, 
            $bulan, 
            $tahun
        );
    }

    /**
     * Generate perihal
     */
    private function generatePerihal($pengajuan)
    {
        return "{$pengajuan->jenisSurat->nama_jenis} - {$pengajuan->nama_mahasiswa} ({$pengajuan->nim})";
    }

    /**
     * Get roman month
     */
    private function getRomanMonth($month)
    {
        $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romans[$month - 1];
    }

    /**
     * Tracking view
     */
    public function tracking($id)
    {
        $surat = Surat::with(['currentStatus', 'createdBy'])->findOrFail($id);
        return view('staff.surat.tracking', compact('surat'));
    }
}