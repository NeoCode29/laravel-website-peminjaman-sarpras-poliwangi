<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\UserQuota;

class UserQuotaService
{
    /**
     * Increment current borrowings when peminjaman enters active set (pending/approved/picked_up).
     */
    public function incrementIfActive(Peminjaman $peminjaman): void
    {
        if (!in_array($peminjaman->status, [Peminjaman::STATUS_PENDING, Peminjaman::STATUS_APPROVED, Peminjaman::STATUS_PICKED_UP])) {
            return;
        }
        $quota = UserQuota::getOrCreateForUser($peminjaman->user_id);
        $quota->increment('current_borrowings');
    }

    /**
     * Decrement current borrowings when peminjaman leaves active set (returned/cancelled/rejected).
     */
    public function decrementIfInactive(Peminjaman $peminjaman): void
    {
        if (!in_array($peminjaman->status, [Peminjaman::STATUS_RETURNED, Peminjaman::STATUS_CANCELLED, Peminjaman::STATUS_REJECTED])) {
            return;
        }
        $quota = UserQuota::getOrCreateForUser($peminjaman->user_id);
        if ($quota->current_borrowings > 0) {
            $quota->decrement('current_borrowings');
        }
    }
}



