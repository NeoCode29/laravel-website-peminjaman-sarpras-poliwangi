<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SaranaApiController;
use App\Http\Controllers\Api\ApprovalApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes (no authentication required)
Route::get('/jurusan/{jurusan}/prodi', function ($jurusanId) {
    $prodi = \App\Models\Prodi::where('jurusan_id', $jurusanId)
        ->orderBy('nama_prodi')
        ->get(['id', 'nama_prodi']);
    
    return response()->json($prodi);
});

// API Routes for Sarana
Route::middleware(['auth:sanctum'])->group(function () {
    // Sarana API endpoints
    Route::get('/sarana', [SaranaApiController::class, 'index']);
    Route::get('/sarana/{sarana}', [SaranaApiController::class, 'show']);
    Route::post('/sarana', [SaranaApiController::class, 'store']);
    Route::put('/sarana/{sarana}', [SaranaApiController::class, 'update']);
    Route::delete('/sarana/{sarana}', [SaranaApiController::class, 'destroy']);
    
    // Unit management API
    Route::post('/sarana/{sarana}/units', [SaranaApiController::class, 'storeUnit']);
    Route::post('/sarana/{sarana}/units/bulk', [SaranaApiController::class, 'storeBulkUnits']);
    Route::put('/sarana/{sarana}/units/bulk-status', [SaranaApiController::class, 'updateBulkUnitStatus']);
    Route::put('/units/{unit}', [SaranaApiController::class, 'updateUnit']);
    Route::delete('/units/{unit}', [SaranaApiController::class, 'destroyUnit']);
    
    // Pooled status update API
    Route::put('/sarana/{sarana}/pooled-status', [SaranaApiController::class, 'updatePooledStatus']);
    
    // Approval API endpoints
    Route::prefix('approvals')->group(function () {
        Route::get('/pending', [ApprovalApiController::class, 'getPending']);
        Route::get('/status/{peminjaman}', [ApprovalApiController::class, 'getStatus']);
        Route::post('/workflow/{workflow}/approve', [ApprovalApiController::class, 'approveWorkflow']);
        Route::post('/workflow/{workflow}/reject', [ApprovalApiController::class, 'rejectWorkflow']);
        Route::post('/workflow/{workflow}/override', [ApprovalApiController::class, 'overrideWorkflow']);
        
        // Global approval
        Route::post('/global/{peminjaman}/approve', [ApprovalApiController::class, 'approveGlobal']);
        Route::post('/global/{peminjaman}/reject', [ApprovalApiController::class, 'rejectGlobal']);
        
        // Specific sarana approval
        Route::post('/sarana/{peminjaman}/{sarana}/approve', [ApprovalApiController::class, 'approveSpecificSarana']);
        Route::post('/sarana/{peminjaman}/{sarana}/reject', [ApprovalApiController::class, 'rejectSpecificSarana']);
        
        // Specific prasarana approval
        Route::post('/prasarana/{peminjaman}/{prasarana}/approve', [ApprovalApiController::class, 'approveSpecificPrasarana']);
        Route::post('/prasarana/{peminjaman}/{prasarana}/reject', [ApprovalApiController::class, 'rejectSpecificPrasarana']);
        
        // Override approval
        Route::post('/override/{peminjaman}', [ApprovalApiController::class, 'overrideApproval']);
        
        // Utility endpoints
        Route::get('/multiple-approvers', [ApprovalApiController::class, 'getMultipleApprovers']);
        Route::get('/check-override-permission', [ApprovalApiController::class, 'checkOverridePermission']);
        Route::post('/calculate-final-status/{peminjaman}', [ApprovalApiController::class, 'calculateFinalStatus']);
    });
});
