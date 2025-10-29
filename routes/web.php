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
    Route::get('/dashboard/calendar', [App\Http\Controllers\DashboardController::class, 'getDashboardCalendarEvents'])->name('dashboard.calendar');
    Route::get('/dashboard/kpi', [App\Http\Controllers\DashboardController::class, 'getDashboardKpi'])->name('dashboard.kpi');
    Route::get('/dashboard/trend', [App\Http\Controllers\DashboardController::class, 'getDashboardTrend'])->name('dashboard.trend');
    Route::get('/dashboard/filters', [App\Http\Controllers\DashboardController::class, 'getDashboardFilterOptions'])->name('dashboard.filters');
    Route::get('/dashboard/yearly-totals', [App\Http\Controllers\DashboardController::class, 'getDashboardYearlyTotals'])->name('dashboard.yearly-totals');
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
    Route::post('/{sarana}/units', [App\Http\Controllers\SaranaController::class, 'storeUnit'])->name('units.store');
    Route::put('/{sarana}/units/{unit}', [App\Http\Controllers\SaranaController::class, 'updateUnit'])->name('units.update');
    Route::delete('/{sarana}/units/{unit}', [App\Http\Controllers\SaranaController::class, 'destroyUnit'])->name('units.destroy')->where('unit', '[0-9]+');
    
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

// Kategori Prasarana Management Routes
Route::prefix('kategori-prasarana')->name('kategori-prasarana.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\KategoriPrasaranaController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\KategoriPrasaranaController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\KategoriPrasaranaController::class, 'store'])->name('store');
    Route::get('/{kategoriPrasarana}', [App\Http\Controllers\KategoriPrasaranaController::class, 'show'])->name('show');
    Route::get('/{kategoriPrasarana}/edit', [App\Http\Controllers\KategoriPrasaranaController::class, 'edit'])->name('edit');
    Route::put('/{kategoriPrasarana}', [App\Http\Controllers\KategoriPrasaranaController::class, 'update'])->name('update');
    Route::delete('/{kategoriPrasarana}', [App\Http\Controllers\KategoriPrasaranaController::class, 'destroy'])->name('destroy');
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
    
    // Status update
    Route::patch('/{prasarana}/status', [App\Http\Controllers\PrasaranaController::class, 'updateStatus'])->name('status.update');

    // Image management
    Route::delete('/images/{image}', [App\Http\Controllers\PrasaranaController::class, 'destroyImage'])->name('images.destroy');
});

// Peminjaman Management Routes
Route::prefix('peminjaman')->name('peminjaman.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\PeminjamanController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\PeminjamanController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\PeminjamanController::class, 'store'])->name('store');
    Route::get('/{peminjaman}', [App\Http\Controllers\PeminjamanController::class, 'show'])->name('show');
    Route::post('/{peminjaman}/assign-units', [App\Http\Controllers\PeminjamanController::class, 'assignUnits'])->name('assign_units');
    Route::get('/{peminjaman}/edit', [App\Http\Controllers\PeminjamanController::class, 'edit'])->name('edit');
    Route::put('/{peminjaman}', [App\Http\Controllers\PeminjamanController::class, 'update'])->name('update');
    Route::delete('/{peminjaman}', [App\Http\Controllers\PeminjamanController::class, 'destroy'])->name('destroy');
    
    // Approval routes
    Route::post('/{peminjaman}/approve', [App\Http\Controllers\PeminjamanController::class, 'approve'])->name('approve');
    Route::post('/{peminjaman}/reject', [App\Http\Controllers\PeminjamanController::class, 'reject'])->name('reject');
    Route::post('/{peminjaman}/validate-pickup', [App\Http\Controllers\PeminjamanController::class, 'validatePickup'])->name('validate_pickup');
    Route::post('/{peminjaman}/validate-return', [App\Http\Controllers\PeminjamanController::class, 'validateReturn'])->name('validate_return');
    // Peminjam upload-only (tanpa validasi petugas)
    Route::post('/{peminjaman}/upload-pickup-photo', [App\Http\Controllers\PeminjamanController::class, 'uploadPickupPhoto'])->name('upload_pickup_photo');
    Route::post('/{peminjaman}/upload-return-photo', [App\Http\Controllers\PeminjamanController::class, 'uploadReturnPhoto'])->name('upload_return_photo');
    Route::patch('/{peminjaman}/cancel', [App\Http\Controllers\PeminjamanController::class, 'cancel'])->name('cancel');
    Route::get('/pending', [App\Http\Controllers\PeminjamanController::class, 'pending'])->name('pending');
});

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

