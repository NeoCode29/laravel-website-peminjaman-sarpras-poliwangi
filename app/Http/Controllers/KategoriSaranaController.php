<?php

namespace App\Http\Controllers;

use App\Models\KategoriSarana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriSaranaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:sarpras.view')->only(['index', 'show']);
        $this->middleware('permission:sarpras.create')->only(['create', 'store']);
        $this->middleware('permission:sarpras.edit')->only(['edit', 'update']);
        $this->middleware('permission:sarpras.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KategoriSarana::withCount('sarana');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $kategoriSarana = $query->orderBy('name')->paginate(15);

        return view('kategori-sarana.index', compact('kategoriSarana'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kategori-sarana.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:kategori_sarana,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        KategoriSarana::create($data);

        return redirect()->route('kategori-sarana.index')
            ->with('success', 'Kategori sarana berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KategoriSarana $kategoriSarana)
    {
        $sarana = $kategoriSarana->sarana()
            ->with(['creator', 'kategori'])
            ->orderBy('name')
            ->paginate(15);

        return view('kategori-sarana.show', compact('kategoriSarana', 'sarana'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriSarana $kategoriSarana)
    {
        return view('kategori-sarana.edit', compact('kategoriSarana'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KategoriSarana $kategoriSarana)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:kategori_sarana,name,' . $kategoriSarana->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $kategoriSarana->update($data);

        return redirect()->route('kategori-sarana.show', $kategoriSarana->id)
            ->with('success', 'Kategori sarana berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriSarana $kategoriSarana)
    {
        // Check if kategori is being used by sarana
        if ($kategoriSarana->sarana()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh sarana.');
        }

        $kategoriSarana->delete();

        return redirect()->route('kategori-sarana.index')
            ->with('success', 'Kategori sarana berhasil dihapus.');
    }
}
