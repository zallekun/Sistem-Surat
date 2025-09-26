<?php
// ============================================
// FILE 1: app/Http/Middleware/DebugRequestMiddleware.php
// ============================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $debugId = uniqid('req_');
        
        // Log request masuk
        Log::channel('debug')->info("[$debugId] REQUEST IN", [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Track redirect count
        $redirectCount = $request->session()->get('debug_redirect_count', 0);
        if ($redirectCount > 5) {
            Log::channel('debug')->error("[$debugId] REDIRECT LOOP DETECTED", [
                'redirect_count' => $redirectCount,
                'url' => $request->fullUrl()
            ]);
            
            return response()->json([
                'error' => 'Redirect loop detected',
                'redirect_count' => $redirectCount,
                'url' => $request->fullUrl(),
                'suggestion' => 'Check laravel.log for details'
            ], 500);
        }
        
        $request->session()->put('debug_redirect_count', $redirectCount + 1);
        
        // Process request
        $response = $next($request);
        
        // Log response keluar
        Log::channel('debug')->info("[$debugId] RESPONSE OUT", [
            'status' => $response->status(),
            'redirect' => $response->isRedirect(),
            'redirect_to' => $response->isRedirect() ? $response->headers->get('Location') : null,
            'content_type' => $response->headers->get('Content-Type'),
            'content_length' => strlen($response->getContent())
        ]);
        
        // Reset counter jika sukses
        if ($response->isSuccessful() && !$response->isRedirect()) {
            $request->session()->forget('debug_redirect_count');
        }
        
        return $response;
    }
}

// ============================================
// FILE 2: routes/debug.php (buat file baru)
// ============================================

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

// Debug route untuk simulate print (tanpa update DB)
Route::get('/debug/print-simulate/{id}', function($id) {
    $log = [];
    $log[] = '=== PRINT SIMULATION ===';
    $log[] = 'Timestamp: ' . now()->toDateTimeString();
    
    try {
        $pengajuan = PengajuanSurat::findOrFail($id);
        $log[] = 'Pengajuan loaded: YES';
        $log[] = 'Status: ' . $pengajuan->status;
        
        // Check view
        $viewPath = 'surat.pdf.fsi-print';
        $log[] = 'Checking view: ' . $viewPath;
        
        if (view()->exists($viewPath)) {
            $log[] = 'View exists: YES';
            
            try {
                // Try to render view (without PDF)
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
                
                // Return HTML for inspection
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

// Debug route untuk check tracking history
Route::get('/debug/tracking-check/{id}', function($id) {
    $data = [];
    
    try {
        $pengajuan = PengajuanSurat::with('trackingHistory')->findOrFail($id);
        
        $data['pengajuan_id'] = $pengajuan->id;
        $data['status'] = $pengajuan->status;
        $data['tracking_history_count'] = $pengajuan->trackingHistory->count();
        
        $data['history'] = $pengajuan->trackingHistory->map(function($h) {
            return [
                'id' => $h->id,
                'status' => $h->status,
                'description' => $h->description,
                'created_at' => $h->created_at->toDateTimeString(),
                'created_by' => $h->created_by
            ];
        });
        
        // Try to create test tracking
        try {
            DB::beginTransaction();
            
            $test = \App\Models\TrackingHistory::create([
                'pengajuan_id' => $pengajuan->id,
                'status' => 'debug_test',
                'description' => 'Debug test entry',
                'created_by' => auth()->id()
            ]);
            
            DB::rollback(); // Rollback test
            
            $data['tracking_test'] = 'SUCCESS';
        } catch (\Exception $e) {
            $data['tracking_test'] = 'FAILED: ' . $e->getMessage();
        }
        
    } catch (\Exception $e) {
        $data['error'] = $e->getMessage();
    }
    
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');

// Debug route untuk check redirect chain
Route::get('/debug/redirect-chain', function() {
    $session = session()->all();
    
    return response()->json([
        'session_data' => $session,
        'redirect_count' => session('debug_redirect_count', 0),
        'last_url' => session('_previous.url'),
        'intended_url' => session('url.intended')
    ], 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');

// ============================================
// FILE 3: config/logging.php - Tambahkan channel debug
// ============================================

// Tambahkan di array 'channels':
/*
'debug' => [
    'driver' => 'daily',
    'path' => storage_path('logs/debug.log'),
    'level' => 'debug',
    'days' => 7,
],
*/

// ============================================
// FILE 4: routes/web.php - Tambahkan route debug
// ============================================

// Tambahkan di bagian bawah sebelum require:
/*
if (config('app.debug')) {
    require __DIR__ . '/debug.php';
}
*/

// ============================================
// FILE 5: routes/web.php - Tambahkan middleware ke route print
// ============================================

// Update route print FSI dengan middleware debug:
/*
Route::middleware(['auth', 'role:staff_fakultas'])->prefix('fakultas')->name('fakultas.')->group(function () {
    // ... existing routes ...
    
    Route::prefix('surat/fsi')->name('surat.fsi.')->group(function () {
        Route::get('preview/{id}', [SuratFSIController::class, 'preview'])->name('preview');
        Route::post('save-edits/{id}', [SuratFSIController::class, 'saveEdits'])->name('save-edits');
        
        // Add debug middleware ONLY when APP_DEBUG=true
        Route::get('print/{id}', [SuratFSIController::class, 'printSurat'])
            ->name('print')
            ->middleware(config('app.debug') ? 'App\Http\Middleware\DebugRequestMiddleware' : []);
        
        Route::post('upload-signed/{id}', [SuratFSIController::class, 'uploadSignedLink'])->name('upload-signed');
        Route::post('reject/{id}', [SuratFSIController::class, 'rejectSurat'])->name('reject');
    });
});
*/

// ============================================
// FILE 6: app/Http/Kernel.php - Register middleware
// ============================================

// Tambahkan di array $routeMiddleware:
/*
'debug.request' => \App\Http\Middleware\DebugRequestMiddleware::class,
*/