// Reports Routes
Route::prefix('reports')->name('reports.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
    Route::get('/export', [App\Http\Controllers\ReportController::class, 'export'])->name('export');
});

// Audit Log Routes
Route::prefix('audit-logs')->name('audit-logs.')->middleware(['auth', 'user.not.blocked', 'profile.completed', 'permission:log.view'])->group(function () {
    Route::get('/', [App\Http\Controllers\AuditLogController::class, 'index'])->name('index');
});

// System Settings Routes
Route::prefix('system-settings')->name('system-settings.')->middleware(['auth', 'user.not.blocked', 'profile.completed', 'permission:system.settings'])->group(function () {
    Route::get('/', [App\Http\Controllers\SystemSettingController::class, 'index'])->name('index');
    Route::get('/data', [App\Http\Controllers\SystemSettingController::class, 'getSettingsData'])->name('data');
    Route::get('/{key}', [App\Http\Controllers\SystemSettingController::class, 'show'])->name('show');
    Route::post('/', [App\Http\Controllers\SystemSettingController::class, 'store'])->name('store');
    Route::put('/{key}', [App\Http\Controllers\SystemSettingController::class, 'update'])->name('update');
    Route::put('/', [App\Http\Controllers\SystemSettingController::class, 'updateMultiple'])->name('update-multiple');
    Route::post('/{key}/reset', [App\Http\Controllers\SystemSettingController::class, 'reset'])->name('reset');
    Route::post('/reset-all', [App\Http\Controllers\SystemSettingController::class, 'resetAll'])->name('reset-all');
    Route::get('/stats', [App\Http\Controllers\SystemSettingController::class, 'stats'])->name('stats');
    Route::post('/clear-cache', [App\Http\Controllers\SystemSettingController::class, 'clearCache'])->name('clear-cache');
    Route::post('/validate', [App\Http\Controllers\SystemSettingController::class, 'validateSetting'])->name('validate');
});

// Public System Settings Routes (for getting values without permission check)
Route::prefix('api/system-settings')->name('api.system-settings.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/{key}', [App\Http\Controllers\SystemSettingController::class, 'getValue'])->name('get-value');
    Route::get('/', [App\Http\Controllers\SystemSettingController::class, 'getAllValues'])->name('get-all-values');
});

// Notifications Routes
Route::prefix('notifications')->name('notifications.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::post('/{notification}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::get('/{notification}', [App\Http\Controllers\NotificationController::class, 'show'])->name('show');
    Route::get('/{notification}/click', [App\Http\Controllers\NotificationController::class, 'click'])->name('click');
});

// Profile Routes
Route::prefix('profile')->name('profile.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [App\Http\Controllers\ProfileController::class, 'update'])->name('update');
});

// Approval Assignment Routes
Route::prefix('approval-assignment')->name('approval-assignment.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    // Global Approvers
    Route::get('/global', [App\Http\Controllers\ApprovalAssignmentController::class, 'globalIndex'])->name('global.index');
    Route::post('/global', [App\Http\Controllers\ApprovalAssignmentController::class, 'storeGlobal'])->name('global.store');
    Route::put('/global/{id}', [App\Http\Controllers\ApprovalAssignmentController::class, 'updateGlobal'])->name('global.update');
    Route::delete('/global/{id}', [App\Http\Controllers\ApprovalAssignmentController::class, 'destroyGlobal'])->name('global.destroy');
    
    // Sarana Approvers
    Route::get('/sarana', [App\Http\Controllers\ApprovalAssignmentController::class, 'saranaIndex'])->name('sarana.index');
    Route::post('/sarana', [App\Http\Controllers\ApprovalAssignmentController::class, 'storeSarana'])->name('sarana.store');
    Route::put('/sarana/{id}', [App\Http\Controllers\ApprovalAssignmentController::class, 'updateSarana'])->name('sarana.update');
    Route::delete('/sarana/{id}', [App\Http\Controllers\ApprovalAssignmentController::class, 'destroySarana'])->name('sarana.destroy');
    Route::post('/sarana/bulk-assign', [App\Http\Controllers\ApprovalAssignmentController::class, 'bulkAssignSarana'])->name('sarana.bulk-assign');
    Route::get('/sarana/{saranaId}/approvers', [App\Http\Controllers\ApprovalAssignmentController::class, 'getSaranaApprovers'])->name('sarana.approvers');
    
    // Prasarana Approvers
    Route::get('/prasarana', [App\Http\Controllers\ApprovalAssignmentController::class, 'prasaranaIndex'])->name('prasarana.index');
    Route::post('/prasarana', [App\Http\Controllers\ApprovalAssignmentController::class, 'storePrasarana'])->name('prasarana.store');
    Route::put('/prasarana/{id}', [App\Http\Controllers\ApprovalAssignmentController::class, 'updatePrasarana'])->name('prasarana.update');
    Route::delete('/prasarana/{id}', [App\Http\Controllers\ApprovalAssignmentController::class, 'destroyPrasarana'])->name('prasarana.destroy');
    Route::post('/prasarana/bulk-assign', [App\Http\Controllers\ApprovalAssignmentController::class, 'bulkAssignPrasarana'])->name('prasarana.bulk-assign');
    Route::get('/prasarana/{prasaranaId}/approvers', [App\Http\Controllers\ApprovalAssignmentController::class, 'getPrasaranaApprovers'])->name('prasarana.approvers');
});

