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

    // Pengajuan Mahasiswa
    Route::prefix('surat/pengajuan')->name('surat.pengajuan.')->group(function () {
        Route::get('/', [SuratController::class, 'pengajuanIndex'])->name('index');
        Route::get('/{id}', [SuratController::class, 'pengajuanShow'])->name('show');
        Route::post('/{id}/approve', [SuratController::class, 'approvePengajuan'])->name('approve');
        Route::post('/{id}/reject', [SuratController::class, 'rejectPengajuan'])->name('reject');
    });

    // Staff Routes (Prodi & Fakultas)
    Route::middleware(['role:staff_prodi,staff_fakultas'])->prefix('staff')->name('staff.')->group(function () {
        Route::resource('surat', SuratController::class)->except(['index']);
        Route::get('surat', [SuratController::class, 'staffIndex'])->name('surat.index');

        // Pengajuan (staff view)
        Route::get('pengajuan', [SuratController::class, 'pengajuanIndex'])->name('pengajuan.index');
        Route::get('pengajuan/{id}', [SuratController::class, 'pengajuanShow'])->name('pengajuan.show');

        // Staff Prodi only
        Route::middleware(['role:staff_prodi'])->group(function () {
            Route::post('pengajuan/{id}/process', [PublicSuratController::class, 'createSuratFromPengajuan'])
                ->name('pengajuan.process');
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

    // Fakultas Workflow
    Route::middleware(['role:staff_fakultas'])->prefix('fakultas')->group(function () {
        Route::get('pengajuan', [FakultasStaffController::class, 'pengajuanFromProdi'])
            ->name('pengajuan.fakultas.index');
        Route::post('pengajuan/{id}/process', [FakultasStaffController::class, 'processPengajuanFromProdi'])
            ->name('pengajuan.fakultas.process');
        Route::post('pengajuan/{id}/generate-surat', [FakultasStaffController::class, 'generateSuratFromPengajuan'])
            ->name('pengajuan.fakultas.generate');
    });

    // Admin Routes
    Route::middleware(['role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('fakultas', FakultasController::class);
        Route::resource('prodi', ProdiController::class);
        Route::resource('jabatan', JabatanController::class);
    });

    // General Admin Resources
    Route::resource('tracking', TrackingController::class);
});

/*
|--------------------------------------------------------------------------
| FSI Surat Routes (Staff Fakultas Only)
|--------------------------------------------------------------------------
*/
// FSI Surat Routes untuk Staff Fakultas
Route::middleware(['auth', 'role:staff_fakultas'])->group(function () {
    // FSI Preview & Generate
    Route::get('fakultas/surat/fsi/preview/{id}', [App\Http\Controllers\SuratFSIController::class, 'preview'])
        ->name('fakultas.surat.fsi.preview');
    Route::post('fakultas/surat/fsi/generate-pdf/{id}', [App\Http\Controllers\SuratFSIController::class, 'generatePdf'])
        ->name('fakultas.surat.fsi.generate-pdf');
        
    // Optional: Status check
    Route::get('fakultas/surat/fsi/status/{pengajuanId}', [App\Http\Controllers\SuratFSIController::class, 'getSuratStatus'])
        ->name('fakultas.surat.fsi.status');
});

// Optional: Admin routes untuk barcode management (jika diperlukan)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('barcode-signatures', function() {
        $barcodes = \App\Models\BarcodeSignature::with('fakultas')->paginate(10);
        return view('admin.barcode-signatures', compact('barcodes'));
    })->name('admin.barcode-signatures.index');
    
    Route::post('barcode-signatures', function(\Illuminate\Http\Request $request) {
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
    })->name('admin.barcode-signatures.store');
});

/*
|--------------------------------------------------------------------------
| Admin Barcode Management
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
| Public Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/pengajuan-surat', [PublicSuratController::class, 'create'])->name('public.pengajuan.create');
    Route::post('/pengajuan-surat', [PublicSuratController::class, 'store'])->name('public.pengajuan.store');

    Route::get('/tracking', [PublicSuratController::class, 'trackingIndex'])->name('tracking.public');
    Route::get('/tracking/{token}', [PublicSuratController::class, 'trackingShow'])->name('tracking.show');
    Route::post('/tracking/search', [PublicSuratController::class, 'trackingSearch'])->name('tracking.search');
    Route::post('/tracking/api', [PublicSuratController::class, 'trackingApi'])->name('tracking.api');
});

// Dosen Wali API
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/api/dosen-wali/{prodi_id}', [PublicSuratController::class, 'getDosenWali'])->name('api.dosen-wali.get');
    Route::post('/api/dosen-wali/search', [PublicSuratController::class, 'searchDosenWali'])->name('api.dosen-wali.search');
});

/*
|--------------------------------------------------------------------------
| Debug & Includes
|--------------------------------------------------------------------------
*/
Route::get('/debug-alpine', fn() => view('debug.alpine'));

require __DIR__ . '/auth.php';
require __DIR__ . '/fakultas.php';
require __DIR__ . '/staff.php';
