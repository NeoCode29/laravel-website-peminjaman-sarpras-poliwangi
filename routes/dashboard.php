<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Routes khusus untuk dashboard dan fitur terkait
|
*/

// Dashboard routes
Route::middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard specific routes
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
        Route::get('/activities', [DashboardController::class, 'getActivities'])->name('activities');
        Route::get('/notifications', [DashboardController::class, 'getNotifications'])->name('notifications');
    });
});
