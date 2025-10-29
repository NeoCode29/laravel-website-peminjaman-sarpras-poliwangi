<?php

namespace App\Services;

use App\Helpers\SystemSettingHelper;
use App\Models\AuditLog;
use App\Models\KategoriPrasarana;
use App\Models\Marking;
use App\Models\Notification;
use App\Models\Peminjaman;
use App\Models\Prasarana;
use App\Models\Sarana;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class DashboardService
{
    /**
     * Build aggregated dashboard data for the Blade view.
     */
    public function buildDashboardData(User $user): array
    {
        $defaultFilters = $this->getDefaultFilters();

        return [
            'user_info' => $this->formatUserInfo($user),
            'quick_stats' => $this->getStats($user),
            'recent_activities' => $this->getRecentActivities($user)->toArray(),
            'notifications' => $this->getNotifications($user)->toArray(),
            'quick_actions' => $this->getQuickActions($user),
            'widgets' => $this->getWidgets($user),
            'calendar_events' => $this->getCalendarEvents($user),
            'kpi' => $this->getKpiMetrics($user, $defaultFilters),
            'trend' => $this->getTrendData($user, $defaultFilters),
            'filters' => $this->formatFiltersPayload($defaultFilters),
            'yearly_totals' => $this->getYearlyLoanTotals($user),
        ];
    }

    protected function buildMarkingStats(User $user): array
    {
        if (!$this->hasPermission($user, 'marking.view')) {
            return [
                'total' => 0,
                'active' => 0,
                'upcoming' => 0,
                'completed' => 0,
            ];
        }

        $now = now();

        return [
            'total' => Marking::query()->count(),
            'active' => Marking::query()
                ->where('start_datetime', '<=', $now)
                ->where('end_datetime', '>=', $now)
                ->count(),
            'upcoming' => Marking::query()
                ->where('start_datetime', '>', $now)
                ->count(),
            'completed' => Marking::query()
                ->where('end_datetime', '<', $now)
                ->count(),
        ];
    }

    /**
     * Compute dashboard statistics based on permissions.
     */
    public function getStats(User $user): array
    {
        $stats = [];

        if ($this->hasPermission($user, 'user.view')) {
            $stats['users'] = $this->buildUserStats();
        }

        if ($this->hasPermission($user, 'sarpras.view')) {
            $stats['sarpras'] = $this->buildSarprasStats();
        }

        $stats['peminjaman'] = $this->buildPeminjamanStats($user);
        $stats['marking'] = $this->buildMarkingStats($user);

        return $stats;
    }

    /**
     * Get recent activities collection tailored to permissions.
     */
    public function getRecentActivities(User $user, int $limit = 10): Collection
    {
        if ($this->hasPermission($user, 'log.view')) {
            $query = AuditLog::query()
                ->with('user:id,name')
                ->orderByDesc('created_at')
                ->limit($limit);
        } else {
            $query = AuditLog::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit($limit);
        }

        return $query->get()->map(function (AuditLog $log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'created_at' => optional($log->created_at)->toIso8601String(),
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                ] : null,
            ];
        });
    }

    /**
     * Get unread notifications for the dashboard.
     */
    public function getNotifications(User $user, int $limit = 5): Collection
    {
        if (!$this->hasPermission($user, 'notification.view')) {
            return collect();
        }

        return Notification::query()
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (Notification $notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => !is_null($notification->read_at),
                    'action_url' => $notification->action_url,
                    'is_clickable' => (bool) $notification->is_clickable,
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                ];
            });
    }

    /**
     * Build calendar events for peminjaman and marking data.
     */
    public function getCalendarEvents(User $user, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $startDate = $start ? clone $start : now()->startOfDay();
        $endDate = $end ? clone $end : now()->copy()->addDays(30)->endOfDay();

        $peminjamanEvents = $this->buildPeminjamanEvents($user, $startDate, $endDate);
        $markingEvents = $this->buildMarkingEvents($user, $startDate, $endDate);

        return array_values(array_merge($peminjamanEvents, $markingEvents));
    }

    /**
     * Quick actions based on permissions and available routes.
     */
    public function getQuickActions(User $user): array
    {
        $actions = [];

        if ($this->hasPermission($user, 'user.create')) {
            $this->appendAction($actions, [
                'permission' => 'user.create',
                'title' => 'Tambah User',
                'icon' => 'fas fa-user-plus',
                'route' => 'user-management.create',
                'color' => 'primary',
            ]);
        }

        if ($this->hasPermission($user, 'peminjaman.create')) {
            $this->appendAction($actions, [
                'permission' => 'peminjaman.create',
                'title' => 'Ajukan Peminjaman',
                'icon' => 'fas fa-clipboard-list',
                'route' => 'peminjaman.create',
                'color' => 'success',
            ]);
        }

        if ($this->hasPermission($user, 'peminjaman.approve')) {
            $this->appendAction($actions, [
                'permission' => 'peminjaman.approve',
                'title' => 'Approval Pending',
                'icon' => 'fas fa-check-circle',
                'route' => 'peminjaman.pending',
                'color' => 'warning',
            ]);
        }

        if ($this->hasPermission($user, 'report.view')) {
            $this->appendAction($actions, [
                'permission' => 'report.view',
                'title' => 'Lihat Laporan',
                'icon' => 'fas fa-chart-bar',
                'route' => 'reports.index',
                'color' => 'secondary',
            ]);
        }

        return $actions;
    }

    /**
     * Dashboard widgets configuration per permission.
     */
    public function getWidgets(User $user): array
    {
        $widgets = [];

        if ($this->hasPermission($user, 'user.view')) {
            $widgets[] = [
                'type' => 'user_management',
                'title' => 'Manajemen User',
                'icon' => 'fas fa-users',
                'permission' => 'user.view',
                'data' => $this->buildUserStats(),
            ];
        }

        if ($this->hasPermission($user, 'sarpras.view')) {
            $widgets[] = [
                'type' => 'sarpras_management',
                'title' => 'Sarana & Prasarana',
                'icon' => 'fas fa-boxes',
                'permission' => 'sarpras.view',
                'data' => $this->buildSarprasStats(),
            ];
        }

        $widgets[] = [
            'type' => 'peminjaman_overview',
            'title' => $this->hasPermission($user, 'peminjaman.view') ? 'Peminjaman' : 'Aktivitas Saya',
            'icon' => 'fas fa-clipboard-list',
            'permission' => null,
            'data' => $this->buildPeminjamanStats($user),
        ];

        return $widgets;
    }

    /**
     * Dashboard summary data for API.
     */
    public function getSummary(User $user): array
    {
        $permissions = $user->getPermissions()->pluck('name')->toArray();

        $featureMap = [
            'user.view' => 'Manajemen User',
            'sarpras.view' => 'Sarana & Prasarana',
            'peminjaman.view' => 'Peminjaman',
            'report.view' => 'Laporan',
            'system.settings' => 'Pengaturan Sistem',
        ];

        $availableFeatures = array_values(array_filter($featureMap, function ($label, $permission) use ($permissions) {
            return in_array($permission, $permissions, true);
        }, ARRAY_FILTER_USE_BOTH));

        return [
            'user_info' => [
                'name' => $user->name,
                'role' => $user->getRoleDisplayName(),
                'permissions_count' => count($permissions),
            ],
            'available_features' => $availableFeatures,
            'quick_stats' => $this->getStats($user),
        ];
    }

    /**
     * Build navigation items for the sidebar menu.
     */
    public function getMenuItems(User $user): array
    {
        $items = [];

        $items[] = $this->makeMenuItem([
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'route' => 'dashboard',
            'always_visible' => true,
        ]);

        $permissionRoutes = [
            'peminjaman.view' => ['title' => 'Peminjaman', 'icon' => 'fas fa-clipboard-list', 'route' => 'peminjaman.index'],
            'sarpras.view' => ['title' => 'Sarana & Prasarana', 'icon' => 'fas fa-boxes', 'route' => 'sarana.index'],
            'user.view' => ['title' => 'Manajemen User', 'icon' => 'fas fa-users', 'route' => 'user-management.index'],
            'role.view' => ['title' => 'Role & Permission', 'icon' => 'fas fa-user-shield', 'route' => 'role-management.index'],
            'report.view' => ['title' => 'Laporan', 'icon' => 'fas fa-chart-bar', 'route' => 'reports.index'],
            'system.settings' => ['title' => 'Pengaturan', 'icon' => 'fas fa-cog', 'route' => 'system-settings.index'],
            'notification.view' => ['title' => 'Notifikasi', 'icon' => 'fas fa-bell', 'route' => 'notifications.index'],
        ];

        foreach ($permissionRoutes as $permission => $meta) {
            if ($this->hasPermission($user, $permission)) {
                $items[] = $this->makeMenuItem(array_merge($meta, [
                    'permission' => $permission,
                ]));
            }
        }

        $items[] = $this->makeMenuItem([
            'title' => 'Profil',
            'icon' => 'fas fa-user',
            'route' => 'profile.show',
            'always_visible' => true,
        ]);

        return array_values(array_filter($items));
    }

    /**
     * Dashboard configuration payload for API consumers.
     */
    public function getConfiguration(User $user): array
    {
        return [
            'user' => $this->formatUserInfo($user),
            'dashboard' => [
                'title' => 'Dashboard ' . $user->getRoleDisplayName(),
                'subtitle' => 'Selamat datang, ' . $user->name . '!',
                'theme' => 'light',
            ],
            'features' => $this->getSummary($user)['available_features'],
            'widgets' => $this->getWidgets($user),
            'quick_actions' => $this->getQuickActions($user),
            'menu_items' => $this->getMenuItems($user),
        ];
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    protected function formatUserInfo(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->getRoleDisplayName(),
            'user_type' => $user->getUserTypeDisplayAttribute(),
            'status' => $user->getStatusDisplayAttribute(),
            'profile_completed' => $user->isProfileCompleted(),
            'last_login_at' => optional($user->last_login_at)->toIso8601String(),
        ];
    }

    protected function buildUserStats(): array
    {
        $now = now();

        return [
            'total' => User::query()->where('status', 'active')->count(),
            'mahasiswa' => User::query()->where('status', 'active')->where('user_type', 'mahasiswa')->count(),
            'staff' => User::query()->where('status', 'active')->where('user_type', 'staff')->count(),
            'blocked' => User::query()->whereNotNull('blocked_until')->where('blocked_until', '>', $now)->count(),
            'new_today' => User::query()->whereDate('created_at', $now->toDateString())->count(),
        ];
    }

    protected function buildSarprasStats(): array
    {
        $today = now()->toDateString();

        $totalSarana = Sarana::query()->count();
        $totalPrasarana = Prasarana::query()->count();

        return [
            'total_sarana' => $totalSarana,
            'total_prasarana' => $totalPrasarana,
            'available_units' => Sarana::query()->sum('jumlah_tersedia'),
            'maintenance_units' => Sarana::query()->sum('jumlah_maintenance'),
            'new_today' => (
                Sarana::query()->whereDate('created_at', $today)->count()
                + Prasarana::query()->whereDate('created_at', $today)->count()
            ),
        ];
    }

    protected function buildPeminjamanStats(User $user): array
    {
        if ($this->hasPermission($user, 'peminjaman.view')) {
            $query = Peminjaman::query();
        } else {
            $query = Peminjaman::query()->where('user_id', $user->id);
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', Peminjaman::STATUS_PENDING)->count(),
            'active' => (clone $query)->whereIn('status', [
                Peminjaman::STATUS_APPROVED,
                Peminjaman::STATUS_PICKED_UP,
            ])->count(),
            'completed' => (clone $query)->where('status', Peminjaman::STATUS_RETURNED)->count(),
        ];
    }

    public function getYearlyLoanTotals(User $user, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        $baseQuery = $this->hasPermission($user, 'peminjaman.view')
            ? Peminjaman::query()
            : Peminjaman::query()->where('user_id', $user->id);

        $availableYears = (clone $baseQuery)
            ->selectRaw('DISTINCT YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(static fn ($value) => (int) $value)
            ->values()
            ->all();

        if (!in_array($year, $availableYears, true)) {
            $availableYears[] = $year;
        }

        $availableYears = array_values(array_unique($availableYears));
        rsort($availableYears);

        $monthlyCounts = (clone $baseQuery)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $series = [];

        for ($month = 1; $month <= 12; $month++) {
            $series[] = (int) ($monthlyCounts[$month] ?? 0);
        }

        $total = array_sum($series);
        $max = $total > 0 ? max($series) : 0;
        $peakIndex = $max > 0 ? array_search($max, $series, true) : null;
        $peakLabel = $peakIndex !== null ? $monthLabels[$peakIndex] : null;

        return [
            'year' => $year,
            'labels' => $monthLabels,
            'data' => $series,
            'total' => $total,
            'max' => $max,
            'peak_month' => $peakLabel,
            'available_years' => $availableYears,
        ];
    }

    protected function buildPeminjamanEvents(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $query = Peminjaman::query();

        if (!$this->hasPermission($user, 'peminjaman.view')) {
            $query->where('user_id', $user->id);
        }

        $peminjaman = $query
            ->with(['prasarana:id,name'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($nested) use ($startDate, $endDate) {
                        $nested->where('start_date', '<', $startDate->toDateString())
                            ->where('end_date', '>', $endDate->toDateString());
                    });
            })
            ->whereIn('status', [
                Peminjaman::STATUS_PENDING,
                Peminjaman::STATUS_APPROVED,
                Peminjaman::STATUS_PICKED_UP,
            ])
            ->limit(50)
            ->orderBy('start_date')
            ->get();

        return $peminjaman->map(function (Peminjaman $item) {
            $startTime = optional($item->start_time)->format('H:i');
            $endTime = optional($item->end_time)->format('H:i');

            $startDateTime = null;
            if ($item->start_date) {
                $startDateTime = $item->start_date->copy();
                if ($startTime) {
                    $startDateTime->setTimeFromTimeString($startTime);
                }
            }

            $endDateTime = null;
            if ($item->end_date) {
                $endDateTime = $item->end_date->copy();
                if ($endTime) {
                    $endDateTime->setTimeFromTimeString($endTime);
                }
            }

            return [
                'id' => 'peminjaman-' . $item->id,
                'title' => $item->event_name ?? 'Peminjaman #' . $item->id,
                'start' => $startDateTime ? $startDateTime->toIso8601String() : ($item->start_date ? $item->start_date->toDateString() : null),
                'end' => $endDateTime ? $endDateTime->toIso8601String() : ($item->end_date ? $item->end_date->toDateString() : null),
                'status' => $this->mapPeminjamanStatusForCalendar($item->status),
                'source' => 'peminjaman',
                'location' => $item->prasarana?->name ?? $item->lokasi_custom,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'url' => Route::has('peminjaman.show') ? route('peminjaman.show', $item->id) : null,
            ];
        })->all();
    }

    protected function buildMarkingEvents(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $query = Marking::query();

        if (!$this->hasPermission($user, 'peminjaman.view')) {
            $query->where('user_id', $user->id);
        }

        $markings = $query
            ->with(['prasarana:id,name'])
            ->whereBetween('start_datetime', [$startDate, $endDate])
            ->orWhereBetween('end_datetime', [$startDate, $endDate])
            ->limit(50)
            ->orderBy('start_datetime')
            ->get();

        return $markings->map(function (Marking $marking) {
            return [
                'id' => 'marking-' . $marking->id,
                'title' => $marking->event_name ?? 'Marking #' . $marking->id,
                'start' => optional($marking->start_datetime)->toIso8601String(),
                'end' => optional($marking->end_datetime)->toIso8601String(),
                'status' => 'marking',
                'source' => 'marking',
                'location' => $marking->prasarana?->name ?? $marking->lokasi_custom,
                'url' => Route::has('marking.show') ? route('marking.show', $marking->id) : null,
            ];
        })->all();
    }

    protected function appendAction(array &$actions, array $meta): void
    {
        if (!empty($meta['route']) && Route::has($meta['route'])) {
            $actions[] = [
                'title' => $meta['title'],
                'icon' => $meta['icon'],
                'url' => route($meta['route']),
                'color' => $meta['color'] ?? 'primary',
                'permission' => $meta['permission'] ?? null,
            ];
        }
    }

    protected function makeMenuItem(array $meta): ?array
    {
        if (!empty($meta['route']) && !Route::has($meta['route'])) {
            return null;
        }

        return [
            'title' => $meta['title'],
            'icon' => $meta['icon'] ?? null,
            'url' => !empty($meta['route']) ? route($meta['route']) : ($meta['url'] ?? '#'),
            'permission' => $meta['permission'] ?? null,
            'always_visible' => (bool) ($meta['always_visible'] ?? false),
        ];
    }

    protected function mapPeminjamanStatusForCalendar(string $status): string
    {
        return match ($status) {
            Peminjaman::STATUS_PENDING => 'pending',
            Peminjaman::STATUS_APPROVED, Peminjaman::STATUS_PICKED_UP => 'approved',
            Peminjaman::STATUS_RETURNED => 'completed',
            default => $status,
        };
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        try {
            return $user->hasPermission($permission);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function getDefaultFilters(): array
    {
        $now = now();

        return [
            'range' => 'month',
            'start_date' => $now->copy()->startOfMonth(),
            'end_date' => $now->copy()->endOfMonth(),
            'status' => null,
            'category_id' => null,
        ];
    }

    public function formatFiltersPayload(array $filters): array
    {
        return [
            'range' => $filters['range'],
            'start_date' => $filters['start_date']?->toDateString(),
            'end_date' => $filters['end_date']?->toDateString(),
            'status' => $filters['status'],
            'category_id' => $filters['category_id'],
        ];
    }

    public function getKpiMetrics(User $user, array $filters): array
    {
        $query = $this->scopedPeminjamanQuery($user);

        $activeQuery = (clone $query)->whereIn('status', [
            Peminjaman::STATUS_PENDING,
            Peminjaman::STATUS_APPROVED,
            Peminjaman::STATUS_PICKED_UP,
        ]);

        $pendingQuery = (clone $query)->where('status', Peminjaman::STATUS_PENDING);

        $dueTodayQuery = (clone $query)->whereDate('end_date', now()->toDateString())
            ->whereIn('status', [
                Peminjaman::STATUS_APPROVED,
                Peminjaman::STATUS_PICKED_UP,
            ]);

        $completedLastWeekQuery = (clone $query)
            ->where('status', Peminjaman::STATUS_RETURNED)
            ->whereBetween('return_validated_at', [now()->copy()->subDays(6)->startOfDay(), now()->endOfDay()]);

        $lateLastWeekQuery = (clone $completedLastWeekQuery)
            ->whereColumn('return_validated_at', '>', DB::raw('end_date'));

        $totalSarprasReady = Sarana::query()->sum('jumlah_tersedia') + Prasarana::query()->where('status', 'tersedia')->count();

        $latePercentage = 0;
        $completedCount = $completedLastWeekQuery->count();
        if ($completedCount > 0) {
            $lateCount = $lateLastWeekQuery->count();
            $latePercentage = round(($lateCount / $completedCount) * 100, 2);
        }

        $slaTargetHours = (int) SystemSettingHelper::get('approval_sla_hours', 4);
        $averageApprovalHours = $this->calculateAverageApprovalHours(clone $query, $filters);
        $slaDelta = $averageApprovalHours !== null ? round($averageApprovalHours - $slaTargetHours, 2) : null;

        return [
            'active_loans' => $activeQuery->count(),
            'pending_loans' => $pendingQuery->count(),
            'due_today' => $dueTodayQuery->count(),
            'resources_ready' => $totalSarprasReady,
            'late_percentage' => $latePercentage,
            'average_approval_hours' => $averageApprovalHours,
            'sla_target_hours' => $slaTargetHours,
            'sla_delta' => $slaDelta,
        ];
    }

    public function getTrendData(User $user, array $filters): array
    {
        $periods = $this->buildTrendPeriods($filters['range'], $filters['start_date'], $filters['end_date']);

        $datasets = [
            'submitted' => [],
            'approved' => [],
            'rejected' => [],
            'marking' => [],
        ];

        foreach ($periods as $period) {
            $datasets['submitted'][] = $this->countPeminjamanByPeriod($user, $period, null);
            $datasets['approved'][] = $this->countPeminjamanByPeriod($user, $period, Peminjaman::STATUS_APPROVED);
            $datasets['rejected'][] = $this->countPeminjamanByPeriod($user, $period, Peminjaman::STATUS_REJECTED);
            $datasets['marking'][] = $this->countMarkingByPeriod($user, $period);
        }

        return [
            'labels' => array_map(fn ($period) => $period['label'], $periods),
            'datasets' => $datasets,
        ];
    }

    protected function scopedPeminjamanQuery(User $user): Builder
    {
        $query = Peminjaman::query();

        if (!$this->hasPermission($user, 'peminjaman.view')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    protected function calculateAverageApprovalHours(Builder $query, array $filters): ?float
    {
        $filteredQuery = (clone $query)
            ->whereNotNull('approved_at')
            ->whereNotNull('created_at');

        $this->applyRangeFilter($filteredQuery, $filters);

        $stats = $filteredQuery
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, approved_at)) as avg_hours'))
            ->first();

        if (!$stats || $stats->avg_hours === null) {
            return null;
        }

        return round((float) $stats->avg_hours, 2);
    }

    protected function applyRangeFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('created_at', [
                $filters['start_date']->copy()->startOfDay(),
                $filters['end_date']->copy()->endOfDay(),
            ]);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('prasarana', function (Builder $builder) use ($filters) {
                $builder->where('kategori_id', $filters['category_id']);
            });
        }
    }

    protected function buildTrendPeriods(string $range, Carbon $start, Carbon $end): array
    {
        $periods = [];

        $cursor = $start->copy();

        while ($cursor->lte($end) && count($periods) < 12) {
            if ($range === 'day') {
                $periodStart = $cursor->copy()->startOfDay();
                $periodEnd = $cursor->copy()->endOfDay();
                $label = $cursor->format('d M');
                $cursor->addDay();
            } elseif ($range === 'week') {
                $periodStart = $cursor->copy()->startOfWeek();
                $periodEnd = $cursor->copy()->endOfWeek();
                $label = 'Minggu ' . $cursor->format('W');
                $cursor->addWeek();
            } else {
                $periodStart = $cursor->copy()->startOfMonth();
                $periodEnd = $cursor->copy()->endOfMonth();
                $label = $cursor->translatedFormat('M Y');
                $cursor->addMonth();
            }

            $periods[] = [
                'start' => $periodStart,
                'end' => $periodEnd,
                'label' => $label,
            ];
        }

        return $periods;
    }

    protected function countPeminjamanByPeriod(User $user, array $period, ?string $status): int
    {
        $query = $this->scopedPeminjamanQuery($user)
            ->whereBetween('created_at', [
                $period['start']->copy()->startOfDay(),
                $period['end']->copy()->endOfDay(),
            ]);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->count();
    }

    protected function countMarkingByPeriod(User $user, array $period): int
    {
        $query = Marking::query()
            ->whereBetween('created_at', [
                $period['start']->copy()->startOfDay(),
                $period['end']->copy()->endOfDay(),
            ]);

        if (!$this->hasPermission($user, 'peminjaman.view')) {
            $query->where('user_id', $user->id);
        }

        return $query->count();
    }

    public function getFilterOptions(User $user): array
    {
        $defaultFilters = $this->getDefaultFilters();

        $statuses = [
            Peminjaman::STATUS_PENDING => 'Pending',
            Peminjaman::STATUS_APPROVED => 'Disetujui',
            Peminjaman::STATUS_PICKED_UP => 'Sedang Diambil',
            Peminjaman::STATUS_RETURNED => 'Selesai',
            Peminjaman::STATUS_REJECTED => 'Ditolak',
            Peminjaman::STATUS_CANCELLED => 'Dibatalkan',
        ];

        $categories = KategoriPrasarana::query()
            ->select('id', 'name', 'is_active')
            ->orderBy('name')
            ->get()
            ->filter(fn ($kategori) => $kategori->is_active !== false)
            ->map(fn ($kategori) => [
                'value' => $kategori->id,
                'label' => $kategori->name,
            ])->values()->all();

        return [
            'defaults' => $this->formatFiltersPayload($defaultFilters),
            'options' => [
                'range' => [
                    ['value' => 'day', 'label' => 'Harian'],
                    ['value' => 'week', 'label' => 'Mingguan'],
                    ['value' => 'month', 'label' => 'Bulanan'],
                ],
                'status' => array_map(static fn ($value, $label) => [
                    'value' => $value,
                    'label' => $label,
                ], array_keys($statuses), array_values($statuses)),
                'categories' => $categories,
            ],
        ];
    }
}
