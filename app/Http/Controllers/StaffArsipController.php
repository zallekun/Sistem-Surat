<?php

namespace App\Http\Controllers;  // ✅ Langsung di root

use App\Models\PengajuanSurat;
use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArsipSuratExport;

class StaffArsipController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:staff_prodi,kaprodi');
    }

    /**
     * Display archived surat (completed only)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole(['staff_prodi', 'kaprodi'])) {
            abort(403, 'Unauthorized');
        }

        $query = PengajuanSurat::with(['mahasiswa', 'jenisSurat', 'prodi'])
            ->where('prodi_id', $user->prodi_id)
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
        $jenisSurat = JenisSurat::all();
        
        // Count for stats
        $totalCount = PengajuanSurat::where('prodi_id', $user->prodi_id)
            ->where('status', 'completed')
            ->count();
        
        $thisMonthCount = PengajuanSurat::where('prodi_id', $user->prodi_id)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        return view('staff.arsip.index', compact(
            'pengajuans', 
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
        
        $pengajuan = PengajuanSurat::with([
            'mahasiswa',
            'prodi',
            'jenisSurat',
            'approvalHistories.performedBy',
            'trackingHistory'
        ])
        ->where('prodi_id', $user->prodi_id)
        ->where('status', 'completed')
        ->findOrFail($id);

        $additionalData = $this->parseAdditionalData($pengajuan->additional_data);

        return view('staff.arsip.show', compact('pengajuan', 'additionalData'));
    }

    /**
     * Export arsip to Excel
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        
        $query = PengajuanSurat::with(['mahasiswa', 'jenisSurat', 'prodi'])
            ->where('prodi_id', $user->prodi_id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc');

        // Apply same filters as index
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_token', 'like', "%{$request->search}%")
                  ->orWhereHas('mahasiswa', function($mq) use ($request) {
                      $mq->where('nim', 'like', "%{$request->search}%")
                         ->orWhere('nama', 'like', "%{$request->search}%");
                  });
            });
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

        $filename = 'arsip_surat_' . $user->prodi->kode_prodi . '_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new ArsipSuratExport($data, 'prodi'), $filename);
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