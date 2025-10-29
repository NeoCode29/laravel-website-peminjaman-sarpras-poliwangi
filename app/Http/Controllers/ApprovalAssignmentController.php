<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalApprover;
use App\Models\SaranaApprover;
use App\Models\PrasaranaApprover;
use App\Models\Sarana;
use App\Models\Prasarana;
use App\Models\User;
use App\Services\PeminjamanApprovalService;

class ApprovalAssignmentController extends Controller
{
    protected $approvalService;

    public function __construct(PeminjamanApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display global approvers management
     */
    public function globalIndex()
    {
        $this->authorize('sarpras.approval_global');

        $globalApprovers = GlobalApprover::with('approver')
            ->orderBy('approval_level')
            ->orderBy('created_at')
            ->paginate(20);

        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'approver', 'global_approver', 'specific_approver']);
        })->get();

        return view('approval-assignment.global-index', compact('globalApprovers', 'users'));
    }

    /**
     * Store global approver
     */
    public function storeGlobal(Request $request)
    {
        $this->authorize('sarpras.approval_global');

        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'approval_level' => 'required|integer|min:1|max:10',
        ]);

        // Check if combination already exists
        $existing = GlobalApprover::where('approver_id', $request->approver_id)
            ->where('approval_level', $request->approval_level)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['approver_id' => 'User sudah menjadi global approver dengan level yang sama.'])
                ->withInput();
        }

        GlobalApprover::create([
            'approver_id' => $request->approver_id,
            'approval_level' => $request->approval_level,
            'is_active' => true,
        ]);

        return redirect()->route('approval-assignment.global.index')
            ->with('success', 'Global approver berhasil ditambahkan.');
    }

    /**
     * Update global approver
     */
    public function updateGlobal(Request $request, $id)
    {
        $this->authorize('sarpras.approval_global');

        $request->validate([
            'approval_level' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        $globalApprover = GlobalApprover::findOrFail($id);
        
        // Check if new combination already exists (excluding current record)
        $existing = GlobalApprover::where('approver_id', $globalApprover->approver_id)
            ->where('approval_level', $request->approval_level)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['approval_level' => 'User sudah menjadi global approver dengan level yang sama.'])
                ->withInput();
        }

        $globalApprover->update([
            'approval_level' => $request->approval_level,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('approval-assignment.global.index')
            ->with('success', 'Global approver berhasil diperbarui.');
    }

    /**
     * Delete global approver
     */
    public function destroyGlobal($id)
    {
        $this->authorize('sarpras.approval_global');

        $globalApprover = GlobalApprover::findOrFail($id);
        $globalApprover->delete();

        return redirect()->route('approval-assignment.global.index')
            ->with('success', 'Global approver berhasil dihapus.');
    }

    /**
     * Display sarana approvers management
     */
    public function saranaIndex(Request $request)
    {
        $this->authorize('sarpras.approval_assign');

        // Alihkan ke halaman detail sarana jika ada parameter sarana_id
        if ($request->filled('sarana_id')) {
            return redirect()->route('sarana.show', $request->sarana_id);
        }

        // Ambil data sarana approvers dengan pagination
        $saranaApprovers = SaranaApprover::with(['sarana.kategori', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Ambil semua sarana untuk dropdown
        $saranas = Sarana::with('kategori')->orderBy('name')->get();

        // Ambil semua users untuk dropdown
        $users = User::orderBy('name')->get();

        return view('approval-assignment.sarana-index', compact('saranaApprovers', 'saranas', 'users'));
    }

    /**
     * Store sarana approver
     */
    public function storeSarana(Request $request)
    {
        $this->authorize('sarpras.approval_assign');

        $request->validate([
            'sarana_id' => 'required|exists:sarana,id',
            'approver_id' => 'required|exists:users,id',
            'approval_level' => 'required|integer|min:1|max:10',
        ]);

        // Check if combination already exists
        $existing = SaranaApprover::where('sarana_id', $request->sarana_id)
            ->where('approver_id', $request->approver_id)
            ->where('approval_level', $request->approval_level)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['approver_id' => 'User sudah menjadi approver untuk sarana ini dengan level yang sama.'])
                ->withInput();
        }

        SaranaApprover::create([
            'sarana_id' => $request->sarana_id,
            'approver_id' => $request->approver_id,
            'approval_level' => $request->approval_level,
            'is_active' => true,
        ]);

        return redirect()->route('sarana.show', $request->sarana_id)
            ->with('success', 'Sarana approver berhasil ditambahkan.');
    }

    /**
     * Update sarana approver
     */
    public function updateSarana(Request $request, $id)
    {
        $this->authorize('sarpras.approval_assign');

        $request->validate([
            'approval_level' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        $saranaApprover = SaranaApprover::findOrFail($id);
        
        // Check if new combination already exists (excluding current record)
        $existing = SaranaApprover::where('sarana_id', $saranaApprover->sarana_id)
            ->where('approver_id', $saranaApprover->approver_id)
            ->where('approval_level', $request->approval_level)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['approval_level' => 'User sudah menjadi approver untuk sarana ini dengan level yang sama.'])
                ->withInput();
        }

        $saranaApprover->update([
            'approval_level' => $request->approval_level,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('sarana.show', $saranaApprover->sarana_id)
            ->with('success', 'Sarana approver berhasil diperbarui.');
    }

    /**
     * Delete sarana approver
     */
    public function destroySarana($id)
    {
        $this->authorize('sarpras.approval_assign');

        $saranaApprover = SaranaApprover::findOrFail($id);
        $saranaId = $saranaApprover->sarana_id;
        $saranaApprover->delete();

        return redirect()->route('sarana.show', $saranaId)
            ->with('success', 'Sarana approver berhasil dihapus.');
    }

    /**
     * Display prasarana approvers management
     */
    public function prasaranaIndex(Request $request)
    {
        $this->authorize('sarpras.approval_assign');

        // Alihkan ke halaman detail prasarana jika ada parameter prasarana_id
        if ($request->filled('prasarana_id')) {
            return redirect()->route('prasarana.show', $request->prasarana_id);
        }

        // Jika tidak ada konteks spesifik, arahkan ke daftar prasarana
        return redirect()->route('prasarana.index');
    }

    /**
     * Store prasarana approver
     */
    public function storePrasarana(Request $request)
    {
        $this->authorize('sarpras.approval_assign');

        $request->validate([
            'prasarana_id' => 'required|exists:prasarana,id',
            'approver_id' => 'required|exists:users,id',
            'approval_level' => 'required|integer|min:1|max:10',
        ]);

        // Check if combination already exists
        $existing = PrasaranaApprover::where('prasarana_id', $request->prasarana_id)
            ->where('approver_id', $request->approver_id)
            ->where('approval_level', $request->approval_level)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['approver_id' => 'User sudah menjadi approver untuk prasarana ini dengan level yang sama.'])
                ->withInput();
        }

        PrasaranaApprover::create([
            'prasarana_id' => $request->prasarana_id,
            'approver_id' => $request->approver_id,
            'approval_level' => $request->approval_level,
            'is_active' => true,
        ]);

        return redirect()->route('prasarana.show', $request->prasarana_id)
            ->with('success', 'Prasarana approver berhasil ditambahkan.');
    }

    /**
     * Update prasarana approver
     */
    public function updatePrasarana(Request $request, $id)
    {
        $this->authorize('sarpras.approval_assign');

        $request->validate([
            'approval_level' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        $prasaranaApprover = PrasaranaApprover::findOrFail($id);
        
        // Check if new combination already exists (excluding current record)
        $existing = PrasaranaApprover::where('prasarana_id', $prasaranaApprover->prasarana_id)
            ->where('approver_id', $prasaranaApprover->approver_id)
            ->where('approval_level', $request->approval_level)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['approval_level' => 'User sudah menjadi approver untuk prasarana ini dengan level yang sama.'])
                ->withInput();
        }

        $prasaranaApprover->update([
            'approval_level' => $request->approval_level,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('prasarana.show', $prasaranaApprover->prasarana_id)
            ->with('success', 'Prasarana approver berhasil diperbarui.');
    }

    /**
     * Delete prasarana approver
     */
    public function destroyPrasarana($id)
    {
        $this->authorize('sarpras.approval_assign');

        $prasaranaApprover = PrasaranaApprover::findOrFail($id);
        $prasaranaId = $prasaranaApprover->prasarana_id;
        $prasaranaApprover->delete();

        return redirect()->route('prasarana.show', $prasaranaId)
            ->with('success', 'Prasarana approver berhasil dihapus.');
    }

    /**
     * Get approvers for specific sarana
     */
    public function getSaranaApprovers($saranaId)
    {
        $this->authorize('sarpras.approval_assign');

        $approvers = SaranaApprover::where('sarana_id', $saranaId)
            ->active()
            ->with('approver')
            ->orderBy('approval_level')
            ->get();

        return response()->json($approvers);
    }

    /**
     * Get approvers for specific prasarana
     */
    public function getPrasaranaApprovers($prasaranaId)
    {
        $this->authorize('sarpras.approval_assign');

        $approvers = PrasaranaApprover::where('prasarana_id', $prasaranaId)
            ->active()
            ->with('approver')
            ->orderBy('approval_level')
            ->get();

        return response()->json($approvers);
    }

    /**
     * Bulk assign approvers to sarana
     */
    public function bulkAssignSarana(Request $request)
    {
        $this->authorize('sarpras.approval_assign');

        $request->validate([
            'sarana_ids' => 'required|array|min:1',
            'sarana_ids.*' => 'exists:sarana,id',
            'approver_id' => 'required|exists:users,id',
            'approval_level' => 'required|integer|min:1|max:10',
        ]);

        $assigned = 0;
        $skipped = 0;

        foreach ($request->sarana_ids as $saranaId) {
            $existing = SaranaApprover::where('sarana_id', $saranaId)
                ->where('approver_id', $request->approver_id)
                ->where('approval_level', $request->approval_level)
                ->first();

            if (!$existing) {
                SaranaApprover::create([
                    'sarana_id' => $saranaId,
                    'approver_id' => $request->approver_id,
                    'approval_level' => $request->approval_level,
                    'is_active' => true,
                ]);
                $assigned++;
            } else {
                $skipped++;
            }
        }

        $message = "Berhasil menambahkan {$assigned} approver";
        if ($skipped > 0) {
            $message .= ", {$skipped} sudah ada sebelumnya";
        }

        return redirect()->route('approval-assignment.sarana.index')
            ->with('success', $message . '.');
    }

    /**
     * Bulk assign approvers to prasarana
     */
    public function bulkAssignPrasarana(Request $request)
    {
        $this->authorize('sarpras.approval_assign');

        $request->validate([
            'prasarana_ids' => 'required|array|min:1',
            'prasarana_ids.*' => 'exists:prasarana,id',
            'approver_id' => 'required|exists:users,id',
            'approval_level' => 'required|integer|min:1|max:10',
        ]);

        $assigned = 0;
        $skipped = 0;

        foreach ($request->prasarana_ids as $prasaranaId) {
            $existing = PrasaranaApprover::where('prasarana_id', $prasaranaId)
                ->where('approver_id', $request->approver_id)
                ->where('approval_level', $request->approval_level)
                ->first();

            if (!$existing) {
                PrasaranaApprover::create([
                    'prasarana_id' => $prasaranaId,
                    'approver_id' => $request->approver_id,
                    'approval_level' => $request->approval_level,
                    'is_active' => true,
                ]);
                $assigned++;
            } else {
                $skipped++;
            }
        }

        $message = "Berhasil menambahkan {$assigned} approver";
        if ($skipped > 0) {
            $message .= ", {$skipped} sudah ada sebelumnya";
        }

        return redirect()->route('approval-assignment.prasarana.index')
            ->with('success', $message . '.');
    }
}
