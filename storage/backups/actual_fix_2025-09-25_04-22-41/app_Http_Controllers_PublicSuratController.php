<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicSuratController extends Controller
{
    /**
     * Display list for staff or redirect to form for public
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('public.pengajuan.create');
        }
        
        $user = auth()->user();
        if (!$user->hasRole('staff_prodi')) {
            abort(403, 'Unauthorized access');
        }
        
        $pengajuans = PengajuanSurat::where('prodi_id', $user->prodi_id)
            ->with(['prodi', 'jenisSurat'])
            ->latest()
            ->paginate(10);
        
        return view('public.pengajuan.list', compact('pengajuans'));
    }

    /**
     * Show form for public submission
     */
    public function create()
    {
        $prodi = Prodi::select('id', 'nama_prodi', 'kode_prodi')
            ->orderBy('nama_prodi')
            ->get();
        $jenisSurat = JenisSurat::select('id', 'nama_jenis', 'kode_surat')
            ->orderBy('nama_jenis')
            ->get();
        
        return view('public.pengajuan.form', compact('prodi', 'jenisSurat'));
    }

    /**
     * Store pengajuan from public form
     */
    public function store(Request $request)
    {
        // Debug logging
        \Log::info('Store method called', [
            'method' => $request->method(),
            'has_token' => $request->has('_token'),
            'token' => substr($request->input('_token'), 0, 10) . '...',
            'is_ajax' => $request->ajax(),
        ]);
        
        // Base validation rules
        $rules = [
            'jenis_surat_id' => 'required|exists:jenis_surat,id',
            'nim' => 'required|string|max:20',
            'nama_mahasiswa' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'prodi_id' => 'required|exists:prodi,id',
            'keperluan' => 'required|string',

                    // UNIVERSAL FIELDS - Required for ALL jenis surat
            'semester' => 'required|integer|min:1|max:14',
            'tahun_akademik' => 'required|string|max:10',
            'dosen_wali_nama' => 'required|string|max:255',
            'dosen_wali_nid' => 'nullable|string|max:50',
        ];

            // Dynamic validation berdasarkan jenis surat
    $jenisId = $request->jenis_surat_id;
    
    if ($jenisId == 1) {
        // Surat Mahasiswa Aktif - HANYA validasi orang tua (semester & tahun akademik sudah di base rules)
        $rules = array_merge($rules, [
            'nama_orang_tua' => 'required|string|max:255',
            'tempat_lahir_ortu' => 'required|string|max:100',
            'tanggal_lahir_ortu' => 'required|date',
            'pekerjaan_ortu' => 'required|string|max:100',
            'pendidikan_terakhir_ortu' => 'required|string|max:20',
            'alamat_rumah_ortu' => 'required|string',
        ]);
    } else if ($jenisId == 2) {
        // Surat Kerja Praktek
        $rules = array_merge($rules, [
            'nama_perusahaan' => 'required|string|max:255',
            'alamat_perusahaan' => 'required|string',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after:periode_mulai',
        ]);
    } else if ($jenisId == 3) {
        // Surat Tugas Akhir
        $rules = array_merge($rules, [
            'judul_ta' => 'required|string',
            'dosen_pembimbing1' => 'required|string|max:255',
        ]);
    }

    // Validate request
    $validated = $request->validate($rules);

    // Generate tracking token
    $trackingToken = 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 8));

    // Prepare additional data untuk JSON storage
    $additionalData = [
        // UNIVERSAL DATA - included for all jenis surat
        'semester' => $request->semester,
        'tahun_akademik' => $request->tahun_akademik,
        'dosen_wali' => [
            'nama' => $request->dosen_wali_nama,
            'nid' => $request->dosen_wali_nid
        ]
    ];
    
    if ($jenisId == 1) {
        // Mahasiswa Aktif - add orang tua data
        $additionalData['orang_tua'] = [
            'nama' => $request->nama_orang_tua,
            'tempat_lahir' => $request->tempat_lahir_ortu,
            'tanggal_lahir' => $request->tanggal_lahir_ortu,
            'pekerjaan' => $request->pekerjaan_ortu,
            'nip' => $request->nip_ortu,
            'jabatan' => $request->jabatan_ortu,
            'pangkat_golongan' => $request->pangkat_golongan_ortu,
            'pendidikan_terakhir' => $request->pendidikan_terakhir_ortu,
            'alamat_instansi' => $request->alamat_instansi_ortu,
            'alamat_rumah' => $request->alamat_rumah_ortu,
        ];
    } else if ($jenisId == 2) {
        // Kerja Praktek
        $additionalData['kerja_praktek'] = [
            'nama_perusahaan' => $request->nama_perusahaan,
            'alamat_perusahaan' => $request->alamat_perusahaan,
            'periode_mulai' => $request->periode_mulai,
            'periode_selesai' => $request->periode_selesai,
            'bidang_kerja' => $request->bidang_kerja,
        ];
    } else if ($jenisId == 3) {
        // Tugas Akhir
        $additionalData['tugas_akhir'] = [
            'judul_ta' => $request->judul_ta,
            'dosen_pembimbing1' => $request->dosen_pembimbing1,
            'dosen_pembimbing2' => $request->dosen_pembimbing2,
            'lokasi_penelitian' => $request->lokasi_penelitian,
        ];
    } else if ($jenisId == 4) {
        // Keterangan
        $additionalData['keterangan_khusus'] = $request->keterangan_khusus;
    }

    try {
        // Simpan ke database - rest of the method remains the same
        $pengajuan = PengajuanSurat::create([
            'tracking_token' => $trackingToken,
            'nim' => $validated['nim'],
            'nama_mahasiswa' => $validated['nama_mahasiswa'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'prodi_id' => $validated['prodi_id'],
            'jenis_surat_id' => $validated['jenis_surat_id'],
            'keperluan' => $validated['keperluan'],
            'additional_data' => json_encode($additionalData),
            'status' => 'pending',
        ]);

        // Return success response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan surat berhasil dikirim',
                'tracking_token' => $trackingToken,
                'pengajuan_id' => $pengajuan->id,
            ]);
        }

        return redirect()->route('tracking.show', $trackingToken)->with('success', 
            'Pengajuan surat berhasil dikirim. Token tracking: ' . $trackingToken
        );

    } catch (\Exception $e) {
        \Log::error('Error creating pengajuan surat', [
            'error' => $e->getMessage(),
            'request_data' => $request->except(['_token']),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
            ], 500);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
    }
}

    /**
     * Show detail pengajuan for staff
     */
    public function show($id)
    {
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat'])->findOrFail($id);
        
        // Decode additional data dari JSON
        $additionalData = json_decode($pengajuan->additional_data, true) ?? [];
        
        return view('staff.pengajuan.show', compact('pengajuan', 'additionalData'));
    }

    /**
     * Generate surat data from pengajuan
     */
    public function generateSuratData($pengajuanId)
    {
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat'])->findOrFail($pengajuanId);
        $additionalData = json_decode($pengajuan->additional_data, true) ?? [];
        
        $suratData = [
            'mahasiswa' => [
                'nim' => $pengajuan->nim,
                'nama' => $pengajuan->nama_mahasiswa,
                'prodi' => $pengajuan->prodi->nama_prodi,
                'email' => $pengajuan->email,
                'phone' => $pengajuan->phone,
            ],
            'keperluan' => $pengajuan->keperluan,
            'jenis_surat' => $pengajuan->jenisSurat->nama_jenis,
            'additional_data' => $additionalData,
            'tanggal_pengajuan' => $pengajuan->created_at,
        ];
        
        return $suratData;
    }

    /**
     * Process pengajuan to create surat
     */
    public function createSuratFromPengajuan(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanSurat::findOrFail($id);

            if (auth()->user()->prodi_id != $pengajuan->prodi_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $pengajuan->update([
                'status' => 'processed',
                'processed_by' => auth()->id(),
                'processed_at' => now()
            ]);

            session()->flash('pengajuan_data', [
                'perihal' => $this->generatePerihal($pengajuan),
                'pengajuan_id' => $pengajuan->id,
                'nama_mahasiswa' => $pengajuan->nama_mahasiswa,
                'nim' => $pengajuan->nim,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pengajuan berhasil diproses.', 'redirect_url' => route('staff.surat.create')]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing pengajuan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memproses pengajuan.'], 500);
        }
    }

    // TRACKING METHODS - CLEANED UP
    
    /**
     * Show tracking index page (public.tracking.index) 
     * Route name: tracking.public
     */
    public function trackingIndex()
    {
        return view('public.tracking.index');
    }

    /**
 * Get dosen wali list by prodi (for dropdown)
 * API endpoint: /api/dosen-wali/{prodi_id}
 */
