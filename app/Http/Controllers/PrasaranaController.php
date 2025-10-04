<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrasaranaRequest;
use App\Http\Requests\UpdatePrasaranaRequest;
use App\Models\KategoriPrasarana;
use App\Models\Prasarana;
use App\Models\PrasaranaImage;
use App\Services\PrasaranaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrasaranaController extends Controller
{
    public function __construct(private PrasaranaService $service)
    {
        $this->middleware(['auth', 'user.not.blocked', 'profile.completed']);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Prasarana::class);
        $filters = $request->only(['search', 'kategori_id', 'status']);
        $items = $this->service->list($filters, 15);
        $kategories = KategoriPrasarana::orderBy('name')->get();
        return view('prasarana.index', compact('items', 'kategories', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Prasarana::class);
        $kategories = KategoriPrasarana::orderBy('name')->get();
        return view('prasarana.create', compact('kategories'));
    }

    public function store(StorePrasaranaRequest $request): RedirectResponse
    {
        $this->authorize('create', Prasarana::class);
        $data = $request->validated();
        $images = $request->file('images', []);
        $this->service->create($data, $request->user()->id, $images);
        return redirect()->route('prasarana.index')->with('success', 'Prasarana berhasil dibuat');
    }

    public function show(Prasarana $prasarana): View
    {
        $this->authorize('view', $prasarana);
        $prasarana->load(['kategori', 'images']);
        return view('prasarana.show', compact('prasarana'));
    }

    public function edit(Prasarana $prasarana): View
    {
        $this->authorize('update', $prasarana);
        $kategories = KategoriPrasarana::orderBy('name')->get();
        $prasarana->load(['images']);
        return view('prasarana.edit', compact('prasarana', 'kategories'));
    }

    public function update(UpdatePrasaranaRequest $request, Prasarana $prasarana): RedirectResponse
    {
        $this->authorize('update', $prasarana);
        $data = $request->validated();
        $images = $request->file('images', []);
        $this->service->update($prasarana, $data, $images);
        return redirect()->route('prasarana.show', $prasarana)->with('success', 'Prasarana berhasil diupdate');
    }

    public function destroy(Prasarana $prasarana): RedirectResponse
    {
        $this->authorize('delete', $prasarana);
        $this->service->delete($prasarana);
        return redirect()->route('prasarana.index')->with('success', 'Prasarana berhasil dihapus');
    }

    public function destroyImage(PrasaranaImage $image): RedirectResponse
    {
        $prasarana = $image->prasarana;
        $this->authorize('update', $prasarana);
        $this->service->deleteImage($image);
        return redirect()->route('prasarana.edit', $prasarana)->with('success', 'Gambar dihapus');
    }
}



