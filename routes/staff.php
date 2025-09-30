<?php

use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    
    // Surat Index - List all surat for staff
    Route::get('/surat', [SuratController::class, 'staffIndex'])->name('surat.index');
    
    // Surat CRUD
    Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
    Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
    
    // Surat Actions
    Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');
    Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');
    Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');
    
});

Route::middleware(['auth', 'role:staff_prodi,kaprodi'])->prefix('staff')->name('staff.')->group(function () {
    // Existing pengajuan routes
    Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
        Route::get('/', [App\Http\Controllers\StaffPengajuanController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\StaffPengajuanController::class, 'show'])->name('show');
        Route::post('/{id}/process', [App\Http\Controllers\StaffPengajuanController::class, 'processPengajuan'])->name('process');
        
        // TAMBAHKAN ROUTES SURAT PENGANTAR DI SINI
        Route::get('/{id}/pengantar/preview', [App\Http\Controllers\StaffPengajuanController::class, 'previewPengantar'])
            ->name('pengantar.preview');
        Route::post('/{id}/pengantar/store', [App\Http\Controllers\StaffPengajuanController::class, 'storePengantar'])
            ->name('pengantar.store');
    });
});