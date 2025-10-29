<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Peminjaman;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Buat notifikasi sederhana sesuai PRD dengan deep linking.
     */
    public function create(int $userId, string $title, string $message, string $type, ?string $actionUrl = null, ?\DateTimeInterface $expiresAt = null, bool $isClickable = true): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'is_clickable' => $isClickable,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Helper lama untuk kompatibilitas.
     */
    public function sendToUser(int $userId, string $title, string $message, string $type, ?string $actionUrl = null, bool $clickable = true): Notification
    {
        return $this->create($userId, $title, $message, $type, $actionUrl, null, $clickable);
    }

    /**
     * Notifikasi peminjaman disetujui.
     */
    public function notifyApproval(Peminjaman $peminjaman): void
    {
        $this->create(
            $peminjaman->user_id,
            'Peminjaman Disetujui',
            "Mari hadir dan siapkan diri untuk acara {$peminjaman->event_name}.",
            'peminjaman_approved',
            "/peminjaman/{$peminjaman->id}"
        );
    }

    /**
     * Notifikasi peminjaman ditolak.
     */
    public function notifyRejection(Peminjaman $peminjaman, ?string $reason = null): void
    {
        $message = "Pengajuan sarpras acara '{$peminjaman->event_name}' ditolak" . ($reason ? ": {$reason}" : '');
        $this->create(
            $peminjaman->user_id,
            'Peminjaman Ditolak',
            $message,
            'peminjaman_rejected',
            "/peminjaman/{$peminjaman->id}"
        );
    }

    /**
     * List notifikasi user dengan filter dasar: unread, type, expired.
     */
    public function listForUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Notification::where('user_id', $userId);

        if (($filters['unread'] ?? false) === true) {
            $query->unread();
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (($filters['hide_expired'] ?? true) === true) {
            $query->notExpired();
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Tandai satu notifikasi sebagai read dan kembalikan modelnya.
     */
    public function markAsRead(Notification $notification): Notification
    {
        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }
        return $notification->refresh();
    }

    /**
     * Tandai semua notifikasi user sebagai read.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Hapus notifikasi yang sudah expired (opsional housekeeping).
     */
    public function purgeExpired(): int
    {
        try {
            return Notification::whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->delete();
        } catch (\Throwable $e) {
            Log::error('Purge expired notifications failed: '.$e->getMessage());
            return 0;
        }
    }

    /**
     * Notifikasi approval workflow diperlukan
     */
    public function notifyApprovalRequired(Peminjaman $peminjaman, int $approverId, string $approvalType, ?string $sarprasName = null): void
    {
        $title = 'Approval Diperlukan';
        $message = "Approval diperlukan untuk pengajuan '{$peminjaman->event_name}'";
        
        if ($sarprasName) {
            $message .= " - {$sarprasName}";
        }
        
        $this->create(
            $approverId,
            $title,
            $message,
            'approval_required',
            "/approvals/pending"
        );
    }

    /**
     * Notifikasi approval workflow disetujui
     */
    public function notifyApprovalWorkflowApproved(Peminjaman $peminjaman, int $approverId, string $approvalType, ?string $sarprasName = null): void
    {
        $title = 'Approval Workflow Disetujui';
        $message = "Approval workflow untuk pengajuan '{$peminjaman->event_name}' telah disetujui";
        
        if ($sarprasName) {
            $message .= " - {$sarprasName}";
        }
        
        $this->create(
            $approverId,
            $title,
            $message,
            'approval_workflow_approved',
            "/approvals/workflow/{$peminjaman->id}"
        );
    }

    /**
     * Notifikasi approval workflow ditolak
     */
    public function notifyApprovalWorkflowRejected(Peminjaman $peminjaman, int $approverId, string $approvalType, ?string $sarprasName = null, ?string $reason = null): void
    {
        $title = 'Approval Workflow Ditolak';
        $message = "Approval workflow untuk pengajuan '{$peminjaman->event_name}' ditolak";
        
        if ($sarprasName) {
            $message .= " - {$sarprasName}";
        }
        
        if ($reason) {
            $message .= ": {$reason}";
        }
        
        $this->create(
            $approverId,
            $title,
            $message,
            'approval_workflow_rejected',
            "/approvals/workflow/{$peminjaman->id}"
        );
    }

    /**
     * Notifikasi approval override
     */
    public function notifyApprovalOverride(Peminjaman $peminjaman, int $approverId, string $action, ?string $sarprasName = null, ?string $reason = null): void
    {
        $title = 'Approval Override';
        $message = "Approval untuk pengajuan '{$peminjaman->event_name}' telah di-override ({$action})";
        
        if ($sarprasName) {
            $message .= " - {$sarprasName}";
        }
        
        if ($reason) {
            $message .= ": {$reason}";
        }
        
        $this->create(
            $approverId,
            $title,
            $message,
            'approval_override',
            "/approvals/workflow/{$peminjaman->id}"
        );
    }

    /**
     * Notifikasi konflik prioritas
     */
    public function notifyPriorityConflict(Peminjaman $peminjaman1, ?Peminjaman $peminjaman2, string $conflictType): void
    {
        $title = '⚠️ NOTIFIKASI: Konflik Prioritas';
        $secondName = $peminjaman2?->event_name ?? 'pengajuan baru';
        $message = "Konflik prioritas sarpras acara '{$peminjaman1->event_name}' vs '{$secondName}' - Segera diselesaikan!";
        
        // Notifikasi ke admin
        $adminUsers = \App\Models\User::query()->role('admin')->get();
        foreach ($adminUsers as $admin) {
            $this->create(
                $admin->id,
                $title,
                $message,
                'priority_conflict',
                "/approvals/pending"
            );
        }
    }

    /**
     * Notifikasi approval status berubah
     */
    public function notifyApprovalStatusChanged(Peminjaman $peminjaman, string $newStatus): void
    {
        $title = 'Status Approval Berubah';
        $message = "Status approval untuk pengajuan '{$peminjaman->event_name}' berubah menjadi: {$newStatus}";
        
        $this->create(
            $peminjaman->user_id,
            $title,
            $message,
            'approval_status_changed',
            "/peminjaman/{$peminjaman->id}"
        );
    }
}

