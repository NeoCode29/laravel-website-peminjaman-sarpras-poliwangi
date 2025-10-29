<?php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    /**
     * Determine whether the user can view any peminjaman.
     */
    public function viewAny(User $user): bool
    {
        // Admin (role/permission) bisa melihat semua, selainnya tetap boleh akses index (akan difilter di controller)
        return $user->hasPermission('peminjaman.view') || $user->getRoleName() === 'admin' || $user->hasPermission('peminjaman.create');
    }

    /**
     * Determine whether the user can view the peminjaman.
     */
    public function view(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->hasPermission('peminjaman.view') || $user->getRoleName() === 'admin') {
            return true;
        }
        return $peminjaman->user_id === $user->id;
    }

    /**
     * Determine whether the user can create peminjaman.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('peminjaman.create') || $user->getRoleName() === 'admin';
    }

    /**
     * Determine whether the user can update the peminjaman.
     */
    public function update(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->getRoleName() === 'admin' || $user->hasPermission('peminjaman.approve')) {
            return true;
        }

        return $peminjaman->user_id === $user->id && ($peminjaman->isPending() || $peminjaman->isApproved());
    }

    /**
     * Determine whether the user can delete the peminjaman.
     */
    public function delete(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->getRoleName() === 'admin' || $user->hasPermission('peminjaman.approve')) {
            return true;
        }

        return $peminjaman->user_id === $user->id && ($peminjaman->isPending() || $peminjaman->isApproved());
    }

    /**
     * Determine whether the user can cancel the peminjaman.
     */
    public function cancel(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->getRoleName() === 'admin' || $user->hasPermission('peminjaman.approve')) {
            return $peminjaman->isPending() || $peminjaman->isApproved();
        }

        return $peminjaman->user_id === $user->id && $peminjaman->isPending();
    }

    /**
     * Determine whether the user can approve the peminjaman.
     */
    public function approve(User $user, Peminjaman $peminjaman): bool
    {
        return $user->hasPermission('peminjaman.approve')
            || $user->hasPermission('peminjaman.approve_specific')
            || $user->getRoleName() === 'admin';
    }

    /**
     * Determine whether the user can reject the peminjaman.
     */
    public function reject(User $user, Peminjaman $peminjaman): bool
    {
        return $user->hasPermission('peminjaman.reject')
            || $user->hasPermission('peminjaman.reject_specific')
            || $user->hasPermission('peminjaman.approve_specific')
            || $user->getRoleName() === 'admin';
    }

    /**
     * Determine whether the user can override another approver's decision.
     */
    public function override(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->getRoleName() === 'admin') {
            return true;
        }

        if ($user->hasPermission('peminjaman.override')) {
            return true;
        }

        if (! $user->hasPermission('peminjaman.approve') && ! $user->hasPermission('peminjaman.approve_specific')) {
            return false;
        }

        return $this->userCanOverrideWorkflow($user->id, $peminjaman);
    }

    protected function userCanOverrideWorkflow(int $userId, Peminjaman $peminjaman): bool
    {
        $workflows = $peminjaman->approvalWorkflow()->whereNotNull('approver_id')->get(['approver_id', 'approval_level', 'approval_type', 'sarana_id', 'prasarana_id']);
        $userAssignments = $workflows->where('approver_id', $userId);

        if ($userAssignments->isEmpty()) {
            return false;
        }

        foreach ($userAssignments as $assignment) {
            $canOverride = $workflows->contains(function ($other) use ($assignment) {
                if ($other->approver_id === $assignment->approver_id) {
                    return false;
                }

                if ($other->approval_type !== $assignment->approval_type) {
                    return false;
                }

                if ($assignment->approval_type === 'sarana' && $other->sarana_id !== $assignment->sarana_id) {
                    return false;
                }

                if ($assignment->approval_type === 'prasarana' && $other->prasarana_id !== $assignment->prasarana_id) {
                    return false;
                }

                return $assignment->approval_level < $other->approval_level;
            });

            if ($canOverride) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can validate pickup.
     */
    public function validate_pickup(User $user, Peminjaman $peminjaman): bool
    {
        return $user->hasPermission('peminjaman.validate_pickup') || $user->getRoleName() === 'admin';
    }

    /**
     * Determine whether the user can validate return.
     */
    public function validate_return(User $user, Peminjaman $peminjaman): bool
    {
        return $user->hasPermission('peminjaman.validate_return') || $user->getRoleName() === 'admin';
    }
}


