<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show dashboard based on user role
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

        // Route to appropriate dashboard based on role
        $role = $user->roles->first();
        
        if (!$role) {
            return redirect()->route('login')
                ->with('error', 'Role tidak ditemukan. Silakan hubungi administrator.');
        }

        switch ($role->name) {
            case 'admin':
                return $this->adminDashboard();
            case 'peminjam':
                return $this->peminjamDashboard();
            default:
                return $this->peminjamDashboard(); // Default to peminjam
        }
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
    public function getStats()
    {
        $user = Auth::user();
        $role = $user->roles->first();
        
        if ($role->name === 'admin') {
            return response()->json($this->getAdminStats());
        } else {
            return response()->json($this->getPeminjamStats($user->id));
        }
    }

    /**
     * Get recent activities (API endpoint)
     */
    public function getActivities()
    {
        $user = Auth::user();
        $role = $user->roles->first();
        
        if ($role->name === 'admin') {
            $activities = $this->getRecentActivities(20);
        } else {
            $activities = $this->getUserRecentActivities($user->id, 10);
        }
        
        return response()->json($activities);
    }

    /**
     * Get user notifications (API endpoint)
     */
    public function getNotifications()
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
}