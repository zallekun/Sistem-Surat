<?php

namespace App\Http\Controllers;  // ✅ Langsung di root

use App\Models\PengajuanSurat;
use App\Models\JenisSurat;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArsipSuratExport;

class FakultasArsipController extends Controller
{
 public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:staff_fakultas');
    }

    /**
     * Display archived surat (completed only) - All Prodi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->load('prodi.fakultas');
        
        $fakultasId = $user->prodi?->fakultas_id;
        if (!$fakultasId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke fakultas manapun');
        }

        $query = PengajuanSurat::with(['mahasiswa', 'jenisSurat', 'prodi'])
            ->whereHas('prodi', function($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            })
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc');

        // Apply filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_token', 'like', "%{$request->search}%")
                  ->orWhereHas('mahasiswa', function($mq) use ($request) {
                      $mq->where('nim', 'like', "%{$request->search}%")
                         ->orWhere('nama', 'like', "%{$request->search}%");
                  });
            });
        }

        if ($request->prodi_id) {
            $query->where('prodi_id', $request->prodi_id);
        }

        if ($request->jenis_surat_id) {
            $query->where('jenis_surat_id', $request->jenis_surat_id);
        }

        if ($request->date_from) {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        $pengajuans = $query->paginate(15);
        
        // Get filter options
        $prodis = Prodi::where('fakultas_id', $fakultasId)->get();
        $jenisSurat = JenisSurat::all();
        
        // Count for stats
        $totalCount = PengajuanSurat::whereHas('prodi', function($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            })
            ->where('status', 'completed')
            ->count();
        
        $thisMonthCount = PengajuanSurat::whereHas('prodi', function($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            })
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        return view('fakultas.arsip.index', compact(
            'pengajuans',
            'prodis',
            'jenisSurat',
            'totalCount',
            'thisMonthCount'
        ));
    }

    /**
     * Display the specified archived surat
     */
    public function show($id)
    {
        $user = Auth::user();
        $user->load('prodi.fakultas');
        
        $fakultasId = $user->prodi?->fakultas_id;
        
        $pengajuan = PengajuanSurat::with([
            'mahasiswa',
            'prodi',
            'jenisSurat',
            'approvalHistories.performedBy',
            'trackingHistory'
        ])
        ->whereHas('prodi', function($q) use ($fakultasId) {
            $q->where('fakultas_id', $fakultasId);
        })
        ->where('status', 'completed')
        ->findOrFail($id);

        $additionalData = $this->parseAdditionalData($pengajuan->additional_data);

        return view('fakultas.arsip.show', compact('pengajuan', 'additionalData'));
    }

    /**
     * Export arsip to Excel
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $user->load('prodi.fakultas');
        
        $fakultasId = $user->prodi?->fakultas_id;
        
        $query = PengajuanSurat::with(['mahasiswa', 'jenisSurat', 'prodi'])
            ->whereHas('prodi', function($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            })
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc');

        // Apply same filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_token', 'like', "%{$request->search}%")
                  ->orWhereHas('mahasiswa', function($mq) use ($request) {
                      $mq->where('nim', 'like', "%{$request->search}%")
                         ->orWhere('nama', 'like', "%{$request->search}%");
                  });
            });
        }

        if ($request->prodi_id) {
            $query->where('prodi_id', $request->prodi_id);
        }

        if ($request->jenis_surat_id) {
            $query->where('jenis_surat_id', $request->jenis_surat_id);
        }

        if ($request->date_from) {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        $data = $query->get();

        $filename = 'arsip_surat_fakultas_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new ArsipSuratExport($data, 'fakultas'), $filename);
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
                \Log::error('Error parsing additional_data', ['error' => $e->getMessage()]);
            }
            return ['data' => $data];
        }
        
        if (is_object($data)) {
            return (array) $data;
        }
        
        return null;
    }
}