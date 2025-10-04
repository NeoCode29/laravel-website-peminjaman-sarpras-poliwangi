<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Root route - redirect based on authentication status
Route::get('/', function () {
    if (auth()->check()) {
        // Check if profile needs completion
        $user = auth()->user();
        if (method_exists($user, 'isProfileCompleted') && !$user->isProfileCompleted()) {
            return redirect()->route('profile.setup');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Home route - fallback for RouteServiceProvider::HOME
Route::get('/home', function () {
    if (auth()->check()) {
        // Check if profile needs completion
        $user = auth()->user();
        if (method_exists($user, 'isProfileCompleted') && !$user->isProfileCompleted()) {
            return redirect()->route('profile.setup');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::group(['namespace' => 'App\Http\Controllers'], function() {
    Route::group(['middleware' => ['guest']], function() {
        // Login routes - with both old and new names for compatibility
        Route::get('/login', 'AuthController@showLoginForm')->name('login');
        Route::post('/login', 'AuthController@login')->name('login.perform');
        
        // Registration routes
        Route::get('/register', 'AuthController@showRegisterForm')->name('register');
        Route::post('/register', 'AuthController@register')->name('register.perform');
        
        
        // OAuth/SSO routes
        Route::get('/oauth/login', [App\Http\Controllers\OAuthController::class, 'redirect'])->name('auth.oauth.login');
        Route::get('/oauth/callback', [App\Http\Controllers\OAuthController::class, 'callback'])->name('auth.oauth.callback');
        Route::get('/oauth/refresh', [App\Http\Controllers\OAuthController::class, 'refresh'])->name('auth.oauth.refresh');
        Route::get('/oauth/status', [App\Http\Controllers\OAuthController::class, 'status'])->name('auth.oauth.status');
        Route::get('/oauth/debug', [App\Http\Controllers\OAuthController::class, 'debug'])->name('auth.oauth.debug');
        Route::get('/oauth/test-callback', [App\Http\Controllers\OAuthController::class, 'testCallback'])->name('auth.oauth.test-callback');
        Route::get('/oauth/generate-auth-url', [App\Http\Controllers\OAuthController::class, 'generateAuthUrl'])->name('auth.oauth.generate-auth-url');
    });

    Route::group(['middleware' => ['auth']], function() {
        // Logout routes - with both old and new names for compatibility
        Route::post('/logout', 'AuthController@logout')->name('logout');
        Route::get('/logout', 'AuthController@logout')->name('logout.get');
        
        // OAuth logout
        Route::post('/oauth/logout', [App\Http\Controllers\OAuthController::class, 'logout'])->name('auth.oauth.logout');
    });
});

// Dashboard route - using DashboardController
Route::middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getDashboardStats'])->name('dashboard.stats');
    Route::get('/dashboard/activities', [App\Http\Controllers\DashboardController::class, 'getDashboardActivities'])->name('dashboard.activities');
    Route::get('/dashboard/notifications', [App\Http\Controllers\DashboardController::class, 'getDashboardNotifications'])->name('dashboard.notifications');
    Route::get('/dashboard/summary', [App\Http\Controllers\DashboardController::class, 'getDashboardSummaryData'])->name('dashboard.summary');
    Route::get('/dashboard/menu-items', [App\Http\Controllers\DashboardController::class, 'getDashboardMenuItems'])->name('dashboard.menu-items');
    Route::get('/dashboard/config', [App\Http\Controllers\DashboardController::class, 'getDashboardConfiguration'])->name('dashboard.config');
});

// Profile Setup Routes (without profile.completed middleware)
Route::middleware(['auth', 'user.not.blocked'])->group(function () {
    Route::get('/setup', [App\Http\Controllers\ProfileController::class, 'setup'])->name('profile.setup');
    Route::post('/setup', [App\Http\Controllers\ProfileController::class, 'completeSetup'])->name('profile.complete-setup');
    Route::get('/profile/get-prodis', [App\Http\Controllers\ProfileController::class, 'getProdisByJurusan'])->name('profile.get-prodis');
});

// User Management Routes
Route::prefix('user-management')->name('user-management.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\UserManagementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\UserManagementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\UserManagementController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\UserManagementController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\UserManagementController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\UserManagementController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\UserManagementController::class, 'destroy'])->name('destroy');
    
    // Additional user management routes
    Route::post('/{id}/block', [App\Http\Controllers\UserManagementController::class, 'block'])->name('block');
    Route::post('/{id}/unblock', [App\Http\Controllers\UserManagementController::class, 'unblock'])->name('unblock');
    Route::put('/{id}/role', [App\Http\Controllers\UserManagementController::class, 'updateRole'])->name('update-role');
});

// Role Management Routes
Route::prefix('role-management')->name('role-management.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\RoleManagementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\RoleManagementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\RoleManagementController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\RoleManagementController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\RoleManagementController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\RoleManagementController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\RoleManagementController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\RoleManagementController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/bulk-toggle-status', [App\Http\Controllers\RoleManagementController::class, 'bulkToggleStatus'])->name('bulk-toggle-status');
    Route::get('/statistics', [App\Http\Controllers\RoleManagementController::class, 'getStatistics'])->name('statistics');
});

// Permission Management Routes
Route::prefix('permission-management')->name('permission-management.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\PermissionManagementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\PermissionManagementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\PermissionManagementController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\PermissionManagementController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\PermissionManagementController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\PermissionManagementController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\PermissionManagementController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [App\Http\Controllers\PermissionManagementController::class, 'toggleStatus'])->name('toggle-status');
});

// Role Permission Matrix Routes
Route::prefix('role-permission-matrix')->name('role-permission-matrix.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\RolePermissionMatrixController::class, 'index'])->name('index');
    Route::post('/update-role-permissions', [App\Http\Controllers\RolePermissionMatrixController::class, 'updateRolePermissions'])->name('update-role-permissions');
    Route::post('/bulk-update-role-permissions', [App\Http\Controllers\RolePermissionMatrixController::class, 'bulkUpdateRolePermissions'])->name('bulk-update-role-permissions');
    Route::get('/get-role-permissions/{roleId}', [App\Http\Controllers\RolePermissionMatrixController::class, 'getRolePermissions'])->name('get-role-permissions');
});

// Sarana Management Routes
Route::prefix('sarana')->name('sarana.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\SaranaController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\SaranaController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\SaranaController::class, 'store'])->name('store');
    Route::get('/{sarana}', [App\Http\Controllers\SaranaController::class, 'show'])->name('show');
    Route::get('/{sarana}/edit', [App\Http\Controllers\SaranaController::class, 'edit'])->name('edit');
    Route::put('/{sarana}', [App\Http\Controllers\SaranaController::class, 'update'])->name('update');
    Route::delete('/{sarana}', [App\Http\Controllers\SaranaController::class, 'destroy'])->name('destroy');
    
    // Unit management routes
    Route::get('/{sarana}/units', [App\Http\Controllers\SaranaController::class, 'manageUnits'])->name('units');
    Route::post('/{sarana}/units', [App\Http\Controllers\SaranaController::class, 'storeUnit'])->name('store-unit');
    Route::put('/units/{unit}', [App\Http\Controllers\SaranaController::class, 'updateUnit'])->name('update-unit');
    Route::delete('/units/{unit}', [App\Http\Controllers\SaranaController::class, 'destroyUnit'])->name('destroy-unit');
    
    // Bulk unit operations
    Route::post('/{sarana}/units/bulk', [App\Http\Controllers\SaranaController::class, 'storeBulkUnits'])->name('store-bulk-units');
    Route::put('/{sarana}/units/bulk-status', [App\Http\Controllers\SaranaController::class, 'updateBulkUnitStatus'])->name('update-bulk-unit-status');
    
    // Pooled status update
    Route::put('/{sarana}/pooled-status', [App\Http\Controllers\SaranaController::class, 'updatePooledStatus'])->name('update-pooled-status');
});

