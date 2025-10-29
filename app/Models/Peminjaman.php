<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'user_id',
        'prasarana_id',
        'lokasi_custom',
        'jumlah_peserta',
        'ukm_id',
        'event_name',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'status',
        'konflik',
        'surat_path',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'pickup_validated_by',
        'pickup_validated_at',
        'return_validated_by',
        'return_validated_at',
        'cancelled_by',
        'cancelled_reason',
        'cancelled_at',
        'foto_pickup_path',
        'foto_return_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'pickup_validated_at' => 'datetime',
        'return_validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user who made the peminjaman
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prasarana for this peminjaman
     */
    public function prasarana(): BelongsTo
    {
        return $this->belongsTo(Prasarana::class);
    }

    /**
     * Get the UKM for this peminjaman (if mahasiswa)
     */
    public function ukm(): BelongsTo
    {
        return $this->belongsTo(Ukm::class, 'ukm_id');
    }

    /**
     * Get the user who approved this peminjaman
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who validated pickup
     */
    public function pickupValidatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pickup_validated_by');
    }

    /**
     * Get the user who validated return
     */
    public function returnValidatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_validated_by');
    }

    /**
     * Get the user who cancelled this peminjaman
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get the peminjaman items
     */
    public function items(): HasMany
    {
        return $this->hasMany(PeminjamanItem::class);
    }

    /**
     * Get the assigned units through items
     */
    public function itemUnits()
    {
        return $this->hasManyThrough(PeminjamanItemUnit::class, PeminjamanItem::class);
    }

    /**
     * Get the approval workflow
     */
    public function approvalWorkflow(): HasMany
    {
        return $this->hasMany(PeminjamanApprovalWorkflow::class);
    }

    /**
     * Determine if any approval workflow step has been overridden.
     */
    public function getHasOverrideAttribute(): bool
    {
        if ($this->relationLoaded('approvalWorkflow')) {
            return $this->approvalWorkflow->contains(fn ($workflow) => $workflow->isOverridden());
        }

        return $this->approvalWorkflow()->whereNotNull('overridden_at')->exists();
    }

    /**
     * Get the approval status
     */
    public function approvalStatus(): HasOne
    {
        return $this->hasOne(PeminjamanApprovalStatus::class);
    }

    /**
     * Check if peminjaman is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if peminjaman is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if peminjaman is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if peminjaman is picked up
     */
    public function isPickedUp(): bool
    {
        return $this->status === self::STATUS_PICKED_UP;
    }

    /**
     * Check if peminjaman is returned
     */
    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    /**
     * Check if peminjaman is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get the duration in days
     */
    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Get the duration in hours
     */
    public function getDurationInHours(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = $this->start_date->copy()->setTimeFromTimeString($this->start_time);
        $end = $this->end_date->copy()->setTimeFromTimeString($this->end_time);
        
        return $start->diffInHours($end);
    }

    /**
     * Scope for active peminjaman (not cancelled or returned)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_PICKED_UP
        ]);
    }

    /**
     * Scope for pending approval
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved peminjaman
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected peminjaman
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PICKED_UP => 'Picked Up',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_PICKED_UP => 'badge-info',
            self::STATUS_RETURNED => 'badge-primary',
            self::STATUS_CANCELLED => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Determine if peminjaman is currently marked as conflict group.
     */
    public function getIsKonflikAttribute(): bool
    {
        return !empty($this->konflik);
    }

    /**
     * Display status badge accessor
     */
    public function getDisplayStatusBadgeAttribute(): array
    {
        $status = $this->status ?? self::STATUS_PENDING;
        $label = ucfirst(str_replace('_', ' ', $status));
        $class = 'status-' . $status;

        $approvalStatus = $this->relationLoaded('approvalStatus')
            ? $this->getRelation('approvalStatus')
            : $this->approvalStatus()->first();

        $globalStatus = optional($approvalStatus)->global_approval_status;
        $overallStatus = optional($approvalStatus)->overall_status;

        if ($globalStatus === 'approved' && $overallStatus === 'pending') {
            return [
                'label' => 'Disetujui Global',
                'class' => 'status-approved',
            ];
        }

        if ($globalStatus === 'rejected' && $overallStatus === 'pending') {
            return [
                'label' => 'Ditolak Global',
                'class' => 'status-rejected',
            ];
        }

        return [
            'label' => $label,
            'class' => $class,
        ];
    }

}