<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarkingController;

/*
|--------------------------------------------------------------------------
| Marking Routes
|--------------------------------------------------------------------------
|
| Routes for the marking feature
|
*/

Route::middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    // Marking routes
    Route::prefix('marking')->name('marking.')->group(function () {
        Route::get('/', [MarkingController::class, 'index'])->name('index');
        Route::get('/create', [MarkingController::class, 'create'])->name('create');
        Route::post('/', [MarkingController::class, 'store'])->name('store');
        Route::get('/{marking}', [MarkingController::class, 'show'])->name('show');
        Route::get('/{marking}/edit', [MarkingController::class, 'edit'])->name('edit');
        Route::put('/{marking}', [MarkingController::class, 'update'])->name('update');
        Route::delete('/{marking}', [MarkingController::class, 'destroy'])->name('destroy');
        
        // Additional marking routes
        Route::post('/{marking}/convert', [MarkingController::class, 'convertToPeminjaman'])->name('convert');
        Route::post('/{marking}/extend', [MarkingController::class, 'extend'])->name('extend');
    });
});







