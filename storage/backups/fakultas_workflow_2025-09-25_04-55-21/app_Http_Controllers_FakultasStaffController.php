<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\PengajuanSurat;
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
     * Display combined list: surats + pengajuan for fakultas staff
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->load('prodi.fakultas', 'role');
        
        $fakultasId = $user->prodi?->fakultas_id;
        if (!$fakultasId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke fakultas manapun');
        }
        
        // 1. Get existing surats (surat yang sudah dibuat prodi)
        $suratQuery = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan', 'prodi.fakultas'])
                      ->whereHas('prodi', function($q) use ($fakultasId) {
                          $q->where('fakultas_id', $fakultasId);
                      })
                      ->whereHas('currentStatus', function($q) {
                          $q->where('kode_status', 'disetujui_kaprodi');
                      });
        
        // 2. Get pengajuan yang sudah disetujui prodi
        $pengajuanQuery = PengajuanSurat::with(['prodi', 'jenisSurat'])
                                   ->whereHas('prodi', function($q) use ($fakultasId) {
                                       $q->where('fakultas_id', $fakultasId);
                                   })
                                   ->where('status', 'processed');
        
        // Apply filters untuk both queries
        if ($request->search) {
        $suratQuery->where(function($q) use ($request) {
            $q->where('perihal', 'like', '%' . $request->search . '%')
              ->orWhere('nomor_surat', 'like', '%' . $request->search . '%');
        });
            
            $pengajuanQuery->where(function($q) use ($request) {
            $q->where('nim', 'like', "%{$request->search}%")
              ->orWhere('nama_mahasiswa', 'like', "%{$request->search}%")
              ->orWhere('tracking_token', 'like', "%{$request->search}%");
        });
    }
        
        if ($request->prodi_id) {
            $suratQuery->where('prodi_id', $request->prodi_id);
            $pengajuanQuery->where('prodi_id', $request->prodi_id);
        }
        
        // Get data
        $surats = $suratQuery->get();
        $pengajuans = $pengajuanQuery->get();
        
        // Transform pengajuan ke format yang cocok untuk tabel
$pengajuanItems = $pengajuans->map(function($pengajuan) {
    return (object)[
        'id' => $pengajuan->id,
        'type' => 'pengajuan',
        'nomor_surat' => $pengajuan->tracking_token, // Ini untuk ditampilkan
        'tracking_token' => $pengajuan->tracking_token, // Backup field
        'nim' => $pengajuan->nim, // Tambahkan field nim langsung
        'nama_mahasiswa' => $pengajuan->nama_mahasiswa, // Tambahkan nama langsung
        'perihal' => $pengajuan->jenisSurat->nama_jenis . ' - ' . $pengajuan->nama_mahasiswa,
        'prodi' => $pengajuan->prodi,
        'created_at' => $pengajuan->created_at,
        'status_display' => 'Perlu Generate Surat',
        'status_class' => 'bg-blue-100 text-blue-800',
        'createdBy' => (object)['nama' => $pengajuan->nama_mahasiswa, 'name' => $pengajuan->nama_mahasiswa],
        'currentStatus' => (object)['kode_status' => 'pending_generate'],
        'original_pengajuan' => $pengajuan
    ];
});
        
        // Transform existing surats
        $suratItems = $surats->map(function($surat) {
            return (object)[
                'id' => $surat->id,
                'type' => 'surat',
                'nomor_surat' => $surat->nomor_surat,
                'perihal' => $surat->perihal,
                'prodi' => $surat->prodi,
                'created_at' => $surat->created_at,
                'status_display' => $surat->currentStatus->nama_status ?? 'Disetujui Kaprodi',
                'status_class' => match($surat->currentStatus->kode_status ?? 'disetujui_kaprodi') {
                    'disetujui_kaprodi' => 'bg-yellow-100 text-yellow-800',
                    'diproses_fakultas' => 'bg-blue-100 text-blue-800',
                    'disetujui_fakultas' => 'bg-green-100 text-green-800',
                    'ditolak_fakultas' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                },
                'createdBy' => $surat->createdBy,
                'currentStatus' => $surat->currentStatus,
                'original_surat' => $surat
            ];
        });
        
        // Combine and sort
        $allItems = $pengajuanItems->concat($suratItems)->sortByDesc('created_at')->values();
        
        // Manual pagination
        $perPage = 15;
        $currentPage = intval(request()->get('page', 1));
        $offset = ($currentPage - 1) * $perPage;
        $items = $allItems->slice($offset, $perPage);
        $total = $allItems->count();
        
        // Get filter options
        $prodis = \App\Models\Prodi::where('fakultas_id', $fakultasId)->get();
        $statuses = StatusSurat::whereIn('kode_status', [
            'disetujui_kaprodi', 
            'diproses_fakultas', 
            'disetujui_fakultas',
            'ditolak_fakultas'
        ])->get();
        
        // Create pagination info object
        $paginationInfo = (object)[
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'has_pages' => $total > $perPage,
            'first_item' => $offset + 1,
            'last_item' => min($offset + $perPage, $total)
        ];
        
        return view('fakultas.surat.index', compact('items', 'prodis', 'statuses', 'paginationInfo'));
    }

    /**
 * Display the specified surat with proper data handling
 */
