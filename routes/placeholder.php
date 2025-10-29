<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Placeholder Routes
|--------------------------------------------------------------------------
|
| Routes sementara untuk fitur yang belum diimplementasi
| Akan dihapus setelah implementasi lengkap
|
*/

Route::middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    
    // Peminjaman routes (placeholder) - disabled to use real controller routes
    // Route::prefix('peminjaman')->name('peminjaman.')->group(function () {
    //     Route::get('/', function () { return view('peminjaman.index'); })->name('index');
    //     Route::get('/create', function () { return view('peminjaman.create'); })->name('create');
    //     Route::get('/{id}', function ($id) { return view('peminjaman.show', compact('id')); })->name('show');
    //     Route::get('/{id}/approve', function ($id) { return redirect()->back()->with('info', 'Fitur approve akan segera tersedia'); })->name('approve');
    //     Route::get('/{id}/reject', function ($id) { return redirect()->back()->with('info', 'Fitur reject akan segera tersedia'); })->name('reject');
    // });
    
    // Marking routes (placeholder)
    Route::prefix('marking')->name('marking.')->group(function () {
        Route::get('/create', function () {
            return view('marking.create');
        })->name('create');
    });
    
    // Sarpras routes (placeholder)
    Route::prefix('sarpras')->name('sarpras.')->group(function () {
        Route::get('/', function () {
            return view('sarpras.index');
        })->name('index');
        
        Route::get('/{id}', function ($id) {
            return view('sarpras.show', compact('id'));
        })->name('show');
    });
    
    // Users routes (placeholder)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', function () {
            return view('users.index');
        })->name('index');
    });
    
    // Roles routes (placeholder)
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', function () {
            return view('roles.index');
        })->name('index');
    });
    
    // System routes (placeholder)
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/settings', function () {
            return view('system.settings');
        })->name('settings');
    });
    
    // Notifications routes (placeholder) - disabled to use real controller
    // Route::prefix('notifications')->name('notifications.')->group(function () {
    //     Route::get('/', function () {
    //         return view('notifications.index');
    //     })->name('index');
    // });
    
});
