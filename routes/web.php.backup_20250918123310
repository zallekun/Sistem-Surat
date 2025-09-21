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
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes (handled by Breeze)
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Surat Routes - Main functionality
Route::middleware(['auth'])->group(function () {
    // Core surat routes
    Route::get('/surat/{id}', [SuratController::class, 'show'])->name('surat.show');
    Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('surat.edit');
    Route::put('/surat/{id}', [SuratController::class, 'update'])->name('surat.update');
    Route::post('/surat/{id}/approve', [SuratController::class, 'approve'])->name('surat.approve');
    Route::post('/surat/{id}/reject', [SuratController::class, 'reject'])->name('surat.reject');
    
    // Additional surat functionality
    Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
    Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');
    Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');
    Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');
});

// Staff Surat Routes
Route::middleware(['auth', 'role:staff_prodi,kaprodi'])->group(function () {
    Route::get('/staff/surat', [App\Http\Controllers\SuratController::class, 'staffIndex'])->name('staff.surat.index');
    Route::get('/staff/surat/create', [App\Http\Controllers\SuratController::class, 'create'])->name('staff.surat.create');
    Route::post('/staff/surat', [App\Http\Controllers\SuratController::class, 'store'])->name('staff.surat.store');
    Route::get('/staff/surat/{id}', [App\Http\Controllers\SuratController::class, 'show'])->name('staff.surat.show');
    Route::get('/staff/surat/{id}/edit', [App\Http\Controllers\SuratController::class, 'edit'])->name('staff.surat.edit');
    Route::put('/staff/surat/{id}', [App\Http\Controllers\SuratController::class, 'update'])->name('staff.surat.update');
    Route::delete('/staff/surat/{id}', [App\Http\Controllers\SuratController::class, 'destroy'])->name('staff.surat.destroy');
});


// Role-specific routes
Route::middleware(['auth'])->group(function () {
    // Pimpinan routes
    Route::prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('/surat/disposisi', function () {
            return view('pimpinan.surat.disposisi');
        })->name('surat.disposisi');
        
        Route::post('/surat/{id}/disposisi', [DisposisiController::class, 'store'])->name('surat.disposisi.store');
        
        Route::get('/surat/ttd', function () {
            return view('pimpinan.surat.ttd');
        })->name('surat.ttd');
        
        Route::post('/surat/{id}/ttd', [SuratController::class, 'tandaTangan'])->name('surat.ttd.process');
    });
    
    // Kabag routes
    Route::prefix('kabag')->name('kabag.')->group(function () {
        Route::get('/surat', function () {
            return view('kabag.surat.index');
        })->name('surat.index');
    });
    
    // Divisi routes
    Route::prefix('divisi')->name('divisi.')->group(function () {
        Route::get('/surat', function () {
            return view('divisi.surat.index');
        })->name('surat.index');
    });
    
    // Kaprodi routes
    Route::prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/surat/approval', [SuratController::class, 'approvalList'])->name('surat.approval');
    });
    
    // Staff routes
    // Staff routes
    // Staff routes - Fixed
    Route::prefix('staff')->name('staff.')->middleware(['auth'])->group(function () {
        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
        Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');
        Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');
        Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');
    });
});

require __DIR__.'/fakultas.php';

// Admin routes
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('fakultas', FakultasController::class); // This should come after
    Route::resource('prodi', ProdiController::class);
    Route::resource('jabatan', JabatanController::class);
    Route::resource('tracking', TrackingController::class);
});

// Admin routes
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('fakultas', FakultasController::class);
    Route::resource('prodi', ProdiController::class);
    Route::resource('jabatan', JabatanController::class);
    Route::resource('tracking', TrackingController::class);
});

require __DIR__.'/auth.php';

// Include staff routes
require __DIR__.'/staff.php';


// Include fakultas staff routes
require __DIR__.'/fakultas.php';
