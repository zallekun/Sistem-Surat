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