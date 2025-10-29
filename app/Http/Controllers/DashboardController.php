<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'isBlocked') && $user->isBlocked()) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif atau diblokir. Silakan hubungi administrator.');
        }

        if (method_exists($user, 'isProfileCompleted') && !$user->isProfileCompleted()) {
            return redirect()->route('profile.setup')
                ->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu sebelum melanjutkan.');
        }

        $role = $user->roles->first();

        if (!$role) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Role tidak ditemukan. Silakan hubungi administrator.');
        }

        $permissions = $user->getPermissions();

        $dashboardData = $this->dashboardService->buildDashboardData($user);

        return view('dashboard', [
            'user' => $user,
            'role' => $role,
            'permissions' => $permissions,
            'dashboardData' => $dashboardData,
        ]);
    }

    public function getDashboardStats(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($this->dashboardService->getStats($user));
    }

    public function getDashboardActivities(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $limit = (int) $request->query('limit', 20);

        $activities = $this->dashboardService
            ->getRecentActivities($user, max($limit, 1));

        return response()->json($activities);
    }

    public function getDashboardNotifications(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $limit = (int) $request->query('limit', 10);

        $notifications = $this->dashboardService
            ->getNotifications($user, max($limit, 1));

        return response()->json($notifications);
    }

    public function getDashboardSummaryData(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($this->dashboardService->getSummary($user));
    }

    public function getDashboardMenuItems(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($this->dashboardService->getMenuItems($user));
    }

    public function getDashboardConfiguration(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($this->dashboardService->getConfiguration($user));
    }

    public function getDashboardCalendarEvents(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $start = $this->parseCarbonDate($request->query('start'));
        $end = $this->parseCarbonDate($request->query('end'));

        $events = $this->dashboardService->getCalendarEvents($user, $start, $end);

        return response()->json($events);
    }

    public function getDashboardKpi(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $filters = $this->buildFilters($request);

        return response()->json([
            'data' => $this->dashboardService->getKpiMetrics($user, $filters),
            'filters' => $this->dashboardService->formatFiltersPayload($filters),
        ]);
    }

    public function getDashboardTrend(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $filters = $this->buildFilters($request);

        return response()->json([
            'data' => $this->dashboardService->getTrendData($user, $filters),
            'filters' => $this->dashboardService->formatFiltersPayload($filters),
        ]);
    }

    public function getDashboardFilterOptions(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json($this->dashboardService->getFilterOptions($user));
    }

    public function getDashboardYearlyTotals(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $year = $request->query('year');
        $year = is_numeric($year) ? (int) $year : null;

        return response()->json($this->dashboardService->getYearlyLoanTotals($user, $year));
    }

    protected function parseCarbonDate(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function buildFilters(Request $request): array
    {
        $filters = $this->dashboardService->getDefaultFilters();

        $range = $request->query('range');
        if (is_string($range) && in_array($range, ['day', 'week', 'month'], true)) {
            $filters['range'] = $range;
        }

        $startDate = $this->parseCarbonDate($request->query('start_date'));
        $endDate = $this->parseCarbonDate($request->query('end_date'));

        if ($startDate) {
            $filters['start_date'] = $startDate->copy()->startOfDay();
        }

        if ($endDate) {
            $filters['end_date'] = $endDate->copy()->endOfDay();
        }

        if ($filters['start_date'] && $filters['end_date'] && $filters['start_date']->gt($filters['end_date'])) {
            [$filters['start_date'], $filters['end_date']] = [$filters['end_date'], $filters['start_date']];
        }

        $status = $request->query('status');
        $allowedStatuses = [
            Peminjaman::STATUS_PENDING,
            Peminjaman::STATUS_APPROVED,
            Peminjaman::STATUS_PICKED_UP,
            Peminjaman::STATUS_RETURNED,
            Peminjaman::STATUS_REJECTED,
            Peminjaman::STATUS_CANCELLED,
        ];

        if (is_string($status) && in_array($status, $allowedStatuses, true)) {
            $filters['status'] = $status;
        }

        $categoryId = $request->query('category_id');
        if (is_numeric($categoryId)) {
            $filters['category_id'] = (int) $categoryId;
        }

        return $filters;
    }
}