// Kategori Sarana Management Routes
Route::prefix('kategori-sarana')->name('kategori-sarana.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\KategoriSaranaController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\KategoriSaranaController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\KategoriSaranaController::class, 'store'])->name('store');
    Route::get('/{kategoriSarana}', [App\Http\Controllers\KategoriSaranaController::class, 'show'])->name('show');
    Route::get('/{kategoriSarana}/edit', [App\Http\Controllers\KategoriSaranaController::class, 'edit'])->name('edit');
    Route::put('/{kategoriSarana}', [App\Http\Controllers\KategoriSaranaController::class, 'update'])->name('update');
    Route::delete('/{kategoriSarana}', [App\Http\Controllers\KategoriSaranaController::class, 'destroy'])->name('destroy');
});

// Prasarana Management Routes
Route::prefix('prasarana')->name('prasarana.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\PrasaranaController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\PrasaranaController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\PrasaranaController::class, 'store'])->name('store');
    Route::get('/{prasarana}', [App\Http\Controllers\PrasaranaController::class, 'show'])->name('show');
    Route::get('/{prasarana}/edit', [App\Http\Controllers\PrasaranaController::class, 'edit'])->name('edit');
    Route::put('/{prasarana}', [App\Http\Controllers\PrasaranaController::class, 'update'])->name('update');
    Route::delete('/{prasarana}', [App\Http\Controllers\PrasaranaController::class, 'destroy'])->name('destroy');

    // Image management
    Route::delete('/images/{image}', [App\Http\Controllers\PrasaranaController::class, 'destroyImage'])->name('images.destroy');
});

