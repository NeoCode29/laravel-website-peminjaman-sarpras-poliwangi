<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\PeminjamanItemUnit;
use App\Models\SaranaUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\UserQuotaService;

class PickupReturnService
{
    /**
     * Validate pickup: ensure serialized items have exact unit assignments and no double-booking overlap.
     */
    public function validatePickup(Peminjaman $peminjaman, ?string $fotoPath = null): void
    {
        if ($peminjaman->status !== Peminjaman::STATUS_APPROVED) {
            throw new \RuntimeException('Hanya peminjaman approved yang dapat divalidasi pengambilannya.');
        }

        DB::transaction(function () use ($peminjaman, $fotoPath) {
            // For each serialized item, ensure assigned units count equals qty_approved
            $items = PeminjamanItem::where('peminjaman_id', $peminjaman->id)->get();
            foreach ($items as $item) {
                $sarana = $item->sarana;
                if ($sarana && $sarana->type === 'serialized') {
                    $assignedUnits = $this->getActiveAssignmentIds($item);
                    if ($assignedUnits->count() !== (int) $item->qty_approved) {
                        throw new \RuntimeException('Jumlah unit yang ditetapkan tidak sama dengan qty_approved.');
                    }

                    // Double-booking guard: no overlapping peminjaman with approved/picked_up for the same unit
                    foreach ($assignedUnits as $unitId) {
                        $overlap = DB::table('peminjaman_item_units as piu')
                            ->join('peminjaman_items as it', 'piu.peminjaman_item_id', '=', 'it.id')
                            ->join('peminjaman as p', 'it.peminjaman_id', '=', 'p.id')
                            ->where('piu.unit_id', $unitId)
                            ->where('piu.status', 'active')
                            ->whereIn('p.status', [Peminjaman::STATUS_APPROVED, Peminjaman::STATUS_PICKED_UP])
                            ->where('p.id', '!=', $peminjaman->id)
                            ->where(function ($q) use ($peminjaman) {
                                $q->whereBetween('p.start_date', [$peminjaman->start_date, $peminjaman->end_date])
                                  ->orWhereBetween('p.end_date', [$peminjaman->start_date, $peminjaman->end_date])
                                  ->orWhere(function ($q2) use ($peminjaman) {
                                      $q2->where('p.start_date', '<=', $peminjaman->start_date)
                                         ->where('p.end_date', '>=', $peminjaman->end_date);
                                  });
                            })
                            ->exists();
                        if ($overlap) {
                            throw new \RuntimeException('Unit bernomor sedang terpakai pada slot waktu yang overlap.');
                        }
                    }
                }
            }

            $peminjaman->update([
                'status' => Peminjaman::STATUS_PICKED_UP,
                'pickup_validated_by' => Auth::id(),
                'pickup_validated_at' => now(),
                'foto_pickup_path' => $fotoPath,
            ]);
        });
    }

    /**
     * Validate return: mark peminjaman returned and release units.
     */
    public function validateReturn(Peminjaman $peminjaman, ?string $fotoPath = null): void
    {
        if ($peminjaman->status !== Peminjaman::STATUS_PICKED_UP) {
            throw new \RuntimeException('Hanya peminjaman yang sudah diambil yang dapat divalidasi pengembaliannya.');
        }

        DB::transaction(function () use ($peminjaman, $fotoPath) {
            $items = PeminjamanItem::where('peminjaman_id', $peminjaman->id)->get();
            foreach ($items as $item) {
                $sarana = $item->sarana;
                if ($sarana && $sarana->type === 'serialized') {
                    // Release units: application layer may also update sarana_units status elsewhere
                    // Here we just ensure assignments exist
                    $assignedUnits = $this->getActiveAssignmentIds($item);
                    if ($assignedUnits->isEmpty() && (int) $item->qty_approved > 0) {
                        throw new \RuntimeException('Tidak ada unit yang ditetapkan untuk item serialized.');
                    }
                }
            }

            $peminjaman->update([
                'status' => Peminjaman::STATUS_RETURNED,
                'return_validated_by' => Auth::id(),
                'return_validated_at' => now(),
                'foto_return_path' => $fotoPath,
            ]);

            // Quota update: leaving active set
            app(UserQuotaService::class)->decrementIfInactive($peminjaman);

            $this->releaseSerializedUnits($peminjaman);

            app(PeminjamanApprovalService::class)->syncAllPooledSarana($peminjaman->id, 'return');
        });
    }

    public function releaseSerializedUnits(Peminjaman $peminjaman): void
    {
        $items = PeminjamanItem::with('sarana')
            ->where('peminjaman_id', $peminjaman->id)
            ->get();

        $releasedBy = Auth::id();
        foreach ($items as $item) {
            $sarana = $item->sarana;
            if (!$sarana || $sarana->type !== 'serialized') {
                continue;
            }

            $this->releaseItemAssignments($peminjaman, $item, null, $releasedBy);

            $activeCount = PeminjamanItemUnit::where('peminjaman_item_id', $item->id)
                ->where('peminjaman_id', $peminjaman->id)
                ->where('status', 'active')
                ->count();

            if ($item->qty_approved !== $activeCount) {
                $item->update(['qty_approved' => $activeCount]);
            }
        }
    }

    private function getActiveAssignmentIds(PeminjamanItem $item)
    {
        return PeminjamanItemUnit::where('peminjaman_item_id', $item->id)
            ->where('status', 'active')
            ->pluck('unit_id');
    }

    private function releaseItemAssignments(Peminjaman $peminjaman, PeminjamanItem $item, ?array $unitIds = null, ?int $releasedBy = null): void
    {
        $releasedBy = $releasedBy ?? Auth::id();

        $assignmentsQuery = PeminjamanItemUnit::where('peminjaman_item_id', $item->id)
            ->where('peminjaman_id', $peminjaman->id)
            ->where('status', 'active')
            ->with('unit');

        if (!empty($unitIds)) {
            $assignmentsQuery->whereIn('unit_id', $unitIds);
        }

        $assignments = $assignmentsQuery->get();

        foreach ($assignments as $assignment) {
            $unit = $assignment->unit;

            if ($unit) {
                if (!in_array($unit->unit_status, ['tersedia', 'dipinjam'], true)) {
                    \Log::warning('Serialized unit not released due to non-available status', [
                        'unit_id' => $unit->id,
                        'current_status' => $unit->unit_status,
                        'peminjaman_id' => $peminjaman->id,
                        'peminjaman_item_id' => $item->id,
                        'context' => 'releaseItemAssignments',
                    ]);
                } elseif ($unit->unit_status !== 'tersedia') {
                    $unit->updateStatus('tersedia');
                }
            }

            $assignment->update([
                'status' => 'released',
                'released_by' => $releasedBy,
                'released_at' => now(),
            ]);
        }
    }
}


