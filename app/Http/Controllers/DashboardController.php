<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show unified dashboard based on user permissions
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is blocked
        if (method_exists($user, 'isBlocked') && $user->isBlocked()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif atau diblokir. Silakan hubungi administrator.');
        }

        // Check if profile is completed
        if (method_exists($user, 'isProfileCompleted') && !$user->isProfileCompleted()) {
            return redirect()->route('profile.setup')
                ->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu sebelum melanjutkan.');
        }

        // Get user permissions and role
        $role = $user->roles->first();
        $permissions = $user->getPermissions();
        
        if (!$role) {
            return redirect()->route('login')
                ->with('error', 'Role tidak ditemukan. Silakan hubungi administrator.');
        }

        // Get dashboard data based on permissions
        $dashboardData = $this->getDashboardData($user, $permissions);

        return view('dashboard', compact('user', 'role', 'permissions', 'dashboardData'));
    }

    /**
     * Get dashboard data based on user permissions
     */
    private function getDashboardData($user, $permissions)
    {
        $data = [
            'user_info' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->getUserTypeDisplayAttribute(),
                'status' => $user->getStatusDisplayAttribute(),
                'role' => $user->getRoleDisplayName(),
                'profile_completed' => $user->isProfileCompleted(),
                'last_login' => $user->last_login_at,
            ],
            'permissions' => $permissions->pluck('name')->toArray(),
            'stats' => [],
            'recent_activities' => [],
            'notifications' => [],
            'quick_actions' => [],
            'widgets' => []
        ];

        // Get statistics based on permissions
        if ($permissions->contains('user.view')) {
            $data['stats']['users'] = $this->getUserStats();
        }
        
        if ($permissions->contains('sarpras.view')) {
            $data['stats']['sarpras'] = $this->getSarprasStats();
        }
        
        if ($permissions->contains('peminjaman.view')) {
            $data['stats']['peminjaman'] = $this->getPeminjamanStats($user->id);
        }

        // Get recent activities based on permissions
        if ($permissions->contains('log.view')) {
            $data['recent_activities'] = $this->getRecentActivities(10);
        } else {
            $data['recent_activities'] = $this->getUserRecentActivities($user->id, 5);
        }

        // Get notifications
        $data['notifications'] = $this->getUserNotifications($user->id, 5);

        // Get quick actions based on permissions
        $data['quick_actions'] = $this->getQuickActions($permissions);

        // Get widgets based on permissions
        $data['widgets'] = $this->getWidgets($permissions, $user);

        return $data;
    }

    /**
     * Get user statistics
     */
    private function getUserStats()
    {
        try {
            return [
                'total' => $this->safeCount('users', ['status' => 'active']),
                'mahasiswa' => $this->safeCount('users', ['user_type' => 'mahasiswa', 'status' => 'active']),
                'staff' => $this->safeCount('users', ['user_type' => 'staff', 'status' => 'active']),
                'blocked' => $this->safeCount('users', ['blocked_until' => '>', now()]),
                'new_today' => $this->safeCount('users', ['created_at' => today()])
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'mahasiswa' => 0,
                'staff' => 0,
                'blocked' => 0,
                'new_today' => 0
            ];
        }
    }

    /**
     * Get sarpras statistics
     */
    private function getSarprasStats()
    {
        try {
            return [
                'total_sarana' => $this->safeCount('sarana'),
                'total_prasarana' => $this->safeCount('prasarana'),
                'available_sarana' => $this->safeCount('sarana', ['jumlah_tersedia' => '>', 0]),
                'maintenance_sarana' => $this->safeCount('sarana', ['status' => 'maintenance']),
                'new_today' => $this->safeCount('sarana', ['created_at' => today()]) + $this->safeCount('prasarana', ['created_at' => today()])
            ];
        } catch (\Exception $e) {
            return [
                'total_sarana' => 0,
                'total_prasarana' => 0,
                'available_sarana' => 0,
                'maintenance_sarana' => 0,
                'new_today' => 0
            ];
        }
    }

    /**
     * Get peminjaman statistics
     */
    private function getPeminjamanStats($userId = null)
    {
        try {
            $baseConditions = $userId ? ['user_id' => $userId] : [];
            
            return [
                'total' => $this->safeCount('peminjaman', $baseConditions),
                'pending' => $this->safeCount('peminjaman', array_merge($baseConditions, ['status' => 'pending'])),
                'approved' => $this->safeCount('peminjaman', array_merge($baseConditions, ['status' => 'approved'])),
                'active' => $this->safeCount('peminjaman', array_merge($baseConditions, ['status' => ['approved', 'picked_up']])),
                'completed' => $this->safeCount('peminjaman', array_merge($baseConditions, ['status' => 'returned'])),
                'rejected' => $this->safeCount('peminjaman', array_merge($baseConditions, ['status' => 'rejected'])),
                'today' => $this->safeCount('peminjaman', array_merge($baseConditions, ['created_at' => today()]))
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'active' => 0,
                'completed' => 0,
                'rejected' => 0,
                'today' => 0
            ];
        }
    }

    /**
     * Get quick actions based on permissions
     */
    private function getQuickActions($permissions)
    {
        $actions = [];

        if ($permissions->contains('user.create')) {
            $actions[] = [
                'title' => 'Tambah User',
                'icon' => 'fas fa-user-plus',
                'url' => route('user-management.create'),
                'color' => 'primary',
                'permission' => 'user.create'
            ];
        }

        if ($permissions->contains('peminjaman.create')) {
            $actions[] = [
                'title' => 'Ajukan Peminjaman',
                'icon' => 'fas fa-clipboard-list',
                'url' => route('peminjaman.create'),
                'color' => 'success',
                'permission' => 'peminjaman.create'
            ];
        }

        if ($permissions->contains('sarpras.create')) {
            $actions[] = [
                'title' => 'Tambah Sarpras',
                'icon' => 'fas fa-boxes',
                'url' => route('sarpras.create'),
                'color' => 'info',
                'permission' => 'sarpras.create'
            ];
        }

        if ($permissions->contains('peminjaman.approve')) {
            $actions[] = [
                'title' => 'Approval Pending',
                'icon' => 'fas fa-check-circle',
                'url' => route('peminjaman.pending'),
                'color' => 'warning',
                'permission' => 'peminjaman.approve'
            ];
        }

        if ($permissions->contains('report.view')) {
            $actions[] = [
                'title' => 'Lihat Laporan',
                'icon' => 'fas fa-chart-bar',
                'url' => route('reports.index'),
                'color' => 'secondary',
                'permission' => 'report.view'
            ];
        }

        return $actions;
    }

    /**
     * Get widgets based on permissions
     */
    private function getWidgets($permissions, $user)
    {
        $widgets = [];

        // User management widget
        if ($permissions->contains('user.view')) {
            $widgets[] = [
                'type' => 'user_management',
                'title' => 'Manajemen User',
                'icon' => 'fas fa-users',
                'data' => $this->getUserStats(),
                'permission' => 'user.view'
            ];
        }

        // Sarpras management widget
        if ($permissions->contains('sarpras.view')) {
            $widgets[] = [
                'type' => 'sarpras_management',
                'title' => 'Sarana & Prasarana',
                'icon' => 'fas fa-boxes',
                'data' => $this->getSarprasStats(),
                'permission' => 'sarpras.view'
            ];
        }

        // Peminjaman widget
        if ($permissions->contains('peminjaman.view')) {
            $widgets[] = [
                'type' => 'peminjaman_management',
                'title' => 'Peminjaman',
                'icon' => 'fas fa-clipboard-list',
                'data' => $this->getPeminjamanStats($user->id),
                'permission' => 'peminjaman.view'
            ];
        }

        // System health widget (admin only)
        if ($permissions->contains('system.monitoring')) {
            $widgets[] = [
                'type' => 'system_health',
                'title' => 'Kesehatan Sistem',
                'icon' => 'fas fa-heartbeat',
                'data' => ['health' => $this->getSystemHealth()],
                'permission' => 'system.monitoring'
            ];
        }

        return $widgets;
    }

    /**
     * Admin Dashboard
     */
    private function adminDashboard()
    {
        $user = Auth::user();
        
        // Get statistics
        $stats = $this->getAdminStats();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities(10);
        
        // Get pending approvals
        $pendingApprovals = $this->getPendingApprovals();
        
        // Get system alerts
        $systemAlerts = $this->getSystemAlerts();
        
        // Get upcoming events
        $upcomingEvents = $this->getUpcomingEvents(7);

        return view('dashboard.admin', compact(
            'user', 
            'stats', 
            'recentActivities', 
            'pendingApprovals', 
            'systemAlerts', 
            'upcomingEvents'
        ));
    }

    /**
     * Peminjam Dashboard
     */
    private function peminjamDashboard()
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = $this->getPeminjamStats($user->id);
        
        // Get user's recent activities
        $recentActivities = $this->getUserRecentActivities($user->id, 5);
        
        // Get user's active borrowings
        $activeBorrowings = $this->getActiveBorrowings($user->id);
        
        // Get user's notifications
        $notifications = $this->getUserNotifications($user->id, 5);
        
        // Get available sarpras (quick access)
        $availableSarpras = $this->getAvailableSarpras(6);
        
        // Get upcoming events (user's borrowings)
        $upcomingEvents = $this->getUserUpcomingEvents($user->id, 7);

        return view('dashboard.peminjam', compact(
            'user', 
            'stats', 
            'recentActivities', 
            'activeBorrowings', 
            'notifications', 
            'availableSarpras', 
            'upcomingEvents'
        ));
    }

    /**
     * Get admin statistics
     */
    private function getAdminStats()
    {
        try {
            return [
                'total_users' => $this->safeCount('users', ['status' => 'active']),
                'total_sarana' => $this->safeCount('sarana'),
                'total_prasarana' => $this->safeCount('prasarana'),
                'pending_borrowings' => $this->safeCount('peminjaman', ['status' => 'pending']),
                'active_borrowings' => $this->safeCount('peminjaman', ['status' => ['approved', 'picked_up']]),
                'total_borrowings_today' => $this->safeCount('peminjaman', ['created_at' => today()]),
                'blocked_users' => $this->safeCount('users', ['blocked_until' => '>', now()]),
                'system_health' => $this->getSystemHealth()
            ];
        } catch (\Exception $e) {
            return [
                'total_users' => 0,
                'total_sarana' => 0,
                'total_prasarana' => 0,
                'pending_borrowings' => 0,
                'active_borrowings' => 0,
                'total_borrowings_today' => 0,
                'blocked_users' => 0,
                'system_health' => 0
            ];
        }
    }

    /**
     * Get peminjam statistics
     */
    private function getPeminjamStats($userId)
    {
        try {
            return [
                'total_borrowings' => $this->safeCount('peminjaman', ['user_id' => $userId]),
                'active_borrowings' => $this->safeCount('peminjaman', ['user_id' => $userId, 'status' => ['pending', 'approved', 'picked_up']]),
                'completed_borrowings' => $this->safeCount('peminjaman', ['user_id' => $userId, 'status' => 'returned']),
                'pending_borrowings' => $this->safeCount('peminjaman', ['user_id' => $userId, 'status' => 'pending']),
                'rejected_borrowings' => $this->safeCount('peminjaman', ['user_id' => $userId, 'status' => 'rejected']),
                'quota_used' => $this->getUserQuotaUsed($userId),
                'quota_limit' => $this->getUserQuotaLimit($userId)
            ];
        } catch (\Exception $e) {
            return [
                'total_borrowings' => 0,
                'active_borrowings' => 0,
                'completed_borrowings' => 0,
                'pending_borrowings' => 0,
                'rejected_borrowings' => 0,
                'quota_used' => 0,
                'quota_limit' => 3
            ];
        }
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($limit = 10)
    {
        try {
            return DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->select('audit_logs.*', 'users.name as user_name')
                ->orderBy('audit_logs.created_at', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Get user recent activities
     */
    private function getUserRecentActivities($userId, $limit = 5)
    {
        return DB::table('audit_logs')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending approvals
     */
    private function getPendingApprovals()
    {
        return DB::table('peminjaman')
            ->join('users', 'peminjaman.user_id', '=', 'users.id')
            ->select('peminjaman.*', 'users.name as user_name')
            ->where('peminjaman.status', 'pending')
            ->orderBy('peminjaman.created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get active borrowings for user
     */
    private function getActiveBorrowings($userId)
    {
        return DB::table('peminjaman')
            ->leftJoin('prasarana', 'peminjaman.prasarana_id', '=', 'prasarana.id')
            ->select('peminjaman.*', 'prasarana.name as prasarana_name')
            ->where('peminjaman.user_id', $userId)
            ->whereIn('peminjaman.status', ['pending', 'approved', 'picked_up'])
            ->orderBy('peminjaman.start_date', 'asc')
            ->get();
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts()
    {
        $alerts = [];
        
        // Check for blocked users
        $blockedUsers = DB::table('users')->where('blocked_until', '>', now())->count();
        if ($blockedUsers > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Ada {$blockedUsers} user yang diblokir",
                'icon' => 'fas fa-user-slash'
            ];
        }
        
        // Check for pending approvals
        $pendingCount = DB::table('peminjaman')->where('status', 'pending')->count();
        if ($pendingCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "Ada {$pendingCount} pengajuan yang menunggu persetujuan",
                'icon' => 'fas fa-clock'
            ];
        }
        
        return $alerts;
    }

    /**
     * Get user notifications
     */
    private function getUserNotifications($userId, $limit = 5)
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get available sarpras
     */
    private function getAvailableSarpras($limit = 6)
    {
        return DB::table('sarana')
            ->where('jumlah_tersedia', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get upcoming events
     */
    private function getUpcomingEvents($days = 7)
    {
        $startDate = now();
        $endDate = now()->addDays($days);
        
        return DB::table('peminjaman')
            ->join('users', 'peminjaman.user_id', '=', 'users.id')
            ->leftJoin('prasarana', 'peminjaman.prasarana_id', '=', 'prasarana.id')
            ->select('peminjaman.*', 'users.name as user_name', 'prasarana.name as prasarana_name')
            ->whereBetween('peminjaman.start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('peminjaman.status', ['approved', 'picked_up'])
            ->orderBy('peminjaman.start_date', 'asc')
            ->get();
    }

    /**
     * Get user upcoming events
     */
    private function getUserUpcomingEvents($userId, $days = 7)
    {
        $startDate = now();
        $endDate = now()->addDays($days);
        
        return DB::table('peminjaman')
            ->leftJoin('prasarana', 'peminjaman.prasarana_id', '=', 'prasarana.id')
            ->select('peminjaman.*', 'prasarana.name as prasarana_name')
            ->where('peminjaman.user_id', $userId)
            ->whereBetween('peminjaman.start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('peminjaman.status', ['approved', 'picked_up'])
            ->orderBy('peminjaman.start_date', 'asc')
            ->get();
    }

    /**
     * Get system health
     */
    private function getSystemHealth()
    {
        $health = 100;
        
        // Check database connection
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $health -= 30;
        }
        
        // Check for errors in logs
        $errorCount = DB::table('audit_logs')
            ->where('action', 'system_error')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
        
        if ($errorCount > 10) {
            $health -= 20;
        }
        
        return max(0, $health);
    }

    /**
     * Get user quota used
     */
    private function getUserQuotaUsed($userId)
    {
        return DB::table('peminjaman')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved', 'picked_up'])
            ->count();
    }

    /**
     * Get user quota limit
     */
    private function getUserQuotaLimit($userId)
    {
        $quota = DB::table('user_quotas')
            ->where('user_id', $userId)
            ->first();
            
        return $quota ? $quota->max_active_borrowings : 3; // Default 3
    }

    /**
     * Get dashboard statistics (API endpoint)
     */
    public function getDashboardStats()
    {
        $user = Auth::user();
        $permissions = $user->getPermissions();
        
        $stats = [];
        
        if ($permissions->contains('user.view')) {
            $stats['users'] = $this->getUserStats();
        }
        
        if ($permissions->contains('sarpras.view')) {
            $stats['sarpras'] = $this->getSarprasStats();
        }
        
        if ($permissions->contains('peminjaman.view')) {
            $stats['peminjaman'] = $this->getPeminjamanStats($user->id);
        }
        
        if ($permissions->contains('system.monitoring')) {
            $stats['system_health'] = $this->getSystemHealth();
        }
        
        return response()->json($stats);
    }

    /**
     * Get recent dashboard activities (API endpoint)
     */
    public function getDashboardActivities()
    {
        $user = Auth::user();
        $permissions = $user->getPermissions();
        
        if ($permissions->contains('log.view')) {
            $activities = $this->getRecentActivities(20);
        } else {
            $activities = $this->getUserRecentActivities($user->id, 10);
        }
        
        return response()->json($activities);
    }

    /**
     * Get user dashboard notifications (API endpoint)
     */
    public function getDashboardNotifications()
    {
        $user = Auth::user();
        $notifications = $this->getUserNotifications($user->id, 10);
        
        return response()->json($notifications);
    }

    /**
     * Safe count method to handle missing tables
     */
    private function safeCount($table, $conditions = [])
    {
        try {
            $query = DB::table($table);
            
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } elseif (is_string($value) && strpos($value, '>') === 0) {
                    $query->where($column, '>', substr($value, 1));
                } else {
                    $query->where($column, $value);
                }
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get dashboard data for specific permission
     */
    private function getDashboardDataForPermission($permission, $user)
    {
        switch ($permission) {
            case 'user.view':
                return $this->getUserStats();
            case 'sarpras.view':
                return $this->getSarprasStats();
            case 'peminjaman.view':
                return $this->getPeminjamanStats($user->id);
            case 'system.monitoring':
                return ['health' => $this->getSystemHealth()];
            default:
                return [];
        }
    }

    /**
     * Get quick actions for specific permission
     */
    private function getQuickActionsForPermission($permission)
    {
        $actions = [];
        
        switch ($permission) {
            case 'user.create':
                $actions[] = [
                    'title' => 'Tambah User',
                    'icon' => 'fas fa-user-plus',
                    'url' => route('user-management.create'),
                    'color' => 'primary'
                ];
                break;
            case 'peminjaman.create':
                $actions[] = [
                    'title' => 'Ajukan Peminjaman',
                    'icon' => 'fas fa-clipboard-list',
                    'url' => route('peminjaman.create'),
                    'color' => 'success'
                ];
                break;
            case 'sarpras.create':
                $actions[] = [
                    'title' => 'Tambah Sarpras',
                    'icon' => 'fas fa-boxes',
                    'url' => route('sarpras.create'),
                    'color' => 'info'
                ];
                break;
            case 'peminjaman.approve':
                $actions[] = [
                    'title' => 'Approval Pending',
                    'icon' => 'fas fa-check-circle',
                    'url' => route('peminjaman.pending'),
                    'color' => 'warning'
                ];
                break;
            case 'report.view':
                $actions[] = [
                    'title' => 'Lihat Laporan',
                    'icon' => 'fas fa-chart-bar',
                    'url' => route('reports.index'),
                    'color' => 'secondary'
                ];
                break;
        }
        
        return $actions;
    }

    /**
     * Get widget configuration for specific permission
     */
    private function getWidgetConfigForPermission($permission)
    {
        $configs = [
            'user.view' => [
                'type' => 'user_management',
                'title' => 'Manajemen User',
                'icon' => 'fas fa-users'
            ],
            'sarpras.view' => [
                'type' => 'sarpras_management',
                'title' => 'Sarana & Prasarana',
                'icon' => 'fas fa-boxes'
            ],
            'peminjaman.view' => [
                'type' => 'peminjaman_management',
                'title' => 'Peminjaman',
                'icon' => 'fas fa-clipboard-list'
            ],
            'system.monitoring' => [
                'type' => 'system_health',
                'title' => 'Kesehatan Sistem',
                'icon' => 'fas fa-heartbeat'
            ]
        ];
        
        return $configs[$permission] ?? null;
    }

    /**
     * Get dashboard summary data for user
     */
    public function getDashboardSummaryData()
    {
        $user = Auth::user();
        $permissions = $user->getPermissions();
        
        $summary = [
            'user_info' => [
                'name' => $user->name,
                'role' => $user->getRoleDisplayName(),
                'permissions_count' => $permissions->count()
            ],
            'available_features' => [],
            'quick_stats' => []
        ];
        
        // Get available features based on permissions
        $featureMap = [
            'user.view' => 'Manajemen User',
            'sarpras.view' => 'Sarana & Prasarana',
            'peminjaman.view' => 'Peminjaman',
            'report.view' => 'Laporan',
            'system.settings' => 'Pengaturan Sistem'
        ];
        
        foreach ($featureMap as $permission => $feature) {
            if ($permissions->contains($permission)) {
                $summary['available_features'][] = $feature;
            }
        }
        
        // Get quick stats
        if ($permissions->contains('user.view')) {
            $summary['quick_stats']['total_users'] = $this->safeCount('users', ['status' => 'active']);
        }
        
        if ($permissions->contains('peminjaman.view')) {
            $summary['quick_stats']['my_borrowings'] = $this->safeCount('peminjaman', ['user_id' => $user->id]);
        }
        
        return response()->json($summary);
    }

    /**
     * Get permission-based dashboard menu items
     */
    public function getDashboardMenuItems()
    {
        $user = Auth::user();
        $permissions = $user->getPermissions();
        
        $menuItems = [
            [
                'title' => 'Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'url' => route('dashboard'),
                'permission' => null,
                'always_visible' => true
            ]
        ];
        
        $permissionMenuMap = [
            'peminjaman.view' => [
                'title' => 'Peminjaman',
                'icon' => 'fas fa-clipboard-list',
                'url' => route('peminjaman.index')
            ],
            'sarpras.view' => [
                'title' => 'Sarana & Prasarana',
                'icon' => 'fas fa-boxes',
                'url' => route('sarpras.index')
            ],
            'user.view' => [
                'title' => 'Manajemen User',
                'icon' => 'fas fa-users',
                'url' => route('user-management.index')
            ],
            'role.view' => [
                'title' => 'Role & Permission',
                'icon' => 'fas fa-user-shield',
                'url' => route('role-management.index')
            ],
            'report.view' => [
                'title' => 'Laporan',
                'icon' => 'fas fa-chart-bar',
                'url' => route('reports.index')
            ],
            'system.settings' => [
                'title' => 'Pengaturan',
                'icon' => 'fas fa-cog',
                'url' => route('system.settings')
            ],
            'notification.view' => [
                'title' => 'Notifikasi',
                'icon' => 'fas fa-bell',
                'url' => route('notifications.index')
            ]
        ];
        
        foreach ($permissionMenuMap as $permission => $menuItem) {
            if ($permissions->contains($permission)) {
                $menuItem['permission'] = $permission;
                $menuItem['always_visible'] = false;
                $menuItems[] = $menuItem;
            }
        }
        
        // Add profile and logout (always visible)
        $menuItems[] = [
            'title' => 'Profil',
            'icon' => 'fas fa-user',
            'url' => route('profile.index'),
            'permission' => null,
            'always_visible' => true
        ];
        
        return response()->json($menuItems);
    }

    /**
     * Get dashboard configuration for user
     */
    public function getDashboardConfiguration()
    {
        $user = Auth::user();
        $permissions = $user->getPermissions();
        
        $config = [
            'user' => [
                'name' => $user->name,
                'role' => $user->getRoleDisplayName(),
                'permissions' => $permissions->pluck('name')->toArray()
            ],
            'dashboard' => [
                'title' => 'Dashboard ' . $user->getRoleDisplayName(),
                'subtitle' => 'Selamat datang, ' . $user->name . '!',
                'theme' => $this->getDashboardTheme($user),
                'layout' => $this->getDashboardLayout($permissions)
            ],
            'features' => $this->getAvailableFeatures($permissions),
            'widgets' => $this->getAvailableWidgets($permissions, $user),
            'quick_actions' => $this->getQuickActions($permissions),
            'menu_items' => $this->getMenuItemsForUser($permissions)
        ];
        
        return response()->json($config);
    }

    /**
     * Get dashboard theme based on user role
     */
    private function getDashboardTheme($user)
    {
        $role = $user->roles->first();
        
        $themes = [
            'admin' => 'dark',
            'peminjam' => 'light',
            'approver' => 'blue',
            'global_approver' => 'purple',
            'specific_approver' => 'green'
        ];
        
        return $themes[$role->name] ?? 'light';
    }

    /**
     * Get dashboard layout based on permissions
     */
    private function getDashboardLayout($permissions)
    {
        $layout = [
            'columns' => 12,
            'widgets_per_row' => 3,
            'show_sidebar' => true,
            'show_header' => true,
            'show_footer' => true
        ];
        
        // Adjust layout based on permissions
        if ($permissions->contains('user.view') && $permissions->contains('sarpras.view')) {
            $layout['widgets_per_row'] = 2; // More space for admin widgets
        }
        
        return $layout;
    }


    /**
     * Get available widgets for user
     */
    private function getAvailableWidgets($permissions, $user)
    {
        $widgets = [];
        
        if ($permissions->contains('user.view')) {
            $widgets[] = [
                'type' => 'user_management',
                'title' => 'Manajemen User',
                'icon' => 'fas fa-users',
                'data' => $this->getUserStats(),
                'permission' => 'user.view'
            ];
        }
        
        if ($permissions->contains('sarpras.view')) {
            $widgets[] = [
                'type' => 'sarpras_management',
                'title' => 'Sarana & Prasarana',
                'icon' => 'fas fa-boxes',
                'data' => $this->getSarprasStats(),
                'permission' => 'sarpras.view'
            ];
        }
        
        if ($permissions->contains('peminjaman.view')) {
            $widgets[] = [
                'type' => 'peminjaman_management',
                'title' => 'Peminjaman',
                'icon' => 'fas fa-clipboard-list',
                'data' => $this->getPeminjamanStats($user->id),
                'permission' => 'peminjaman.view'
            ];
        }
        
        if ($permissions->contains('system.monitoring')) {
            $widgets[] = [
                'type' => 'system_health',
                'title' => 'Kesehatan Sistem',
                'icon' => 'fas fa-heartbeat',
                'data' => ['health' => $this->getSystemHealth()],
                'permission' => 'system.monitoring'
            ];
        }
        
        return $widgets;
    }

    /**
     * Get menu items for user
     */
    private function getMenuItemsForUser($permissions)
    {
        $menuItems = [];
        
        $permissionMenuMap = [
            'peminjaman.view' => [
                'title' => 'Peminjaman',
                'icon' => 'fas fa-clipboard-list',
                'url' => route('peminjaman.index')
            ],
            'sarpras.view' => [
                'title' => 'Sarana & Prasarana',
                'icon' => 'fas fa-boxes',
                'url' => route('sarpras.index')
            ],
            'user.view' => [
                'title' => 'Manajemen User',
                'icon' => 'fas fa-users',
                'url' => route('user-management.index')
            ],
            'role.view' => [
                'title' => 'Role & Permission',
                'icon' => 'fas fa-user-shield',
                'url' => route('role-management.index')
            ],
            'report.view' => [
                'title' => 'Laporan',
                'icon' => 'fas fa-chart-bar',
                'url' => route('reports.index')
            ],
            'system.settings' => [
                'title' => 'Pengaturan',
                'icon' => 'fas fa-cog',
                'url' => route('system.settings')
            ],
            'notification.view' => [
                'title' => 'Notifikasi',
                'icon' => 'fas fa-bell',
                'url' => route('notifications.index')
            ]
        ];
        
        foreach ($permissionMenuMap as $permission => $menuItem) {
            if ($permissions->contains($permission)) {
                $menuItems[] = $menuItem;
            }
        }
        
        return $menuItems;
    }



    /**
     * Get available features for user
     */
    private function getAvailableFeatures($permissions)
    {
        $features = [];
        
        $featureMap = [
            'user.view' => [
                'name' => 'Manajemen User',
                'description' => 'Kelola data pengguna sistem',
                'icon' => 'fas fa-users',
                'color' => 'primary'
            ],
            'sarpras.view' => [
                'name' => 'Sarana & Prasarana',
                'description' => 'Kelola data sarana dan prasarana',
                'icon' => 'fas fa-boxes',
                'color' => 'info'
            ],
            'peminjaman.view' => [
                'name' => 'Peminjaman',
                'description' => 'Kelola data peminjaman',
                'icon' => 'fas fa-clipboard-list',
                'color' => 'success'
            ],
            'report.view' => [
                'name' => 'Laporan',
                'description' => 'Lihat laporan dan analisis',
                'icon' => 'fas fa-chart-bar',
                'color' => 'secondary'
            ],
            'system.settings' => [
                'name' => 'Pengaturan Sistem',
                'description' => 'Konfigurasi sistem',
                'icon' => 'fas fa-cog',
                'color' => 'warning'
            ]
        ];
        
        foreach ($featureMap as $permission => $feature) {
            if ($permissions->contains($permission)) {
                $features[] = $feature;
            }
        }
        
        return $features;
    }

}