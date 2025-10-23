<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\StaffArsipController;
use App\Http\Controllers\FakultasArsipController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPengajuanController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminJenisSuratController;
use App\Http\Controllers\AdminProdiController;
use App\Http\Controllers\AdminFakultasController;
use App\Http\Controllers\AdminDosenWaliController;
use App\Http\Controllers\StaffPengajuanController;
use App\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// All web routes will now be protected by the 'web' middleware group
Route::middleware('web')->group(function () {
    
    // Public routes (no auth required)
    Route::get('/', function () {
        return view('welcome');
    });

    /*
    |--------------------------------------------------------------------------
    | PUBLIC ROUTES - FIXED
    |--------------------------------------------------------------------------
    */
    Route::middleware(['throttle:30,1'])->group(function () {
        // Pengajuan routes
        Route::get('/pengajuan-surat', [PublicSuratController::class, 'create'])->name('public.pengajuan.create');
        Route::post('/pengajuan-surat', [PublicSuratController::class, 'store'])->name('public.pengajuan.store');

        // Tracking - SPECIFIC ROUTES FIRST
        Route::get('/tracking', [PublicSuratController::class, 'trackingIndex'])->name('tracking.public');
        Route::post('/tracking/search', [PublicSuratController::class, 'trackingSearch'])->name('tracking.search');
        Route::post('/tracking/api', [PublicSuratController::class, 'trackingApi'])->name('tracking.api');
        Route::get('/tracking/download/{id}', [PublicSuratController::class, 'downloadSurat'])->name('tracking.download')->where('id', '[0-9]+');
        
        // Dynamic route LAST
        Route::get('/tracking/{token}', [PublicSuratController::class, 'trackingShow'])->name('tracking.show');
    });

    // Dosen Wali API 
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::get('/api/dosen-wali/{prodi_id}', [PublicSuratController::class, 'getDosenWali'])->name('api.dosen-wali.get');
        Route::post('/api/dosen-wali/search', [PublicSuratController::class, 'searchDosenWali'])->name('api.dosen-wali.search');
    });

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED ROUTES
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

        // General Surat Routes
        Route::prefix('surat')->name('surat.')->group(function () {
            Route::get('/', [SuratController::class, 'index'])->name('index');
            Route::get('/{id}', [SuratController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SuratController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SuratController::class, 'update'])->name('update');
            Route::post('/{id}/approve', [SuratController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [SuratController::class, 'reject'])->name('reject');
            Route::post('/{id}/submit', [SuratController::class, 'submit'])->name('submit');
            Route::get('/{id}/tracking', [SuratController::class, 'tracking'])->name('tracking');
            Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');
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

        /*
        |--------------------------------------------------------------------------
        | FAKULTAS STAFF ROUTES - CONSOLIDATED
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:staff_fakultas'])->prefix('fakultas')->name('fakultas.')->group(function () {
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
        | ADMIN ROUTES
        |--------------------------------------------------------------------------
        */
        Route::prefix('admin')->name('admin.')->group(function() {
            
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

            // Master Data
            Route::resource('prodi', AdminProdiController::class);
            Route::resource('jenis-surat', AdminJenisSuratController::class);
            Route::resource('fakultas', AdminFakultasController::class);
            Route::resource('dosen-wali', AdminDosenWaliController::class);

            // Audit Trail
            Route::get('/audit-trail', [AdminDashboardController::class, 'auditTrail'])->name('audit-trail.index');
            Route::get('/audit-trail/{id}', [AdminDashboardController::class, 'auditTrailShow'])->name('audit-trail.show');

            // Export
            Route::get('/pengajuan/export', [AdminPengajuanController::class, 'export'])->name('pengajuan.export');
            Route::get('/audit-trail/export', [AdminDashboardController::class, 'auditTrailExport'])->name('audit-trail.export');

            // Settings
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
            Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.test-email');

            // Intervention Actions
            Route::post('/pengajuan/{id}/force-complete', [AdminPengajuanController::class, 'forceComplete'])->name('pengajuan.force-complete');
            Route::post('/pengajuan/{id}/reopen', [AdminPengajuanController::class, 'reopen'])->name('pengajuan.reopen');
            Route::post('/pengajuan/{id}/change-status', [AdminPengajuanController::class, 'changeStatus'])->name('pengajuan.change-status');

            // Barcode Management
            Route::prefix('barcode-signatures')->name('barcode-signatures.')->group(function () {
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
        });

        /*
        |--------------------------------------------------------------------------
        | Arsip Routes
        |--------------------------------------------------------------------------
        */
        // Staff Prodi - Arsip
        Route::middleware(['role:staff_prodi,kaprodi'])->prefix('staff')->name('staff.')->group(function() {
            Route::get('/arsip', [StaffArsipController::class, 'index'])->name('arsip.index');
            Route::get('/arsip/{id}', [StaffArsipController::class, 'show'])->name('arsip.show');
            Route::get('/arsip-export/excel', [StaffArsipController::class, 'exportExcel'])->name('arsip.export');
        });

        // Staff Fakultas - Arsip
        Route::middleware(['role:staff_fakultas'])->prefix('fakultas')->name('fakultas.')->group(function() {
            Route::get('/arsip', [FakultasArsipController::class, 'index'])->name('arsip.index');
            Route::get('/arsip/{id}', [FakultasArsipController::class, 'show'])->name('arsip.show');
            Route::get('/arsip-export/excel', [FakultasArsipController::class, 'exportExcel'])->name('arsip.export');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | DEBUG ROUTES
    |--------------------------------------------------------------------------
    */
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

    // Include additional route files
    require __DIR__ . '/auth.php';
    require __DIR__ . '/fakultas.php';
    require __DIR__ . '/staff.php';
});