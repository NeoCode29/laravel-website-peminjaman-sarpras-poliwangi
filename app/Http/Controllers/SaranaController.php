<?php

namespace App\Http\Controllers;

use App\Models\Sarana;
use App\Models\KategoriSarana;
use App\Models\SaranaUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SaranaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:sarpras.view')->only(['index', 'show']);
        $this->middleware('permission:sarpras.create')->only(['create', 'store']);
        $this->middleware('permission:sarpras.edit')->only(['edit', 'update']);
        $this->middleware('permission:sarpras.delete')->only(['destroy']);
        $this->middleware('permission:sarpras.unit_manage')->only(['manageUnits', 'storeUnit', 'updateUnit', 'destroyUnit']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sarana::with(['kategori', 'creator']);

        // Filter berdasarkan kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter berdasarkan tipe
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter berdasarkan status ketersediaan
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'tersedia':
                    $query->where('jumlah_tersedia', '>', 0);
                    break;
                case 'kosong':
                    $query->where('jumlah_tersedia', 0);
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $sarana = $query->paginate(15);
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategori_sarana,id',
            'type' => 'required|in:serialized,pooled',
            'jumlah_total' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'lokasi' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['created_by'] = Auth::id();

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sarana', 'public');
            $data['image_url'] = $imagePath;
        }

        // Set default values untuk statistik
        $data['jumlah_tersedia'] = $data['jumlah_total'];
        $data['jumlah_rusak'] = 0;
        $data['jumlah_maintenance'] = 0;
        $data['jumlah_hilang'] = 0;

        $sarana = Sarana::create($data);

        return redirect()->route('sarana.show', $sarana->id)
            ->with('success', 'Sarana berhasil dibuat.');
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
    public function update(Request $request, Sarana $sarana)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategori_sarana,id',
            'type' => 'required|in:serialized,pooled',
            'jumlah_total' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'lokasi' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($sarana->image_url) {
                Storage::disk('public')->delete($sarana->image_url);
            }
            
            $imagePath = $request->file('image')->store('sarana', 'public');
            $data['image_url'] = $imagePath;
        }

        // Validasi jumlah_total untuk sarana serialized
        if ($data['type'] === 'serialized') {
            $existingUnits = $sarana->units()->count();
            if ($data['jumlah_total'] < $existingUnits) {
                return redirect()->back()
                    ->withErrors(['jumlah_total' => 'Jumlah total tidak boleh lebih kecil dari jumlah unit yang sudah terdaftar.'])
                    ->withInput();
            }
        }

        $sarana->update($data);

        // Update statistik
        $sarana->updateStats();

        return redirect()->route('sarana.show', $sarana->id)
            ->with('success', 'Sarana berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sarana $sarana)
    {
        // Check if sarana is being used in peminjaman
        $isUsed = \DB::table('peminjaman_items')
            ->where('sarana_id', $sarana->id)
            ->exists();

        if ($isUsed) {
            return redirect()->back()
                ->with('error', 'Sarana tidak dapat dihapus karena sedang digunakan dalam peminjaman.');
        }

        // Delete image
        if ($sarana->image_url) {
            Storage::disk('public')->delete($sarana->image_url);
        }

        $sarana->delete();

        return redirect()->route('sarana.index')
            ->with('success', 'Sarana berhasil dihapus.');
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
        if ($sarana->type !== 'serialized') {
            return redirect()->back()
                ->with('error', 'Hanya sarana bertipe serialized yang dapat dikelola unitnya.');
        }

        $validator = Validator::make($request->all(), [
            'unit_code' => 'required|string|max:80',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if unit_code already exists for this sarana
        $existingUnit = $sarana->units()
            ->where('unit_code', $request->unit_code)
            ->exists();

        if ($existingUnit) {
            return redirect()->back()
                ->withErrors(['unit_code' => 'Unit code sudah ada untuk sarana ini.'])
                ->withInput();
        }

        // Check if adding this unit would exceed jumlah_total
        $currentUnits = $sarana->units()->count();
        if ($currentUnits >= $sarana->jumlah_total) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menambah unit karena sudah mencapai batas maksimal.');
        }

        SaranaUnit::create([
            'sarana_id' => $sarana->id,
            'unit_code' => $request->unit_code,
            'unit_status' => 'tersedia',
        ]);

        // Update sarana stats
        $sarana->updateStats();

        return redirect()->back()
            ->with('success', 'Unit berhasil ditambahkan.');
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

        $unit->updateStatus($request->unit_status);

        return redirect()->back()
            ->with('success', 'Status unit berhasil diperbarui.');
    }

    /**
     * Delete unit
     */
    public function destroyUnit(SaranaUnit $unit)
    {
        // Check if unit is being used in peminjaman
        $isUsed = \DB::table('peminjaman_item_units')
            ->where('unit_id', $unit->id)
            ->exists();

        if ($isUsed) {
            return redirect()->back()
                ->with('error', 'Unit tidak dapat dihapus karena sedang digunakan dalam peminjaman.');
        }

        $sarana = $unit->sarana;
        $unit->delete();

        // Update sarana stats
        $sarana->updateStats();

        return redirect()->back()
            ->with('success', 'Unit berhasil dihapus.');
    }
}