public function show($id)
{
    $user = Auth::user();
    $user->load('prodi.fakultas');
    
    $fakultasId = $user->prodi?->fakultas_id;
    if (!$fakultasId) {
        return redirect()->route('fakultas.surat.index')
                       ->with('error', 'Anda tidak memiliki akses ke fakultas manapun');
    }
    
    // Initialize variables dengan default value
    $surat = null;
    $pengajuan = null;
    
    // Cari di surat dulu
    $surat = Surat::with([
        'jenisSurat', 'currentStatus', 'createdBy.jabatan', 
        'tujuanJabatan', 'prodi.fakultas', 'statusHistories.user', 
        'statusHistories.status'
    ])->find($id);
    
    if ($surat && $surat->prodi->fakultas_id === $fakultasId) {
        $surat->type = 'surat';
        return view('fakultas.surat.show', compact('surat', 'pengajuan'));
    }
    
    // Cari di pengajuan
    $pengajuan = PengajuanSurat::with(['jenisSurat', 'prodi.fakultas'])->find($id);
    
    if ($pengajuan && $pengajuan->prodi->fakultas_id === $fakultasId) {
        // Transform untuk compatibility
        $surat = new \stdClass();
        $surat->id = $pengajuan->id;
        $surat->type = 'pengajuan';
        $surat->nomor_surat = $pengajuan->tracking_token;
        $surat->perihal = $pengajuan->jenisSurat->nama_jenis . ' - ' . $pengajuan->nama_mahasiswa;
        $surat->jenisSurat = $pengajuan->jenisSurat;
        $surat->prodi = $pengajuan->prodi;
        $surat->created_at = $pengajuan->created_at;
        $surat->createdBy = (object)['nama' => $pengajuan->nama_mahasiswa, 'jabatan' => null];
        $surat->currentStatus = (object)[
            'kode_status' => 'pending_generate',
            'nama_status' => 'Perlu Generate Surat',
            'created_at' => $pengajuan->created_at
        ];
        $surat->statusHistories = collect([]);
        $surat->isi_surat = null;
        $surat->original_pengajuan = $pengajuan;
        
        return view('fakultas.surat.show', compact('surat', 'pengajuan'));
    }
    
    // Jika tidak ditemukan, tetap kirim null values
    return view('fakultas.surat.show', compact('surat', 'pengajuan'))
           ->with('error', 'Data tidak ditemukan');
}

    /**
     * Display pengajuan from prodi that are approved (separate page jika masih diperlukan)
     */
    public function pengajuanFromProdi(Request $request)
    {
        $user = Auth::user();
        $user->load('prodi.fakultas');
        
        $fakultasId = $user->prodi?->fakultas_id;
        if (!$fakultasId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke fakultas manapun');
        }
        
        $query = PengajuanSurat::with(['prodi', 'jenisSurat', 'approvedByProdi'])
                               ->whereHas('prodi', function($q) use ($fakultasId) {
                                   $q->where('fakultas_id', $fakultasId);
                               })
                               ->whereIn('status', ['approved_prodi', 'approved_prodi_direct_fakultas']);
        
        // Apply filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nim', 'like', "%{$request->search}%")
                  ->orWhere('nama_mahasiswa', 'like', "%{$request->search}%")
                  ->orWhere('tracking_token', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->prodi_id) {
            $query->where('prodi_id', $request->prodi_id);
        }
        
        if ($request->jenis_surat_id) {
            $query->where('jenis_surat_id', $request->jenis_surat_id);
        }
        
        $pengajuans = $query->latest()->paginate(15);
        
        $prodis = \App\Models\Prodi::where('fakultas_id', $fakultasId)->get();
        $jenisSurat = \App\Models\JenisSurat::all();
        
        return view('fakultas.pengajuan.index', compact('pengajuans', 'prodis', 'jenisSurat'));
    }

    

    

