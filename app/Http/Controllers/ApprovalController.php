<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\PeminjamanApprovalWorkflow;
use App\Models\Sarana;
use App\Models\Prasarana;
use App\Services\PeminjamanApprovalService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function __construct(private PeminjamanApprovalService $approvalService)
    {
    }

    /**
     * Pending approvals for current approver.
     */
    public function pending(Request $request)
    {
        $this->authorize('viewAny', Peminjaman::class);

        $approverId = Auth::id();
        $workflows = PeminjamanApprovalWorkflow::with(['peminjaman.user', 'sarana', 'prasarana'])
            ->forApprover($approverId)
            ->pending()
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('approvals.pending', compact('workflows'));
    }

    /**
     * Approve a specific workflow item.
     */
    public function approve(Request $request, PeminjamanApprovalWorkflow $workflow)
    {
        $this->authorize('approve', $workflow->peminjaman);
        
        try {
        $notes = $request->input('notes');
        $this->approvalService->approveWorkflow($workflow, $notes);
            
        // Notifikasi ke peminjam jika perubahan mempengaruhi status final
        $workflow->load('peminjaman');
        app(NotificationService::class)->notifyApproval($workflow->peminjaman);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Approval berhasil.',
                    'workflow' => $workflow->fresh()
                ]);
            }
            
        return back()->with('success', 'Approval berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal approve: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    /**
     * Reject a specific workflow item.
     */
    public function reject(Request $request, PeminjamanApprovalWorkflow $workflow)
    {
        $this->authorize('reject', $workflow->peminjaman);
        $request->validate(['notes' => 'required|string|max:1000']);
        
        try {
        $this->approvalService->rejectWorkflow($workflow, $request->input('notes'));
        $workflow->load('peminjaman');
        app(NotificationService::class)->notifyRejection($workflow->peminjaman, $request->input('notes'));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rejection berhasil.',
                    'workflow' => $workflow->fresh()
                ]);
            }
            
        return back()->with('success', 'Rejection berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal reject: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal reject: ' . $e->getMessage());
        }
    }

    /**
     * Override decision by higher-level approver.
     */
    public function override(Request $request, PeminjamanApprovalWorkflow $workflow)
    {
        $this->authorize('override', $workflow->peminjaman);
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:1000',
        ]);

        $this->approvalService->overrideWorkflow($workflow, $request->input('action'), $request->input('reason'));
        $workflow->load('peminjaman');
        if ($request->input('action') === 'approve') {
            app(NotificationService::class)->notifyApproval($workflow->peminjaman);
        } else {
            app(NotificationService::class)->notifyRejection($workflow->peminjaman, $request->input('reason'));
        }
        return back()->with('success', 'Override berhasil.');
    }

    /**
     * Show workflow for a peminjaman.
     */
    public function workflow(Peminjaman $peminjaman)
    {
        $this->authorize('view', $peminjaman);
        $peminjaman->load(['approvalWorkflow.approver', 'approvalStatus']);
        return view('approvals.workflow', compact('peminjaman'));
    }

    /**
     * Get status for a peminjaman.
     */
    public function status(Peminjaman $peminjaman)
    {
        $this->authorize('view', $peminjaman);
        $peminjaman->load(['approvalStatus']);
        return response()->json([
            'overall_status' => $peminjaman->approvalStatus?->overall_status ?? 'pending',
            'global_status' => $peminjaman->approvalStatus?->global_approval_status ?? 'pending',
        ]);
    }

    /**
     * Display a listing of pending approvals.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PeminjamanApprovalWorkflow::class);

        $query = PeminjamanApprovalWorkflow::with([
            'peminjaman.user',
            'peminjaman.prasarana',
            'peminjaman.items.sarana',
            'approver',
            'sarana',
            'prasarana'
        ])->where('status', 'pending');

        // Filter by approval type
        if ($request->filled('approval_type')) {
            $query->where('approval_type', $request->approval_type);
        }

        // Filter by approver
        if ($request->filled('approver_id')) {
            $query->where('approver_id', $request->approver_id);
        }

        // Filter by sarana/prasarana
        if ($request->filled('sarana_id')) {
            $query->where('sarana_id', $request->sarana_id);
        }
        if ($request->filled('prasarana_id')) {
            $query->where('prasarana_id', $request->prasarana_id);
        }

        // Filter by user's approval permissions
        if (!Auth::user()->hasPermissionTo('peminjaman.approve_all')) {
            $query->where('approver_id', Auth::id());
        }

        $approvals = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('approval.index', compact('approvals'));
    }

    /**
     * Display the specified approval.
     */
    public function show(PeminjamanApprovalWorkflow $approval)
    {
        $this->authorize('view', $approval);

        $approval->load([
            'peminjaman.user',
            'peminjaman.prasarana',
            'peminjaman.items.sarana',
            'approver',
            'sarana',
            'prasarana'
        ]);

        return view('approvals.show', compact('approval'));
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
     * Approve global approval
     */
    public function approveGlobal(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('approve', $peminjaman);
        
        try {
            $approverId = Auth::id();
            $notes = $request->input('notes');
            
            $this->approvalService->approveGlobal($peminjaman->id, $approverId, $notes);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Global approval berhasil.'
                ]);
            }
            
            return back()->with('success', 'Global approval berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal approve global: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal approve global: ' . $e->getMessage());
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
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Global rejection berhasil.'
                ]);
            }
            
            return back()->with('success', 'Global rejection berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal reject global: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal reject global: ' . $e->getMessage());
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
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sarana approval berhasil.'
                ]);
            }
            
            return back()->with('success', 'Sarana approval berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal approve sarana: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal approve sarana: ' . $e->getMessage());
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
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sarana rejection berhasil.'
                ]);
            }
            
            return back()->with('success', 'Sarana rejection berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal reject sarana: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal reject sarana: ' . $e->getMessage());
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
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Prasarana approval berhasil.'
                ]);
            }
            
            return back()->with('success', 'Prasarana approval berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal approve prasarana: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal approve prasarana: ' . $e->getMessage());
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
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Prasarana rejection berhasil.'
                ]);
            }
            
            return back()->with('success', 'Prasarana rejection berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal reject prasarana: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal reject prasarana: ' . $e->getMessage());
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
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Override approval berhasil.'
                ]);
            }
            
            return back()->with('success', 'Override approval berhasil.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal override approval: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal override approval: ' . $e->getMessage());
        }
    }
}