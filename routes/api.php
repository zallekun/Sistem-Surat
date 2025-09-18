<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SuratApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Surat API Endpoints
    Route::get('/surat', [SuratApiController::class, 'index']);
    Route::post('/surat', [SuratApiController::class, 'store']);
    Route::get('/surat/{id}', [SuratApiController::class, 'show']);
    Route::put('/surat/{id}', [SuratApiController::class, 'update']);
    Route::post('/surat/{id}/submit', [SuratApiController::class, 'submit']);
    Route::post('/surat/{id}/verify', [SuratApiController::class, 'verify']);
    Route::post('/surat/{id}/disposisi', [SuratApiController::class, 'disposisi']);
    Route::get('/surat/{id}/tracking', [SuratApiController::class, 'tracking']);
    Route::post('/surat/{id}/final', [SuratApiController::class, 'final']);
});