public function getDosenWali($prodiId)
{
    try {
        $dosenWaliList = \App\Models\DosenWali::where('prodi_id', $prodiId)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nid']);

        return response()->json([
            'success' => true,
            'data' => $dosenWaliList
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching dosen wali', [
            'error' => $e->getMessage(),
            'prodi_id' => $prodiId,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengambil data dosen wali'
        ], 500);
    }
}

/**
 * Search dosen wali by name (for autocomplete)
 */
public function searchDosenWali(Request $request)
{
    $request->validate([
        'prodi_id' => 'required|exists:prodi,id',
        'query' => 'required|string|min:2',
    ]);

    try {
        $dosenWaliList = \App\Models\DosenWali::where('prodi_id', $request->prodi_id)
            ->where('is_active', true)
            ->where('nama', 'LIKE', '%' . $request->query . '%')
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nama', 'nid']);

        return response()->json([
            'success' => true,
            'data' => $dosenWaliList
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mencari dosen wali'
        ], 500);
    }
}

    public function trackingShow($token)
{
    try {
        // Find pengajuan with suratGenerated relationship
        $pengajuan = PengajuanSurat::with(['jenisSurat', 'prodi', 'suratGenerated'])
            ->where('tracking_token', $token)
            ->first();

        if (!$pengajuan) {
            return view('public.tracking.index', [
                'error' => 'Token tidak ditemukan atau tidak valid',
                'searched_token' => $token
            ]);
        }

        // Parse additional data
        $additionalData = [];
        if ($pengajuan->additional_data) {
            $additionalData = is_string($pengajuan->additional_data) 
                ? json_decode($pengajuan->additional_data, true) 
                : $pengajuan->additional_data;
        }

        // Check if PDF available - TAMBAHKAN INI
        $pdfAvailable = ($pengajuan->status === 'completed' && 
                        $pengajuan->suratGenerated && 
                        $pengajuan->suratGenerated->file_path);

        return view('public.tracking.show', [
            'pengajuan' => $pengajuan,
            'additionalData' => $additionalData,
            'token' => $token,
            'pdfAvailable' => $pdfAvailable // TAMBAHKAN INI
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Tracking show error: ' . $e->getMessage());
        
        return view('public.tracking.index', [
            'error' => 'Terjadi kesalahan sistem'
        ]);
    }
}

    /**
     * Handle tracking search from POST form
     */
    public function trackingSearch(Request $request)
    {
        $request->validate([
            'token' => 'required|string|min:3'
        ]);

        // Clean the token
        $token = strtoupper(trim($request->token));
        
        // Redirect to show page for clean URL
        return redirect()->route('tracking.show', $token);
    }

    /**
     * Existing tracking method - works as router
     */
    public function tracking($token = null)
    {
        if (!$token) {
            // No token provided, show index
            return $this->trackingIndex();
        }
        
        // Token provided, show result
        return $this->trackingShow($token);
    }

    public function trackingApi(Request $request)
{
    $request->validate([
        'tracking_token' => 'required|string|min:10|max:15',
    ]);

    try {
        $token = strtoupper(trim($request->tracking_token));
        
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat', 'suratGenerated'])
                                  ->where('tracking_token', $token)
                                  ->first();

        if (!$pengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan dengan token tracking "' . $token . '" tidak ditemukan.'
            ], 404);
        }

        // Check if PDF is available using model method
        $pdfAvailable = $pengajuan->hasPdfFile(); // Menggunakan method dari model

        return response()->json([
            'success' => true,
            'pengajuan' => [
                'id' => $pengajuan->id,
                'tracking_token' => $pengajuan->tracking_token,
                'nim' => $pengajuan->nim,
                'nama_mahasiswa' => $pengajuan->nama_mahasiswa,
                'email' => $pengajuan->email,
                'phone' => $pengajuan->phone,
                'keperluan' => $pengajuan->keperluan,
                'status' => $pengajuan->status,
                'additional_data' => $pengajuan->additional_data,
                'created_at' => $pengajuan->created_at,
                'updated_at' => $pengajuan->updated_at,
                'pdf_available' => $pdfAvailable,
                'download_url' => $pdfAvailable ? route('tracking.download', $pengajuan->id) : null,
                'prodi' => $pengajuan->prodi ? [
                    'id' => $pengajuan->prodi->id,
                    'nama_prodi' => $pengajuan->prodi->nama_prodi,
                    'kode_prodi' => $pengajuan->prodi->kode_prodi,
                ] : null,
                'jenis_surat' => $pengajuan->jenisSurat ? [
                    'id' => $pengajuan->jenisSurat->id,
                    'nama_jenis' => $pengajuan->jenisSurat->nama_jenis,
                    'kode_surat' => $pengajuan->jenisSurat->kode_surat,
                ] : null,
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in tracking API', [
            'error' => $e->getMessage(),
            'token' => $request->tracking_token,
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.'
        ], 500);
    }
}

    // PRIVATE METHODS

    private function generatePerihal($pengajuan)
    {
        $templates = [
            'aktif_kuliah' => 'Surat Keterangan Aktif Kuliah untuk ' . $pengajuan->nama_mahasiswa,
            'izin_penelitian' => 'Surat Izin Penelitian untuk ' . $pengajuan->nama_mahasiswa,
        ];
        
        return $templates[$pengajuan->jenis_surat] ?? 'Permohonan Surat - ' . $pengajuan->nama_mahasiswa;
    }

    private function sendTrackingEmail(PengajuanSurat $pengajuan)
    {
        try {
            $subject = "Konfirmasi Pengajuan Surat";
            $message = "Pengajuan surat Anda telah diterima. Token: {$pengajuan->tracking_token}";

            Mail::raw($message, function ($mail) use ($pengajuan, $subject) {
                $mail->to($pengajuan->email)->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send tracking email', ['error' => $e->getMessage()]);
        }
    }

    private function notifyTUProdi(PengajuanSurat $pengajuan)
    {
        try {
            $tuProdiUsers = User::whereHas('jabatan', function($query) {
                $query->where('nama_jabatan', 'LIKE', '%TU%')
                      ->orWhere('nama_jabatan', 'LIKE', '%Tata Usaha%');
            })
            ->where('prodi_id', $pengajuan->prodi_id)
            ->where('is_active', true)
            ->get();

            // Create notifications...
        } catch (\Exception $e) {
            Log::error('Failed to notify TU Prodi', ['error' => $e->getMessage()]);
        }
    }

    private function getTimeline(PengajuanSurat $pengajuan)
    {
        $timeline = [
            [
                'title' => 'Pengajuan Diterima',
                'description' => 'Pengajuan berhasil dikirim ke sistem',
                'date' => $pengajuan->created_at->format('d/m/Y H:i'),
                'status' => 'completed',
                'icon' => '📝'
            ]
        ];

        if ($pengajuan->processed_at) {
            $timeline[] = [
                'title' => 'Diproses TU Prodi',
                'description' => "Diproses oleh {$pengajuan->processedBy?->nama}",
                'date' => $pengajuan->processed_at->format('d/m/Y H:i'),
                'status' => 'completed',
                'icon' => '⚙️'
            ];
        }

        return $timeline;
    }

    /**
 * Download PDF surat melalui tracking
 */
public function downloadSurat($id)
{
    try {
        $pengajuan = PengajuanSurat::with(['suratGenerated', 'jenisSurat'])->findOrFail($id);
        
        // Check if surat completed and has file
        if ($pengajuan->status !== 'completed') {
            return redirect()->route('tracking.public')
                           ->with('error', 'Surat belum selesai diproses.');
        }
        
        if (!$pengajuan->suratGenerated || !$pengajuan->suratGenerated->file_path) {
            return redirect()->route('tracking.public')
                           ->with('error', 'File surat belum tersedia.');
        }
        
        $suratGenerated = $pengajuan->suratGenerated;
        $filePath = $suratGenerated->file_path;
        
        // Try multiple possible paths
        $possiblePaths = [
            storage_path('app/public/' . $filePath),
            storage_path('app/' . $filePath),
            public_path('storage/' . $filePath)
        ];
        
        $fullPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $fullPath = $path;
                break;
            }
        }
        
        if (!$fullPath) {
            \Log::error('PDF file not found', [
                'pengajuan_id' => $pengajuan->id,
                'file_path' => $filePath,
                'tried_paths' => $possiblePaths,
                'tracking_token' => $pengajuan->tracking_token
            ]);
            
            return redirect()->route('tracking.public')
                           ->with('error', 'File surat tidak ditemukan di server.');
        }
        
        // Generate clean filename
        $jenisSurat = preg_replace('/[^A-Za-z0-9]/', '_', $pengajuan->jenisSurat->nama_jenis ?? 'Surat');
        $nim = preg_replace('/[^A-Za-z0-9]/', '_', $pengajuan->nim ?? 'Unknown');
        $fileName = "Surat_{$jenisSurat}_{$nim}_" . now()->format('Y-m-d') . ".pdf";
        
        // Log successful download
        \Log::info('Surat downloaded via tracking', [
            'pengajuan_id' => $pengajuan->id,
            'nim' => $pengajuan->nim,
            'tracking_token' => $pengajuan->tracking_token,
            'file_path' => $fullPath,
            'filename' => $fileName
        ]);
        
        return response()->download($fullPath, $fileName);
        
    } catch (\Exception $e) {
        \Log::error('Error downloading surat via tracking', [
            'pengajuan_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('tracking.public')
                       ->with('error', 'Gagal mendownload surat: ' . $e->getMessage());
    }
        
        if (!$pengajuan->suratGenerated) {
            return redirect()->route('tracking.public')
                           ->with('error', 'File surat belum tersedia.');
        }
        
        $suratGenerated = $pengajuan->suratGenerated;
        $filePath = $suratGenerated->file_path;
        
        if (!$filePath) {
            return redirect()->route('tracking.public')
                           ->with('error', 'Path file tidak ditemukan.');
        }
        
        // Try multiple possible paths
        $possiblePaths = [
            storage_path('app/public/' . $filePath),
            storage_path('app/' . $filePath),
            public_path('storage/' . $filePath)
        ];
        
        $fullPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $fullPath = $path;
                break;
            }
        }
        
        if (!$fullPath) {
            // Log untuk debugging
            \Log::error('PDF file not found', [
                'pengajuan_id' => $pengajuan->id,
                'file_path' => $filePath,
                'tried_paths' => $possiblePaths,
                'tracking_token' => $pengajuan->tracking_token
            ]);
            
            return redirect()->route('tracking.public')
                           ->with('error', 'File surat tidak ditemukan di server.');
        }
        
        // Generate clean filename
        $jenisSurat = preg_replace('/[^A-Za-z0-9]/', '_', $pengajuan->jenisSurat->nama_jenis ?? 'Surat');
        $nim = preg_replace('/[^A-Za-z0-9]/', '_', $pengajuan->nim ?? 'Unknown');
        $fileName = "Surat_{$jenisSurat}_{$nim}_" . now()->format('Y-m-d') . ".pdf";
        
        // Log successful download
        \Log::info('Surat downloaded via tracking', [
            'pengajuan_id' => $pengajuan->id,
            'nim' => $pengajuan->nim,
            'tracking_token' => $pengajuan->tracking_token,
            'file_path' => $fullPath,
            'filename' => $fileName
        ]);
        
        return response()->download($fullPath, $fileName);
        
    } catch (\Exception $e) {
        \Log::error('Error downloading surat via tracking', [
            'pengajuan_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('tracking.public')
                       ->with('error', 'Gagal mendownload surat: ' . $e->getMessage());
    }
}
}