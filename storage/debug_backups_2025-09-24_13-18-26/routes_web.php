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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public Routes (No Auth Required) - FIXED untuk existing views
Route::middleware(['throttle:30,1'])->group(function () {
    // Form pengajuan surat mahasiswa
    Route::get('/pengajuan-surat', [PublicSuratController::class, 'create'])
        ->name('public.pengajuan.create');
    
    Route::post('/pengajuan-surat', [PublicSuratController::class, 'store'])
        ->name('public.pengajuan.store');
    
    // FIXED: Tracking routes untuk existing structure
    // Landing page - public.tracking.index
    Route::get('/tracking', [PublicSuratController::class, 'trackingIndex'])
        ->name('tracking.public');
    
    // Show specific result - public.tracking.show
    Route::get('/tracking/{token}', [PublicSuratController::class, 'trackingShow'])
        ->name('tracking.show');
    
    // Handle search form POST
    Route::post('/tracking/search', [PublicSuratController::class, 'trackingSearch'])
        ->name('tracking.search');
    
    // Keep existing API endpoint for AJAX
    Route::post('/tracking/api', [PublicSuratController::class, 'trackingApi'])
        ->name('tracking.api');
});

Route::middleware(['auth'])->group(function() {
    // Pengajuan mahasiswa routes
    Route::get('/surat/pengajuan', [SuratController::class, 'pengajuanIndex'])->name('surat.pengajuan.index');
    Route::get('/surat/pengajuan/{id}', [SuratController::class, 'pengajuanShow'])->name('surat.pengajuan.show');
    Route::post('/surat/pengajuan/{id}/approve', [SuratController::class, 'approvePengajuan'])->name('surat.pengajuan.approve');
    Route::post('/surat/pengajuan/{id}/reject', [SuratController::class, 'rejectPengajuan'])->name('surat.pengajuan.reject');
    
    // Existing surat routes
    Route::resource('surat', SuratController::class);
    Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');
});

Route::get('/debug-alpine', function() {
    return view('debug.alpine');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
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
        Route::get('/{id}/download', [SuratController::class, 'download'])->name('download');
    });

    // Staff Routes
    Route::middleware(['role:staff_prodi,staff_fakultas'])->prefix('staff')->name('staff.')->group(function () {
        // Surat Management
        Route::get('/surat', [SuratController::class, 'staffIndex'])->name('surat.index');
        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
        Route::get('/surat/{id}', [SuratController::class, 'show'])->name('surat.show');
        Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('surat.edit');
        Route::put('/surat/{id}', [SuratController::class, 'update'])->name('surat.update');
        Route::delete('/surat/{id}', [SuratController::class, 'destroy'])->name('surat.destroy');
        
        // Pengajuan Management for Staff (Prodi & Fakultas)
        Route::get('/pengajuan', [SuratController::class, 'pengajuanIndex'])->name('pengajuan.index');
        Route::get('/pengajuan/{id}', [SuratController::class, 'pengajuanShow'])->name('pengajuan.show');

        // Pengajuan Management (Staff Prodi Only Actions)
        Route::middleware(['role:staff_prodi'])->group(function () {
            Route::post('/pengajuan/{id}/process', [PublicSuratController::class, 'createSuratFromPengajuan'])
                ->name('pengajuan.process');
            Route::get('/surat/create-from-pengajuan/{id}', [SuratController::class, 'createFromPengajuan'])
                ->name('surat.create-from-pengajuan');
        });
    });

    // Kaprodi Routes
    Route::middleware(['role:kaprodi'])->prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/surat/approval', [SuratController::class, 'approvalList'])->name('surat.approval');
    });

    // Pimpinan Routes
    Route::middleware(['role:pimpinan,dekan,wd1,wd2,wd3'])->prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('/surat/disposisi', function () {
            return view('pimpinan.surat.disposisi');
        })->name('surat.disposisi');
        Route::post('/surat/{id}/disposisi', [DisposisiController::class, 'store'])->name('surat.disposisi.store');
        Route::get('/surat/ttd', function () {
            return view('pimpinan.surat.ttd');
        })->name('surat.ttd');
        Route::post('/surat/{id}/ttd', [SuratController::class, 'tandaTangan'])->name('surat.ttd.process');
    });

    // Admin Routes
    Route::middleware(['role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('fakultas', FakultasController::class);
        Route::resource('prodi', ProdiController::class);
        Route::resource('jabatan', JabatanController::class);
    });

    // General Admin Resources (accessible by authorized roles)
    Route::resource('tracking', TrackingController::class);
});

// Public Routes - Add Dosen Wali API
Route::middleware(['throttle:60,1'])->group(function () {
    // Get dosen wali by prodi
    Route::get('/api/dosen-wali/{prodi_id}', [PublicSuratController::class, 'getDosenWali'])
        ->name('api.dosen-wali.get');
    
    // Search dosen wali (autocomplete)
    Route::post('/api/dosen-wali/search', [PublicSuratController::class, 'searchDosenWali'])
        ->name('api.dosen-wali.search');
});

Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/pengajuan-surat', [PublicSuratController::class, 'create'])
        ->name('public.pengajuan.create');
    
    Route::post('/pengajuan-surat', [PublicSuratController::class, 'store'])
        ->name('public.pengajuan.store');
    
    // Fixed tracking routes
    Route::get('/tracking', [PublicSuratController::class, 'trackingIndex'])
        ->name('tracking.public');
    
    Route::get('/tracking/{token}', [PublicSuratController::class, 'trackingShow'])
        ->name('tracking.show');
    
    Route::post('/tracking/search', [PublicSuratController::class, 'trackingSearch'])
        ->name('tracking.search');
    
    Route::post('/tracking/api', [PublicSuratController::class, 'trackingApi'])
        ->name('tracking.api');
});

Route::middleware(['auth'])->group(function () {
    
    // Prodi Workflow - Process pengajuan
    Route::middleware('role:staff_prodi,kaprodi')->group(function () {
        Route::post('/pengajuan/{id}/prodi/process', [SuratController::class, 'processProdiPengajuan'])
            ->name('pengajuan.prodi.process');
    });
    
    // Fakultas Workflow - Process dari prodi
    Route::middleware('role:staff_fakultas')->group(function () {
        Route::get('/fakultas/pengajuan', [\App\Http\Controllers\FakultasStaffController::class, 'pengajuanFromProdi'])
            ->name('pengajuan.fakultas.index');
        
        Route::post('/pengajuan/{id}/fakultas/process', [\App\Http\Controllers\FakultasStaffController::class, 'processPengajuanFromProdi'])
            ->name('pengajuan.fakultas.process');
        
        Route::post('/pengajuan/{id}/fakultas/generate-surat', [\App\Http\Controllers\FakultasStaffController::class, 'generateSuratFromPengajuan'])
            ->name('pengajuan.fakultas.generate');
    });
});








// Include additional route files
require __DIR__.'/auth.php';
require __DIR__.'/fakultas.php';
require __DIR__.'/staff.php';