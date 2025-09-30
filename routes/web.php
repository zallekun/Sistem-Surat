<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FakultasController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\PublicSuratController;
use App\Http\Controllers\SuratFSIController;
use App\Http\Controllers\FakultasStaffController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffArsipController;
use App\Http\Controllers\FakultasArsipController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPengajuanController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminJenisSuratController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});



/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

    // Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function() {
    
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Pengajuan Management
    Route::get('/pengajuan', [AdminPengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [AdminPengajuanController::class, 'show'])->name('pengajuan.show');
    Route::delete('/pengajuan/{id}', [AdminPengajuanController::class, 'destroy'])->name('pengajuan.destroy');
    Route::post('/pengajuan/{id}/restore', [AdminPengajuanController::class, 'restore'])->name('pengajuan.restore');
    
    // User Management
    Route::resource('users', AdminUserController::class);
    Route::post('/users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
});

// Admin Master Data Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function() {
    // ... existing routes
    
    // Master Data - Prodi
    Route::resource('prodi', App\Http\Controllers\AdminProdiController::class);
    
    // Master Data - Jenis Surat
    Route::resource('jenis-surat', App\Http\Controllers\AdminJenisSuratController::class);
    
    // Master Data - Fakultas
    Route::resource('fakultas', App\Http\Controllers\AdminFakultasController::class);

        // Audit Trail
    Route::get('/audit-trail', [AdminDashboardController::class, 'auditTrail'])->name('audit-trail.index');
    Route::get('/audit-trail/{id}', [AdminDashboardController::class, 'auditTrailShow'])->name('audit-trail.show');

     // Export
    Route::get('/pengajuan/export', [AdminPengajuanController::class, 'export'])->name('pengajuan.export');
    Route::get('/audit-trail/export', [AdminDashboardController::class, 'auditTrailExport'])->name('audit-trail.export');
});

// Admin Intervention Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function() {
    // ... existing routes
    
    // Intervention Actions
    Route::post('/pengajuan/{id}/force-complete', [AdminPengajuanController::class, 'forceComplete'])->name('pengajuan.force-complete');
    Route::post('/pengajuan/{id}/reopen', [AdminPengajuanController::class, 'reopen'])->name('pengajuan.reopen');
    Route::post('/pengajuan/{id}/change-status', [AdminPengajuanController::class, 'changeStatus'])->name('pengajuan.change-status');
});
    

    // Surat (general)
    Route::prefix('surat')->name('surat.')->group(function () {
        Route::get('/', [SuratController::class, 'index'])->name('index');
        Route::get('/{id}', [SuratController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SuratController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SuratController::class, 'update'])->name('update');
        Route::post('/{id}/approve', [SuratController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [SuratController::class, 'reject'])->name('reject');
        Route::post('/{id}/submit', [SuratController::class, 'submit'])->name('submit');
        Route::get('/{id}/tracking', [SuratController::class, 'tracking'])->name('tracking');
        Route::get('/{id}/download', [SuratController::class, 'download'])->name('download');
    });

    // Staff Routes (Prodi & Fakultas)
    Route::middleware(['role:staff_prodi,staff_fakultas'])->prefix('staff')->name('staff.')->group(function () {
        Route::resource('surat', SuratController::class)->except(['index']);
        Route::get('surat', [SuratController::class, 'staffIndex'])->name('surat.index');


                // TAMBAHKAN: Routes untuk surat pengantar
        Route::get('/{id}/pengantar/preview', [App\Http\Controllers\StaffPengajuanController::class, 'previewPengantar'])
            ->name('pengantar.preview');
        Route::post('/{id}/pengantar/store', [App\Http\Controllers\StaffPengajuanController::class, 'storePengantar'])
            ->name('pengantar.store');
        });

        // Staff Prodi only
        Route::middleware(['role:staff_prodi'])->group(function () {
            Route::get('surat/create-from-pengajuan/{id}', [SuratController::class, 'createFromPengajuan'])
                ->name('surat.create-from-pengajuan');
        });

    // Kaprodi Routes
    Route::middleware(['role:kaprodi'])->prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('surat/approval', [SuratController::class, 'approvalList'])->name('surat.approval');
    });

    // Pimpinan Routes
    Route::middleware(['role:pimpinan,dekan,wd1,wd2,wd3'])->prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('surat/disposisi', fn() => view('pimpinan.surat.disposisi'))->name('surat.disposisi');
        Route::post('surat/{id}/disposisi', [DisposisiController::class, 'store'])->name('surat.disposisi.store');
        Route::get('surat/ttd', fn() => view('pimpinan.surat.ttd'))->name('surat.ttd');
        Route::post('surat/{id}/ttd', [SuratController::class, 'tandaTangan'])->name('surat.ttd.process');
    });




