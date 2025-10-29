<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sarana extends Model
{
    use HasFactory;

    protected $table = 'sarana';

    protected $fillable = [
        'name',
        'kategori_id',
        'type',
        'jumlah_total',
        'description',
        'image_url',
        'lokasi',
        'jumlah_tersedia',
        'jumlah_rusak',
        'jumlah_maintenance',
        'jumlah_hilang',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke kategori sarana
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSarana::class, 'kategori_id');
    }

    /**
     * Relasi ke user yang membuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke unit sarana (untuk tipe serialized)
     */
    public function units(): HasMany
    {
        return $this->hasMany(SaranaUnit::class, 'sarana_id');
    }

    /**
     * Relasi ke peminjaman items
     */
    public function peminjamanItems(): HasMany
    {
        return $this->hasMany(PeminjamanItem::class, 'sarana_id');
    }

    /**
     * Relasi ke sarana approvers
     */
    public function approvers(): HasMany
    {
        return $this->hasMany(SaranaApprover::class, 'sarana_id');
    }

    /**
     * Relasi ke approval workflows
     */
    public function approvalWorkflows(): HasMany
    {
        return $this->hasMany(PeminjamanApprovalWorkflow::class, 'sarana_id');
    }

    /**
     * Scope untuk sarana serialized
     */
    public function scopeSerialized($query)
    {
        return $query->where('type', 'serialized');
    }

    /**
     * Scope untuk sarana pooled
     */
    public function scopePooled($query)
    {
        return $query->where('type', 'pooled');
    }

    /**
     * Hitung statistik untuk sarana serialized
     */
    public function calculateSerializedStats()
    {
        if ($this->type !== 'serialized') {
            return;
        }

        try {
            // jumlah_total tidak boleh diubah, hanya hitung breakdown
            $this->jumlah_tersedia = $this->units()->where('unit_status', 'tersedia')->count();
            $this->jumlah_rusak = $this->units()->where('unit_status', 'rusak')->count();
            $this->jumlah_maintenance = $this->units()->where('unit_status', 'maintenance')->count();
            $this->jumlah_hilang = $this->units()->where('unit_status', 'hilang')->count();
            $this->save();
        } catch (\Exception $e) {
            \Log::error('Error calculating serialized stats', [
                'sarana_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Hitung ketersediaan untuk sarana pooled
     */
    public function calculatePooledAvailability()
    {
        if ($this->type !== 'pooled') {
            return;
        }

        try {
            // Hitung qty yang sedang dipinjam aktif (termasuk pending sesuai PRD)
            $qtyDipinjamAktif = \DB::table('peminjaman_items')
                ->join('peminjaman', 'peminjaman_items.peminjaman_id', '=', 'peminjaman.id')
                ->where('peminjaman_items.sarana_id', $this->id)
                ->whereIn('peminjaman.status', ['pending', 'approved', 'picked_up'])
                ->sum('peminjaman_items.qty_approved');

            $this->jumlah_tersedia = max(0, (int) $this->jumlah_total - 
                ((int) $this->jumlah_rusak + (int) $this->jumlah_maintenance + (int) $this->jumlah_hilang) - 
                (int) $qtyDipinjamAktif);
            $this->save();
        } catch (\Exception $e) {
            \Log::error('Error calculating pooled availability', [
                'sarana_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update statistik otomatis
     */
    public function updateStats()
    {
        try {
            if ($this->type === 'serialized') {
                $this->calculateSerializedStats();
            } else {
                $this->calculatePooledAvailability();
            }
        } catch (\Exception $e) {
            \Log::error('Error updating sarana stats', [
                'sarana_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Accessor untuk sisa unit yang belum ditambahkan (serialized)
     */
    public function getRemainingUnitsAttribute(): int
    {
        if ($this->type !== 'serialized') {
            return 0;
        }
        
        $currentUnits = $this->units()->count();
        return max(0, $this->jumlah_total - $currentUnits);
    }
}
