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
        'headers' => $request->headers->all()
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
    ];

    // Dynamic validation berdasarkan jenis surat
    $jenisId = $request->jenis_surat_id;
    
    if ($jenisId == 1) {
        // Surat Mahasiswa Aktif - validasi tambahan
        $rules = array_merge($rules, [
            'semester' => 'required|integer|min:1|max:14',
            'tahun_akademik' => 'required|string|max:10',
            'nama_orang_tua' => 'required|string|max:255',
            'tempat_lahir_ortu' => 'required|string|max:100',
            'tanggal_lahir_ortu' => 'required|date',
            'pekerjaan_ortu' => 'required|string|max:100',
            'pendidikan_terakhir_ortu' => 'required|string|max:20',
            'alamat_rumah_ortu' => 'required|string',
        ]);
    } else if ($jenisId == 2) {
        // Surat Kerja Praktek - validasi tambahan
        $rules = array_merge($rules, [
            'nama_perusahaan' => 'required|string|max:255',
            'alamat_perusahaan' => 'required|string',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after:periode_mulai',
        ]);
    } else if ($jenisId == 3) {
        // Surat Tugas Akhir - validasi tambahan
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
    $additionalData = [];
    
    if ($jenisId == 1) {
        // Mahasiswa Aktif - simpan data tambahan
        $additionalData = [
            'semester' => $request->semester,
            'tahun_akademik' => $request->tahun_akademik,
            'orang_tua' => [
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
            ]
        ];
    } else if ($jenisId == 2) {
        // Kerja Praktek - simpan data tambahan
        $additionalData = [
            'kerja_praktek' => [
                'nama_perusahaan' => $request->nama_perusahaan,
                'alamat_perusahaan' => $request->alamat_perusahaan,
                'periode_mulai' => $request->periode_mulai,
                'periode_selesai' => $request->periode_selesai,
                'bidang_kerja' => $request->bidang_kerja,
            ]
        ];
    } else if ($jenisId == 3) {
        // Tugas Akhir - simpan data tambahan
        $additionalData = [
            'tugas_akhir' => [
                'judul_ta' => $request->judul_ta,
                'dosen_pembimbing1' => $request->dosen_pembimbing1,
                'dosen_pembimbing2' => $request->dosen_pembimbing2,
                'lokasi_penelitian' => $request->lokasi_penelitian,
            ]
        ];
    } else if ($jenisId == 4) {
        // Keterangan - simpan data tambahan
        $additionalData = [
            'keterangan_khusus' => $request->keterangan_khusus,
        ];
    }

    try {
        // Simpan ke database
        $pengajuan = PengajuanSurat::create([
            'tracking_token' => $trackingToken,
            'nim' => $validated['nim'],
            'nama_mahasiswa' => $validated['nama_mahasiswa'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'prodi_id' => $validated['prodi_id'],
            'jenis_surat_id' => $validated['jenis_surat_id'],
            'keperluan' => $validated['keperluan'],
            'additional_data' => json_encode($additionalData), // Simpan sebagai JSON
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

        return redirect()->back()->with('success', 
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
    public function createSuratFromPengajuan($id)
    {
        $pengajuan = PengajuanSurat::findOrFail($id);
        
        if (auth()->user()->prodi_id != $pengajuan->prodi_id) {
            abort(403);
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
        
        return redirect()->route('staff.surat.create');
    }

    /**
     * Public tracking
     */
    public function tracking($token = null)
{
    return view('public.tracking.show', compact('token'));
}

/**
 * API endpoint for tracking search
 * Route: POST /tracking/api
 */
public function trackingApi(Request $request)
{
    $request->validate([
        'tracking_token' => 'required|string|min:10|max:15', // Flexible untuk format TRK-XXXXXXXX
    ]);

    try {
        // Clean token format
        $token = strtoupper(trim($request->tracking_token));
        
        // Find pengajuan by tracking token
        $pengajuan = PengajuanSurat::with(['prodi', 'jenisSurat'])
                                  ->where('tracking_token', $token)
                                  ->first();

        if (!$pengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan dengan token tracking "' . $token . '" tidak ditemukan. Pastikan token yang Anda masukkan benar.'
            ], 404);
        }

        // Return pengajuan data dengan structure yang clean
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

    // Private methods
    private function generatePerihal($pengajuan)
    {
        $templates = [
            'aktif_kuliah' => 'Surat Keterangan Aktif Kuliah untuk ' . $pengajuan->nama_mahasiswa,
            'izin_penelitian' => 'Surat Izin Penelitian untuk ' . $pengajuan->nama_mahasiswa,
        ];
        
        return $templates[$pengajuan->jenis_surat] ?? 'Permohonan Surat - ' . $pengajuan->nama_mahasiswa;
    }

    private function prepareAdditionalData(Request $request)
    {
        $jenisSurat = JenisSurat::find($request->jenis_surat_id);
        $additional = [];

        if (!$jenisSurat) {
            return $additional;
        }

        if ($jenisSurat->kode_surat === 'MA') {
            $additional = [
                'semester' => $request->semester,
                'ipk' => $request->ipk,
                'tahun_akademik' => $request->tahun_akademik
            ];
        } elseif ($jenisSurat->kode_surat === 'KP') {
            $additional = [
                'nama_perusahaan' => $request->nama_perusahaan,
                'alamat_perusahaan' => $request->alamat_perusahaan,
                'periode_mulai' => $request->periode_mulai,
                'periode_selesai' => $request->periode_selesai,
                'bidang_kerja' => $request->bidang_kerja
            ];
        } elseif ($jenisSurat->kode_surat === 'TA') {
            $additional = [
                'judul_ta' => $request->judul_ta,
                'dosen_pembimbing_1' => $request->dosen_pembimbing_1,
                'dosen_pembimbing_2' => $request->dosen_pembimbing_2,
                'lokasi_penelitian' => $request->lokasi_penelitian,
                'periode_penelitian' => $request->periode_penelitian
            ];
        }

        return array_filter($additional);
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
}