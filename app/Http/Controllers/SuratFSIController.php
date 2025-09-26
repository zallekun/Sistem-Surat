<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\SuratGenerated;
use App\Models\BarcodeSignature;
use App\Models\TrackingHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SuratFSIController extends Controller
{
    /**
     * Preview surat dengan kemampuan edit nomor surat dan penandatangan
     */
    public function preview($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole("staff_fakultas")) {
                abort(403, "Unauthorized access");
            }
            
            $pengajuan = PengajuanSurat::with(["jenisSurat", "prodi.fakultas", "trackingHistory"])
                ->findOrFail($id);
            
            // Security check - ensure pengajuan belongs to user's fakultas
            if ($pengajuan->prodi->fakultas_id !== $user->prodi->fakultas_id) {
                abort(403, "Tidak memiliki akses ke pengajuan ini");
            }
            
            $allowedStatuses = [
                'approved_fakultas',
                'processed',
                'approved_prodi',
                'sedang_ditandatangani',
                'completed'
            ];
            
            if (!in_array($pengajuan->status, $allowedStatuses)) {
                return back()->with('error', 'Pengajuan dengan status "' . $pengajuan->status . '" tidak dapat diakses untuk preview');
            }
            
            // Generate default or get saved nomor surat
            $nomorSurat = $this->generateNomorSurat($pengajuan);
            $tanggalSurat = now()->locale("id")->isoFormat("D MMMM Y");
            
            // Default penandatangan data
            $penandatangan = [
                'nama' => 'AGUS KOMARUDIN, S.Kom., M.T.',
                'pangkat' => 'PENATA MUDA TK.I – III/B',
                'jabatan' => 'WAKIL DEKAN III FAKULTAS SAINS DAN INFORMATIKA UNJANI',
                'nid' => '4121 758 78'
            ];
            
            // Check if saved data exists
            if ($pengajuan->surat_data) {
                $savedData = $pengajuan->surat_data;
                if (isset($savedData['nomor_surat'])) {
                    $nomorSurat = $savedData['nomor_surat'];
                }
                if (isset($savedData['penandatangan'])) {
                    $penandatangan = array_merge($penandatangan, $savedData['penandatangan']);
                }
                if (isset($savedData['tanggal_surat'])) {
                    $tanggalSurat = $savedData['tanggal_surat'];
                }
            }
            
            $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
            
            // Dynamic capabilities based on status
            $canEdit = in_array($pengajuan->status, ['approved_fakultas', 'processed', 'approved_prodi']);
            $canPrint = in_array($pengajuan->status, ['approved_fakultas', 'processed', 'approved_prodi']) && !$pengajuan->printed_at;
            $canUploadSigned = ($pengajuan->status === 'sedang_ditandatangani');
            $isCompleted = ($pengajuan->status === 'completed');
            
            $data = [
                "pengajuan" => $pengajuan,
                "additionalData" => $additionalData,
                "nomorSurat" => $nomorSurat,
                "tanggalSurat" => $tanggalSurat,
                "penandatangan" => $penandatangan,
                "isPreview" => true,
                "canEdit" => $canEdit,
                "canPrint" => $canPrint,
                "canUploadSigned" => $canUploadSigned,
                "isCompleted" => $isCompleted,
                "displayData" => $pengajuan->surat_data['edited_fields'] ?? []
            ];
            
            return view("surat.fsi.preview-editable", $data);
            
        } catch (\Exception $e) {
            Log::error("Error in FSI preview", [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Gagal memuat preview surat: ' . $e->getMessage());
        }
    }
    
    /**
     * Save edited data (nomor surat, penandatangan, dll)
     */
    public function saveEdits(Request $request, $id)
    {
        $request->validate([
            'nomor_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|string|max:255',
            'penandatangan.nama' => 'required|string|max:255',
            'penandatangan.pangkat' => 'required|string|max:255',
            'penandatangan.jabatan' => 'required|string|max:500',
            'penandatangan.nid' => 'required|string|max:50',
            'mahasiswa' => 'nullable|array',
            'additional_data' => 'nullable|array'
        ]);
        
        try {
            DB::beginTransaction();
            
            $pengajuan = PengajuanSurat::findOrFail($id);
            $user = Auth::user();
            
            // Security check
            if ($pengajuan->prodi->fakultas_id !== $user->prodi->fakultas_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            // Prevent editing if already in signing process
            if (!$pengajuan->canEditSurat()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Surat tidak dapat diedit pada status ini'
                ], 400);
            }
            
            // Merge additional data dengan data yang sudah ada
            $existingAdditionalData = $this->parseAdditionalData($pengajuan->additional_data);
            $newAdditionalData = array_merge($existingAdditionalData, $request->additional_data ?? []);
            
            // Update pengajuan data if mahasiswa data changed
            if ($request->has('mahasiswa')) {
                $mahasiswaData = $request->mahasiswa;
                if (isset($mahasiswaData['nama_mahasiswa'])) {
                    $pengajuan->nama_mahasiswa = $mahasiswaData['nama_mahasiswa'];
                }
                if (isset($mahasiswaData['nim'])) {
                    $pengajuan->nim = $mahasiswaData['nim'];
                }
            }
            
            // Save all surat data
            $suratData = array_merge($pengajuan->surat_data ?? [], [
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'penandatangan' => $request->penandatangan,
                'mahasiswa' => $request->mahasiswa ?? [],
                'additional_data' => $newAdditionalData,
                'last_edited_by' => $user->id,
                'last_edited_at' => now()->toISOString()
            ]);
            
            $pengajuan->setSuratData($suratData);
            $pengajuan->additional_data = json_encode($newAdditionalData);
            $pengajuan->save();
            
            // Log the edit
            TrackingHistory::log(
                $pengajuan->id,
                'edited',
                'Data surat diedit oleh ' . $user->nama,
                $user->id
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error saving edits", [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Print surat untuk TTD fisik dan update status
     */
    public function printSurat($id)
    {
        try {
            DB::beginTransaction();
            
            $pengajuan = PengajuanSurat::findOrFail($id);
            $user = Auth::user();
            
            // Security check
            if ($pengajuan->prodi->fakultas_id !== $user->prodi->fakultas_id) {
                abort(403, "Unauthorized access");
            }
            
            // If already printed, just generate PDF without updating status
            if ($pengajuan->printed_at) {
                return $this->generatePrintPDF($pengajuan);
            }
            
            // Check if can print
            if (!$pengajuan->canPrintSurat()) {
                return back()->with('error', 'Surat tidak dapat dicetak pada status ini');
            }
            
            // Update status to "sedang ditandatangani"
            $pengajuan->markAsPrinted($user->id);
            
            // Add tracking history
            TrackingHistory::log(
                $pengajuan->id,
                'sedang_ditandatangani',
                'Surat dicetak untuk proses tanda tangan fisik oleh ' . $user->nama,
                $user->id
            );
            
            DB::commit();
            
            // Generate and return PDF for printing
            return $this->generatePrintPDF($pengajuan);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error printing surat", [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Gagal print surat: ' . $e->getMessage());
        }
    }
    
    /**
     * Upload link surat yang sudah ditandatangani fisik
     */
    public function uploadSignedLink(Request $request, $id)
    {
        $request->validate([
            'signed_url' => 'required|url|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        try {
            DB::beginTransaction();
            
            $pengajuan = PengajuanSurat::findOrFail($id);
            $user = Auth::user();
            
            // Security check
            if ($pengajuan->prodi->fakultas_id !== $user->prodi->fakultas_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            // Check if can upload signed link
            if (!$pengajuan->canUploadSignedLink()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status surat tidak sesuai untuk upload link'
                ], 400);
            }
            
            // Get saved surat data
            $suratData = $pengajuan->surat_data ?? [];
            $penandatangan = $suratData['penandatangan'] ?? [];
            
            // Complete the surat with signed link
            $pengajuan->completeWithSignedLink(
                $request->signed_url,
                $user->id,
                $request->notes
            );
            
            // Update or create SuratGenerated record
            $suratGenerated = SuratGenerated::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                [
                    'nomor_surat' => $suratData['nomor_surat'] ?? $this->generateNomorSurat($pengajuan),
                    'status' => 'completed',
                    'signed_url' => $request->signed_url,
                    'signed_by' => $penandatangan['nama'] ?? 'Pejabat Fakultas',
                    'signed_at' => now(),
                    'generated_by' => $user->id,
                    'notes' => $request->notes,
                    'metadata' => [
                        'penandatangan' => $penandatangan,
                        'completed_at' => now()->toISOString(),
                        'completed_by' => $user->nama
                    ]
                ]
            );
            
            // Update pengajuan with surat_generated reference
            $pengajuan->update(['surat_generated_id' => $suratGenerated->id]);
            
            // Add tracking history
            TrackingHistory::log(
                $pengajuan->id,
                'completed',
                'Surat telah ditandatangani dan diselesaikan. Link surat tersedia untuk download.',
                $user->id
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Surat berhasil diselesaikan. Link telah tersimpan.',
                'tracking_url' => route('tracking.show', $pengajuan->tracking_token)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error uploading signed link", [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan link: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reject surat dengan alasan
     */
    public function rejectSurat(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);
        
        try {
            DB::beginTransaction();
            
            $pengajuan = PengajuanSurat::findOrFail($id);
            $user = Auth::user();
            
            // Security check
            if ($pengajuan->prodi->fakultas_id !== $user->prodi->fakultas_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            // Reject by fakultas
            $pengajuan->rejectByFakultas($user->id, $request->rejection_reason);
            
            // Add tracking history
            TrackingHistory::log(
                $pengajuan->id,
                'rejected_fakultas',
                'Surat ditolak fakultas: ' . $request->rejection_reason,
                $user->id
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Surat berhasil ditolak'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error rejecting surat", [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak surat: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * UPDATED: Generate PDF yang support multiple jenis surat
     */
    private function generatePrintPDF($pengajuan)
    {
        // Get saved data
        $suratData = $pengajuan->surat_data ?? [];
        $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
        
        // Apply edited fields to additional data
        if (isset($suratData['additional_data'])) {
            $additionalData = array_merge($additionalData, $suratData['additional_data']);
        }
        
        $pdfData = [
            "pengajuan" => $pengajuan,
            "additionalData" => $additionalData,
            "nomorSurat" => $suratData['nomor_surat'] ?? $this->generateNomorSurat($pengajuan),
            "tanggalSurat" => $suratData['tanggal_surat'] ?? now()->locale("id")->isoFormat("D MMMM Y"),
            "penandatangan" => $suratData['penandatangan'] ?? [
                'nama' => 'AGUS KOMARUDIN, S.Kom., M.T.',
                'pangkat' => 'PENATA MUDA TK.I – III/B',
                'jabatan' => 'WAKIL DEKAN III',
                'nid' => '4121 758 78'
            ],
            "barcodeImage" => null,
            "displayData" => $suratData,
            "forPrint" => true
        ];
        
        // NEW: Dynamic view path based on jenis surat
        $viewPath = $this->getViewPath($pengajuan->jenisSurat);
        
        // For KP, extract mahasiswa data properly
        if ($pengajuan->jenisSurat->kode_surat === 'KP' && isset($additionalData['kerja_praktek']['mahasiswa_kp'])) {
            $pdfData['mahasiswa'] = $additionalData['kerja_praktek']['mahasiswa_kp'];
        }
        
        // Check if view exists
        if (!view()->exists($viewPath)) {
            Log::error("View not found: " . $viewPath);
            
            // Try absolute path as fallback
            $absolutePath = resource_path('views/' . str_replace('.', '/', $viewPath) . '.blade.php');
            if (file_exists($absolutePath)) {
                $html = view()->file($absolutePath, $pdfData)->render();
                $pdf = PDF::loadHTML($html);
            } else {
                throw new \Exception("View file not found: " . $viewPath . " at " . $absolutePath);
            }
        } else {
            $pdf = PDF::loadView($viewPath, $pdfData);
        }
        
        $pdf->setPaper("A4", "portrait");
        
        $jenisSurat = $pengajuan->jenisSurat->kode_surat ?? 'SURAT';
        $fileName = "Surat_{$jenisSurat}_" . $pengajuan->nim . "_" . date('YmdHis') . ".pdf";
        
        return $pdf->download($fileName);
    }
    
    /**
     * NEW: Get view path based on jenis surat
     */
    private function getViewPath($jenisSurat)
    {
        $kodeSurat = $jenisSurat->kode_surat ?? 'MA';
        
        $viewMap = [
            'MA' => 'surat.pdf.surat-ma',
            'KP' => 'surat.pdf.surat-kp',
            'TA' => 'surat.pdf.surat-ta',
            'SKM' => 'surat.pdf.surat-skm',
        ];
        
        return $viewMap[$kodeSurat] ?? 'surat.pdf.surat-ma';
    }
    
    /**
     * Get status surat
     */
    public function getSuratStatus($pengajuanId)
    {
        try {
            $pengajuan = PengajuanSurat::with(['suratGenerated', 'trackingHistory'])
                ->findOrFail($pengajuanId);
            
            return response()->json([
                'success' => true,
                'status' => $pengajuan->status,
                'status_label' => $pengajuan->status_label,
                'can_edit' => $pengajuan->canEditSurat(),
                'can_print' => $pengajuan->canPrintSurat(),
                'can_upload_signed' => $pengajuan->canUploadSignedLink(),
                'is_completed' => $pengajuan->isCompleted(),
                'has_pdf' => $pengajuan->hasPdfFile(),
                'download_url' => $pengajuan->download_url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan'
            ], 404);
        }
    }
    
    /**
     * Generate PDF (existing method - keep compatibility)
     */
    public function generatePdf($id)
    {
        try {
            $pengajuan = PengajuanSurat::findOrFail($id);
            
            // Just generate PDF without changing status (for preview)
            return $this->generatePrintPDF($pengajuan);
            
        } catch (\Exception $e) {
            Log::error("Error generating PDF", [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }
    
    // Helper methods
    private function parseAdditionalData($data)
    {
        if (empty($data)) return [];
        if (is_array($data)) return $data;
        
        if (is_string($data)) {
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // Post-process KP mahasiswa data if needed
                    if (isset($decoded['kerja_praktek']['mahasiswa_kp'])) {
                        // Ensure mahasiswa_kp is always an array
                        if (is_string($decoded['kerja_praktek']['mahasiswa_kp'])) {
                            $mahasiswaDecoded = json_decode($decoded['kerja_praktek']['mahasiswa_kp'], true);
                            if (is_array($mahasiswaDecoded)) {
                                $decoded['kerja_praktek']['mahasiswa_kp'] = $mahasiswaDecoded;
                            }
                        }
                    }
                    
                    return $decoded;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to parse additional_data", ['error' => $e->getMessage()]);
            }
        }
        
        if (is_object($data)) return (array) $data;
        return [];
    }
    
    private function generateNomorSurat($pengajuan)
    {
        try {
            $currentYear = date("Y");
            $bulanRomawi = $this->getRomanMonth(date("n"));
            
            // Get last number for this year and jenis surat
            $lastNumber = SuratGenerated::whereYear("created_at", $currentYear)
                ->whereHas('pengajuan', function($query) use ($pengajuan) {
                    $query->where('jenis_surat_id', $pengajuan->jenis_surat_id);
                })
                ->where('pengajuan_id', '!=', $pengajuan->id)
                ->count();
            
            $nomorUrut = str_pad($lastNumber + 1, 3, "0", STR_PAD_LEFT);
            
            // Different prefix based on jenis surat
            $kodeSurat = $pengajuan->jenisSurat->kode_surat ?? 'MA';
            
            $prefixMap = [
                'MA' => 'P',        // Pernyataan (Mahasiswa Aktif)
                'KP' => 'BI',       // Bisnis Internal (Kerja Praktek)  
                'TA' => 'PI',       // Penelitian Internal (Tugas Akhir)
                'SKM' => 'SK',      // Surat Keterangan
            ];
            
            $prefix = $prefixMap[$kodeSurat] ?? 'P';
            
            return "{$prefix}/{$nomorUrut}/FSI-UNJANI/{$bulanRomawi}/{$currentYear}";
            
        } catch (\Exception $e) {
            $defaultPrefix = 'P';
            return "{$defaultPrefix}/001/FSI-UNJANI/" . $this->getRomanMonth(date("n")) . "/" . date("Y");
        }
    }
    
    private function getRomanMonth($month)
    {
        $romans = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
        return ($month >= 1 && $month <= 12) ? $romans[$month - 1] : "I";
    }
}