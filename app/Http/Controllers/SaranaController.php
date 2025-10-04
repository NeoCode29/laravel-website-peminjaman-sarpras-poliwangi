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
        $kategori = KategoriSarana::orderBy('name')->get();

        return view('sarana.index', compact('sarana', 'kategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = KategoriSarana::orderBy('name')->get();
        return view('sarana.create', compact('kategori'));
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
        $sarana->load(['kategori', 'creator', 'units']);
        
        return view('sarana.show', compact('sarana'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sarana $sarana)
    {
        $kategori = KategoriSarana::orderBy('name')->get();
        return view('sarana.edit', compact('sarana', 'kategori'));
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

        $units = $sarana->units()->orderBy('unit_code')->get();
        return view('sarana.units', compact('sarana', 'units'));
    }

    /**
     * Store a new unit
     */
    public function storeUnit(Request $request, Sarana $sarana)
    {
        $validator = Validator::make($request->all(), [
            'unit_code' => 'required|string|max:80',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $unit = $this->service->addUnit($sarana, $request->unit_code);

            // Audit log: create unit
            $this->logAudit('create', 'SaranaUnit', $unit->id, null, $unit->toArray(), 'Menambah unit sarana');

            return redirect()->back()
                ->with('success', 'Unit berhasil ditambahkan.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update unit status
     */
    public function updateUnit(Request $request, SaranaUnit $unit)
    {
        $validator = Validator::make($request->all(), [
            'unit_status' => 'required|in:tersedia,rusak,maintenance,hilang',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $old = $unit->toArray();
            $this->service->updateUnitStatus($unit, $request->unit_status);
            
            // Audit log: update unit
            $this->logAudit('update', 'SaranaUnit', $unit->id, $old, $unit->fresh()->toArray(), 'Memperbarui status unit sarana');

            return redirect()->back()
                ->with('success', 'Status unit berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete unit
     */
    public function destroyUnit(SaranaUnit $unit)
    {
        try {
            $old = $unit->toArray();
            $this->service->deleteUnit($unit);

            // Audit log: delete unit
            $this->logAudit('delete', 'SaranaUnit', $old['id'] ?? null, $old, null, 'Menghapus unit sarana');

            return redirect()->back()
                ->with('success', 'Unit berhasil dihapus.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $units = $this->service->addBulkUnits($sarana, $request->unit_codes);

            // Audit log: bulk create units
            $this->logAudit('create', 'SaranaUnit', null, null, ['count' => count($units)], 'Menambah ' . count($units) . ' unit sarana sekaligus');

            return redirect()->back()
                ->with('success', count($units) . ' unit berhasil ditambahkan.');
        } catch (\InvalidArgumentException $e) {
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
}
