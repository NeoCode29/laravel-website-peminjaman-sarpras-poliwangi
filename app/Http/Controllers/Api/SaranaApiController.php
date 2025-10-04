<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sarana;
use App\Models\SaranaUnit;
use App\Services\SaranaService;
use App\Http\Requests\StoreSaranaRequest;
use App\Http\Requests\UpdateSaranaRequest;
use Illuminate\Http\Request;

class SaranaApiController extends Controller
{
    public function __construct(private SaranaService $service)
    {
        $this->middleware(['auth:sanctum']);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Sarana::class);
        $filters = $request->only(['kategori_id', 'type', 'status', 'search']);
        $items = $this->service->list($filters, 15);
        return response()->json($items);
    }

    public function show(Sarana $sarana)
    {
        $this->authorize('view', $sarana);
        $sarana->load(['kategori', 'units']);
        return response()->json($sarana);
    }

    public function store(StoreSaranaRequest $request)
    {
        $this->authorize('create', Sarana::class);
        
        try {
            $data = $request->validated();
            $image = $request->file('image');
            $sarana = $this->service->create($data, auth()->id(), $image);
            
            return response()->json($sarana, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function update(UpdateSaranaRequest $request, Sarana $sarana)
    {
        $this->authorize('update', $sarana);
        
        try {
            $data = $request->validated();
            $image = $request->file('image');
            $sarana = $this->service->update($sarana, $data, $image);
            
            return response()->json($sarana);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(Sarana $sarana)
    {
        $this->authorize('delete', $sarana);
        
        try {
            $this->service->delete($sarana);
            return response()->json(['message' => 'Sarana berhasil dihapus']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function storeUnit(Request $request, Sarana $sarana)
    {
        $this->authorize('unitManage', $sarana);
        
        $request->validate(['unit_code' => 'required|string|max:80']);
        
        try {
            $unit = $this->service->addUnit($sarana, $request->unit_code);
            return response()->json($unit, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function storeBulkUnits(Request $request, Sarana $sarana)
    {
        $this->authorize('unitManage', $sarana);
        
        $request->validate([
            'unit_codes' => 'required|array|min:1',
            'unit_codes.*' => 'required|string|max:80',
        ]);
        
        try {
            $units = $this->service->addBulkUnits($sarana, $request->unit_codes);
            return response()->json(['units' => $units, 'count' => count($units)], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function updateBulkUnitStatus(Request $request, Sarana $sarana)
    {
        $this->authorize('unitManage', $sarana);
        
        $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'required|integer|exists:sarana_units,id',
            'status' => 'required|in:tersedia,rusak,maintenance,hilang',
        ]);
        
        try {
            $updatedCount = $this->service->updateBulkUnitStatus($sarana, $request->unit_ids, $request->status);
            return response()->json(['updated_count' => $updatedCount]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function updateUnit(Request $request, SaranaUnit $unit)
    {
        $this->authorize('unitManage', $unit->sarana);
        
        $request->validate(['unit_status' => 'required|in:tersedia,rusak,maintenance,hilang']);
        
        try {
            $this->service->updateUnitStatus($unit, $request->unit_status);
            return response()->json($unit->fresh());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroyUnit(SaranaUnit $unit)
    {
        $this->authorize('unitManage', $unit->sarana);
        
        try {
            $this->service->deleteUnit($unit);
            return response()->json(['message' => 'Unit berhasil dihapus']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function updatePooledStatus(Request $request, Sarana $sarana)
    {
        $this->authorize('statusUpdate', $sarana);
        
        $request->validate([
            'jumlah_tersedia' => 'required|integer|min:0',
            'jumlah_rusak' => 'required|integer|min:0',
            'jumlah_maintenance' => 'required|integer|min:0',
            'jumlah_hilang' => 'required|integer|min:0',
        ]);
        
        try {
            $sarana = $this->service->updatePooledStatus($sarana, $request->all());
            return response()->json($sarana);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}


