<?php

namespace App\Http\Controllers;

use App\Models\KategoriPrasarana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriPrasaranaController extends Controller
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
        $query = KategoriPrasarana::withCount('prasarana');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $kategoriPrasarana = $query->orderBy('name')->paginate(15);

        return view('kategori-prasarana.index', compact('kategoriPrasarana'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kategori-prasarana.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:kategori_prasarana,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        KategoriPrasarana::create($request->all());

        return redirect()->route('kategori-prasarana.index')
            ->with('success', 'Kategori prasarana berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KategoriPrasarana $kategoriPrasarana)
    {
        $kategoriPrasarana->loadCount('prasarana');
        $kategoriPrasarana->load(['prasarana' => function($query) {
            $query->with(['createdBy'])->orderBy('name');
        }]);

        return view('kategori-prasarana.show', compact('kategoriPrasarana'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriPrasarana $kategoriPrasarana)
    {
        return view('kategori-prasarana.edit', compact('kategoriPrasarana'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KategoriPrasarana $kategoriPrasarana)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:kategori_prasarana,name,' . $kategoriPrasarana->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $kategoriPrasarana->update($request->all());

        return redirect()->route('kategori-prasarana.show', $kategoriPrasarana->id)
            ->with('success', 'Kategori prasarana berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriPrasarana $kategoriPrasarana)
    {
        // Check if kategori is being used by prasarana
        if ($kategoriPrasarana->prasarana()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh prasarana.');
        }

        $kategoriPrasarana->delete();

        return redirect()->route('kategori-prasarana.index')
            ->with('success', 'Kategori prasarana berhasil dihapus.');
    }
}
