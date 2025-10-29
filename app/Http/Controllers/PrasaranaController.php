<?php

namespace App\Http\Controllers;

use App\Models\Prasarana;
use App\Models\KategoriPrasarana;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrasaranaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:sarpras.view')->only(['index', 'show']);
        $this->middleware('permission:sarpras.create')->only(['create', 'store']);
        $this->middleware('permission:sarpras.edit')->only(['edit', 'update']);
        $this->middleware('permission:sarpras.delete')->only(['destroy']);
        $this->middleware('permission:sarpras.status_update')->only(['updateStatus']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Prasarana::with(['kategori', 'images'])
            ->withCount('peminjaman');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Location filter
        if ($request->filled('lokasi')) {
            $query->where('lokasi', 'like', "%{$request->lokasi}%");
        }

        $prasarana = $query->orderBy('created_at', 'desc')->paginate(15);
        $kategoriPrasarana = KategoriPrasarana::where('is_active', true)->orderBy('name')->get();

        return view('prasarana.index', compact('prasarana', 'kategoriPrasarana'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoriPrasarana = KategoriPrasarana::where('is_active', true)->orderBy('name')->get();
        return view('prasarana.create', compact('kategoriPrasarana'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_prasarana,id',
            'description' => 'nullable|string',
            'lokasi' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer|min:1',
            'status' => 'required|in:tersedia,rusak,maintenance',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ], [
            'name.required' => 'Nama prasarana harus diisi.',
            'kategori_id.required' => 'Kategori harus dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'lokasi.required' => 'Lokasi harus diisi.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'kapasitas.integer' => 'Kapasitas harus berupa angka.',
            'kapasitas.min' => 'Kapasitas minimal 1 orang.',
            'images.*.image' => 'File harus berupa gambar.',
            'images.*.mimes' => 'Gambar harus berformat JPEG, PNG, JPG, atau GIF.',
            'images.*.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        try {
            DB::beginTransaction();

            $prasarana = Prasarana::create([
                'name' => $request->name,
                'kategori_id' => $request->kategori_id,
                'description' => $request->description,
                'lokasi' => $request->lokasi,
                'kapasitas' => $request->kapasitas,
                'status' => $request->status,
                'created_by' => auth()->id(),
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $this->handleImageUploads($request->file('images'), $prasarana);
            }

            DB::commit();

            return redirect()->route('prasarana.index')
                ->with('success', 'Prasarana berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan prasarana: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Prasarana $prasarana)
    {
        $prasarana->load(['kategori', 'images', 'createdBy', 'approvers.approver']);
        
        // Users untuk pemilihan approver (role yang diizinkan)
        $users = \App\Models\User::where('status', 'active')
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'approver', 'global_approver', 'specific_approver']);
            })
            ->orderBy('name')
            ->get();
        
        // Get recent peminjaman history
        $peminjamanHistory = Peminjaman::with(['user'])
            ->where('prasarana_id', $prasarana->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('prasarana.show', compact('prasarana', 'peminjamanHistory', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prasarana $prasarana)
    {
        $prasarana->load(['kategori', 'images']);
        $kategoriPrasarana = KategoriPrasarana::where('is_active', true)->orderBy('name')->get();
        
        return view('prasarana.edit', compact('prasarana', 'kategoriPrasarana'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prasarana $prasarana)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_prasarana,id',
            'description' => 'nullable|string',
            'lokasi' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer|min:1',
            'status' => 'required|in:tersedia,rusak,maintenance',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ], [
            'name.required' => 'Nama prasarana harus diisi.',
            'kategori_id.required' => 'Kategori harus dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'lokasi.required' => 'Lokasi harus diisi.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'kapasitas.integer' => 'Kapasitas harus berupa angka.',
            'kapasitas.min' => 'Kapasitas minimal 1 orang.',
            'images.*.image' => 'File harus berupa gambar.',
            'images.*.mimes' => 'Gambar harus berformat JPEG, PNG, JPG, atau GIF.',
            'images.*.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        try {
            DB::beginTransaction();

            $prasarana->update([
                'name' => $request->name,
                'kategori_id' => $request->kategori_id,
                'description' => $request->description,
                'lokasi' => $request->lokasi,
                'kapasitas' => $request->kapasitas,
                'status' => $request->status,
            ]);

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $this->handleImageUploads($request->file('images'), $prasarana);
            }

            DB::commit();

            return redirect()->route('prasarana.show', $prasarana)
                ->with('success', 'Prasarana berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui prasarana: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prasarana $prasarana)
    {
        try {
            DB::beginTransaction();

            // Check if prasarana has active peminjaman
            $activePeminjaman = $prasarana->peminjaman()
                ->whereIn('status', ['pending', 'approved', 'picked_up'])
                ->exists();

            if ($activePeminjaman) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus prasarana yang memiliki peminjaman aktif.');
            }

            // Delete images
            foreach ($prasarana->images as $image) {
                Storage::disk('public')->delete($image->image_url);
                $image->delete();
            }

            // Hard delete prasarana
            $prasarana->forceDelete();

            DB::commit();

            return redirect()->route('prasarana.index')
                ->with('success', 'Prasarana berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus prasarana: ' . $e->getMessage());
        }
    }

    /**
     * Update prasarana status
     */
    public function updateStatus(Request $request, Prasarana $prasarana)
    {
        $request->validate([
            'status' => 'required|in:tersedia,rusak,maintenance',
        ], [
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        try {
            $prasarana->update(['status' => $request->status]);

            return redirect()->back()
                ->with('success', 'Status prasarana berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Remove prasarana image
     */
    public function destroyImage($imageId)
    {
        try {
            $image = \App\Models\PrasaranaImage::findOrFail($imageId);
            
            // Check if user has permission to edit this prasarana
            if (!auth()->user()->can('sarpras.edit')) {
                abort(403, 'Anda tidak memiliki izin untuk menghapus gambar.');
            }

            Storage::disk('public')->delete($image->image_url);
            $image->delete();

            return redirect()->back()
                ->with('success', 'Gambar berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus gambar: ' . $e->getMessage());
        }
    }

    /**
     * Handle image uploads
     */
    private function handleImageUploads($images, $prasarana)
    {
        foreach ($images as $image) {
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('prasarana/images', $filename, 'public');

            $prasarana->images()->create([
                'image_url' => $path,
                'sort_order' => $prasarana->images()->count() + 1,
            ]);
        }
    }
}