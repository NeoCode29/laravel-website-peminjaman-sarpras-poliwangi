<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ukm extends Model
{
    use HasFactory;

    protected $table = 'ukm';

    protected $fillable = [
        'nama',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the markings for this UKM
     */
    public function markings(): HasMany
    {
        return $this->hasMany(Marking::class);
    }

    /**
     * Check if UKM is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope for active UKM
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive UKM
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the active markings count
     */
    public function getActiveMarkingsCount(): int
    {
        return $this->markings()
            ->where('status', Marking::STATUS_ACTIVE)
            ->where('expires_at', '>', now())
            ->count();
    }

    /**
     * Get the total markings count
     */
    public function getTotalMarkingsCount(): int
    {
        return $this->markings()->count();
    }
}