// API Routes for AJAX calls
Route::prefix('api')->name('api.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    // Jurusan-Prodi relationship
    Route::get('/jurusan/{jurusanId}/prodi', [App\Http\Controllers\ApiController::class, 'getProdisByJurusan'])->name('jurusan.prodi');
    
    // Unit-Position relationship
    Route::get('/units/{unitId}/positions', [App\Http\Controllers\ApiController::class, 'getPositionsByUnit'])->name('units.positions');
    
// Sarana units API removed - units will be auto-allocated during approval
});

// Approval Workflow Routes
Route::prefix('approvals')->name('approvals.')->middleware(['auth', 'user.not.blocked', 'profile.completed'])->group(function () {
    Route::get('/pending', [App\Http\Controllers\ApprovalController::class, 'pending'])->name('pending');
    Route::get('/{workflow}', [App\Http\Controllers\ApprovalController::class, 'show'])->name('show');
    Route::post('/workflow/{workflow}/approve', [App\Http\Controllers\ApprovalController::class, 'approve'])->name('workflow.approve');
    Route::post('/workflow/{workflow}/reject', [App\Http\Controllers\ApprovalController::class, 'reject'])->name('workflow.reject');
    Route::post('/workflow/{workflow}/override', [App\Http\Controllers\ApprovalController::class, 'override'])->name('workflow.override');
    Route::get('/workflow/{peminjaman}', [App\Http\Controllers\ApprovalController::class, 'workflow'])->name('workflow');
    Route::get('/status/{peminjaman}', [App\Http\Controllers\ApprovalController::class, 'status'])->name('status');
    
    // Global approval routes
    Route::post('/global/{peminjaman}/approve', [App\Http\Controllers\ApprovalController::class, 'approveGlobal'])->name('global.approve');
    Route::post('/global/{peminjaman}/reject', [App\Http\Controllers\ApprovalController::class, 'rejectGlobal'])->name('global.reject');
    
    // Specific sarana approval routes
    Route::post('/sarana/{peminjaman}/{sarana}/approve', [App\Http\Controllers\ApprovalController::class, 'approveSpecificSarana'])->name('sarana.approve');
    Route::post('/sarana/{peminjaman}/{sarana}/reject', [App\Http\Controllers\ApprovalController::class, 'rejectSpecificSarana'])->name('sarana.reject');
    
    // Specific prasarana approval routes
    Route::post('/prasarana/{peminjaman}/{prasarana}/approve', [App\Http\Controllers\ApprovalController::class, 'approveSpecificPrasarana'])->name('prasarana.approve');
    Route::post('/prasarana/{peminjaman}/{prasarana}/reject', [App\Http\Controllers\ApprovalController::class, 'rejectSpecificPrasarana'])->name('prasarana.reject');
    
    // Override approval
    Route::post('/override/{peminjaman}', [App\Http\Controllers\ApprovalController::class, 'overrideApproval'])->name('override');
    
    // API routes
    Route::get('/api/pending', [App\Http\Controllers\ApprovalController::class, 'getPending'])->name('api.pending');
    Route::get('/api/status/{peminjaman}', [App\Http\Controllers\ApprovalController::class, 'getStatus'])->name('api.status');
});