<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\PeminjamanApprovalWorkflow;
use App\Models\Sarana;
use App\Models\Prasarana;
use App\Services\PeminjamanApprovalService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalApiController extends Controller
{
    public function __construct(private PeminjamanApprovalService $approvalService)
    {
    }

    /**
     * Get pending approvals for current user
     */
    public function getPending()
    {
        $approverId = Auth::id();
        $workflows = $this->approvalService->getPendingApprovals($approverId);
        
        return response()->json([
            'success' => true,
            'data' => $workflows
        ]);
    }

    /**
     * Get approval status for a peminjaman
     */
    public function getStatus(Peminjaman $peminjaman)
    {
        $this->authorize('view', $peminjaman);
        
        $status = $this->approvalService->getApprovalStatus($peminjaman->id);
        
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    /**
     * Approve a specific workflow item
     */
    public function approveWorkflow(Request $request, PeminjamanApprovalWorkflow $workflow)
    {
        $this->authorize('approve', $workflow->peminjaman);
        
        try {
            $notes = $request->input('notes');
            $this->approvalService->approveWorkflow($workflow, $notes);
            
            // Notifikasi ke peminjam
            $workflow->load('peminjaman');
            app(NotificationService::class)->notifyApproval($workflow->peminjaman);
            
            return response()->json([
                'success' => true,
                'message' => 'Approval berhasil.',
                'workflow' => $workflow->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a specific workflow item
     */
    public function rejectWorkflow(Request $request, PeminjamanApprovalWorkflow $workflow)
    {
        $this->authorize('reject', $workflow->peminjaman);
        $request->validate(['notes' => 'required|string|max:1000']);
        
        try {
            $this->approvalService->rejectWorkflow($workflow, $request->input('notes'));
            
            // Notifikasi ke peminjam
            $workflow->load('peminjaman');
            app(NotificationService::class)->notifyRejection($workflow->peminjaman, $request->input('notes'));
            
            return response()->json([
                'success' => true,
                'message' => 'Rejection berhasil.',
                'workflow' => $workflow->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Override decision by higher-level approver
     */
    public function overrideWorkflow(Request $request, PeminjamanApprovalWorkflow $workflow)
    {
        $this->authorize('override', $workflow->peminjaman);
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->approvalService->overrideWorkflow($workflow, $request->input('action'), $request->input('reason'));
            
            // Notifikasi ke peminjam
            $workflow->load('peminjaman');
            if ($request->input('action') === 'approve') {
                app(NotificationService::class)->notifyApproval($workflow->peminjaman);
            } else {
                app(NotificationService::class)->notifyRejection($workflow->peminjaman, $request->input('reason'));
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Override berhasil.',
                'workflow' => $workflow->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal override: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve global approval
     */
    public function approveGlobal(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('approve', $peminjaman);
        
        try {
            $approverId = Auth::id();
            $notes = $request->input('notes');
            
            $this->approvalService->approveGlobal($peminjaman->id, $approverId, $notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Global approval berhasil.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve global: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject global approval
     */
    public function rejectGlobal(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('reject', $peminjaman);
        $request->validate(['notes' => 'required|string|max:1000']);
        
        try {
            $approverId = Auth::id();
            $reason = $request->input('notes');
            
            $this->approvalService->rejectGlobal($peminjaman->id, $approverId, $reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Global rejection berhasil.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject global: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve specific sarana
     */
    public function approveSpecificSarana(Request $request, Peminjaman $peminjaman, Sarana $sarana)
    {
        $this->authorize('approve', $peminjaman);
        
        try {
            $approverId = Auth::id();
            $notes = $request->input('notes');
            
            $this->approvalService->approveSpecificSarana($peminjaman->id, $sarana->id, $approverId, $notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Sarana approval berhasil.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve sarana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject specific sarana
     */
    public function rejectSpecificSarana(Request $request, Peminjaman $peminjaman, Sarana $sarana)
    {
        $this->authorize('reject', $peminjaman);
        $request->validate(['notes' => 'required|string|max:1000']);
        
        try {
            $approverId = Auth::id();
            $reason = $request->input('notes');
            
            $this->approvalService->rejectSpecificSarana($peminjaman->id, $sarana->id, $approverId, $reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Sarana rejection berhasil.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject sarana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve specific prasarana
     */
    public function approveSpecificPrasarana(Request $request, Peminjaman $peminjaman, Prasarana $prasarana)
    {
        $this->authorize('approve', $peminjaman);
        
        try {
            $approverId = Auth::id();
            $notes = $request->input('notes');
            
            $this->approvalService->approveSpecificPrasarana($peminjaman->id, $prasarana->id, $approverId, $notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Prasarana approval berhasil.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve prasarana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject specific prasarana
     */
    public function rejectSpecificPrasarana(Request $request, Peminjaman $peminjaman, Prasarana $prasarana)
    {
        $this->authorize('reject', $peminjaman);
        $request->validate(['notes' => 'required|string|max:1000']);
        
        try {
            $approverId = Auth::id();
            $reason = $request->input('notes');
            
            $this->approvalService->rejectSpecificPrasarana($peminjaman->id, $prasarana->id, $approverId, $reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Prasarana rejection berhasil.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject prasarana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Override approval with higher level
     */
    public function overrideApproval(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('override', $peminjaman);
        $request->validate([
            'sarpras_id' => 'required|integer',
            'sarpras_type' => 'required|in:sarana,prasarana',
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:1000'
        ]);
        
        try {
            $approverId = Auth::id();
            $sarprasId = $request->input('sarpras_id');
            $sarprasType = $request->input('sarpras_type');
            $action = $request->input('action');
            $reason = $request->input('reason');
            
            $this->approvalService->overrideApproval($peminjaman->id, $sarprasId, $approverId, $action, $reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Override approval berhasil.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal override approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get multiple approvers for sarpras
     */
    public function getMultipleApprovers(Request $request)
    {
        $request->validate([
            'sarpras_id' => 'required|integer',
            'type' => 'required|in:sarana,prasarana'
        ]);
        
        $approvers = $this->approvalService->getMultipleApprovers(
            $request->input('sarpras_id'),
            $request->input('type')
        );
        
        return response()->json([
            'success' => true,
            'data' => $approvers
        ]);
    }

    /**
     * Check override permission
     */
    public function checkOverridePermission(Request $request)
    {
        $request->validate([
            'target_approver_id' => 'required|integer',
            'sarpras_id' => 'required|integer',
            'type' => 'required|in:sarana,prasarana'
        ]);
        
        $canOverride = $this->approvalService->checkOverridePermission(
            Auth::id(),
            $request->input('target_approver_id'),
            $request->input('sarpras_id'),
            $request->input('type')
        );
        
        return response()->json([
            'success' => true,
            'can_override' => $canOverride
        ]);
    }

    /**
     * Calculate final status
     */
    public function calculateFinalStatus(Peminjaman $peminjaman)
    {
        $this->authorize('view', $peminjaman);
        
        $result = $this->approvalService->calculateFinalStatus($peminjaman->id);
        
        return response()->json([
            'success' => true,
            'result' => $result
        ]);
    }
}

