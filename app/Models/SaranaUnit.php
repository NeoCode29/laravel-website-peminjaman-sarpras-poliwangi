<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaranaUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'sarana_id',
        'unit_code',
        'unit_status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke sarana
     */
    public function sarana(): BelongsTo
    {
        return $this->belongsTo(Sarana::class, 'sarana_id');
    }

    /**
     * Scope untuk unit tersedia
     */
    public function scopeTersedia($query)
    {
        return $query->where('unit_status', 'tersedia');
    }

    /**
     * Scope untuk unit rusak
     */
    public function scopeRusak($query)
    {
        return $query->where('unit_status', 'rusak');
    }

    /**
     * Scope untuk unit maintenance
     */
    public function scopeMaintenance($query)
    {
        return $query->where('unit_status', 'maintenance');
    }

    /**
     * Scope untuk unit hilang
     */
    public function scopeHilang($query)
    {
        return $query->where('unit_status', 'hilang');
    }

    /**
     * Update status unit dan recalculate sarana stats
     */
    public function updateStatus($status)
    {
        try {
            $this->unit_status = $status;
            $this->save();
            
            // Update statistik sarana
            $this->sarana->updateStats();
        } catch (\Exception $e) {
            \Log::error('Error updating unit status', [
                'unit_id' => $this->id,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Relasi ke peminjaman item units
     */
    public function peminjamanItemUnits()
    {
        return $this->hasMany(PeminjamanItemUnit::class, 'unit_id');
    }

    /**
     * Check if unit is currently borrowed
     */
    public function isCurrentlyBorrowed(): bool
    {
        try {
            // Use direct query to avoid complex relationships
            return \DB::table('peminjaman_item_units')
                ->join('peminjaman', 'peminjaman_item_units.peminjaman_id', '=', 'peminjaman.id')
                ->where('peminjaman_item_units.unit_id', $this->id)
                ->whereIn('peminjaman.status', ['approved', 'picked_up'])
                ->exists();
        } catch (\Exception $e) {
            \Log::error('Error checking if unit is borrowed: ' . $e->getMessage());
            return false; // Return false if check fails
        }
    }
}