/**
 * Process pengajuan from prodi (approve/reject at fakultas level)
 */
public function processPengajuanFromProdi(Request $request, $id)
{
    $request->validate([
        'action' => 'required|in:approve,reject',
        'rejection_reason' => 'required_if:action,reject|string|max:500',
    ]);
    
    $user = Auth::user();
    $user->load('prodi.fakultas');
    
    // Check authorization
    if (!$user->hasRole('staff_fakultas')) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat'])
                               ->forFakultas($user->prodi->fakultas_id)
                               ->where('id', $id)
                               ->first();
    
    if (!$pengajuan) {
        return response()->json([
            'success' => false, 
            'message' => 'Pengajuan tidak ditemukan atau bukan milik fakultas Anda'
        ], 404);
    }
    
    // Check if can be processed by fakultas
    if (!$pengajuan->canBeProcessedByFakultas()) {
        return response()->json([
            'success' => false, 
            'message' => 'Pengajuan tidak dapat diproses. Status: ' . $pengajuan->status
        ], 400);
    }
    
    try {
        DB::beginTransaction();
        
        if ($request->action === 'approve') {
            $pengajuan->approveByFakultas($user->id);
            $message = 'Pengajuan berhasil disetujui fakultas. Siap untuk generate surat.';
        } else {
            $pengajuan->rejectByFakultas($user->id, $request->rejection_reason);
            $message = 'Pengajuan berhasil ditolak fakultas';
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        
        Log::error('Error processing fakultas pengajuan', [
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
 * Generate surat from approved pengajuan
 */
public function generateSuratFromPengajuan(Request $request, $id)
{
    $user = Auth::user();
    $user->load('prodi.fakultas');
    
    $pengajuan = PengajuanSurat::with(['prodi.fakultas', 'jenisSurat'])
                               ->forFakultas($user->prodi->fakultas_id)
                               ->where('id', $id)
                               ->first();
    
    if (!$pengajuan) {
        return response()->json([
            'success' => false, 
            'message' => 'Pengajuan tidak ditemukan'
        ], 404);
    }
    
    if (!$pengajuan->canGenerateSurat()) {
        return response()->json([
            'success' => false, 
            'message' => 'Pengajuan belum dapat di-generate surat. Status: ' . $pengajuan->status
        ], 400);
    }
    
    try {
        DB::beginTransaction();
        
        // Get draft status
        $draftStatus = \App\Models\StatusSurat::where('kode_status', 'draft')->first();
        if (!$draftStatus) {
            throw new \Exception('Status draft tidak ditemukan');
        }
        
        // Generate nomor surat
        $nomorSurat = $this->generateNomorSurat($pengajuan);
        
        // Create surat with pre-filled data
        $surat = \App\Models\Surat::create([
            'nomor_surat' => $nomorSurat,
            'tanggal_surat' => now(),
            'perihal' => $this->generatePerihal($pengajuan),
            'isi_surat' => $this->generateIsiSurat($pengajuan),
            'tipe_surat' => 'keluar',
            'sifat_surat' => 'biasa',
            'jenis_id' => $pengajuan->jenis_surat_id,
            'status_id' => $draftStatus->id,
            'created_by' => $user->id,
            'prodi_id' => $pengajuan->prodi_id,
            'fakultas_id' => $pengajuan->prodi->fakultas_id,
            'tujuan_jabatan_id' => $this->getDefaultTujuanJabatan(),
        ]);
        
        // Update pengajuan status
        $pengajuan->markSuratGenerated($surat->id, $user->id);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Surat berhasil di-generate dari pengajuan',
            'edit_url' => route('staff.surat.edit', $surat->id),
            'surat_id' => $surat->id
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        
        Log::error('Error generating surat from pengajuan', [
            'error' => $e->getMessage(),
            'pengajuan_id' => $id,
            'user_id' => $user->id
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal generate surat: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Generate nomor surat
 */
private function generateNomorSurat($pengajuan)
{
    $jenisKode = $pengajuan->jenisSurat->kode_surat;
    $tahun = date('Y');
    $bulan = $this->getRomanMonth(date('n'));
    
    // Count existing surat for this year and jenis
    $lastNumber = \App\Models\Surat::whereHas('jenisSurat', function($q) use ($jenisKode) {
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
 * Generate perihal surat
 */
private function generatePerihal($pengajuan)
{
    return "{$pengajuan->jenisSurat->nama_jenis} - {$pengajuan->nama_mahasiswa} ({$pengajuan->nim})";
}

/**
 * Generate isi surat with pre-filled data
 */
private function generateIsiSurat($pengajuan)
{
    $additionalData = $pengajuan->additional_data ?? [];
    
    $content = "<h3>SURAT " . strtoupper($pengajuan->jenisSurat->nama_jenis) . "</h3>";
    $content .= "<p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>";
    $content .= "<table style='width: 100%; margin: 20px 0; border-collapse: collapse;'>";
    $content .= "<tr><td style='padding: 5px; width: 30%;'><strong>Nama</strong></td><td style='padding: 5px;'>: {$pengajuan->nama_mahasiswa}</td></tr>";
    $content .= "<tr><td style='padding: 5px;'><strong>NIM</strong></td><td style='padding: 5px;'>: {$pengajuan->nim}</td></tr>";
    $content .= "<tr><td style='padding: 5px;'><strong>Program Studi</strong></td><td style='padding: 5px;'>: {$pengajuan->prodi->nama_prodi}</td></tr>";
    $content .= "<tr><td style='padding: 5px;'><strong>Keperluan</strong></td><td style='padding: 5px;'>: {$pengajuan->keperluan}</td></tr>";
    
    // Add specific data based on jenis surat
    if (isset($additionalData['semester'])) {
        $content .= "<tr><td style='padding: 5px;'><strong>Semester</strong></td><td style='padding: 5px;'>: {$additionalData['semester']}</td></tr>";
    }
    
    if (isset($additionalData['tahun_akademik'])) {
        $content .= "<tr><td style='padding: 5px;'><strong>Tahun Akademik</strong></td><td style='padding: 5px;'>: {$additionalData['tahun_akademik']}</td></tr>";
    }
    
    $content .= "</table>";
    
    // Add parent data for MA (Surat Keterangan Mahasiswa Aktif)
    if ($pengajuan->jenisSurat->kode_surat == 'MA' && !empty($additionalData)) {
        $content .= "<p><strong>Data Orang Tua/Wali:</strong></p>";
        $content .= "<table style='width: 100%; margin: 20px 0; border-collapse: collapse;'>";
        
        if (isset($additionalData['nama_orang_tua'])) {
            $content .= "<tr><td style='padding: 5px; width: 30%;'><strong>Nama</strong></td><td style='padding: 5px;'>: {$additionalData['nama_orang_tua']}</td></tr>";
        }
        if (isset($additionalData['tempat_lahir_ortu']) && isset($additionalData['tanggal_lahir_ortu'])) {
            $content .= "<tr><td style='padding: 5px;'><strong>Tempat/Tgl Lahir</strong></td><td style='padding: 5px;'>: {$additionalData['tempat_lahir_ortu']}, {$additionalData['tanggal_lahir_ortu']}</td></tr>";
        }
        if (isset($additionalData['pekerjaan_ortu'])) {
            $content .= "<tr><td style='padding: 5px;'><strong>Pekerjaan</strong></td><td style='padding: 5px;'>: {$additionalData['pekerjaan_ortu']}</td></tr>";
        }
        if (isset($additionalData['alamat_rumah_ortu'])) {
            $content .= "<tr><td style='padding: 5px;'><strong>Alamat</strong></td><td style='padding: 5px;'>: {$additionalData['alamat_rumah_ortu']}</td></tr>";
        }
        
        $content .= "</table>";
    }
    
    $content .= "<p>Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>";
    
    return $content;
}

/**
 * Get default tujuan jabatan
 */
private function getDefaultTujuanJabatan()
{
    $dekan = \App\Models\Jabatan::where('nama_jabatan', 'Dekan')->first();
    return $dekan ? $dekan->id : 1;
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
                // Log error if needed
            }
            return ['data' => $data];
        }
        
        if (is_object($data)) {
            return (array) $data;
        }
        
        return null;
    }

    /**
     * Generate PDF untuk pengajuan dan mark as completed
     */
    public function generateSuratPDF(Request $request, $id)
    {
        $user = Auth::user();
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat'])->findOrFail($id);
        
        if (!in_array($pengajuan->status, ['processed', 'approved_prodi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak dapat di-generate PDF. Status: ' . $pengajuan->status
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Generate PDF content (simplified for now)
            $pdfContent = $this->generatePDFContent($pengajuan);
            
            // Save PDF file
            $fileName = "surat_" . $pengajuan->jenisSurat->kode_surat . "_" . $pengajuan->nim . "_" . now()->format('Y-m-d') . ".pdf";
            $filePath = "surat_generated/" . $fileName;
            
            Storage::disk('public')->put($filePath, $pdfContent);
            
            // Create SuratGenerated record
            $suratGenerated = \App\Models\SuratGenerated::create([
                'pengajuan_id' => $pengajuan->id,
                'file_path' => $filePath,
                'original_filename' => $fileName,
                'file_size' => strlen($pdfContent),
                'mime_type' => 'application/pdf',
                'generated_by' => $user->id,
                'generated_at' => now(),
                'is_final' => true,
                'version' => 1
            ]);
            
            // Update pengajuan status to completed
            $pengajuan->update([
                'status' => 'completed',
                'surat_generated_id' => $suratGenerated->id,
                'completed_by' => $user->id,
                'completed_at' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'PDF berhasil di-generate dan pengajuan telah selesai',
                'download_url' => route('tracking.download', $pengajuan->id)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error generating PDF', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate PDF content (placeholder - replace with actual PDF generation)
     */
    private function generatePDFContent($pengajuan)
    {
        // This is a placeholder - replace with actual PDF generation using TCPDF, DOMPDF, or similar
        $additionalData = json_decode($pengajuan->additional_data, true) ?? [];
        
        $content = "
        SURAT KETERANGAN MASIH KULIAH
        
        Mahasiswa:
        NIM: {$pengajuan->nim}
        Nama: {$pengajuan->nama_mahasiswa}
        Prodi: {$pengajuan->prodi->nama_prodi}
        
        Keperluan: {$pengajuan->keperluan}
        ";
        
        if (isset($additionalData['orang_tua'])) {
            $content .= "
        
        Data Orang Tua:
        Nama: " . ($additionalData['orang_tua']['nama'] ?? 'N/A') . "
        Pekerjaan: " . ($additionalData['orang_tua']['pekerjaan'] ?? 'N/A');
        }
        
        // Return as simple PDF content (replace with actual PDF library)
        return $content;
    }

    /**
     * Kirim surat ke pengaju (mark as completed without PDF generation)
     */
    public function kirimKePengaju(Request $request, $id)
    {
        $user = Auth::user();
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat'])->findOrFail($id);
        
        if (!in_array($pengajuan->status, ['processed', 'approved_prodi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak dapat dikirim. Status: ' . $pengajuan->status
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Update pengajuan status to completed
            $pengajuan->update([
                'status' => 'completed',
                'completed_by' => $user->id,
                'completed_at' => now(),
                'completion_note' => $request->input('note', 'Surat telah selesai dan dapat digunakan')
            ]);
            
            // Log activity
            \Log::info('Pengajuan sent to applicant', [
                'pengajuan_id' => $pengajuan->id,
                'nim' => $pengajuan->nim,
                'tracking_token' => $pengajuan->tracking_token,
                'completed_by' => $user->id
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Surat berhasil dikirim ke pengaju. Status pengajuan telah selesai.',
                'tracking_url' => route('tracking.show', $pengajuan->tracking_token)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error sending to applicant', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ke pengaju: ' . $e->getMessage()
            ], 500);
        }
    }
}