<?php

namespace App\Http\Controllers;

use App\Models\Sarana;
use App\Models\KategoriSarana;
use App\Models\SaranaUnit;
use App\Services\SaranaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreSaranaRequest;
use App\Http\Requests\UpdateSaranaRequest;

class SaranaController extends Controller
{
    public function __construct(private SaranaService $service)
    {
        $this->middleware('auth');
        $this->middleware('permission:sarpras.view')->only(['index', 'show']);
        $this->middleware('permission:sarpras.create')->only(['create', 'store']);
        $this->middleware('permission:sarpras.edit')->only(['edit', 'update']);
        $this->middleware('permission:sarpras.delete')->only(['destroy']);
        $this->middleware('permission:sarpras.unit_manage')->only(['manageUnits', 'storeUnit', 'updateUnit', 'destroyUnit', 'storeBulkUnits', 'updateBulkUnitStatus']);
        $this->middleware('permission:sarpras.status_update')->only(['updatePooledStatus']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['kategori_id', 'type', 'status', 'search']);
        $sarana = $this->service->list($filters, 15);
        $kategoriSarana = KategoriSarana::orderBy('name')->get();

        return view('sarana.index', compact('sarana', 'kategoriSarana'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoriSarana = KategoriSarana::orderBy('name')->get();
        return view('sarana.create', compact('kategoriSarana'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaranaRequest $request)
    {
        try {
            $data = $request->validated();
            $image = $request->file('image');
            
            $sarana = $this->service->create($data, Auth::id(), $image);

            // Audit log: create sarana
            $this->logAudit('create', 'Sarana', $sarana->id, null, $sarana->toArray(), 'Membuat sarana baru');

            return redirect()->route('sarana.show', $sarana->id)
                ->with('success', 'Sarana berhasil dibuat.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sarana $sarana)
    {
        $sarana->load(['kategori', 'creator', 'units', 'approvers.approver']);
        
        // Get users for approver selection (hanya role yang diizinkan)
        $users = \App\Models\User::where('status', 'active')
            ->where('id', '!=', Auth::id()) // Exclude current user
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'approver', 'global_approver', 'specific_approver']);
            })
            ->orderBy('name')
            ->get();
        
        return view('sarana.show', compact('sarana', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sarana $sarana)
    {
        $kategoriSarana = KategoriSarana::orderBy('name')->get();
        return view('sarana.edit', compact('sarana', 'kategoriSarana'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSaranaRequest $request, Sarana $sarana)
    {
        try {
            $data = $request->validated();
            $image = $request->file('image');
            $oldValues = $sarana->toArray();
            
            $sarana = $this->service->update($sarana, $data, $image);

            // Audit log: update sarana
            $this->logAudit('update', 'Sarana', $sarana->id, $oldValues, $sarana->toArray(), 'Memperbarui sarana');

            return redirect()->route('sarana.show', $sarana->id)
                ->with('success', 'Sarana berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sarana $sarana)
    {
        try {
            $oldValues = $sarana->toArray();
            $this->service->delete($sarana);

            // Audit log: delete sarana
            $this->logAudit('delete', 'Sarana', $oldValues['id'] ?? null, $oldValues, null, 'Menghapus sarana');

            return redirect()->route('sarana.index')
                ->with('success', 'Sarana berhasil dihapus.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Manage units for serialized sarana
     */
    public function manageUnits(Sarana $sarana)
    {
        if ($sarana->type !== 'serialized') {
            return redirect()->back()
                ->with('error', 'Hanya sarana bertipe serialized yang dapat dikelola unitnya.');
        }

        $units = $sarana->units()->orderBy('unit_code')->paginate(15);
        return view('sarana.units', compact('sarana', 'units'));
    }

    /**
     * Store a new unit
     */
    public function storeUnit(Request $request, Sarana $sarana)
    {
        $validator = Validator::make($request->all(), [
            'unit_code' => 'required|string|max:80',
            'unit_status' => 'required|in:tersedia,rusak,maintenance,hilang',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [];
            
            if ($errors->has('unit_code')) {
                $errorMessages[] = 'Kode unit: ' . implode(', ', $errors->get('unit_code'));
            }
            if ($errors->has('unit_status')) {
                $errorMessages[] = 'Status unit: ' . implode(', ', $errors->get('unit_status'));
            }
            
            $message = 'Validasi gagal. ' . implode(' ', $errorMessages);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $errors
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $unit = $this->service->addUnit($sarana, $request->unit_code, $request->unit_status);

            // Audit log: create unit
            $this->logAudit('create', 'SaranaUnit', $unit->id, null, $unit->toArray(), 'Menambah unit sarana');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Unit berhasil ditambahkan.',
                    'unit' => $unit
                ]);
            }

            return redirect()->back()
                ->with('success', 'Unit berhasil ditambahkan.');
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update unit
     */
    public function updateUnit(Request $request, SaranaUnit $unit)
    {
        $validator = Validator::make($request->all(), [
            'unit_code' => 'required|string|max:80',
            'unit_status' => 'required|in:tersedia,rusak,maintenance,hilang',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [];
            
            if ($errors->has('unit_code')) {
                $errorMessages[] = 'Kode unit: ' . implode(', ', $errors->get('unit_code'));
            }
            if ($errors->has('unit_status')) {
                $errorMessages[] = 'Status unit: ' . implode(', ', $errors->get('unit_status'));
            }
            
            $message = 'Validasi gagal. ' . implode(' ', $errorMessages);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $errors
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $old = $unit->toArray();
            $this->service->updateUnit($unit, $request->only(['unit_code', 'unit_status']));
            
            // Audit log: update unit
            $this->logAudit('update', 'SaranaUnit', $unit->id, $old, $unit->fresh()->toArray(), 'Memperbarui unit sarana');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Unit berhasil diperbarui.',
                    'unit' => $unit->fresh()
                ]);
            }

            return redirect()->back()
                ->with('success', 'Unit berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete unit
     */
    public function destroyUnit(Request $request, $saranaId, $unitId)
    {
        \Log::info('Starting delete unit process', ['sarana_id' => $saranaId, 'unit_id' => $unitId]);
        
        try {
            // Find the unit manually
            $unit = SaranaUnit::where('id', $unitId)
                ->where('sarana_id', $saranaId)
                ->first();
                
            if (!$unit) {
                $message = 'Unit tidak ditemukan atau tidak milik sarana ini.';
                \Log::warning('Unit not found', ['sarana_id' => $saranaId, 'unit_id' => $unitId]);
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 404);
                }
                return redirect()->back()->with('error', $message);
            }
            
            \Log::info('Unit found', ['unit_id' => $unit->id, 'unit_code' => $unit->unit_code]);
            
            // Check if unit is currently borrowed
            \Log::info('Checking if unit is borrowed...');
            try {
                $isBorrowed = $unit->isCurrentlyBorrowed();
                \Log::info('Unit borrowed check result', ['is_borrowed' => $isBorrowed]);
                
                if ($isBorrowed) {
                    $message = 'Unit tidak dapat dihapus karena sedang dipinjam.';
                    \Log::info('Unit is borrowed, preventing deletion');
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message
                        ], 400);
                    }
                    return redirect()->back()->with('error', $message);
                }
            } catch (\Exception $e) {
                // If check fails, log but continue with deletion
                \Log::warning('Failed to check if unit is borrowed: ' . $e->getMessage());
            }

            \Log::info('Calling service to delete unit...');
            $old = $unit->toArray();
            $this->service->deleteUnit($unit);
            \Log::info('Unit deleted successfully');

            // Audit log: delete unit
            try {
                $this->logAudit('delete', 'SaranaUnit', $old['id'] ?? null, $old, null, 'Menghapus unit sarana');
            } catch (\Exception $e) {
                \Log::warning('Failed to log audit: ' . $e->getMessage());
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Unit ' . $unit->unit_code . ' berhasil dihapus.'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Unit ' . $unit->unit_code . ' berhasil dihapus.');
        } catch (\InvalidArgumentException $e) {
            \Log::error('InvalidArgumentException in destroyUnit', ['error' => $e->getMessage(), 'unit_id' => $unitId]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            return redirect()->back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Exception in destroyUnit', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'unit_id' => $unitId]);
            $message = 'Terjadi kesalahan saat menghapus unit: ' . $e->getMessage();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }
            return redirect()->back()->with('error', $message);
        }
    }

    /**
     * Bulk add units
     */
    public function storeBulkUnits(Request $request, Sarana $sarana)
    {
        $validator = Validator::make($request->all(), [
            'unit_codes' => 'required|array|min:1',
            'unit_codes.*' => 'required|string|max:80',
            'unit_status' => 'required|in:tersedia,rusak,maintenance,hilang',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [];
            
            if ($errors->has('unit_codes')) {
                $errorMessages[] = 'Daftar kode unit: ' . implode(', ', $errors->get('unit_codes'));
            }
            if ($errors->has('unit_codes.*')) {
                $errorMessages[] = 'Format kode unit tidak valid';
            }
            if ($errors->has('unit_status')) {
                $errorMessages[] = 'Status unit: ' . implode(', ', $errors->get('unit_status'));
            }
            
            $message = 'Validasi gagal. ' . implode(' ', $errorMessages);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $errors
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $units = $this->service->addBulkUnits($sarana, $request->unit_codes, $request->unit_status);

            // Audit log: bulk create units
            $this->logAudit('create', 'SaranaUnit', null, null, ['count' => count($units)], 'Menambah ' . count($units) . ' unit sarana sekaligus');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => count($units) . ' unit berhasil ditambahkan.',
                    'units' => $units
                ]);
            }

            return redirect()->back()
                ->with('success', count($units) . ' unit berhasil ditambahkan.');
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Bulk update unit status
     */
    public function updateBulkUnitStatus(Request $request, Sarana $sarana)
    {
        $validator = Validator::make($request->all(), [
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'required|integer|exists:sarana_units,id',
            'status' => 'required|in:tersedia,rusak,maintenance,hilang',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $updatedCount = $this->service->updateBulkUnitStatus($sarana, $request->unit_ids, $request->status);

            // Audit log: bulk update units
            $this->logAudit('update', 'SaranaUnit', null, null, ['count' => $updatedCount, 'status' => $request->status], 'Memperbarui status ' . $updatedCount . ' unit sarana');

            return redirect()->back()
                ->with('success', $updatedCount . ' unit berhasil diperbarui statusnya.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update pooled sarana status
     */
    public function updatePooledStatus(Request $request, Sarana $sarana)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_tersedia' => 'required|integer|min:0',
            'jumlah_rusak' => 'required|integer|min:0',
            'jumlah_maintenance' => 'required|integer|min:0',
            'jumlah_hilang' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $oldValues = $sarana->toArray();
            $sarana = $this->service->updatePooledStatus($sarana, $request->all());

            // Audit log: update pooled status
            $this->logAudit('update', 'Sarana', $sarana->id, $oldValues, $sarana->toArray(), 'Memperbarui status pooled sarana');

            return redirect()->back()
                ->with('success', 'Status sarana pooled berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Log audit trail
     */
    private function logAudit(string $action, string $model, ?int $modelId, ?array $oldValues, ?array $newValues, string $description): void
    {
        // Implementation for audit logging
        // This would typically use an audit service or model
        \Log::info('Audit Log', [
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()
        ]);
    }
}
