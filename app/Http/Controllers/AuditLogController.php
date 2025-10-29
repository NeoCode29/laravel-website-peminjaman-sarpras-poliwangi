<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'user.not.blocked', 'profile.completed', 'permission:log.view']);
    }

    public function index(Request $request)
    {
        $filters = $this->prepareFilters($request);

        $query = AuditLog::query()
            ->with(['user:id,name'])
            ->orderByDesc('created_at');

        $this->applyFilters($query, $filters);

        $paginator = $query
            ->paginate((int) $request->input('per_page', 15))
            ->appends($filters);

        $users = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(100)
            ->get();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->limit(100)
            ->pluck('action')
            ->filter()
            ->values();

        $models = AuditLog::query()
            ->select('model_type')
            ->distinct()
            ->orderBy('model_type')
            ->limit(100)
            ->pluck('model_type')
            ->filter()
            ->values();

        $summaryQuery = AuditLog::query();
        $this->applyFilters($summaryQuery, $filters);

        $summary = [
            'total_records' => $paginator->total(),
            'unique_users' => $summaryQuery->distinct('user_id')->count('user_id'),
        ];

        return view('audit-logs.index', [
            'paginator' => $paginator,
            'filters' => $filters,
            'users' => $users,
            'actions' => $actions,
            'models' => $models,
            'summary' => $summary,
        ]);
    }

    protected function prepareFilters(Request $request): array
    {
        $defaultEnd = Carbon::now()->endOfDay();
        $defaultStart = $defaultEnd->copy()->subMonth()->startOfDay();

        try {
            $start = $request->filled('start_date')
                ? Carbon::parse($request->input('start_date'))->startOfDay()
                : $defaultStart;
        } catch (\Throwable $e) {
            $start = $defaultStart;
        }

        try {
            $end = $request->filled('end_date')
                ? Carbon::parse($request->input('end_date'))->endOfDay()
                : $defaultEnd;
        } catch (\Throwable $e) {
            $end = $defaultEnd;
        }

        if ($end->lessThan($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $filters = [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'user_id' => $request->input('user_id'),
            'action' => $request->input('action'),
            'model_type' => $request->input('model_type'),
            'search' => $request->input('search'),
            'ip_address' => $request->input('ip_address'),
        ];

        return Arr::where($filters, static fn($value) => !is_null($value) && $value !== '');
    }

    protected function applyFilters($query, array $filters)
    {
        $query->whereBetween('created_at', [
            Carbon::parse($filters['start_date'])->startOfDay(),
            Carbon::parse($filters['end_date'])->endOfDay(),
        ]);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (!empty($filters['ip_address'])) {
            $query->where('ip_address', 'like', '%' . $filters['ip_address'] . '%');
        }

        if (!empty($filters['search'])) {
            $keyword = '%' . $filters['search'] . '%';
            $query->where(function ($builder) use ($keyword) {
                $builder->where('description', 'like', $keyword)
                    ->orWhere('action', 'like', $keyword)
                    ->orWhere('model_type', 'like', $keyword)
                    ->orWhere('user_agent', 'like', $keyword)
                    ->orWhereHas('user', static function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', $keyword);
                    });
            });
        }

        return $query;
    }
}
