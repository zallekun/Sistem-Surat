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

        // Pengajuan routes for staff
        Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
            Route::get('/', [App\Http\Controllers\StaffPengajuanController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\StaffPengajuanController::class, 'show'])->name('show');
            Route::post('/{id}/process', [SuratController::class, 'processProdiPengajuan'])->name('process');
        });

        // Staff Prodi only
        Route::middleware(['role:staff_prodi'])->group(function () {
            Route::get('surat/create-from-pengajuan/{id}', [SuratController::class, 'createFromPengajuan'])
                ->name('surat.create-from-pengajuan');
        });
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

    // Admin Routes
    Route::middleware(['role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('fakultas', FakultasController::class);
        Route::resource('prodi', ProdiController::class);
        Route::resource('jabatan', JabatanController::class);
    });

    // General Admin Resources
    // Route::resource('tracking', TrackingController::class);
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
| DEBUG & INCLUDES
|--------------------------------------------------------------------------
*/
Route::get('/debug-alpine', fn() => view('debug.alpine'));

require __DIR__ . '/auth.php';
require __DIR__ . '/fakultas.php';
require __DIR__ . '/staff.php';