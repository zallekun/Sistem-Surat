<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
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
        $query = PengajuanSurat::with(['mahasiswa','prodi', 'jenisSurat']);
        
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
        
        $pengajuan = PengajuanSurat::with([
            'mahasiswa', 
            'prodi', 
            'jenisSurat', 
            'suratPengantarGeneratedBy',
            'approvalHistories.performedBy', 
            'trackingHistory' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);
        
        // Check if user can view this pengajuan
        if ($user->prodi_id && $pengajuan->prodi_id !== $user->prodi_id) {
            abort(403, 'Unauthorized - Pengajuan dari prodi lain');
        }
        
        // ✅ TAMBAH: Parse additional data untuk display
        $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
        
        return view('staff.pengajuan.show', compact('pengajuan', 'additionalData'));
    }

public function processPengajuan(Request $request, $id)
{
    \Log::info('=== PROCESS PENGAJUAN CALLED ===', [
        'pengajuan_id' => $id,
        'action' => $request->input('action'),
        'user_id' => auth()->id(),
        'timestamp' => now(),
    ]);

    $request->validate([
        'action' => 'required|in:approve,reject',
        'rejection_reason' => 'required_if:action,reject|string|max:500'
    ]);
    
    $user = Auth::user();
    
    if (!$user->hasRole(['staff_prodi', 'kaprodi'])) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $pengajuan = PengajuanSurat::with(['mahasiswa', 'jenisSurat'])
        ->where('prodi_id', $user->prodi_id)
        ->findOrFail($id);
    
    // Check if pengajuan can still be processed
    if (!in_array($pengajuan->status, ['pending', 'processed'])) {
        return response()->json(['success' => false, 'message' => 'Pengajuan sudah diproses sebelumnya'], 400);
    }
    
    try {
        DB::beginTransaction();
        
        if ($request->action === 'approve') {
            \Log::info('APPROVING PENGAJUAN', [
                'pengajuan_id' => $pengajuan->id,
                'status_before' => $pengajuan->status,
            ]);
            
            // Update status
            $pengajuan->status = 'approved_prodi';
            
            // ✅ NEW: Keep old columns for backward compatibility (temporary)
            $pengajuan->approved_by_prodi = $user->id;
            $pengajuan->approved_at_prodi = now();
            
            $pengajuan->save();
            
            // ✅ NEW: Log to approval_histories
            $pengajuan->logApproval(
                action: 'approved_prodi',
                notes: 'Pengajuan disetujui oleh ' . $user->nama
            );
            
            \Log::info('APPROVAL LOGGED', [
                'pengajuan_id' => $pengajuan->id,
                'status_after' => $pengajuan->status,
            ]);
            
            // Tracking history (existing)
            $pengajuan->trackingHistory()->create([
                'status' => 'approved_prodi',
                'description' => 'Pengajuan disetujui oleh ' . $user->nama,
                'created_by' => $user->id
            ]);
            
            // Message
            if ($pengajuan->needsSuratPengantar()) {
                $message = 'Pengajuan disetujui. Silakan generate surat pengantar untuk diteruskan ke fakultas.';
            } else {
                $message = 'Pengajuan disetujui dan diteruskan ke fakultas untuk diproses.';
            }
            
        } else {
            // Reject
            \Log::info('REJECTING PENGAJUAN', [
                'pengajuan_id' => $pengajuan->id,
                'reason' => $request->rejection_reason,
            ]);
            
            $pengajuan->status = 'rejected_prodi';
            
            // ✅ NEW: Keep old columns for backward compatibility (temporary)
            $pengajuan->rejection_reason_prodi = $request->rejection_reason;
            $pengajuan->rejected_by_prodi = $user->id;
            $pengajuan->rejected_at_prodi = now();
            
            $pengajuan->save();
            
            // ✅ NEW: Log to approval_histories with rejection reason
            $pengajuan->logApproval(
                action: 'rejected_prodi',
                notes: $request->rejection_reason
            );
            
            $message = 'Pengajuan ditolak dengan alasan: ' . $request->rejection_reason;
            
            // Tracking history
            $pengajuan->trackingHistory()->create([
                'status' => 'rejected_prodi',
                'description' => 'Pengajuan ditolak oleh ' . $user->nama . '. Alasan: ' . $request->rejection_reason,
                'created_by' => $user->id
            ]);
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        
        Log::error('Error processing pengajuan', [
            'pengajuan_id' => $id,
            'action' => $request->action,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ], 500);
    }
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
    /**
 * Preview form untuk generate surat pengantar
 */
public function previewPengantar($id)
{
    $user = Auth::user();
    
    if (!$user->hasRole(['staff_prodi', 'kaprodi'])) {
        abort(403, 'Unauthorized');
    }
    
        $pengajuan = PengajuanSurat::with([
            'mahasiswa',
            'prodi.fakultas', 
            'jenisSurat', 
            'prodi.kaprodi'
        ])->where('prodi_id', $user->prodi_id)
        ->findOrFail($id);
    
    // Validasi: hanya KP/TA yang butuh pengantar
    if (!$pengajuan->needsSuratPengantar()) {
        return redirect()->route('staff.pengajuan.show', $id)
            ->with('error', 'Jenis surat ini tidak memerlukan surat pengantar');
    }
    
    // Validasi: status harus approved_prodi
    if ($pengajuan->status !== 'approved_prodi') {
        return redirect()->route('staff.pengajuan.show', $id)
            ->with('error', 'Pengajuan harus disetujui terlebih dahulu');
    }
    
    // Validasi: belum punya pengantar
    if ($pengajuan->hasSuratPengantar()) {
        return redirect()->route('staff.pengajuan.show', $id)
            ->with('info', 'Surat pengantar sudah dibuat sebelumnya');
    }
    
    // Get Kaprodi data
    $kaprodi = $pengajuan->prodi->kaprodi;
    
    // TAMBAHKAN INI - Generate default nomor surat dan data lainnya
    $nomorSurat = $pengajuan->surat_pengantar_nomor ?? $this->generateDefaultNomorPengantar($pengajuan);
    $tanggalSurat = now()->locale('id')->isoFormat('D MMMM Y');
    
    // Parse additional data
    $additionalData = $pengajuan->additional_data ?? [];
    if (is_string($additionalData)) {
        $additionalData = json_decode($additionalData, true) ?? [];
    }
    
    // Default penandatangan
    $penandatangan = [
        'nama' => $kaprodi->nama ?? 'Nama Kaprodi',
        'nip' => $kaprodi->nip ?? 'NIP Kaprodi',
        'jabatan' => 'Ketua Program Studi ' . $pengajuan->prodi->nama_prodi,
    ];
    
    $canEdit = true;
    
    return view('staff.pengajuan.preview-pengantar', compact(
        'pengajuan', 
        'kaprodi', 
        'nomorSurat',      // ✅ TAMBAH INI
        'tanggalSurat',    // ✅ TAMBAH INI
        'additionalData',  // ✅ TAMBAH INI
        'penandatangan',   // ✅ TAMBAH INI
        'canEdit'
    ));
}

// TAMBAHKAN method helper untuk generate nomor default
private function generateDefaultNomorPengantar($pengajuan)
{
    $kodeProdi = strtoupper($pengajuan->prodi->kode_prodi);
    $kodeJenis = strtoupper($pengajuan->jenisSurat->kode_surat);
    $tahun = date('Y');
    $bulan = $this->getRomanMonth(date('n'));
    
    // Count existing pengantar for this year and prodi
    $count = PengajuanSurat::where('prodi_id', $pengajuan->prodi_id)
        ->whereNotNull('surat_pengantar_nomor')
        ->whereYear('surat_pengantar_generated_at', $tahun)
        ->count();
    
    $nomorUrut = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    
    return "{$nomorUrut}/SP-{$kodeProdi}/{$bulan}/{$tahun}";
}

// Helper untuk roman month
private function getRomanMonth($month)
{
    $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return $romans[$month - 1];
}

/**
 * Store surat pengantar
 */
public function storePengantar(Request $request, $id)
{
    $request->validate([
        'surat_pengantar_nomor' => 'required|string|max:100',
        'nota_dinas_number' => 'nullable|string|max:50',
        'ttd_kaprodi_image' => 'required|string', // base64 image
        'surat_data' => 'required|array'
    ]);
    
    $user = Auth::user();
    
    if (!$user->hasRole(['staff_prodi', 'kaprodi'])) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $pengajuan = PengajuanSurat::with(['mahasiswa', 'prodi', 'jenisSurat'])
        ->where('prodi_id', $user->prodi_id)
        ->findOrFail($id);
    
    if (!$pengajuan->canGeneratePengantar()) {
        return response()->json([
            'success' => false, 
            'message' => 'Pengajuan tidak dapat dibuatkan surat pengantar'
        ], 400);
    }
    
    try {
        DB::beginTransaction();
        
        // Generate PDF surat pengantar
        $pdf = $this->generatePengantarPDF($pengajuan, $request->surat_data, $request->ttd_kaprodi_image);
        
        // Save PDF to storage
        $fileName = 'surat-pengantar-' . $pengajuan->tracking_token . '-' . time() . '.pdf';
        $filePath = 'surat-pengantar/' . $fileName;
        Storage::disk('public')->put($filePath, $pdf->output());
        
        // Update pengajuan
        $pengajuan->update([
            'surat_pengantar_url' => Storage::url($filePath),
            'surat_pengantar_nomor' => $request->surat_pengantar_nomor,
            'nota_dinas_number' => $request->nota_dinas_number,
            'ttd_kaprodi_image' => $request->ttd_kaprodi_image,
            'surat_pengantar_generated_at' => now(),
            'surat_pengantar_generated_by' => $user->id,
            'status' => 'pengantar_generated',
            'surat_data' => array_merge($pengajuan->surat_data ?? [], [
                'surat_pengantar' => $request->surat_data
            ])
        ]);

                // ✅ NEW: Log to approval_histories
        $pengajuan->logApproval(
            action: 'pengantar_generated',
            notes: 'Surat pengantar berhasil dibuat',
            metadata: [
                'nomor_pengantar' => $request->surat_pengantar_nomor,
                'nota_dinas_number' => $request->nota_dinas_number,
            ]
        );
        
        // Log tracking history
        $pengajuan->trackingHistory()->create([
            'status' => 'pengantar_generated',
            'description' => 'Surat pengantar berhasil dibuat oleh ' . $user->nama,
            'created_by' => $user->id
        ]);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Surat pengantar berhasil dibuat',
            'redirect_url' => route('staff.pengajuan.show', $id)
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        
        Log::error('Error generating surat pengantar', [
            'pengajuan_id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

private function generatePengantarPDF($pengajuan, $suratData, $ttdKaprodi)
{
    // Gunakan satu template untuk semua prodi
    $templateName = "surat.pdf.pengantar-universal";
    
    // Validasi template ada
    if (!view()->exists($templateName)) {
        \Log::error('Template PDF universal tidak ditemukan', [
            'template' => $templateName,
            'pengajuan_id' => $pengajuan->id
        ]);
        
        throw new \Exception(
            "Template surat pengantar universal belum tersedia. " .
            "Harap buat template: resources/views/{$templateName}.blade.php"
        );
    }
    
    // Data untuk template PDF
    $data = [
        'pengajuan' => $pengajuan,
        'surat_data' => $suratData,
        'ttd_kaprodi' => $ttdKaprodi,
        'prodi' => $pengajuan->prodi,
        'fakultas' => $pengajuan->prodi->fakultas,
        'kaprodi' => $pengajuan->prodi->kaprodi,
        'nomor_surat' => $suratData['nomor_nota'] ?? '',
        'tanggal_surat' => $suratData['tempat_tanggal'] ?? now()->locale('id')->isoFormat('D MMMM Y')
    ];
    
    \Log::info('Generating PDF dengan template universal', [
        'template' => $templateName,
        'prodi' => $pengajuan->prodi->nama_prodi,
        'jenis_surat' => $pengajuan->jenisSurat->nama_jenis
    ]);
    
    try {
        $pdf = PDF::loadView($templateName, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false, // Disable karena pakai base64
                'isPhpEnabled' => true,
                'defaultFont' => 'Times New Roman',
                'dpi' => 96,
                'margin-top' => '15mm',
                'margin-bottom' => '15mm',
                'margin-left' => '20mm',
                'margin-right' => '20mm'
            ]);
        
        return $pdf;
        
    } catch (\Exception $e) {
        \Log::error('Error generating PDF', [
            'template' => $templateName,
            'error' => $e->getMessage()
        ]);
        
        throw new \Exception("Gagal generate PDF: " . $e->getMessage());
    }
}
}