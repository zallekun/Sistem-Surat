<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\SuratGenerated;
use App\Models\BarcodeSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SuratFSIController extends Controller
{
    public function preview($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole("staff_fakultas")) {
                abort(403, "Unauthorized access");
            }
            
            $pengajuan = PengajuanSurat::with(["jenisSurat", "prodi.fakultas"])->findOrFail($id);
            
            if ($pengajuan->prodi->fakultas_id !== $user->prodi->fakultas_id) {
                abort(403, "Tidak memiliki akses ke pengajuan ini");
            }
            
            $barcodeSignatures = BarcodeSignature::where("fakultas_id", $pengajuan->prodi->fakultas_id)
                ->where("is_active", true)
                ->orderBy('pejabat_nama')
                ->get();
            
            if ($barcodeSignatures->isEmpty()) {
                $barcodeSignatures = BarcodeSignature::where("is_active", true)
                    ->orderBy('pejabat_nama')
                    ->get();
            }
            
            $nomorSurat = $this->generateNomorSurat($pengajuan);
            $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
            
            $data = [
                "pengajuan" => $pengajuan,
                "additionalData" => $additionalData,
                "nomorSurat" => $nomorSurat,
                "tanggalSurat" => now()->locale("id")->isoFormat("D MMMM Y"),
                "barcodeSignatures" => $barcodeSignatures,
                "isPreview" => true
            ];
            
            return view("surat.fsi.preview-with-signature", $data);
            
        } catch (\Exception $e) {
            Log::error("Error in FSI preview", [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Gagal memuat preview surat: ' . $e->getMessage());
        }
    }
    
    public function generatePdf(Request $request, $id)
    {
        try {
            $request->validate([
                "barcode_signature_id" => "required|exists:barcode_signatures,id"
            ]);
            
            $user = Auth::user();
            
            if (!$user->hasRole("staff_fakultas")) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            DB::beginTransaction();
            
            $pengajuan = PengajuanSurat::with(['jenisSurat', 'prodi.fakultas'])->findOrFail($id);
            $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
            $barcodeSignature = BarcodeSignature::findOrFail($request->barcode_signature_id);
            
            $nomorSurat = $this->generateNomorSurat($pengajuan);
            
            $suratGenerated = SuratGenerated::create([
                "pengajuan_id" => $pengajuan->id,
                "nomor_surat" => $nomorSurat,
                "barcode_signature_id" => $barcodeSignature->id,
                "file_path" => null,
                "generated_by" => $user->id,
                "signed_by" => $barcodeSignature->pejabat_nama,
                "signed_at" => now(),
                "status" => "completed"
            ]);
            
            // Get edited data
            $editedData = $request->input('edited_data', []);
            
            // Apply ALL edited fields to display data
            $displayData = [
                'nama' => $editedData['nama'] ?? $pengajuan->nama_mahasiswa,
                'nim' => $editedData['nim'] ?? $pengajuan->nim,
                'prodi' => $editedData['prodi'] ?? $pengajuan->prodi->nama_prodi,
                'semester' => $editedData['semester'] ?? ($additionalData['semester'] ?? 'Ganjil'),
                'tahun_akademik' => $editedData['tahun_akademik'] ?? ($additionalData['tahun_akademik'] ?? '2024/2025')
            ];
            
            // Update orang tua data if edited
            if (isset($additionalData['orang_tua'])) {
                foreach (['nama', 'tempat_lahir', 'tanggal_lahir', 'pekerjaan', 'nip', 
                         'pangkat_golongan', 'instansi', 'alamat_instansi', 'alamat_rumah'] as $key) {
                    $editKey = "ortu_{$key}";
                    if (isset($editedData[$editKey])) {
                        $additionalData['orang_tua'][$key] = $editedData[$editKey];
                    }
                }
            }
            
            // Update semester & tahun in additionalData if edited
            if (isset($editedData['semester'])) {
                $additionalData['semester'] = $editedData['semester'];
            }
            if (isset($editedData['tahun_akademik'])) {
                $additionalData['tahun_akademik'] = $editedData['tahun_akademik'];
            }
            
            $pdfData = [
                "pengajuan" => $pengajuan,
                "additionalData" => $additionalData,
                "displayData" => $displayData,
                "nomorSurat" => $nomorSurat,
                "tanggalSurat" => now()->locale("id")->isoFormat("D MMMM Y"),
                "barcodeImage" => $this->getBarcodeImage($barcodeSignature),
                "penandatangan" => [
                    "nama" => $barcodeSignature->pejabat_nama,
                    "pangkat" => $barcodeSignature->pejabat_pangkat ?? 'PENATA MUDA TK.I – III/B',
                    "jabatan" => $barcodeSignature->pejabat_jabatan,
                    "nid" => $barcodeSignature->pejabat_nid ?? ''
                ]
            ];
            
            $pdf = PDF::loadView("surat.pdf.fsi-surat-final", $pdfData);
            $pdf->setPaper("A4", "portrait");
            
            $fileName = "surat_fsi_" . $pengajuan->nim . "_" . time() . ".pdf";
            $filePath = "surat/generated/" . $fileName;
            
            $pdfContent = $pdf->output();
            Storage::put("public/" . $filePath, $pdfContent);
            
            $suratGenerated->update(["file_path" => $filePath]);
            
            $pengajuan->update([
                "status" => "completed",
                "surat_generated_id" => $suratGenerated->id,
                "completed_at" => now()
            ]);
            
            DB::commit();
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length' => strlen($pdfContent)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error generating FSI PDF", [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Gagal generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function getBarcodeImage($barcodeSignature)
    {
        try {
            if ($barcodeSignature->barcode_path && Storage::exists("public/" . $barcodeSignature->barcode_path)) {
                $imageContent = Storage::get("public/" . $barcodeSignature->barcode_path);
                return base64_encode($imageContent);
            }
            
            if ($barcodeSignature->barcode_path && Storage::exists($barcodeSignature->barcode_path)) {
                $imageContent = Storage::get($barcodeSignature->barcode_path);
                return base64_encode($imageContent);
            }
        } catch (\Exception $e) {
            Log::error("Error loading barcode image", [
                'barcode_id' => $barcodeSignature->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    private function parseAdditionalData($data)
    {
        if (empty($data)) {
            return [];
        }
        
        if (is_array($data)) {
            return $data;
        }
        
        if (is_string($data)) {
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to parse additional_data", ['error' => $e->getMessage()]);
            }
        }
        
        if (is_object($data)) {
            return (array) $data;
        }
        
        return [];
    }
    
    private function generateNomorSurat($pengajuan)
    {
        try {
            $currentYear = date("Y");
            $lastNumber = SuratGenerated::whereYear("created_at", $currentYear)
                ->where('pengajuan_id', '!=', $pengajuan->id)
                ->count();
            
            $nomorUrut = str_pad($lastNumber + 1, 3, "0", STR_PAD_LEFT);
            $bulanRomawi = $this->getRomanMonth(date("n"));
            $tahun = $currentYear;
            
            return "P/{$nomorUrut}/FSI-UNJANI/{$bulanRomawi}/{$tahun}";
            
        } catch (\Exception $e) {
            return "P/001/FSI-UNJANI/" . $this->getRomanMonth(date("n")) . "/" . date("Y");
        }
    }
    
    private function getRomanMonth($month)
    {
        $romans = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
        return ($month >= 1 && $month <= 12) ? $romans[$month - 1] : "I";
    }
}