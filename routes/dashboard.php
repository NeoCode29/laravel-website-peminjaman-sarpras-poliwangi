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
        Route::get('/stats', [DashboardController::class, 'getDashboardStats'])->name('stats');
        Route::get('/activities', [DashboardController::class, 'getDashboardActivities'])->name('activities');
        Route::get('/notifications', [DashboardController::class, 'getDashboardNotifications'])->name('notifications');
        Route::get('/summary', [DashboardController::class, 'getDashboardSummaryData'])->name('summary');
        Route::get('/menu-items', [DashboardController::class, 'getDashboardMenuItems'])->name('menu-items');
        Route::get('/config', [DashboardController::class, 'getDashboardConfiguration'])->name('config');
        Route::get('/calendar', [DashboardController::class, 'getDashboardCalendarEvents'])->name('calendar');
        Route::get('/kpi', [DashboardController::class, 'getDashboardKpi'])->name('kpi');
        Route::get('/trend', [DashboardController::class, 'getDashboardTrend'])->name('trend');
        Route::get('/filters', [DashboardController::class, 'getDashboardFilterOptions'])->name('filters');
    });
});
