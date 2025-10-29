<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class SlotConflictService
{
    private ?Peminjaman $lastConflict = null;
    private ?string $lastConflictType = null;
    private array $lastConflictDetails = [];

    /**
     * Check conflicts according to alur_peminjaman.json rules:
     * - Overlap date range conflicts (pending/approved/picked_up)
     * - Prasarana exclusive conflict + optional event_gap_hours
     * - Sarana conflicts (simplified: presence of same sarana in overlapping peminjaman)
     */
    public function checkConflicts(Request $request, $excludeId = null): ?string
    {
        $this->lastConflict = null;
        $this->lastConflictType = null;
        $this->lastConflictDetails = [];

        $query = Peminjaman::whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('start_date', '<=', $request->start_date)
                         ->where('end_date', '>=', $request->end_date);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $requestUser = $request->user();
        if (!$requestUser && Auth::check()) {
            $requestUser = Auth::user();
        }

        // Prasarana conflict (exclusive per slot)
        if ($request->prasarana_id) {
            $conflicts = (clone $query)
                ->where('prasarana_id', $request->prasarana_id)
                ->with('user')
                ->get();

            foreach ($conflicts as $conflict) {
                if (!$this->hasTimeOverlap($request->start_date, $request->start_time, $request->end_date, $request->end_time, $conflict)) {
                    continue;
                }
                if ($this->shouldBlockByPriority($requestUser, $conflict)) {
                    $this->registerConflict($conflict, 'prasarana');
                    return 'Prasarana sudah dipinjam pada periode tersebut.';
                }
            }

            // Gap hours validation if time provided
            $gapHours = (int) SystemSetting::getValue('event_gap_hours', 0);
            if ($gapHours > 0 && $request->start_time && $request->end_time) {
                $startDateTime = $this->combineDateAndTime($request->start_date, $request->start_time);
                $endDateTime = $this->combineDateAndTime($request->end_date, $request->end_time);

                $adjacents = Peminjaman::where('prasarana_id', $request->prasarana_id)
                    ->where('id', '!=', $excludeId)
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->with('user')
                    ->get();

                foreach ($adjacents as $candidate) {
                    $pStart = $this->combineDateAndTime($candidate->start_date, $candidate->start_time);
                    $pEnd = $this->combineDateAndTime($candidate->end_date, $candidate->end_time);
                    if ($pStart && $pEnd) {
                        $beforeGap = $pStart->copy()->subHours($gapHours);
                        $afterGap = $pEnd->copy()->addHours($gapHours);
                        $isConflict = $startDateTime->between($beforeGap, $afterGap) || $endDateTime->between($beforeGap, $afterGap);
                        if ($isConflict && $this->shouldBlockByPriority($requestUser, $candidate)) {
                            $this->registerConflict($candidate, 'prasarana_gap');
                            return "Jeda minimal {$gapHours} jam antar acara pada prasarana ini belum terpenuhi.";
                        }
                    }
                }
            }
        }

        // Sarana conflicts (simplified availability check by overlapping presence)
        $saranaIds = collect($request->sarana_items ?? [])->pluck('sarana_id');
        if ($saranaIds->isNotEmpty()) {
            $conflictSaranas = (clone $query)
                ->with(['user', 'items' => function ($q) use ($saranaIds) {
                    $q->whereIn('sarana_id', $saranaIds)
                        ->select('id', 'peminjaman_id', 'sarana_id');
                }])
                ->whereHas('items', function ($q) use ($saranaIds) {
                    $q->whereIn('sarana_id', $saranaIds);
                })
                ->get();

            $groupedConflicts = [];

            foreach ($conflictSaranas as $conflictSarana) {
                if ($excludeId && $conflictSarana->id === (int) $excludeId) {
                    continue;
                }
                if (!$this->hasTimeOverlap($request->start_date, $request->start_time, $request->end_date, $request->end_time, $conflictSarana)) {
                    continue;
                }
                if (!$this->shouldBlockByPriority($requestUser, $conflictSarana)) {
                    continue;
                }

                $conflictSaranaIds = $conflictSarana->items
                    ->pluck('sarana_id')
                    ->unique()
                    ->values();

                foreach ($conflictSaranaIds as $sid) {
                    $groupedConflicts[$sid][] = $conflictSarana;
                }
            }

            if (!empty($groupedConflicts)) {
                $firstConflict = reset($groupedConflicts);
                $this->registerConflict($firstConflict[0], 'sarana');
                $this->lastConflictDetails = [
                    'sarana_ids' => array_keys($groupedConflicts),
                    'grouped' => array_map(function ($conflicts) {
                        return array_map(fn ($p) => $p->id, $conflicts);
                    }, $groupedConflicts),
                ];

                return 'Beberapa sarana sudah dipinjam pada periode tersebut.';
            }
        }

        return null;
    }

    private function hasTimeOverlap($startDate, $startTime, $endDate, $endTime, Peminjaman $existing): bool
    {
        if (!$startTime || !$endTime || !$existing->start_time || !$existing->end_time) {
            return true;
        }

        $requestStart = $this->combineDateAndTime($startDate, $startTime);
        $requestEnd = $this->combineDateAndTime($endDate, $endTime);
        $existingStart = $this->combineDateAndTime($existing->start_date, $existing->start_time);
        $existingEnd = $this->combineDateAndTime($existing->end_date, $existing->end_time);

        if (!$requestStart || !$requestEnd || !$existingStart || !$existingEnd) {
            return true;
        }

        return $requestStart->lt($existingEnd) && $existingStart->lt($requestEnd);
    }

    public function getLastConflict(): ?array
    {
        if (!$this->lastConflict) {
            return null;
        }

        return [
            'peminjaman' => $this->lastConflict,
            'type' => $this->lastConflictType,
            'details' => $this->lastConflictDetails,
        ];
    }

    private function combineDateAndTime($date, $time): ?Carbon
    {
        if (empty($date) || empty($time)) {
            return null;
        }

        $dateInstance = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        if ($time instanceof \DateTimeInterface) {
            $timeString = Carbon::instance($time)->format('H:i');
        } else {
            $timeString = trim((string) $time);
        }

        if ($timeString === '') {
            return null;
        }

        return $dateInstance->setTimeFromTimeString($timeString);
    }

    private function registerConflict(Peminjaman $peminjaman, string $type): void
    {
        $this->lastConflict = $peminjaman;
        $this->lastConflictType = $type;
    }

    public function findPendingConflicts(Peminjaman $peminjaman, array $statuses = ['pending']): Collection
    {
        $peminjaman->loadMissing('items');

        $query = Peminjaman::query()
            ->where('id', '!=', $peminjaman->id)
            ->whereIn('status', $statuses)
            ->where(function ($q) use ($peminjaman) {
                $q->whereBetween('start_date', [$peminjaman->start_date, $peminjaman->end_date])
                    ->orWhereBetween('end_date', [$peminjaman->start_date, $peminjaman->end_date])
                    ->orWhere(function ($nested) use ($peminjaman) {
                        $nested->where('start_date', '<=', $peminjaman->start_date)
                            ->where('end_date', '>=', $peminjaman->end_date);
                    });
            });

        $conflicts = collect();

        if ($peminjaman->prasarana_id) {
            $prasaranaConflicts = (clone $query)
                ->where('prasarana_id', $peminjaman->prasarana_id)
                ->with('user')
                ->get()
                ->filter(function (Peminjaman $candidate) use ($peminjaman) {
                    return $this->hasTimeOverlap(
                        $peminjaman->start_date,
                        $peminjaman->start_time,
                        $peminjaman->end_date,
                        $peminjaman->end_time,
                        $candidate
                    );
                });

            $conflicts = $conflicts->merge($prasaranaConflicts);
        }

        $saranaIds = $peminjaman->items->pluck('sarana_id')->filter()->unique();

        if ($saranaIds->isNotEmpty()) {
            $saranaConflicts = (clone $query)
                ->whereHas('items', function ($q) use ($saranaIds) {
                    $q->whereIn('sarana_id', $saranaIds);
                })
                ->with(['user', 'items' => function ($q) use ($saranaIds) {
                    $q->whereIn('sarana_id', $saranaIds);
                }])
                ->get()
                ->filter(function (Peminjaman $candidate) use ($peminjaman) {
                    return $this->hasTimeOverlap(
                        $peminjaman->start_date,
                        $peminjaman->start_time,
                        $peminjaman->end_date,
                        $peminjaman->end_time,
                        $candidate
                    );
                });

            $conflicts = $conflicts->merge($saranaConflicts);
        }

        return $conflicts->unique('id')->values();
    }

    private function shouldBlockByPriority(?User $requestUser, Peminjaman $existing): bool
    {
        if (!$requestUser) {
            return true;
        }

        $requestPriority = $this->getUserPriority($requestUser);
        $existingPriority = $this->getUserPriority(optional($existing)->user);

        if ($requestPriority > $existingPriority) {
            return false;
        }

        if ($requestPriority < $existingPriority) {
            return true;
        }

        return true;
    }

    private function getUserPriority(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        // Base priority: mahasiswa terendah
        $priority = ($user->user_type ?? null) === 'mahasiswa' ? 0 : 1;

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole(['admin', 'global_approver', 'approver', 'specific_approver'])) {
                $priority = max($priority, 2);
            }
        }

        return $priority;
    }
}



