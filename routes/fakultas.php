<?php

use App\Http\Controllers\FakultasStaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff Fakultas Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff_fakultas'])->prefix('fakultas')->name('fakultas.')->group(function () {
    
    // Dashboard view approved surats
    Route::get('/surat', [FakultasStaffController::class, 'index'])->name('surat.index');
    
    // View detail surat
    Route::get('/surat/{id}', [FakultasStaffController::class, 'show'])->name('surat.show');
    
    // Approve surat (lanjut ke tujuan berikutnya)
    Route::post('/surat/{id}/approve', [FakultasStaffController::class, 'approve'])->name('surat.approve');
    
    // Reject surat
    Route::post('/surat/{id}/reject', [FakultasStaffController::class, 'reject'])->name('surat.reject');
    
    // Update status (untuk proses, setujui, tolak)
    Route::patch('/surat/{id}/status', [FakultasStaffController::class, 'updateStatus'])->name('surat.updateStatus');
    
});