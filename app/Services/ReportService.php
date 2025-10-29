<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\Sarana;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Retrieve paginated peminjaman report data with summary metrics.
     */
    public function getPeminjamanReportData(array $filters, int $perPage = 15): array
    {
        $baseQuery = $this->buildBaseQuery($filters);

        /** @var LengthAwarePaginator $paginator */
        $paginator = (clone $baseQuery)
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($filters);

        $collection = (clone $baseQuery)->get();

        return [
            'paginator' => $paginator,
            'summary' => $this->summarizeCollection($collection),
        ];
    }

    /**
     * Retrieve all peminjaman rows for export (without pagination).
     */
    public function getPeminjamanRowsForExport(array $filters): Collection
    {
        return (clone $this->buildBaseQuery($filters))
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Compute summary metrics for a peminjaman collection.
     */
    public function summarizeCollection(Collection $collection): array
    {
        $statusCounts = $collection
            ->groupBy('status')
            ->map(static fn(Collection $group) => $group->count())
            ->toArray();

        $totalParticipants = $collection->sum(static fn($peminjaman) => (int) ($peminjaman->jumlah_peserta ?? 0));

        $totalDurationHours = $collection->sum(fn($peminjaman) => $this->calculateDurationHours($peminjaman));

        $totalItemsApproved = $collection->sum(function ($peminjaman) {
            return $peminjaman->items->sum(fn($item) => (int) $item->approved_quantity);
        });

        return [
            'total_records' => $collection->count(),
            'status_counts' => $statusCounts,
            'total_participants' => $totalParticipants,
            'total_duration_hours' => $totalDurationHours,
            'total_items_approved' => $totalItemsApproved,
        ];
    }

    /**
     * Build analytics data for top sarana usage within the selected period.
     */
    public function getTopSaranaUsage(array $filters, int $limit = 5): array
    {
        $start = Carbon::parse($filters['start_date'])->startOfDay();
        $end = Carbon::parse($filters['end_date'])->endOfDay();

        $peminjamanCollection = (clone $this->buildBaseQuery($filters))
            ->whereIn('status', [
                Peminjaman::STATUS_APPROVED,
                Peminjaman::STATUS_PICKED_UP,
                Peminjaman::STATUS_RETURNED,
            ])
            ->get();

        if ($peminjamanCollection->isEmpty()) {
            return [];
        }

        $usageMap = [];

        foreach ($peminjamanCollection as $peminjaman) {
            $durationHours = max(1, $this->calculateDurationHours($peminjaman));

            foreach ($peminjaman->items as $item) {
                $sarana = $item->sarana;

                if (!$sarana) {
                    continue;
                }

                $approvedQty = (int) $item->approved_quantity;

                if ($approvedQty <= 0) {
                    continue;
                }

                $saranaId = (int) $sarana->id;

                if (!isset($usageMap[$saranaId])) {
                    $usageMap[$saranaId] = [
                        'sarana_id' => $saranaId,
                        'name' => $sarana->name,
                        'type' => $sarana->type,
                        'total_qty' => 0,
                        'used_hours' => 0,
                    ];
                }

                $usageMap[$saranaId]['total_qty'] += $approvedQty;
                $usageMap[$saranaId]['used_hours'] += $approvedQty * $durationHours;
            }
        }

        if (empty($usageMap)) {
            return [];
        }

        $saranaRecords = Sarana::query()
            ->withCount('units')
            ->select(['id', 'name', 'type', 'jumlah_total', 'jumlah_rusak', 'jumlah_maintenance', 'jumlah_hilang'])
            ->whereIn('id', array_keys($usageMap))
            ->get()
            ->keyBy('id');

        $periodDays = max(1, $start->diffInDays($end) + 1);
        $periodHours = $periodDays * 24;

        $results = collect($usageMap)
            ->sortByDesc('used_hours')
            ->take($limit)
            ->map(function (array $data) use ($saranaRecords, $periodHours) {
                /** @var \App\Models\Sarana|null $sarana */
                $sarana = $saranaRecords->get($data['sarana_id']);

                if (!$sarana) {
                    $availableUnits = 0;
                } elseif ($sarana->type === 'serialized') {
                    $availableUnits = (int) ($sarana->units_count ?? 0);
                } else {
                    $availableUnits = max(0, (int) ($sarana->jumlah_total ?? 0)
                        - (int) ($sarana->jumlah_rusak ?? 0)
                        - (int) ($sarana->jumlah_maintenance ?? 0)
                        - (int) ($sarana->jumlah_hilang ?? 0));
                }

                $availableHours = $availableUnits * $periodHours;
                $usedHours = (int) round($data['used_hours']);
                $utilization = $availableHours > 0
                    ? round(min(100, ($usedHours / $availableHours) * 100), 2)
                    : null;

                return [
                    'sarana_id' => $data['sarana_id'],
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'total_qty' => (int) $data['total_qty'],
                    'used_hours' => $usedHours,
                    'available_units' => $availableUnits,
                    'available_hours' => $availableHours,
                    'utilization_percentage' => $utilization,
                ];
            })
            ->values()
            ->all();

        return $results;
    }

    /**
     * Build the base query for peminjaman reports.
     */
    protected function buildBaseQuery(array $filters): Builder
    {
        $query = Peminjaman::query()->with([
            'user:id,name',
            'prasarana:id,name',
            'items.sarana:id,name,type',
            'items.units',
            'ukm:id,nama',
        ]);

        return $this->applyFilters($query, $filters);
    }

    /**
     * Apply report filters to the query.
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $start = Carbon::parse($filters['start_date'])->startOfDay();
        $end = Carbon::parse($filters['end_date'])->endOfDay();

        $query->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString());

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['search'])) {
            $keyword = '%' . $filters['search'] . '%';

            $query->where(function (Builder $builder) use ($keyword) {
                $builder->where('event_name', 'like', $keyword)
                    ->orWhereHas('user', static function (Builder $userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', $keyword);
                    })
                    ->orWhereHas('prasarana', static function (Builder $prasaranaQuery) use ($keyword) {
                        $prasaranaQuery->where('name', 'like', $keyword);
                    });
            });
        }

        $sarprasType = $filters['sarpras_type'] ?? null;
        $sarprasId = $filters['sarpras_id'] ?? null;

        if ($sarprasType === 'prasarana') {
            if (!empty($sarprasId)) {
                $query->where('prasarana_id', $sarprasId);
            } else {
                $query->whereNotNull('prasarana_id');
            }
        } elseif ($sarprasType === 'sarana') {
            $query->whereHas('items', static function (Builder $itemQuery) use ($sarprasId) {
                if (!empty($sarprasId)) {
                    $itemQuery->where('sarana_id', $sarprasId);
                }
            });
        }

        return $query;
    }

    /**
     * Calculate duration hours for a peminjaman entry.
     */
    protected function calculateDurationHours(Peminjaman $peminjaman): int
    {
        $startDate = $peminjaman->start_date ? $peminjaman->start_date->copy() : null;
        $endDate = $peminjaman->end_date ? $peminjaman->end_date->copy() : null;

        if (!$startDate || !$endDate) {
            return 0;
        }

        if ($peminjaman->start_time && $peminjaman->end_time) {
            $startDateTime = $startDate->copy()->setTimeFromTimeString($peminjaman->start_time);
            $endDateTime = $endDate->copy()->setTimeFromTimeString($peminjaman->end_time);

            if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
                $endDateTime = $endDateTime->addDay();
            }

            return max(1, $startDateTime->diffInHours($endDateTime));
        }

        return max(1, ($startDate->diffInDays($endDate) + 1) * 24);
    }
}