Route::get('/debug-route', function() {
    $routes = Route::getRoutes();
    $trackingRoutes = [];
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'tracking')) {
            $trackingRoutes[] = [
                'uri' => $route->uri(),
                'action' => $route->getActionName(),
                'name' => $route->getName()
            ];
        }
    }
    
    return response()->json($trackingRoutes);
});

/*
|--------------------------------------------------------------------------
| FAKULTAS STAFF ROUTES - CONSOLIDATED
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:staff_fakultas'])->prefix('fakultas')->name('fakultas.')->group(function () {
    // Main surat listing
    Route::get('surat', [FakultasStaffController::class, 'index'])->name('surat.index');
    
    // Standard fakultas routes
    Route::get('surat/preview/{id}', [FakultasStaffController::class, 'previewPengajuan'])->name('surat.preview');
    Route::get('surat/edit/{id}', [FakultasStaffController::class, 'editPengajuan'])->name('surat.edit');
    Route::put('surat/update/{id}', [FakultasStaffController::class, 'updatePengajuan'])->name('surat.update');
    Route::get('surat/generate/{id}', [FakultasStaffController::class, 'generateSuratPDF'])->name('surat.generate');
    Route::post('surat/generate-pdf/{id}', [FakultasStaffController::class, 'generateSuratPDF'])->name('surat.generate-pdf');
    Route::post('surat/kirim-ke-pengaju/{id}', [FakultasStaffController::class, 'kirimKePengaju'])->name('surat.kirim-pengaju');
    
    // Pengajuan processing
    Route::get('pengajuan', [FakultasStaffController::class, 'pengajuanFromProdi'])->name('pengajuan.index');
    Route::post('pengajuan/{id}/process', [FakultasStaffController::class, 'processPengajuanFromProdi'])->name('pengajuan.process');
    Route::post('pengajuan/{id}/generate-surat', [FakultasStaffController::class, 'generateSuratFromPengajuan'])->name('pengajuan.generate');
    
    // FSI Specific Routes - NEW WORKFLOW
    Route::prefix('surat/fsi')->name('surat.fsi.')->group(function () {
        Route::get('preview/{id}', [SuratFSIController::class, 'preview'])->name('preview');
        Route::post('save-edits/{id}', [SuratFSIController::class, 'saveEdits'])->name('save-edits');
        Route::get('print/{id}', [SuratFSIController::class, 'printSurat'])->name('print');
        Route::post('upload-signed/{id}', [SuratFSIController::class, 'uploadSignedLink'])->name('upload-signed');
        Route::post('reject/{id}', [SuratFSIController::class, 'rejectSurat'])->name('reject');
        Route::post('generate-pdf/{id}', [SuratFSIController::class, 'generatePdf'])->name('generate-pdf');
        Route::get('status/{pengajuanId}', [SuratFSIController::class, 'getSuratStatus'])->name('status');
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN BARCODE MANAGEMENT
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin/barcode-signatures')->name('admin.barcode-signatures.')->group(function () {
    Route::get('/', function () {
        $barcodes = \App\Models\BarcodeSignature::with('fakultas')->paginate(10);
        return view('admin.barcode-signatures', compact('barcodes'));
    })->name('index');

    Route::post('/', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'pejabat_nama' => 'required|string|max:255',
            'pejabat_jabatan' => 'required|string|max:255',
            'pejabat_nid' => 'nullable|string|max:50',
            'barcode_image' => 'required|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        $path = $request->file('barcode_image')->store('barcode-signatures', 'public');

        \App\Models\BarcodeSignature::create([
            'fakultas_id' => $request->fakultas_id,
            'pejabat_nama' => $request->pejabat_nama,
            'pejabat_jabatan' => $request->pejabat_jabatan,
            'pejabat_nid' => $request->pejabat_nid,
            'barcode_path' => $path,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Barcode berhasil ditambahkan');
    })->name('store');
});
/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES - FIXED
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:30,1'])->group(function () {
    // Pengajuan routes
    Route::get('/pengajuan-surat', [App\Http\Controllers\PublicSuratController::class, 'create'])->name('public.pengajuan.create');
    Route::post('/pengajuan-surat', [App\Http\Controllers\PublicSuratController::class, 'store'])->name('public.pengajuan.store');

    // Tracking - SPECIFIC ROUTES FIRST
    Route::get('/tracking', [App\Http\Controllers\PublicSuratController::class, 'trackingIndex'])->name('tracking.public');
    Route::post('/tracking/search', [App\Http\Controllers\PublicSuratController::class, 'trackingSearch'])->name('tracking.search');
    Route::post('/tracking/api', [App\Http\Controllers\PublicSuratController::class, 'trackingApi'])->name('tracking.api');
    Route::get('/tracking/download/{id}', [App\Http\Controllers\PublicSuratController::class, 'downloadSurat'])->name('tracking.download')->where('id', '[0-9]+');
    
    // Dynamic route LAST
    Route::get('/tracking/{token}', [App\Http\Controllers\PublicSuratController::class, 'trackingShow'])->name('tracking.show');
});

// Dosen Wali API 
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/api/dosen-wali/{prodi_id}', [App\Http\Controllers\PublicSuratController::class, 'getDosenWali'])->name('api.dosen-wali.get');
    Route::post('/api/dosen-wali/search', [App\Http\Controllers\PublicSuratController::class, 'searchDosenWali'])->name('api.dosen-wali.search');
});

// Public routes (no auth required)
Route::prefix('public')->name('public.')->group(function () {
    // Existing routes...
    Route::get('pengajuan/create', [PublicSuratController::class, 'create'])->name('pengajuan.create');
    Route::post('pengajuan/store', [PublicSuratController::class, 'store'])->name('pengajuan.store');
    
    // Dosen Wali API - with optional parameter
    Route::get('dosen-wali/{prodiId?}', [PublicSuratController::class, 'getDosenWali'])->name('dosen.wali');
});

// Or if you prefer API routes
Route::prefix('api')->group(function () {
    Route::get('dosen-wali/{prodiId}', [PublicSuratController::class, 'getDosenWali']);
});

/*
|--------------------------------------------------------------------------
| Arsip Routes
|--------------------------------------------------------------------------
*/
// Staff Prodi - Arsip
Route::middleware(['auth', 'role:staff_prodi,kaprodi'])->prefix('staff')->name('staff.')->group(function() {
    Route::get('/arsip', [StaffArsipController::class, 'index'])->name('arsip.index');
    Route::get('/arsip/{id}', [StaffArsipController::class, 'show'])->name('arsip.show');
    Route::get('/arsip-export/excel', [StaffArsipController::class, 'exportExcel'])->name('arsip.export');
});

// Staff Fakultas - Arsip
Route::middleware(['auth', 'role:staff_fakultas'])->prefix('fakultas')->name('fakultas.')->group(function() {
    Route::get('/arsip', [FakultasArsipController::class, 'index'])->name('arsip.index');
    Route::get('/arsip/{id}', [FakultasArsipController::class, 'show'])->name('arsip.show');
    Route::get('/arsip-export/excel', [FakultasArsipController::class, 'exportExcel'])->name('arsip.export');
});

/*
|--------------------------------------------------------------------------
| DEBUG & INCLUDES
|--------------------------------------------------------------------------
*/
Route::get('/debug-alpine', fn() => view('debug.alpine'));

Route::get('/test-log', function() {
    \Log::info('LOG TEST VIA BROWSER', ['time' => now()]);
    
    $logPath = storage_path('logs/laravel.log');
    
    if (file_exists($logPath)) {
        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -20);
        
        return '<pre>' . implode("\n", $lastLines) . '</pre>';
    }
    
    return 'Log file not found at: ' . $logPath;
});

require __DIR__ . '/auth.php';
require __DIR__ . '/fakultas.php';
require __DIR__ . '/staff.php';