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
// Route moved to dashboard.php to avoid duplication

// Profile Setup Routes (without profile.completed middleware)
Route::middleware(['auth', 'user.not.blocked'])->group(function () {
    Route::get('/setup', [App\Http\Controllers\ProfileController::class, 'setup'])->name('profile.setup');
    Route::post('/setup', [App\Http\Controllers\ProfileController::class, 'completeSetup'])->name('profile.complete-setup');
    Route::get('/profile/get-prodis', [App\Http\Controllers\ProfileController::class, 'getProdisByJurusan'])->name('profile.get-prodis');
});

// Profile Routes
Route::middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
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