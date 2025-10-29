<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanApprovalStatus extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_approval_status';

    protected $fillable = [
        'peminjaman_id',
        'overall_status',
        'global_approval_status',
        'global_approved_by',
        'global_approved_at',
        'global_rejected_by',
        'global_rejected_at',
        'global_rejection_reason',
    ];

    protected $casts = [
        'global_approved_at' => 'datetime',
        'global_rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke peminjaman
     */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    /**
     * Relasi ke user yang approve global
     */
    public function globalApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'global_approved_by');
    }

    /**
     * Relasi ke user yang reject global
     */
    public function globalRejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'global_rejected_by');
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeByOverallStatus($query, $status)
    {
        return $query->where('overall_status', $status);
    }

    /**
     * Scope untuk global status tertentu
     */
    public function scopeByGlobalStatus($query, $status)
    {
        return $query->where('global_approval_status', $status);
    }

    /**
     * Scope untuk pending
     */
    public function scopePending($query)
    {
        return $query->where('overall_status', 'pending');
    }

    /**
     * Scope untuk partially approved
     */
    public function scopePartiallyApproved($query)
    {
        return $query->where('overall_status', 'partially_approved');
    }

    /**
     * Scope untuk approved
     */
    public function scopeApproved($query)
    {
        return $query->where('overall_status', 'approved');
    }

    /**
     * Scope untuk rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('overall_status', 'rejected');
    }

    /**
     * Get overall status label
     */
    public function getOverallStatusLabelAttribute(): string
    {
        return match($this->overall_status) {
            'pending' => 'Pending',
            'partially_approved' => 'Partially Approved',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->overall_status),
        };
    }

    /**
     * Get overall status badge class
     */
    public function getOverallStatusBadgeClassAttribute(): string
    {
        return match($this->overall_status) {
            'pending' => 'badge-warning',
            'partially_approved' => 'badge-info',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * Get global status label
     */
    public function getGlobalStatusLabelAttribute(): string
    {
        return match($this->global_approval_status) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->global_approval_status),
        };
    }

    /**
     * Get global status badge class
     */
    public function getGlobalStatusBadgeClassAttribute(): string
    {
        return match($this->global_approval_status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * Check if overall status is pending
     */
    public function isPending(): bool
    {
        return $this->overall_status === 'pending';
    }

    /**
     * Check if overall status is partially approved
     */
    public function isPartiallyApproved(): bool
    {
        return $this->overall_status === 'partially_approved';
    }

    /**
     * Check if overall status is approved
     */
    public function isApproved(): bool
    {
        return $this->overall_status === 'approved';
    }

    /**
     * Check if overall status is rejected
     */
    public function isRejected(): bool
    {
        return $this->overall_status === 'rejected';
    }

    /**
     * Check if global status is pending
     */
    public function isGlobalPending(): bool
    {
        return $this->global_approval_status === 'pending';
    }

    /**
     * Check if global status is approved
     */
    public function isGlobalApproved(): bool
    {
        return $this->global_approval_status === 'approved';
    }

    /**
     * Check if global status is rejected
     */
    public function isGlobalRejected(): bool
    {
        return $this->global_approval_status === 'rejected';
    }

    /**
     * Update overall status based on workflow status
     */
    public function updateOverallStatus(): bool
    {
        $peminjamanId = $this->peminjaman_id;
        
        // Get all workflow for this peminjaman
        $workflows = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)->get();
        
        if ($workflows->isEmpty()) {
            $this->overall_status = 'pending';
            return $this->save();
        }

        // Check global approval status
        $globalWorkflows = $workflows->where('approval_type', 'global');
        if ($globalWorkflows->isNotEmpty()) {
            $globalRejected = $globalWorkflows->where('status', 'rejected')->isNotEmpty();
            $globalApproved = $globalWorkflows->where('status', 'approved')->isNotEmpty();

            if ($globalRejected) {
                $this->global_approval_status = 'rejected';
                $this->overall_status = 'rejected';
                return $this->save();
            } elseif ($globalApproved) {
                $this->global_approval_status = 'approved';
            } else {
                $this->global_approval_status = 'pending';
            }
        }

        // Check specific approvals
        $specificWorkflows = $workflows->whereIn('approval_type', ['sarana', 'prasarana']);
        
        if ($specificWorkflows->isEmpty()) {
            // No specific approvals needed, use global status
            $this->overall_status = $this->global_approval_status === 'approved' ? 'approved' : 'pending';
            return $this->save();
        }

        $approvedCount = $specificWorkflows->where('status', 'approved')->count();
        $rejectedCount = $specificWorkflows->where('status', 'rejected')->count();
        $totalCount = $specificWorkflows->count();

        if ($rejectedCount > 0 && $approvedCount > 0) {
            $this->overall_status = 'partially_approved';
        } elseif ($approvedCount === $totalCount) {
            $this->overall_status = 'approved';
        } elseif ($rejectedCount === $totalCount) {
            $this->overall_status = 'rejected';
        } else {
            $this->overall_status = 'pending';
        }

        if ($globalWorkflows->isNotEmpty() && $this->global_approval_status !== 'approved' && $this->overall_status === 'approved') {
            $this->overall_status = 'pending';
        }

        return $this->save();
    }

    /**
     * Set global approval
     */
    public function setGlobalApproval($userId, $reason = null): bool
    {
        $this->global_approval_status = 'approved';
        $this->global_approved_by = $userId;
        $this->global_approved_at = now();
        $this->global_rejected_by = null;
        $this->global_rejected_at = null;
        $this->global_rejection_reason = null;
        
        return $this->save();
    }

    /**
     * Set global rejection
     */
    public function setGlobalRejection($userId, $reason = null): bool
    {
        $this->global_approval_status = 'rejected';
        $this->global_rejected_by = $userId;
        $this->global_rejected_at = now();
        $this->global_rejection_reason = $reason;
        $this->global_approved_by = null;
        $this->global_approved_at = null;
        
        return $this->save();
    }
}