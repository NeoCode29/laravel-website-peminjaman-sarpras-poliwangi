<?php

namespace App\Http\Controllers;

use App\Models\Marking;
use App\Models\MarkingItem;
use App\Models\Ukm;
use App\Models\Prasarana;
use App\Models\Sarana;
use App\Models\SystemSetting;
use App\Services\MarkingService;
use App\Http\Requests\MarkingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MarkingController extends Controller
{
    /**
     * The marking service instance.
     *
     * @var \App\Services\MarkingService
     */
    protected $markingService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\MarkingService  $markingService
     * @return void
     */
    public function __construct(MarkingService $markingService)
    {
        $this->markingService = $markingService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Marking::class);

        $query = Marking::with(['user', 'ukm', 'prasarana', 'items.sarana']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user (for non-admin users)
        if (!Auth::user()->hasPermissionTo('peminjaman.view')) {
            $query->where('user_id', Auth::id());
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('start_datetime', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_datetime', '<=', $request->end_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                  ->orWhere('lokasi_custom', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ukm', function ($ukmQuery) use ($search) {
                      $ukmQuery->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $markings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('marking.index', compact('markings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Marking::class);

        $ukms = Ukm::where('is_active', true)->get();
        $prasaranas = Prasarana::where('status', 'tersedia')->get();
        $saranas = Sarana::where('jumlah_tersedia', '>', 0)->get();
        $markingDuration = SystemSetting::getValue('marking_duration_days', 3);

        return view('marking.create', compact('ukms', 'prasaranas', 'saranas', 'markingDuration'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MarkingRequest $request)
    {
        $this->authorize('create', Marking::class);

        // Validation is handled by MarkingRequest

        // Check for conflicts
        $conflict = $this->markingService->checkConflicts($request->all());
        if ($conflict) {
            return redirect()->back()
                ->with('error', $conflict)
                ->withInput();
        }

        try {
            // Create marking using service
            $marking = $this->markingService->createMarking($request->all());
            
            return redirect()->route('marking.show', $marking)
                ->with('success', 'Marking berhasil dibuat. Marking akan kadaluarsa pada ' . $marking->expires_at->format('d/m/Y H:i') . '.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat marking.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Marking $marking)
    {
        $this->authorize('view', $marking);

        $marking->load(['user', 'ukm', 'prasarana', 'items.sarana']);

        return view('marking.show', compact('marking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marking $marking)
    {
        $this->authorize('update', $marking);

        // Only allow editing if status is active and not expired
        if ($marking->status !== 'active' || $marking->isExpired()) {
            return redirect()->route('marking.show', $marking)
                ->with('error', 'Marking tidak dapat diedit karena sudah kadaluarsa atau tidak aktif.');
        }

        $ukms = Ukm::where('is_active', true)->get();
        $prasaranas = Prasarana::where('status', 'tersedia')->get();
        $saranas = Sarana::where('jumlah_tersedia', '>', 0)->get();

        return view('marking.edit', compact('marking', 'ukms', 'prasaranas', 'saranas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MarkingRequest $request, Marking $marking)
    {
        $this->authorize('update', $marking);

        // Only allow updating if status is active and not expired
        if ($marking->status !== Marking::STATUS_ACTIVE || $marking->isExpired()) {
            return redirect()->route('marking.show', $marking)
                ->with('error', 'Marking tidak dapat diedit karena sudah kadaluarsa atau tidak aktif.');
        }

        // Validation is handled by MarkingRequest

        // Check for conflicts (excluding current marking)
        $conflict = $this->markingService->checkConflicts($request->all(), $marking->id);
        if ($conflict) {
            return redirect()->back()
                ->with('error', $conflict)
                ->withInput();
        }

        try {
            // Update marking using service
            $marking = $this->markingService->updateMarking($marking, $request->all());

            return redirect()->route('marking.show', $marking)
                ->with('success', 'Marking berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui marking.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marking $marking)
    {
        $this->authorize('delete', $marking);

        // Only allow deleting if status is active
        if ($marking->status !== 'active') {
            return redirect()->route('marking.show', $marking)
                ->with('error', 'Marking tidak dapat dihapus karena sudah tidak aktif.');
        }

        try {
            // Cancel marking using service
            $this->markingService->cancelMarking($marking);

            return redirect()->route('marking.index')
                ->with('success', 'Marking berhasil dibatalkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membatalkan marking.');
        }
    }

    /**
     * Convert marking to peminjaman.
     */
    public function convertToPeminjaman(Marking $marking)
    {
        $this->authorize('update', $marking);

        // Only allow converting if status is active and not expired
        if ($marking->status !== Marking::STATUS_ACTIVE || $marking->isExpired()) {
            return redirect()->route('marking.show', $marking)
                ->with('error', 'Marking tidak dapat dikonversi karena sudah kadaluarsa atau tidak aktif.');
        }

        // Redirect to peminjaman create with marking data
        return redirect()->route('peminjaman.create')
            ->with('marking_data', $marking->toArray());
    }

    /**
     * Extend marking expiration.
     */
    public function extend(Request $request, Marking $marking)
    {
        $this->authorize('update', $marking);

        // Only allow extending if status is active and not expired
        if ($marking->status !== 'active' || $marking->isExpired()) {
            return redirect()->route('marking.show', $marking)
                ->with('error', 'Marking tidak dapat diperpanjang karena sudah kadaluarsa atau tidak aktif.');
        }

        $validator = Validator::make($request->all(), [
            'extension_days' => 'required|integer|min:1|max:7',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            // Extend marking using service
            $marking = $this->markingService->extendMarking($marking, $request->extension_days);
            
            return redirect()->route('marking.show', $marking)
                ->with('success', "Marking berhasil diperpanjang hingga {$marking->expires_at->format('d/m/Y H:i')}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperpanjang marking.');
        }
    }

}
