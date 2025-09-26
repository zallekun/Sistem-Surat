<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanSurat;

// Debug route untuk check print status
Route::get('/debug/print-check/{id}', function($id) {
    $data = [
        'timestamp' => now()->toDateTimeString(),
        'pengajuan_id' => $id
    ];
    
    try {
        $pengajuan = PengajuanSurat::with(['prodi.fakultas'])->findOrFail($id);
        
        $data['pengajuan'] = [
            'id' => $pengajuan->id,
            'nim' => $pengajuan->nim,
            'nama' => $pengajuan->nama_mahasiswa,
            'status' => $pengajuan->status,
            'printed_at' => $pengajuan->printed_at ? $pengajuan->printed_at->toDateTimeString() : null,
            'printed_by' => $pengajuan->printed_by,
            'prodi' => $pengajuan->prodi->nama_prodi ?? null,
            'fakultas_id' => $pengajuan->prodi->fakultas_id ?? null
        ];
        
        $data['checks'] = [
            'can_print' => method_exists($pengajuan, 'canPrintSurat') ? $pengajuan->canPrintSurat() : 'method not found',
            'already_printed' => !empty($pengajuan->printed_at),
            'status_valid' => in_array($pengajuan->status, ['approved_fakultas', 'processed'])
        ];
        
        if (auth()->check()) {
            $data['user'] = [
                'id' => auth()->id(),
                'nama' => auth()->user()->nama ?? null,
                'role' => auth()->user()->role->name ?? null,
                'prodi' => auth()->user()->prodi->nama_prodi ?? null,
                'fakultas_id' => auth()->user()->prodi->fakultas_id ?? null
            ];
            
            $data['security'] = [
                'user_fakultas_matches' => ($pengajuan->prodi->fakultas_id === auth()->user()->prodi->fakultas_id)
            ];
        } else {
            $data['user'] = 'Not authenticated';
        }
        
        $data['routes'] = [
            'print_url' => route('fakultas.surat.fsi.print', $id),
            'preview_url' => route('fakultas.surat.fsi.preview', $id)
        ];
        
        $data['view_exists'] = view()->exists('surat.pdf.fsi-print');
        
        $data['success'] = true;
        
    } catch (\Exception $e) {
        $data['success'] = false;
        $data['error'] = $e->getMessage();
        $data['trace'] = $e->getTraceAsString();
    }
    
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');

// Debug route untuk simulate print
Route::get('/debug/print-simulate/{id}', function($id) {
    $log = [];
    $log[] = '=== PRINT SIMULATION ===';
    $log[] = 'Timestamp: ' . now()->toDateTimeString();
    
    try {
        $pengajuan = PengajuanSurat::findOrFail($id);
        $log[] = 'Pengajuan loaded: YES';
        $log[] = 'Status: ' . $pengajuan->status;
        
        $viewPath = 'surat.pdf.fsi-print';
        $log[] = 'Checking view: ' . $viewPath;
        
        if (view()->exists($viewPath)) {
            $log[] = 'View exists: YES';
            
            try {
                $html = view($viewPath, [
                    'pengajuan' => $pengajuan,
                    'additionalData' => [],
                    'nomorSurat' => 'TEST/001/FSI/IX/2024',
                    'tanggalSurat' => '25 September 2024',
                    'penandatangan' => [
                        'nama' => 'TEST NAMA',
                        'pangkat' => 'TEST PANGKAT',
                        'jabatan' => 'TEST JABATAN',
                        'nid' => '123456'
                    ],
                    'forPrint' => true
                ])->render();
                
                $log[] = 'View rendered: YES';
                $log[] = 'HTML length: ' . strlen($html) . ' bytes';
                
                return response($html . '<hr><pre>' . implode("\n", $log) . '</pre>');
                
            } catch (\Exception $e) {
                $log[] = 'View render FAILED';
                $log[] = 'Error: ' . $e->getMessage();
            }
        } else {
            $log[] = 'View exists: NO';
            $log[] = 'Expected path: resources/views/surat/pdf/fsi-print.blade.php';
        }
        
    } catch (\Exception $e) {
        $log[] = 'Exception: ' . $e->getMessage();
    }
    
    return response('<pre>' . implode("\n", $log) . '</pre>');
})->middleware('auth');

// Debug redirect chain
Route::get('/debug/redirect-chain', function() {
    return response()->json([
        'session_data' => session()->all(),
        'redirect_count' => session('debug_redirect_count', 0),
        'last_url' => session('_previous.url'),
        'intended_url' => session('url.intended')
    ], 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');