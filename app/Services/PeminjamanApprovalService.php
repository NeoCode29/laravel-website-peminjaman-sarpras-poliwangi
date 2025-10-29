<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\PeminjamanApprovalStatus;
use App\Models\PeminjamanApprovalWorkflow;
use App\Models\GlobalApprover;
use App\Models\SaranaApprover;
use App\Models\PrasaranaApprover;
use App\Models\PeminjamanItem;
use App\Models\Sarana;
use App\Models\Prasarana;
use App\Models\User;
use App\Services\PickupReturnService;
use App\Services\UserQuotaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeminjamanApprovalService
{
    /**
     * Approve a workflow item and recalculate overall status.
     */
    public function approveWorkflow(PeminjamanApprovalWorkflow $workflow, ?string $notes = null): void
    {
        DB::transaction(function () use ($workflow, $notes) {
            $workflow->approve($notes);
            $this->recalculateOverallStatus($workflow->peminjaman_id);
        });
    }

    /**
     * Reject a workflow item and recalculate overall status.
     */
    public function rejectWorkflow(PeminjamanApprovalWorkflow $workflow, ?string $notes = null): void
    {
        DB::transaction(function () use ($workflow, $notes) {
            $workflow->reject($notes);
            $this->recalculateOverallStatus($workflow->peminjaman_id);
        });
    }

    /**
     * Override: set the status according to higher-level approver decision.
     */
    public function overrideWorkflow(PeminjamanApprovalWorkflow $workflow, string $action, ?string $reason = null): void
    {
        DB::transaction(function () use ($workflow, $action, $reason) {
            $overrideUserId = Auth::id();
            if ($action === 'approve') {
                $workflow->approve($reason);
            } else {
                $workflow->reject($reason);
            }
            $workflow->fill([
                'overridden_by' => $overrideUserId,
                'overridden_at' => now(),
            ])->save();
            $this->recalculateOverallStatus($workflow->peminjaman_id);
        });
    }

    /**
     * Recalculate and persist overall status for a peminjaman.
     */
    public function recalculateOverallStatus(int $peminjamanId): void
    {
        $peminjaman = Peminjaman::findOrFail($peminjamanId);

        /** @var PeminjamanApprovalStatus $status */
        $status = PeminjamanApprovalStatus::firstOrCreate([
            'peminjaman_id' => $peminjaman->id,
        ], [
            'overall_status' => 'pending',
            'global_approval_status' => 'pending',
        ]);

        $status->updateOverallStatus();

        // Sync peminjaman.status when finalized (optional simple rule)
        if ($status->overall_status === 'approved') {
            $peminjaman->update(['status' => Peminjaman::STATUS_APPROVED]);
            $this->resolveKonflikGroup($peminjaman, true);
        } elseif ($status->overall_status === 'rejected') {
            $peminjaman->update(['status' => Peminjaman::STATUS_REJECTED]);
            $this->resolveKonflikGroup($peminjaman, false);
        }
    }

    protected function resolveKonflikGroup(Peminjaman $peminjaman, bool $approved): void
    {
        if (empty($peminjaman->konflik)) {
            return;
        }

        $konflikCode = $peminjaman->konflik;

        $konflikMembers = Peminjaman::where('konflik', $konflikCode)
            ->where('id', '!=', $peminjaman->id)
            ->get();

        if ($konflikMembers->isEmpty()) {
            $peminjaman->forceFill(['konflik' => null])->save();
            return;
        }

        if ($approved) {
            $this->cancelKonflikMembers($peminjaman);
            return;
        }

        $pendingLeft = $konflikMembers->where('status', Peminjaman::STATUS_PENDING);

        if ($pendingLeft->isEmpty()) {
            $konflikMembers->each(fn ($member) => $member->forceFill(['konflik' => null])->save());
            $peminjaman->forceFill(['konflik' => null])->save();
        }
    }

    protected function cancelKonflikMembers(?Peminjaman $peminjaman): void
    {
        if (!$peminjaman || empty($peminjaman->konflik)) {
            return;
        }

        $konflikCode = $peminjaman->konflik;
        $members = Peminjaman::where('konflik', $konflikCode)
            ->where('id', '!=', $peminjaman->id)
            ->get();

        if ($members->isEmpty()) {
            $peminjaman->forceFill(['konflik' => null])->save();
            return;
        }

        $cancelledBy = Auth::id();
        $now = now();

        foreach ($members as $member) {
            if ($member->status === Peminjaman::STATUS_PENDING) {
                $member->update([
                    'status' => Peminjaman::STATUS_CANCELLED,
                    'cancelled_by' => $cancelledBy,
                    'cancelled_reason' => trim(($member->cancelled_reason ?? '')."\nDibatalkan otomatis karena konflik dengan peminjaman {$peminjaman->id}.")
                ]);
                $member->forceFill(['cancelled_at' => $now])->save();
            } else {
                $member->forceFill(['konflik' => null])->save();
            }
        }

        $peminjaman->forceFill(['konflik' => null])->save();
    }

    /**
     * Create approval workflow for a peminjaman
     */
    public function createApprovalWorkflow($peminjamanId): bool
    {
        try {
            DB::beginTransaction();

            $peminjaman = Peminjaman::findOrFail($peminjamanId);
            
            // Create approval status record
            $approvalStatus = PeminjamanApprovalStatus::create([
                'peminjaman_id' => $peminjamanId,
                'overall_status' => 'pending',
                'global_approval_status' => 'pending',
            ]);

            // Create global approval workflows
            $globalApprovers = GlobalApprover::active()->get();
            foreach ($globalApprovers as $approver) {
                PeminjamanApprovalWorkflow::create([
                    'peminjaman_id' => $peminjamanId,
                    'approver_id' => $approver->approver_id,
                    'approval_type' => 'global',
                    'approval_level' => $approver->approval_level,
                    'status' => 'pending',
                ]);
            }

            // Create specific sarana approval workflows
            $peminjamanItems = $peminjaman->items;
            foreach ($peminjamanItems as $item) {
                $saranaApprovers = SaranaApprover::where('sarana_id', $item->sarana_id)
                    ->active()
                    ->get();
                
                foreach ($saranaApprovers as $approver) {
                    PeminjamanApprovalWorkflow::create([
                        'peminjaman_id' => $peminjamanId,
                        'approver_id' => $approver->approver_id,
                        'approval_type' => 'sarana',
                        'sarana_id' => $item->sarana_id,
                        'approval_level' => $approver->approval_level,
                        'status' => 'pending',
                    ]);
                }
            }

            // Create specific prasarana approval workflows
            if ($peminjaman->prasarana_id) {
                $prasaranaApprovers = PrasaranaApprover::where('prasarana_id', $peminjaman->prasarana_id)
                    ->active()
                    ->get();
                
                foreach ($prasaranaApprovers as $approver) {
                    PeminjamanApprovalWorkflow::create([
                        'peminjaman_id' => $peminjamanId,
                        'approver_id' => $approver->approver_id,
                        'approval_type' => 'prasarana',
                        'prasarana_id' => $peminjaman->prasarana_id,
                        'approval_level' => $approver->approval_level,
                        'status' => 'pending',
                    ]);
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating approval workflow', [
                'peminjaman_id' => $peminjamanId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Approve global approval
     */
    public function approveGlobal($peminjamanId, $approverId, $notes = null): bool
    {
        try {
            DB::beginTransaction();

            $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                ->where('approver_id', $approverId)
                ->where('approval_type', 'global')
                ->first();
            if (!$workflow) {
                // Fallback berbasis permission: izinkan approver global dengan permission tanpa entri assignment
                $user = User::findOrFail($approverId);
                if ($user->can('peminjaman.approve')) {
                    $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                        ->where('approval_type', 'global')
                        ->where('status', 'pending')
                        ->orderBy('approval_level')
                        ->first();
                }
            }
            if (!$workflow) {
                throw new \RuntimeException('Tidak ada workflow global pending untuk disetujui.');
            }

            $workflow->approve($notes);

            // Update approval status
            $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
            if ($approvalStatus) {
                $approvalStatus->setGlobalApproval($approverId, $notes);
                $approvalStatus->updateOverallStatus();
            }

            $globalPending = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                ->where('approval_type', 'global')
                ->where('status', 'pending')
                ->exists();

            if (!$globalPending) {
                $this->cancelKonflikMembers(Peminjaman::find($peminjamanId));
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving global', [
                'peminjaman_id' => $peminjamanId,
                'approver_id' => $approverId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reject global approval
     */
    public function rejectGlobal($peminjamanId, $approverId, $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                ->where('approver_id', $approverId)
                ->where('approval_type', 'global')
                ->first();
            if (!$workflow) {
                $user = User::findOrFail($approverId);
                if ($user->can('peminjaman.approve')) {
                    $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                        ->where('approval_type', 'global')
                        ->where('status', 'pending')
                        ->orderBy('approval_level')
                        ->first();
                }
            }
            if (!$workflow) {
                throw new \RuntimeException('Tidak ada workflow global pending untuk ditolak.');
            }

            $workflow->reject($reason);

            // Update approval status
            $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
            if ($approvalStatus) {
                $approvalStatus->setGlobalRejection($approverId, $reason);
                $approvalStatus->updateOverallStatus();
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting global', [
                'peminjaman_id' => $peminjamanId,
                'approver_id' => $approverId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Approve specific sarana
     */
    public function approveSpecificSarana($peminjamanId, $saranaId, $approverId, $notes = null): bool
    {
        try {
            DB::beginTransaction();

            $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                ->where('approver_id', $approverId)
                ->where('approval_type', 'sarana')
                ->where('sarana_id', $saranaId)
                ->first();
            if (!$workflow) {
                $user = User::findOrFail($approverId);
                if ($user->can('peminjaman.approve_specific')) {
                    $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                        ->where('approval_type', 'sarana')
                        ->where('sarana_id', $saranaId)
                        ->where('status', 'pending')
                        ->orderBy('approval_level')
                        ->first();
                }
            }
            if (!$workflow) {
                throw new \RuntimeException('Tidak ada workflow sarana pending untuk disetujui.');
            }

            $workflow->approve($notes);

            $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
            if ($approvalStatus) {
                $approvalStatus->updateOverallStatus();
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving specific sarana', [
                'peminjaman_id' => $peminjamanId,
                'sarana_id' => $saranaId,
                'approver_id' => $approverId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reject specific sarana
     */
    public function rejectSpecificSarana($peminjamanId, $saranaId, $approverId, $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                ->where('approver_id', $approverId)
                ->where('approval_type', 'sarana')
                ->where('sarana_id', $saranaId)
                ->first();
            if (!$workflow) {
                $user = User::findOrFail($approverId);
                if ($user->can('peminjaman.approve_specific')) {
                    $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                        ->where('approval_type', 'sarana')
                        ->where('sarana_id', $saranaId)
                        ->where('status', 'pending')
                        ->orderBy('approval_level')
                        ->first();
                }
            }
            if (!$workflow) {
                throw new \RuntimeException('Tidak ada workflow sarana pending untuk ditolak.');
            }

            $peminjaman = Peminjaman::find($peminjamanId);
            $previousStatus = $peminjaman?->status;

            $workflow->reject($reason);

            $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
            if ($approvalStatus) {
                $approvalStatus->updateOverallStatus();
            }

            if ($peminjaman) {
                $peminjaman->refresh();
                if ($this->hasLeftActiveSet($previousStatus, $peminjaman->status)) {
                    app(UserQuotaService::class)->decrementIfInactive($peminjaman);
                    app(PickupReturnService::class)->releaseSerializedUnits($peminjaman);
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting specific sarana', [
                'peminjaman_id' => $peminjamanId,
                'sarana_id' => $saranaId,
                'approver_id' => $approverId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Approve specific prasarana
     */
    public function approveSpecificPrasarana($peminjamanId, $prasaranaId, $approverId, $notes = null): bool
    {
        try {
            DB::beginTransaction();

            $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                ->where('approver_id', $approverId)
                ->where('approval_type', 'prasarana')
                ->where('prasarana_id', $prasaranaId)
                ->first();
            if (!$workflow) {
                $user = User::findOrFail($approverId);
                if ($user->can('peminjaman.approve_specific')) {
                    $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                        ->where('approval_type', 'prasarana')
                        ->where('prasarana_id', $prasaranaId)
                        ->where('status', 'pending')
                        ->orderBy('approval_level')
                        ->first();
                }
            }
            if (!$workflow) {
                throw new \RuntimeException('Tidak ada workflow prasarana pending untuk disetujui.');
            }

            $workflow->approve($notes);

            // Update overall status
            $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
            if ($approvalStatus) {
                $approvalStatus->updateOverallStatus();
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving specific prasarana', [
                'peminjaman_id' => $peminjamanId,
                'prasarana_id' => $prasaranaId,
                'approver_id' => $approverId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reject specific prasarana
     */
    public function rejectSpecificPrasarana($peminjamanId, $prasaranaId, $approverId, $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                ->where('approver_id', $approverId)
                ->where('approval_type', 'prasarana')
                ->where('prasarana_id', $prasaranaId)
                ->first();
            if (!$workflow) {
                $user = User::findOrFail($approverId);
                if ($user->can('peminjaman.approve_specific')) {
                    $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                        ->where('approval_type', 'prasarana')
                        ->where('prasarana_id', $prasaranaId)
                        ->where('status', 'pending')
                        ->orderBy('approval_level')
                        ->first();
                }
            }
            if (!$workflow) {
                throw new \RuntimeException('Tidak ada workflow prasarana pending untuk ditolak.');
            }

            $peminjaman = Peminjaman::find($peminjamanId);
            $previousStatus = $peminjaman?->status;

            $workflow->reject($reason);

            $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
            if ($approvalStatus) {
                $approvalStatus->updateOverallStatus();
            }

            if ($peminjaman) {
                $peminjaman->refresh();
                if ($this->hasLeftActiveSet($previousStatus, $peminjaman->status)) {
                    app(UserQuotaService::class)->decrementIfInactive($peminjaman);
                    app(PickupReturnService::class)->releaseSerializedUnits($peminjaman);
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting specific prasarana', [
                'peminjaman_id' => $peminjamanId,
                'prasarana_id' => $prasaranaId,
                'approver_id' => $approverId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get approval status for peminjaman
     */
    public function getApprovalStatus($peminjamanId)
    {
        $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
        
        if (!$approvalStatus) {
            return null;
        }

        $workflows = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
            ->with(['approver', 'sarana', 'prasarana'])
            ->get();

        return [
            'approval_status' => $approvalStatus,
            'workflows' => $workflows,
            'global_workflows' => $workflows->where('approval_type', 'global'),
            'sarana_workflows' => $workflows->where('approval_type', 'sarana'),
            'prasarana_workflows' => $workflows->where('approval_type', 'prasarana'),
        ];
    }

    /**
     * Get pending approvals for approver
     */
    public function getPendingApprovals($approverId)
    {
        return PeminjamanApprovalWorkflow::where('approver_id', $approverId)
            ->where('status', 'pending')
            ->with(['peminjaman', 'sarana', 'prasarana'])
            ->get();
    }

    /**
     * Override approval with higher level
     */
    public function overrideApproval($peminjamanId, $sarprasId, $approverId, $action, $reason)
    {
        try {
            DB::beginTransaction();

            // Find the workflow to override
            $workflow = PeminjamanApprovalWorkflow::where('peminjaman_id', $peminjamanId)
                ->where('approver_id', $approverId)
                ->where(function($query) use ($sarprasId) {
                    $query->where('sarana_id', $sarprasId)
                          ->orWhere('prasarana_id', $sarprasId);
                })
                ->firstOrFail();

            // Check if approver can override
            $approver = $this->getApproverForWorkflow($workflow);
            if (!$this->canOverrideApproval($approver, $workflow)) {
                throw new \Exception('Approver tidak memiliki level yang cukup untuk override');
            }

            // Perform action
            $peminjaman = Peminjaman::find($peminjamanId);
            $previousStatus = $peminjaman?->status;

            if ($action === 'approve') {
                $workflow->approve($reason);
                if ($workflow->approval_type === 'sarana' && $workflow->sarana_id) {
                    $this->syncPooledSaranaQuantity($peminjamanId, $workflow->sarana_id, 'approve');
                }
            } else {
                $workflow->reject($reason);
                if ($workflow->approval_type === 'sarana' && $workflow->sarana_id) {
                    $this->syncPooledSaranaQuantity($peminjamanId, $workflow->sarana_id, 'reject');
                }
            }

            $workflow->fill([
                'overridden_by' => Auth::id(),
                'overridden_at' => now(),
            ])->save();

            $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
            if ($approvalStatus) {
                $approvalStatus->updateOverallStatus();
            }

            if ($peminjaman) {
                $peminjaman->refresh();
                if ($this->hasLeftActiveSet($previousStatus, $peminjaman->status)) {
                    app(UserQuotaService::class)->decrementIfInactive($peminjaman);
                    app(PickupReturnService::class)->releaseSerializedUnits($peminjaman);
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error overriding approval', [
                'peminjaman_id' => $peminjamanId,
                'sarpras_id' => $sarprasId,
                'approver_id' => $approverId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get multiple approvers for sarpras
     */
    public function getMultipleApprovers($sarprasId, $type = 'sarana')
    {
        if ($type === 'sarana') {
            return SaranaApprover::where('sarana_id', $sarprasId)
                ->active()
                ->with('approver')
                ->orderBy('approval_level')
                ->get();
        } else {
            return PrasaranaApprover::where('prasarana_id', $sarprasId)
                ->active()
                ->with('approver')
                ->orderBy('approval_level')
                ->get();
        }
    }

    /**
     * Check override permission
     */
    public function checkOverridePermission($approverId, $targetApproverId, $sarprasId, $type = 'sarana')
    {
        $approver = $this->getApproverByType($sarprasId, $approverId, $type);
        $targetApprover = $this->getApproverByType($sarprasId, $targetApproverId, $type);

        if (!$approver || !$targetApprover) {
            return false;
        }

        return $approver->canOverride($targetApprover);
    }

    /**
     * Calculate final status
     */
    public function calculateFinalStatus($peminjamanId)
    {
        $approvalStatus = PeminjamanApprovalStatus::where('peminjaman_id', $peminjamanId)->first();
        
        if ($approvalStatus) {
            return $approvalStatus->updateOverallStatus();
        }

        return false;
    }

    /**
     * Helper method to get approver for workflow
     */
    private function getApproverForWorkflow($workflow)
    {
        if ($workflow->approval_type === 'global') {
            return GlobalApprover::where('approver_id', $workflow->approver_id)->first();
        } elseif ($workflow->approval_type === 'sarana') {
            return SaranaApprover::where('sarana_id', $workflow->sarana_id)
                ->where('approver_id', $workflow->approver_id)
                ->first();
        } else {
            return PrasaranaApprover::where('prasarana_id', $workflow->prasarana_id)
                ->where('approver_id', $workflow->approver_id)
                ->first();
        }
    }

    /**
     * Helper method to check if approver can override
     */
    private function canOverrideApproval($approver, $workflow)
    {
        if (!$approver) {
            return false;
        }

        // Get all workflows for the same sarpras
        $otherWorkflows = PeminjamanApprovalWorkflow::where('peminjaman_id', $workflow->peminjaman_id)
            ->where('approval_type', $workflow->approval_type)
            ->where(function($query) use ($workflow) {
                if ($workflow->sarana_id) {
                    $query->where('sarana_id', $workflow->sarana_id);
                }
                if ($workflow->prasarana_id) {
                    $query->where('prasarana_id', $workflow->prasarana_id);
                }
            })
            ->where('id', '!=', $workflow->id)
            ->get();

        foreach ($otherWorkflows as $otherWorkflow) {
            $otherApprover = $this->getApproverForWorkflow($otherWorkflow);
            if ($otherApprover && $approver->canOverride($otherApprover)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper method to get approver by type
     */
    private function getApproverByType($sarprasId, $approverId, $type)
    {
        if ($type === 'sarana') {
            return SaranaApprover::where('sarana_id', $sarprasId)
                ->where('approver_id', $approverId)
                ->first();
        } else {
            return PrasaranaApprover::where('prasarana_id', $sarprasId)
                ->where('approver_id', $approverId)
                ->first();
        }
    }

    public function syncAllPooledSarana(int $peminjamanId, string $mode = 'approve'): void
    {
        $items = PeminjamanItem::with('sarana')
            ->where('peminjaman_id', $peminjamanId)
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $saranaToRefresh = [];

        foreach ($items as $item) {
            $sarana = $item->sarana;
            if (!$sarana || $sarana->type !== 'pooled') {
                continue;
            }

            if ($mode === 'approve') {
                if (in_array($item->qty_approved, [null, 0], true)) {
                    $item->qty_approved = $item->qty_requested;
                    $item->save();
                }
            } else {
                if ($item->qty_approved !== 0) {
                    $item->qty_approved = 0;
                    $item->save();
                }
            }

            $saranaToRefresh[$sarana->id] = $sarana;
        }

        foreach ($saranaToRefresh as $sarana) {
            $sarana->refresh();
            $sarana->updateStats();
        }
    }

    private function syncPooledSaranaQuantity(int $peminjamanId, ?int $saranaId, string $mode = 'approve'): void
    {
        if (!$saranaId) {
            return;
        }

        $sarana = Sarana::find($saranaId);
        if (!$sarana || $sarana->type !== 'pooled') {
            return;
        }

        $item = PeminjamanItem::where('peminjaman_id', $peminjamanId)
            ->where('sarana_id', $saranaId)
            ->first();
        if (!$item) {
            return;
        }

        if ($mode === 'approve') {
            if (in_array($item->qty_approved, [null, 0], true)) {
                $item->qty_approved = $item->qty_requested;
                $item->save();
            }
        } else {
            if ($item->qty_approved !== 0) {
                $item->qty_approved = 0;
                $item->save();
            }
        }

        $sarana->updateStats();
    }

    private function hasLeftActiveSet(?string $previousStatus, ?string $currentStatus): bool
    {
        if (!$previousStatus || !$currentStatus) {
            return false;
        }

        $activeStatuses = [
            Peminjaman::STATUS_PENDING,
            Peminjaman::STATUS_APPROVED,
            Peminjaman::STATUS_PICKED_UP,
        ];

        $inactiveStatuses = [
            Peminjaman::STATUS_RETURNED,
            Peminjaman::STATUS_CANCELLED,
            Peminjaman::STATUS_REJECTED,
        ];

        return in_array($previousStatus, $activeStatuses, true)
            && in_array($currentStatus, $inactiveStatuses, true);
    }
}






