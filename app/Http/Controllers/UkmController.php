<?php

namespace App\Http\Controllers;

use App\Models\Ukm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UkmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ukm::class);

        $query = Ukm::query();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $ukm = $query->orderBy('nama')->paginate(15);

        return view('ukm.index', compact('ukm'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Ukm::class);

        return view('ukm.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Ukm::class);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:ukm,nama',
            'deskripsi' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Ukm::create([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()->route('ukm.index')
                ->with('success', 'UKM berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menambahkan UKM.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ukm $ukm)
    {
        $this->authorize('view', $ukm);

        $ukm->load(['markings.user', 'markings.prasarana']);

        return view('ukm.show', compact('ukm'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ukm $ukm)
    {
        $this->authorize('update', $ukm);

        return view('ukm.edit', compact('ukm'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ukm $ukm)
    {
        $this->authorize('update', $ukm);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:ukm,nama,' . $ukm->id,
            'deskripsi' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $ukm->update([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()->route('ukm.index')
                ->with('success', 'UKM berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui UKM.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ukm $ukm)
    {
        $this->authorize('delete', $ukm);

        // Check if UKM has active markings
        $activeMarkings = $ukm->markings()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        if ($activeMarkings > 0) {
            return redirect()->back()
                ->with('error', 'UKM tidak dapat dihapus karena masih memiliki marking aktif.');
        }

        try {
            $ukm->delete();

            return redirect()->route('ukm.index')
                ->with('success', 'UKM berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus UKM.');
        }
    }

    /**
     * Toggle UKM active status.
     */
    public function toggleStatus(Ukm $ukm)
    {
        $this->authorize('update', $ukm);

        try {
            $ukm->update(['is_active' => !$ukm->is_active]);

            $status = $ukm->is_active ? 'diaktifkan' : 'dinonaktifkan';
            
            return redirect()->back()
                ->with('success', "UKM berhasil {$status}.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengubah status UKM.');
        }
    }

    /**
     * Get UKM statistics.
     */
    public function statistics(Ukm $ukm)
    {
        $this->authorize('view', $ukm);

        $stats = [
            'total_markings' => $ukm->getTotalMarkingsCount(),
            'active_markings' => $ukm->getActiveMarkingsCount(),
            'expired_markings' => $ukm->markings()->where('status', 'expired')->count(),
            'converted_markings' => $ukm->markings()->where('status', 'converted')->count(),
            'cancelled_markings' => $ukm->markings()->where('status', 'cancelled')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get UKM list for API.
     */
    public function apiIndex(Request $request)
    {
        $this->authorize('viewAny', Ukm::class);

        $query = Ukm::query();

        // Filter by active status
        if ($request->filled('active_only') && $request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $ukm = $query->orderBy('nama')->get();

        return response()->json($ukm);
    }

    /**
     * Get UKM details for API.
     */
    public function apiShow(Ukm $ukm)
    {
        $this->authorize('view', $ukm);

        return response()->json([
            'id' => $ukm->id,
            'nama' => $ukm->nama,
            'deskripsi' => $ukm->deskripsi,
            'is_active' => $ukm->is_active,
            'total_markings' => $ukm->getTotalMarkingsCount(),
            'active_markings' => $ukm->getActiveMarkingsCount(),
            'created_at' => $ukm->created_at,
            'updated_at' => $ukm->updated_at,
        ]);
    }
}
