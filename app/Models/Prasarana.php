<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prasarana extends Model
{
    use HasFactory;

    protected $table = 'prasarana';

    protected $fillable = [
        'name',
        'kategori_id',
        'description',
        'lokasi',
        'kapasitas',
        'status',
        'created_by',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the kategori that owns the prasarana.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPrasarana::class, 'kategori_id');
    }

    /**
     * Get the user who created the prasarana.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the images for the prasarana.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PrasaranaImage::class)->orderBy('sort_order');
    }

    /**
     * Get the peminjaman for the prasarana.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }

    /**
     * Relasi ke prasarana approvers
     */
    public function approvers(): HasMany
    {
        return $this->hasMany(PrasaranaApprover::class, 'prasarana_id');
    }

    /**
     * Relasi ke approval workflows
     */
    public function approvalWorkflows(): HasMany
    {
        return $this->hasMany(PeminjamanApprovalWorkflow::class, 'prasarana_id');
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'tersedia' => 'badge-status_active',
            'rusak' => 'badge-status_blocked',
            'maintenance' => 'badge-status_inactive',
            default => 'badge-status_active',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'tersedia' => 'Tersedia',
            'rusak' => 'Rusak',
            'maintenance' => 'Maintenance',
            default => 'Tersedia',
        };
    }

    /**
     * Get the status icon.
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'tersedia' => 'fas fa-check-circle',
            'rusak' => 'fas fa-times-circle',
            'maintenance' => 'fas fa-tools',
            default => 'fas fa-check-circle',
        };
    }

    /**
     * Check if prasarana is available for peminjaman.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'tersedia';
    }

    /**
     * Check if prasarana is under maintenance.
     */
    public function isMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    /**
     * Check if prasarana is damaged.
     */
    public function isDamaged(): bool
    {
        return $this->status === 'rusak';
    }

    /**
     * Get the main image (first image).
     */
    public function getMainImageAttribute()
    {
        return $this->images()->first();
    }

    /**
     * Scope for available prasarana.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'tersedia');
    }

    /**
     * Scope for maintenance prasarana.
     */
    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Scope for damaged prasarana.
     */
    public function scopeDamaged($query)
    {
        return $query->where('status', 'rusak');
    }

    /**
     * Scope for prasarana by category.
     */
    public function scopeByCategory($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    /**
     * Scope for prasarana by location.
     */
    public function scopeByLocation($query, $lokasi)
    {
        return $query->where('lokasi', 'like', "%{$lokasi}%");
    }

    /**
     * Scope for prasarana search.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('lokasi', 'like', "%{$search}%");
        });
    }
}