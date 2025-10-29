<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanApprovalWorkflow extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_approval_workflow';

    protected $fillable = [
        'peminjaman_id',
        'approver_id',
        'approval_type',
        'sarana_id',
        'prasarana_id',
        'approval_level',
        'status',
        'notes',
        'approved_at',
        'rejected_at',
        'overridden_by',
        'overridden_at',
    ];

    protected $casts = [
        'approval_level' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'overridden_at' => 'datetime',
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
     * Relasi ke user yang menjadi approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Relasi ke user yang melakukan override
     */
    public function overriddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    /**
     * Relasi ke sarana (approval type sarana)
     */
    public function sarana(): BelongsTo
    {
        return $this->belongsTo(Sarana::class, 'sarana_id');
    }

    /**
     * Relasi ke prasarana (approval type prasarana)
     */
    public function prasarana(): BelongsTo
    {
        return $this->belongsTo(Prasarana::class, 'prasarana_id');
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk approval type tertentu
     */
    public function scopeByType($query, $type)
    {
        return $query->where('approval_type', $type);
    }

    /**
     * Scope untuk global approval
     */
    public function scopeGlobal($query)
    {
        return $query->where('approval_type', 'global');
    }

    /**
     * Scope untuk specific sarana approval
     */
    public function scopeSpecificSarana($query)
    {
        return $query->where('approval_type', 'sarana');
    }

    /**
     * Scope untuk specific prasarana approval
     */
    public function scopeSpecificPrasarana($query)
    {
        return $query->where('approval_type', 'prasarana');
    }

    /**
     * Scope untuk pending approval
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope untuk rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope untuk approver tertentu
     */
    public function scopeForApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    /**
     * Scope untuk sarana tertentu
     */
    public function scopeForSarana($query, $saranaId)
    {
        return $query->where('sarana_id', $saranaId);
    }

    /**
     * Scope untuk prasarana tertentu
     */
    public function scopeForPrasarana($query, $prasaranaId)
    {
        return $query->where('prasarana_id', $prasaranaId);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * Get approval type label
     */
    public function getApprovalTypeLabelAttribute(): string
    {
        return match($this->approval_type) {
            'global' => 'Global Approval',
            'sarana' => 'Sarana Approval',
            'prasarana' => 'Prasarana Approval',
            default => ucfirst($this->approval_type),
        };
    }

    /**
     * Check if workflow is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if workflow is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if workflow is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if workflow decision has been overridden
     */
    public function isOverridden(): bool
    {
        return !is_null($this->overridden_at);
    }

    /**
     * Check if workflow is global approval
     */
    public function isGlobal(): bool
    {
        return $this->approval_type === 'global';
    }

    /**
     * Check if workflow is specific sarana approval
     */
    public function isSpecificSarana(): bool
    {
        return $this->approval_type === 'sarana';
    }

    /**
     * Check if workflow is specific prasarana approval
     */
    public function isSpecificPrasarana(): bool
    {
        return $this->approval_type === 'prasarana';
    }

    /**
     * Approve workflow
     */
    public function approve($notes = null): bool
    {
        $this->status = 'approved';
        $this->notes = $notes;
        $this->approved_at = now();
        $this->rejected_at = null;
        
        return $this->save();
    }

    /**
     * Reject workflow
     */
    public function reject($notes = null): bool
    {
        $this->status = 'rejected';
        $this->notes = $notes;
        $this->rejected_at = now();
        $this->approved_at = null;
        
        return $this->save();
    }

    /**
     * Reset workflow to pending
     */
    public function reset(): bool
    {
        $this->status = 'pending';
        $this->notes = null;
        $this->approved_at = null;
        $this->rejected_at = null;
        
        return $this->save();
    }

}