<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaranaApprover extends Model
{
    use HasFactory;

    protected $table = 'sarana_approvers';

    protected $fillable = [
        'sarana_id',
        'approver_id',
        'approval_level',
        'is_active',
    ];

    protected $casts = [
        'approval_level' => 'integer',
        'is_active' => 'boolean',
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
     * Relasi ke user yang menjadi approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope untuk approver aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk sarana tertentu
     */
    public function scopeForSarana($query, $saranaId)
    {
        return $query->where('sarana_id', $saranaId);
    }

    /**
     * Scope untuk level tertentu
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('approval_level', $level);
    }

    /**
     * Scope untuk level primary (1)
     */
    public function scopePrimary($query)
    {
        return $query->where('approval_level', 1);
    }

    /**
     * Scope untuk level secondary (2)
     */
    public function scopeSecondary($query)
    {
        return $query->where('approval_level', 2);
    }

    /**
     * Scope untuk level tertiary (3)
     */
    public function scopeTertiary($query)
    {
        return $query->where('approval_level', 3);
    }

    /**
     * Get level label
     */
    public function getLevelLabelAttribute(): string
    {
        return match($this->approval_level) {
            1 => 'Primary',
            2 => 'Secondary',
            3 => 'Tertiary',
            default => "Level {$this->approval_level}",
        };
    }

    /**
     * Get level badge class
     */
    public function getLevelBadgeClassAttribute(): string
    {
        return match($this->approval_level) {
            1 => 'badge-primary',
            2 => 'badge-secondary',
            3 => 'badge-info',
            default => 'badge-light',
        };
    }

    /**
     * Check if this approver can override another approver
     */
    public function canOverride(SaranaApprover $otherApprover): bool
    {
        return $this->approval_level < $otherApprover->approval_level;
    }

    /**
     * Get all approvers for this sarana with higher level
     */
    public function getHigherLevelApprovers()
    {
        return static::where('sarana_id', $this->sarana_id)
            ->where('approval_level', '<', $this->approval_level)
            ->active()
            ->get();
    }

    /**
     * Get all approvers for this sarana with lower level
     */
    public function getLowerLevelApprovers()
    {
        return static::where('sarana_id', $this->sarana_id)
            ->where('approval_level', '>', $this->approval_level)
            ->active()
            ->get();
    }

    /**
     * Get all approvers for this sarana
     */
    public function getAllApproversForSarana()
    {
        return static::where('sarana_id', $this->sarana_id)
            ->active()
            ->orderBy('approval_level')
            ->get();
    }
}