// Peminjaman Management Routes - TODO: Implement PeminjamanController
// Route::prefix('peminjaman')->name('peminjaman.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
//     Route::get('/', [App\Http\Controllers\PeminjamanController::class, 'index'])->name('index');
//     Route::get('/create', [App\Http\Controllers\PeminjamanController::class, 'create'])->name('create');
//     Route::post('/', [App\Http\Controllers\PeminjamanController::class, 'store'])->name('store');
//     Route::get('/{peminjaman}', [App\Http\Controllers\PeminjamanController::class, 'show'])->name('show');
//     Route::get('/{peminjaman}/edit', [App\Http\Controllers\PeminjamanController::class, 'edit'])->name('edit');
//     Route::put('/{peminjaman}', [App\Http\Controllers\PeminjamanController::class, 'update'])->name('update');
//     Route::delete('/{peminjaman}', [App\Http\Controllers\PeminjamanController::class, 'destroy'])->name('destroy');
//     
//     // Approval routes
//     Route::post('/{peminjaman}/approve', [App\Http\Controllers\PeminjamanController::class, 'approve'])->name('approve');
//     Route::post('/{peminjaman}/reject', [App\Http\Controllers\PeminjamanController::class, 'reject'])->name('reject');
//     Route::get('/pending', [App\Http\Controllers\PeminjamanController::class, 'pending'])->name('pending');
// });

// Sarpras Management Routes - TODO: Implement SarprasController
// Route::prefix('sarpras')->name('sarpras.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
//     Route::get('/', [App\Http\Controllers\SarprasController::class, 'index'])->name('index');
//     Route::get('/create', [App\Http\Controllers\SarprasController::class, 'create'])->name('create');
//     Route::post('/', [App\Http\Controllers\SarprasController::class, 'store'])->name('store');
//     Route::get('/{sarpras}', [App\Http\Controllers\SarprasController::class, 'show'])->name('show');
//     Route::get('/{sarpras}/edit', [App\Http\Controllers\SarprasController::class, 'edit'])->name('edit');
//     Route::put('/{sarpras}', [App\Http\Controllers\SarprasController::class, 'update'])->name('update');
//     Route::delete('/{sarpras}', [App\Http\Controllers\SarprasController::class, 'destroy'])->name('destroy');
// });

// Reports Routes - TODO: Implement ReportController
// Route::prefix('reports')->name('reports.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
//     Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
//     Route::get('/export', [App\Http\Controllers\ReportController::class, 'export'])->name('export');
//     Route::get('/user-activity', [App\Http\Controllers\ReportController::class, 'userActivity'])->name('user-activity');
//     Route::get('/peminjaman-summary', [App\Http\Controllers\ReportController::class, 'peminjamanSummary'])->name('peminjaman-summary');
// });

// System Settings Routes - TODO: Implement SystemController
// Route::prefix('system')->name('system.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
//     Route::get('/settings', [App\Http\Controllers\SystemController::class, 'settings'])->name('settings');
//     Route::put('/settings', [App\Http\Controllers\SystemController::class, 'updateSettings'])->name('update-settings');
//     Route::get('/backup', [App\Http\Controllers\SystemController::class, 'backup'])->name('backup');
//     Route::post('/backup', [App\Http\Controllers\SystemController::class, 'createBackup'])->name('create-backup');
// });

// Notifications Routes - TODO: Implement NotificationController
// Route::prefix('notifications')->name('notifications.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
//     Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
//     Route::post('/{notification}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
//     Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
// });

// Profile Routes
Route::prefix('profile')->name('profile.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [App\Http\Controllers\ProfileController::class, 'update'])->name('update');
});

// API Routes for AJAX calls
Route::prefix('api')->name('api.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    // Jurusan-Prodi relationship
    Route::get('/jurusan/{jurusanId}/prodi', [App\Http\Controllers\ApiController::class, 'getProdisByJurusan'])->name('jurusan.prodi');
    
    // Unit-Position relationship
    Route::get('/units/{unitId}/positions', [App\Http\Controllers\ApiController::class, 'getPositionsByUnit'])->name('units.positions');
});