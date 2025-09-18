<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\JenisSurat;
use App\Models\Jabatan;
use App\Models\Tracking;
use App\Models\User;
use App\Traits\HasNomorSurat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\NewSuratNotification;
use App\Notifications\VerificationCompleteNotification; // Import
use App\Notifications\SuratRejectedNotification; // Import

class ApiSuratController extends Controller
{
    use HasNomorSurat;

    public function index()
    {
        $surats = Surat::with(['jenisSurat', 'currentStatus', 'createdBy', 'tujuanJabatan'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Surat list retrieved successfully.',
            'data' => $surats
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'perihal' => 'required|string|max:255',
            'tujuan_jabatan_id' => 'required|exists:jabatan,id',
            'lampiran' => 'nullable|string|max:255',
            'prodi_id' => 'required|exists:prodi,id',
            'fakultas_id' => 'required|exists:fakultas,id',
            'tanggal_surat' => 'required|date',
            'sifat_surat' => 'required|in:Biasa,Segera,Rahasia',
            'file_surat' => 'required|file|mimes:pdf|max:10240', // Max 10MB
            'created_by_user_id' => 'sometimes|exists:users,id', // Optional: if API user is different from creator
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'data' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($request->hasFile('file_surat')) {
                $filePath = $request->file('file_surat')->store('surat_pdfs', 'public');
            }

            $createdBy = $request->input('created_by_user_id', Auth::id());
            if (!$createdBy) {
                throw new \Exception('User ID for created_by is required or user must be authenticated.');
            }

            $nomorSurat = $this->generateNomorSurat($request->fakultas_id, date('Y', strtotime($request->tanggal_surat)));

            $draftStatus = StatusSurat::where('kode_status', 'draft')->firstOrFail();
            $jenisSuratDefault = JenisSurat::first();

            $surat = Surat::create([
                'nomor_surat' => $nomorSurat,
                'perihal' => $request->perihal,
                'tujuan_jabatan_id' => $request->tujuan_jabatan_id,
                'lampiran' => $request->lampiran,
                'jenis_id' => $jenisSuratDefault ? $jenisSuratDefault->id : null,
                'status_id' => $draftStatus->id,
                'created_by' => $createdBy,
                'tanggal_surat' => $request->tanggal_surat,
                'sifat_surat' => $request->sifat_surat,
                'file_surat' => $filePath,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Surat created successfully.',
                'data' => $surat
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to create surat: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $surat = Surat::find($id);

        if (!$surat) {
            return response()->json([
                'success' => false,
                'message' => 'Surat not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'perihal' => 'sometimes|required|string|max:255',
            'tujuan_jabatan_id' => 'sometimes|required|exists:jabatan,id',
            'lampiran' => 'nullable|string|max:255',
            'prodi_id' => 'sometimes|required|exists:prodi,id',
            'fakultas_id' => 'sometimes|required|exists:fakultas,id',
            'tanggal_surat' => 'sometimes|required|date',
            'sifat_surat' => 'sometimes|required|in:Biasa,Segera,Rahasia',
            'file_surat' => 'nullable|file|mimes:pdf|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'data' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $filePath = $surat->file_surat; // Keep existing file path by default
            if ($request->hasFile('file_surat')) {
                // Delete old file if exists
                if ($filePath) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $request->file('file_surat')->store('surat_pdfs', 'public');
            }

            // Check if any significant fields changed that require a revision number
            $needsRevision = false;
            if ($request->has('perihal') && $surat->perihal !== $request->perihal ||
                $request->has('tujuan_jabatan_id') && $surat->tujuan_jabatan_id !== (int)$request->tujuan_jabatan_id ||
                $request->has('sifat_surat') && $surat->sifat_surat !== $request->sifat_surat ||
                $request->has('tanggal_surat') && $surat->tanggal_surat->format('Y-m-d') !== $request->tanggal_surat ||
                ($request->hasFile('file_surat') && $request->file('file_surat')->isValid()))
            {
                $needsRevision = true;
            }

            $nomorSurat = $surat->nomor_surat;
            if ($needsRevision) {
                $nomorSurat = $this->generateNomorSurat(
                    $request->input('fakultas_id', $surat->fakultas_id),
                    date('Y', strtotime($request->input('tanggal_surat', $surat->tanggal_surat->format('Y-m-d')))),
                    $surat->nomor_surat,
                    true // Indicate it's a revision
                );
            }

            $surat->update([
                'nomor_surat' => $nomorSurat,
                'perihal' => $request->input('perihal', $surat->perihal),
                'tujuan_jabatan_id' => $request->input('tujuan_jabatan_id', $surat->tujuan_jabatan_id),
                'lampiran' => $request->input('lampiran', $surat->lampiran),
                'prodi_id' => $request->input('prodi_id', $surat->prodi_id),
                'fakultas_id' => $request->input('fakultas_id', $surat->fakultas_id),
                'tanggal_surat' => $request->input('tanggal_surat', $surat->tanggal_surat),
                'sifat_surat' => $request->input('sifat_surat', $surat->sifat_surat),
                'file_surat' => $filePath,
                'updated_by' => Auth::id(), // Assuming authenticated user updates
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Surat updated successfully.',
                'data' => $surat
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            // If a new file was uploaded and transaction failed, delete it
            if ($request->hasFile('file_surat') && $filePath && $filePath !== $surat->file_surat) {
                Storage::disk('public')->delete($filePath);
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to update surat: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function submit(Request $request, $id)
    {
        $surat = Surat::find($id);

        if (!$surat) {
            return response()->json([
                'success' => false,
                'message' => 'Surat not found.'
            ], 404);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.'
            ], 401);
        }

        $reviewKaprodiStatus = StatusSurat::where('kode_status', 'review_kaprodi')->firstOrFail();
        $draftStatus = StatusSurat::where('kode_status', 'draft')->firstOrFail();
        $revisiOpsionalStatus = StatusSurat::where('kode_status', 'revisi_opsional')->firstOrFail();

        // Only allow submission if the current status is draft or revisi_opsional
        if (!in_array($surat->status_id, [$draftStatus->id, $revisiOpsionalStatus->id])) {
            return response()->json([
                'success' => false,
                'message' => 'Surat cannot be submitted. Current status is not draft or needs revision.'
            ], 400);
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
                'keterangan' => 'Surat disubmit untuk review Kaprodi via API oleh ' . $user->nama,
                'data_after' => $surat->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Notify Staff Fakultas (Bagian Umum) - Reusing NewSuratNotification for simplicity
            $staffFakultasJabatan = Jabatan::where('nama_jabatan', 'Staff Fakultas')->first();
            if ($staffFakultasJabatan) {
                $staffFakultasUsers = User::where('jabatan_id', $staffFakultasJabatan->id)->get();
                foreach ($staffFakultasUsers as $staffUser) {
                    // Assuming NewSuratNotification is suitable for this context
                    $staffUser->notify(new NewSuratNotification($surat));
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Surat submitted successfully for Kaprodi review.',
                'data' => $surat
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit surat: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function verify(Request $request, $id)
    {
        $surat = Surat::find($id);

        if (!$surat) {
            return response()->json([
                'success' => false,
                'message' => 'Surat not found.'
            ], 404);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'keterangan' => 'nullable|string|max:500', // Required for reject
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'data' => $validator->errors()
            ], 400);
        }

        $verifikasiUmumStatus = StatusSurat::where('kode_status', 'verifikasi_umum')->firstOrFail();

        if ($surat->status_id !== $verifikasiUmumStatus->id) {
            return response()->json([
                'success' => false,
                'message' => 'Surat is not in "verifikasi_umum" status.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($request->action === 'approve') {
                $disposisiPimpinanStatus = StatusSurat::where('kode_status', 'disposisi_pimpinan')->firstOrFail();
                $surat->update([
                    'status_id' => $disposisiPimpinanStatus->id,
                    'updated_by' => $user->id,
                ]);

                Tracking::create([
                    'surat_id' => $surat->id,
                    'user_id' => $user->id,
                    'action' => 'verified_approved',
                    'keterangan' => 'Surat disetujui oleh Bagian Umum via API oleh ' . $user->nama,
                    'data_after' => $surat->toArray(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Notify the creator of the surat
                $surat->createdBy->notify(new VerificationCompleteNotification($surat));

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Surat approved successfully.',
                    'data' => $surat
                ], 200);

            } elseif ($request->action === 'reject') {
                $ditolakUmumStatus = StatusSurat::where('kode_status', 'ditolak_umum')->firstOrFail();

                // Validate keterangan for rejection
                if (empty($request->keterangan)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Keterangan is required for rejection.'
                    ], 400);
                }

                // Generate revision number
                $nomorSurat = $this->generateNomorSurat(
                    $surat->fakultas_id,
                    date('Y', strtotime($surat->tanggal_surat)),
                    $surat->nomor_surat,
                    true // Indicate it's a revision
                );

                $surat->update([
                    'status_id' => $ditolakUmumStatus->id,
                    'nomor_surat' => $nomorSurat,
                    'updated_by' => $user->id,
                ]);

                Tracking::create([
                    'surat_id' => $surat->id,
                    'user_id' => $user->id,
                    'action' => 'verified_rejected',
                    'keterangan' => 'Surat ditolak oleh Bagian Umum via API oleh ' . $user->nama . ': ' . $request->keterangan,
                    'data_after' => $surat->toArray(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Notify the creator of the surat about rejection
                $surat->createdBy->notify(new SuratRejectedNotification($surat, $request->keterangan));

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Surat rejected successfully.',
                    'data' => $surat
                ], 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify surat: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}