<?php

namespace App\Services;

use App\Models\Sarana;
use App\Models\SaranaUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SaranaService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Sarana::query()->with(['kategori', 'creator']);

        if (!empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'tersedia':
                    $query->where('jumlah_tersedia', '>', 0);
                    break;
                case 'kosong':
                    $query->where('jumlah_tersedia', 0);
                    break;
            }
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data, int $creatorId, ?UploadedFile $image = null): Sarana
    {
        return DB::transaction(function () use ($data, $creatorId, $image) {
            $data['created_by'] = $creatorId;

            // Handle image upload
            if ($image) {
                $imagePath = $image->store('sarana', 'public');
                $data['image_url'] = $imagePath;
            }

            // Set default values untuk statistik
            $data['jumlah_tersedia'] = (int) ($data['jumlah_tersedia'] ?? $data['jumlah_total']);
            $data['jumlah_rusak'] = (int) ($data['jumlah_rusak'] ?? 0);
            $data['jumlah_maintenance'] = (int) ($data['jumlah_maintenance'] ?? 0);
            $data['jumlah_hilang'] = (int) ($data['jumlah_hilang'] ?? 0);

            // Validasi konsistensi untuk pooled
            if ($data['type'] === 'pooled') {
                $this->validatePooledBreakdown($data);
            } else {
                // Serialized: breakdown akan dihitung dari units
                $data['jumlah_tersedia'] = 0;
                $data['jumlah_rusak'] = 0;
                $data['jumlah_maintenance'] = 0;
                $data['jumlah_hilang'] = 0;
            }

            $sarana = Sarana::create($data);
            return $sarana->load(['kategori', 'creator']);
        });
    }

    public function update(Sarana $sarana, array $data, ?UploadedFile $image = null): Sarana
    {
        return DB::transaction(function () use ($sarana, $data, $image) {
            // Handle image removal
            if (isset($data['remove_current_image']) && $data['remove_current_image']) {
                if ($sarana->image_url) {
                    Storage::disk('public')->delete($sarana->image_url);
                    $data['image_url'] = null;
                }
                unset($data['remove_current_image']);
            }
            
            // Handle image upload
            if ($image) {
                // Delete old image
                if ($sarana->image_url) {
                    Storage::disk('public')->delete($sarana->image_url);
                }
                
                $imagePath = $image->store('sarana', 'public');
                $data['image_url'] = $imagePath;
            }

            // Penanganan perubahan tipe sarana & validasi
            $newType = $data['type'];
            $oldType = $sarana->type;

            // Validasi jumlah_total untuk sarana serialized
            if ($newType === 'serialized') {
                $existingUnits = $sarana->units()->count();
                if ($data['jumlah_total'] < $existingUnits) {
                    throw new \InvalidArgumentException('Jumlah total tidak boleh lebih kecil dari jumlah unit yang sudah terdaftar.');
                }
                // Serialized: breakdown akan dihitung dari units, paksa nol
                $data['jumlah_tersedia'] = 0;
                $data['jumlah_rusak'] = 0;
                $data['jumlah_maintenance'] = 0;
                $data['jumlah_hilang'] = 0;
            }

            // Validasi konsistensi pooled
            if ($newType === 'pooled') {
                $data['jumlah_tersedia'] = (int) ($data['jumlah_tersedia'] ?? $sarana->jumlah_tersedia);
                $data['jumlah_rusak'] = (int) ($data['jumlah_rusak'] ?? $sarana->jumlah_rusak);
                $data['jumlah_maintenance'] = (int) ($data['jumlah_maintenance'] ?? $sarana->jumlah_maintenance);
                $data['jumlah_hilang'] = (int) ($data['jumlah_hilang'] ?? $sarana->jumlah_hilang);
                $this->validatePooledBreakdown($data);
            }

            // Aturan saat perubahan tipe
            if ($oldType !== $newType) {
                if ($oldType === 'serialized' && $newType === 'pooled') {
                    $existingUnits = $sarana->units()->count();
                    if ($existingUnits > 0) {
                        throw new \InvalidArgumentException('Ubah ke pooled memerlukan penghapusan/arsip semua unit terlebih dahulu.');
                    }
                }
            }

            $sarana->update($data);
            $sarana->updateStats();

            return $sarana->load(['kategori', 'creator']);
        });
    }

    public function delete(Sarana $sarana): void
    {
        // Cegah hapus jika ada peminjaman aktif terkait sarana
        $isUsed = DB::table('peminjaman_items')
            ->join('peminjaman', 'peminjaman_items.peminjaman_id', '=', 'peminjaman.id')
            ->where('peminjaman_items.sarana_id', $sarana->id)
            ->whereIn('peminjaman.status', ['pending', 'approved', 'picked_up'])
            ->exists();

        if ($isUsed) {
            throw new \InvalidArgumentException('Sarana tidak dapat dihapus karena sedang digunakan dalam peminjaman.');
        }

        // Delete image
        if ($sarana->image_url) {
            Storage::disk('public')->delete($sarana->image_url);
        }

        $sarana->delete();
    }

    public function addUnit(Sarana $sarana, string $unitCode, string $unitStatus = 'tersedia'): SaranaUnit
    {
        if ($sarana->type !== 'serialized') {
            throw new \InvalidArgumentException('Hanya sarana bertipe serialized yang dapat dikelola unitnya.');
        }

        return DB::transaction(function () use ($sarana, $unitCode, $unitStatus) {
            // Check if unit_code already exists for this sarana
            $existingUnit = $sarana->units()
                ->where('unit_code', $unitCode)
                ->exists();

            if ($existingUnit) {
                throw new \InvalidArgumentException('Unit code sudah ada untuk sarana ini.');
            }

            // Check if adding this unit would exceed jumlah_total
            $currentUnits = $sarana->units()->count();
            if ($currentUnits >= $sarana->jumlah_total) {
                throw new \InvalidArgumentException('Tidak dapat menambah unit karena sudah mencapai batas maksimal.');
            }

            $unit = SaranaUnit::create([
                'sarana_id' => $sarana->id,
                'unit_code' => $unitCode,
                'unit_status' => $unitStatus,
            ]);

            // Update sarana stats
            $sarana->updateStats();

            return $unit;
        });
    }

    public function updateUnitStatus(SaranaUnit $unit, string $status): void
    {
        DB::transaction(function () use ($unit, $status) {
            $unit->updateStatus($status);
        });
    }

    public function updateUnit(SaranaUnit $unit, array $data): void
    {
        DB::transaction(function () use ($unit, $data) {
            // Check if unit_code is being changed and if it's unique
            if (isset($data['unit_code']) && $data['unit_code'] !== $unit->unit_code) {
                $existingUnit = SaranaUnit::where('sarana_id', $unit->sarana_id)
                    ->where('unit_code', $data['unit_code'])
                    ->where('id', '!=', $unit->id)
                    ->first();
                
                if ($existingUnit) {
                    throw new \InvalidArgumentException('Kode unit sudah digunakan untuk sarana ini.');
                }
            }

            // Update unit data
            $unit->update($data);
            
            // Update sarana stats if status changed
            if (isset($data['unit_status'])) {
                $unit->sarana->updateStats();
            }
        });
    }

    public function deleteUnit(SaranaUnit $unit): void
    {
        \Log::info('Service: Starting delete unit', ['unit_id' => $unit->id]);
        
        // Cegah hapus jika ada peminjaman aktif terkait unit
        \Log::info('Service: Checking if unit is used in peminjaman...');
        $isUsed = DB::table('peminjaman_item_units')
            ->join('peminjaman', 'peminjaman_item_units.peminjaman_id', '=', 'peminjaman.id')
            ->where('peminjaman_item_units.unit_id', $unit->id)
            ->whereIn('peminjaman.status', ['pending', 'approved', 'picked_up'])
            ->exists();

        \Log::info('Service: Unit usage check result', ['is_used' => $isUsed]);

        if ($isUsed) {
            \Log::info('Service: Unit is used, throwing exception');
            throw new \InvalidArgumentException('Unit tidak dapat dihapus karena sedang digunakan dalam peminjaman.');
        }

        \Log::info('Service: Starting database transaction...');
        DB::transaction(function () use ($unit) {
            \Log::info('Service: Inside transaction, getting sarana...');
            $sarana = $unit->sarana;
            \Log::info('Service: Deleting unit...');
            $unit->delete();
            \Log::info('Service: Updating sarana stats...');
            $sarana->updateStats();
            \Log::info('Service: Transaction completed successfully');
        });
    }

    public function addBulkUnits(Sarana $sarana, array $unitCodes, string $unitStatus = 'tersedia'): array
    {
        if ($sarana->type !== 'serialized') {
            throw new \InvalidArgumentException('Hanya sarana bertipe serialized yang dapat dikelola unitnya.');
        }

        return DB::transaction(function () use ($sarana, $unitCodes, $unitStatus) {
            $addedUnits = [];
            $currentUnits = $sarana->units()->count();
            $remainingSlots = $sarana->jumlah_total - $currentUnits;

            if (count($unitCodes) > $remainingSlots) {
                throw new \InvalidArgumentException("Tidak dapat menambah " . count($unitCodes) . " unit karena hanya tersisa {$remainingSlots} slot.");
            }

            // Check for duplicates within input
            if (count($unitCodes) !== count(array_unique($unitCodes))) {
                throw new \InvalidArgumentException('Unit codes tidak boleh duplikat dalam input yang sama.');
            }

            // Check for existing unit codes
            $existingCodes = $sarana->units()
                ->whereIn('unit_code', $unitCodes)
                ->pluck('unit_code')
                ->toArray();

            if (!empty($existingCodes)) {
                throw new \InvalidArgumentException('Unit codes sudah ada: ' . implode(', ', $existingCodes));
            }

            foreach ($unitCodes as $unitCode) {
                $unit = SaranaUnit::create([
                    'sarana_id' => $sarana->id,
                    'unit_code' => $unitCode,
                    'unit_status' => $unitStatus,
                ]);
                $addedUnits[] = $unit;
            }

            // Update sarana stats
            $sarana->updateStats();

            return $addedUnits;
        });
    }

    public function updateBulkUnitStatus(Sarana $sarana, array $unitIds, string $status): int
    {
        if ($sarana->type !== 'serialized') {
            throw new \InvalidArgumentException('Hanya sarana bertipe serialized yang dapat dikelola unitnya.');
        }

        return DB::transaction(function () use ($sarana, $unitIds, $status) {
            $units = $sarana->units()->whereIn('id', $unitIds)->get();
            
            if ($units->count() !== count($unitIds)) {
                throw new \InvalidArgumentException('Beberapa unit tidak ditemukan atau tidak milik sarana ini.');
            }

            $updatedCount = 0;
            foreach ($units as $unit) {
                $unit->updateStatus($status);
                $updatedCount++;
            }

            // Update sarana stats
            $sarana->updateStats();

            return $updatedCount;
        });
    }

    public function updatePooledStatus(Sarana $sarana, array $statusData): Sarana
    {
        if ($sarana->type !== 'pooled') {
            throw new \InvalidArgumentException('Hanya sarana bertipe pooled yang dapat diupdate statusnya.');
        }

        return DB::transaction(function () use ($sarana, $statusData) {
            $data = [
                'jumlah_tersedia' => (int) ($statusData['jumlah_tersedia'] ?? $sarana->jumlah_tersedia),
                'jumlah_rusak' => (int) ($statusData['jumlah_rusak'] ?? $sarana->jumlah_rusak),
                'jumlah_maintenance' => (int) ($statusData['jumlah_maintenance'] ?? $sarana->jumlah_maintenance),
                'jumlah_hilang' => (int) ($statusData['jumlah_hilang'] ?? $sarana->jumlah_hilang),
            ];

            $this->validatePooledBreakdown(array_merge($data, ['jumlah_total' => $sarana->jumlah_total]));

            $sarana->update($data);
            $sarana->updateStats();

            return $sarana->fresh();
        });
    }

    private function validatePooledBreakdown(array $data): void
    {
        foreach (['jumlah_tersedia','jumlah_rusak','jumlah_maintenance','jumlah_hilang'] as $field) {
            if ($data[$field] < 0) {
                throw new \InvalidArgumentException("Nilai {$field} tidak boleh negatif.");
            }
        }
        $breakdownSum = $data['jumlah_tersedia'] + $data['jumlah_rusak'] + $data['jumlah_maintenance'] + $data['jumlah_hilang'];
        if ($breakdownSum !== (int) $data['jumlah_total']) {
            throw new \InvalidArgumentException('Jumlah total harus sama dengan penjumlahan tersedia + rusak + maintenance + hilang.');
        }
    }

    /**
     * Validate pooled breakdown consistency
     */
    // private function validatePooledBreakdown(array $data): void
    // {
    //     $breakdownSum = $data['jumlah_tersedia'] + $data['jumlah_rusak'] + $data['jumlah_maintenance'] + $data['jumlah_hilang'];
    //     if ($breakdownSum !== (int) $data['jumlah_total']) {
    //         throw new \InvalidArgumentException('Jumlah total harus sama dengan penjumlahan tersedia + rusak + maintenance + hilang.');
    //     }
    // }
}
