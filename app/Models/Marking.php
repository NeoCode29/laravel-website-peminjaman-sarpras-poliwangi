<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Marking extends Model
{
    use HasFactory;

    protected $table = 'marking';

    protected $fillable = [
        'user_id',
        'ukm_id',
        'prasarana_id',
        'lokasi_custom',
        'start_datetime',
        'end_datetime',
        'jumlah_peserta',
        'expires_at',
        'planned_submit_by',
        'status',
        'event_name',
        'notes',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'expires_at' => 'datetime',
        'planned_submit_by' => 'datetime',
        'jumlah_peserta' => 'integer',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CONVERTED = 'converted';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user who made the marking
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the UKM for this marking
     */
    public function ukm(): BelongsTo
    {
        return $this->belongsTo(Ukm::class);
    }

    /**
     * Get the prasarana for this marking
     */
    public function prasarana(): BelongsTo
    {
        return $this->belongsTo(Prasarana::class);
    }

    /**
     * Get the marking items
     */
    public function items(): HasMany
    {
        return $this->hasMany(MarkingItem::class);
    }

    /**
     * Check if marking is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if marking is expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->status === self::STATUS_ACTIVE && $this->expires_at->isPast());
    }

    /**
     * Check if marking is converted
     */
    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    /**
     * Check if marking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if marking can be converted to peminjaman
     */
    public function canBeConverted(): bool
    {
        return $this->isActive() && !$this->isExpired();
    }

    /**
     * Get the duration in hours
     */
    public function getDurationInHours(): int
    {
        return $this->start_datetime->diffInHours($this->end_datetime);
    }

    /**
     * Get the duration in days
     */
    public function getDurationInDays(): int
    {
        return $this->start_datetime->diffInDays($this->end_datetime) + 1;
    }

    /**
     * Get the time until expiration
     */
    public function getTimeUntilExpiration(): ?Carbon
    {
        if ($this->isExpired()) {
            return null;
        }

        return $this->expires_at;
    }

    /**
     * Get the time until expiration in hours
     */
    public function getHoursUntilExpiration(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInHours($this->expires_at, false);
    }

    /**
     * Get the location (prasarana name or custom location)
     */
    public function getLocation(): string
    {
        if ($this->prasarana) {
            return $this->prasarana->name;
        }

        return $this->lokasi_custom ?? 'Lokasi tidak ditentukan';
    }

    /**
     * Scope for active markings
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope for expired markings
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_EXPIRED)
              ->orWhere(function ($q2) {
                  $q2->where('status', self::STATUS_ACTIVE)
                     ->where('expires_at', '<=', now());
              });
        });
    }

    /**
     * Scope for converted markings
     */
    public function scopeConverted($query)
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }

    /**
     * Scope for cancelled markings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for markings expiring soon (within 24 hours)
     */
    public function scopeExpiringSoon($query, $hours = 24)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('expires_at', '<=', now()->addHours($hours))
                    ->where('expires_at', '>', now());
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-expire markings when they pass their expiration time
        static::saving(function ($marking) {
            if ($marking->status === self::STATUS_ACTIVE && 
                $marking->expires_at && 
                $marking->expires_at->isPast()) {
                $marking->status = self::STATUS_EXPIRED;
            }
        });
    }
}
