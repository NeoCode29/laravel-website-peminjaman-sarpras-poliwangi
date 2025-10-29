<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\PeminjamanApprovalWorkflow;
use App\Models\PeminjamanApprovalStatus;
use App\Models\PeminjamanItem;
use App\Models\PeminjamanItemUnit;
use App\Models\AuditLog;
use App\Models\PrasaranaApprover;
use App\Models\Prasarana;
use App\Models\Sarana;
use App\Models\SaranaUnit;
use App\Models\SaranaApprover;
use App\Models\SystemSetting;
use App\Models\GlobalApprover;
use App\Models\User;
use App\Models\Ukm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Services\NotificationService;
use App\Services\UserQuotaService;
use App\Services\SlotConflictService;
use App\Services\PickupReturnService;

class PeminjamanController extends Controller
{
    /**
     * Store uploaded file to public disk under a dated directory and return relative path.
     */
    private function storePublicFile(\Illuminate\Http\UploadedFile $file, string $baseDir): string
    {
        $dir = trim($baseDir, '/').'/'.date('Y/m');
        return $file->store($dir, 'public');
    }

    /**
     * Delete a file from public disk if exists.
     */
    private function deletePublicFile(?string $path): void
    {
        if (!empty($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
    }

    private function syncKonflikGroup(Peminjaman $peminjaman, $pendingConflicts): void
    {
        $pendingConflicts = collect($pendingConflicts);

        if ($pendingConflicts->isEmpty()) {
            if (!empty($peminjaman->konflik)) {
                $remaining = Peminjaman::where('konflik', $peminjaman->konflik)
                    ->where('id', '!=', $peminjaman->id)
                    ->exists();

                if (!$remaining) {
                    $peminjaman->forceFill(['konflik' => null])->save();
                }
            }
            return;
        }

        $existingKonflik = $pendingConflicts->first(function ($conflict) {
            return !empty($conflict->konflik);
        })?->konflik;

        $konflikCode = $existingKonflik ?: 'KNF-'.strtoupper(Str::random(10));

        $ids = $pendingConflicts->pluck('id')->push($peminjaman->id);

        Peminjaman::whereIn('id', $ids)->update(['konflik' => $konflikCode]);
    }

    private function buildDisplayStatus(Peminjaman $peminjaman, string $globalStatus, string $overallStatus): array
    {
        $currentStatus = $peminjaman->status ?? 'pending';
        $label = ucfirst(str_replace('_', ' ', $currentStatus));
        $class = 'status-' . $currentStatus;

        if ($globalStatus === 'approved' && $overallStatus === 'pending') {
            return [
                'label' => 'Disetujui Global',
                'class' => 'status-approved',
            ];
        }

        if ($globalStatus === 'rejected' && $overallStatus === 'pending') {
            return [
                'label' => 'Ditolak Global',
                'class' => 'status-rejected',
            ];
        }

        return [
            'label' => $label,
            'class' => $class,
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Peminjaman::class);

        $query = Peminjaman::with(['user', 'prasarana', 'items.sarana', 'approvalStatus'])
            ->withCount([
                'approvalWorkflow as override_events_count' => function ($q) {
                    $q->whereNotNull('overridden_at');
                }
            ]);

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
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('prasarana', function ($prasaranaQuery) use ($search) {
                      $prasaranaQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $peminjaman = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('peminjaman.index', compact('peminjaman'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Peminjaman::class);

        $prasarana = Prasarana::where('status', 'tersedia')->get();
        
        // Get all sarana and update their stats first
        $allSarana = Sarana::all();
        foreach ($allSarana as $s) {
            $s->updateStats();
        }
        
        // Then get sarana with available quantity
        $sarana = Sarana::where('jumlah_tersedia', '>', 0)->get();
        
        // If no sarana available, get all sarana for debugging
        if ($sarana->isEmpty()) {
            $sarana = Sarana::all();
            \Log::warning('No sarana with available quantity found. Showing all sarana for debugging.', [
                'total_sarana' => $sarana->count(),
                'sarana_details' => $sarana->map(function($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'type' => $s->type,
                        'jumlah_total' => $s->jumlah_total,
                        'jumlah_tersedia' => $s->jumlah_tersedia,
                        'jumlah_rusak' => $s->jumlah_rusak,
                        'jumlah_maintenance' => $s->jumlah_maintenance,
                        'jumlah_hilang' => $s->jumlah_hilang,
                    ];
                })
            ]);
        }
        $ukms = [];
        if (Auth::user()->user_type === 'mahasiswa') {
            $ukms = Ukm::orderBy('nama')->get(['id','nama']);
        }
        $maxDuration = SystemSetting::getValue('max_duration_days', 7);
        $maxActiveBorrowings = SystemSetting::getValue('max_active_borrowings', 3);

        // Check user's current active borrowings
        $currentBorrowings = Peminjaman::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'picked_up'])
            ->count();

        return view('peminjaman.create', compact('prasarana', 'sarana', 'ukms', 'maxDuration', 'maxActiveBorrowings', 'currentBorrowings'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Peminjaman::class);

        $rules = [
            'event_name' => 'required|string|max:255',
            'loan_type' => 'required|in:sarana,prasarana,both',
            'prasarana_id' => 'nullable|exists:prasarana,id',
            'lokasi_custom' => 'nullable|string|max:150',
            'jumlah_peserta' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'surat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'sarana_items' => 'nullable|array',
            'sarana_items.*.sarana_id' => 'required_with:sarana_items|distinct|exists:sarana,id',
            'sarana_items.*.qty_requested' => 'required_with:sarana_items|integer|min:1',
            'sarana_items.*.notes' => 'nullable|string|max:500',
        ];

        if (Auth::user()->user_type === 'mahasiswa') {
            $rules['ukm_id'] = 'required|exists:ukm,id';
        } else {
            $rules['ukm_id'] = 'nullable|exists:ukm,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'ukm_id.required' => 'UKM wajib dipilih untuk pengajuan oleh mahasiswa.',
            'ukm_id.exists' => 'UKM tidak valid.',
        ]);

        // Custom validation: prasarana_id XOR lokasi_custom
        $validator->after(function ($validator) use ($request) {
            $this->validateLoanTypeSelection($request, $validator);
            if (in_array($request->input('loan_type'), ['prasarana', 'both'], true) && empty($request->input('jumlah_peserta'))) {
                $validator->errors()->add('jumlah_peserta', 'Jumlah peserta wajib diisi untuk peminjaman prasarana.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $this->normalizeBorrowingPayload($request, $validator->validated());
        $request->merge([
            'loan_type' => $validated['loan_type'],
            'prasarana_id' => $validated['prasarana_id'],
            'lokasi_custom' => $validated['lokasi_custom'],
            'sarana_items' => $validated['sarana_items'],
            'jumlah_peserta' => $validated['jumlah_peserta'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        // Check user's borrowing quota
        $maxActiveBorrowings = SystemSetting::getValue('max_active_borrowings', 3);
        $currentBorrowings = Peminjaman::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'picked_up'])
            ->count();

        if ($currentBorrowings >= $maxActiveBorrowings) {
            return redirect()->back()
                ->with('error', "Kuota peminjaman aktif telah tercapai (maksimal {$maxActiveBorrowings})")
                ->withInput();
        }

        // Check duration limit
        $maxDuration = SystemSetting::getValue('max_duration_days', 7);
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $duration = $startDate->diffInDays($endDate) + 1;

        if ($duration > $maxDuration) {
            return redirect()->back()
                ->with('error', "Durasi peminjaman tidak boleh lebih dari {$maxDuration} hari")
                ->withInput();
        }

        if (!empty($validated['prasarana_id']) && !empty($validated['jumlah_peserta'])) {
            $selectedPrasarana = Prasarana::find($validated['prasarana_id']);
            if ($selectedPrasarana && $selectedPrasarana->kapasitas !== null) {
                $kapasitas = (int) $selectedPrasarana->kapasitas;
                $peserta = (int) $validated['jumlah_peserta'];
                if ($kapasitas > 0 && $peserta > $kapasitas) {
                    return redirect()->back()
                        ->with('error', "Jumlah peserta ({$peserta}) melebihi kapasitas prasarana {$selectedPrasarana->name} ({$kapasitas}).")
                        ->withInput();
                }
            }
        }

        // Check for conflicts via service
        $slotConflictService = app(SlotConflictService::class);
        $conflict = $slotConflictService->checkConflicts($request);
        if ($conflict) {
            if ($lastConflict = $slotConflictService->getLastConflict()) {
                app(NotificationService::class)->notifyPriorityConflict(
                    $lastConflict['peminjaman'],
                    null,
                    $lastConflict['type']
                );
            }
            return redirect()->back()
                ->with('error', $conflict)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Upload surat pengajuan → simpan ke peminjaman/surat/YYYY/MM
            $suratPath = $this->storePublicFile($request->file('surat'), 'peminjaman/surat');

            // Create peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => Auth::id(),
                'prasarana_id' => $validated['prasarana_id'],
                'lokasi_custom' => $validated['lokasi_custom'],
                'jumlah_peserta' => $validated['jumlah_peserta'] ?? null,
                'ukm_id' => $validated['ukm_id'] ?? null,
                'event_name' => $validated['event_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'status' => 'pending',
                'surat_path' => $suratPath,
            ]);

            // Create peminjaman items
            if (!empty($validated['sarana_items'])) {
                foreach ($validated['sarana_items'] as $item) {
                    PeminjamanItem::create([
                        'peminjaman_id' => $peminjaman->id,
                        'sarana_id' => $item['sarana_id'],
                        'qty_requested' => $item['qty_requested'],
                        'qty_approved' => null,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            // Initialize approval status (overall pending, global pending)
            PeminjamanApprovalStatus::firstOrCreate([
                'peminjaman_id' => $peminjaman->id,
            ], [
                'overall_status' => 'pending',
                'global_approval_status' => 'pending',
            ]);

            // Create approval workflow entries
            // 1) Global approvers
            $globalApprovers = GlobalApprover::active()->get();
            foreach ($globalApprovers as $ga) {
                PeminjamanApprovalWorkflow::firstOrCreate([
                    'peminjaman_id' => $peminjaman->id,
                    'approver_id' => $ga->approver_id,
                    'approval_type' => 'global',
                    'sarana_id' => null,
                    'prasarana_id' => null,
                ], [
                    'approval_level' => $ga->approval_level,
                    'status' => 'pending',
                ]);
            }

            // 2) Specific prasarana approvers (if any prasarana selected)
            if ($peminjaman->prasarana_id) {
                $prasaranaApprovers = PrasaranaApprover::active()
                    ->forPrasarana($peminjaman->prasarana_id)
                    ->get();
                foreach ($prasaranaApprovers as $pa) {
                    PeminjamanApprovalWorkflow::firstOrCreate([
                        'peminjaman_id' => $peminjaman->id,
                        'approver_id' => $pa->approver_id,
                        'approval_type' => 'prasarana',
                        'sarana_id' => null,
                        'prasarana_id' => $peminjaman->prasarana_id,
                    ], [
                        'approval_level' => $pa->approval_level,
                        'status' => 'pending',
                    ]);
                }
            }

            // 3) Specific sarana approvers for each requested sarana
            if ($request->filled('sarana_items')) {
                $saranaIds = collect($request->sarana_items)->pluck('sarana_id')->unique();
                foreach ($saranaIds as $saranaId) {
                    $sApprovers = SaranaApprover::active()->forSarana($saranaId)->get();
                    foreach ($sApprovers as $sa) {
                        PeminjamanApprovalWorkflow::firstOrCreate([
                            'peminjaman_id' => $peminjaman->id,
                            'approver_id' => $sa->approver_id,
                            'approval_type' => 'sarana',
                            'sarana_id' => $saranaId,
                            'prasarana_id' => null,
                        ], [
                            'approval_level' => $sa->approval_level,
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            $pendingConflicts = $slotConflictService->findPendingConflicts($peminjaman);
            $this->syncKonflikGroup($peminjaman, $pendingConflicts);

            DB::commit();

            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('success', 'Pengajuan peminjaman berhasil dibuat dan sedang menunggu persetujuan.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if (isset($suratPath)) {
                Storage::disk('public')->delete($suratPath);
            }

            \Log::error('Failed to create peminjaman', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat pengajuan peminjaman.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Peminjaman $peminjaman)
    {
        $this->authorize('view', $peminjaman);

        $peminjaman->load([
            'user',
            'prasarana',
            'items.sarana.units',
            'items.units.unit',
            'approvalStatus',
            'approvalWorkflow.approver',
            'approvalWorkflow.overriddenBy',
            'itemUnits.unit'
        ]);

        $overrideApprovals = $peminjaman->approvalWorkflow
            ->whereNotNull('overridden_at')
            ->sortByDesc('overridden_at')
            ->values();

        $konflikMembers = collect();
        if (!empty($peminjaman->konflik)) {
            $konflikMembers = Peminjaman::with('user')
                ->where('konflik', $peminjaman->konflik)
                ->where('id', '!=', $peminjaman->id)
                ->orderBy('created_at')
                ->get();
        }

        $pickupValidatorName = $peminjaman->pickup_validated_by
            ? optional(User::find($peminjaman->pickup_validated_by))->name
            : null;
        $returnValidatorName = $peminjaman->return_validated_by
            ? optional(User::find($peminjaman->return_validated_by))->name
            : null;

        $prasaranaApprovalSummary = null;
        $approvalStatusModel = $peminjaman->approvalStatus;
        $globalStatus = optional($approvalStatusModel)->global_approval_status ?? 'pending';
        $overallStatus = optional($approvalStatusModel)->overall_status ?? 'pending';
        $statusBadge = $this->buildDisplayStatus($peminjaman, $globalStatus, $overallStatus);
        $isGlobalApproved = $globalStatus === 'approved';

        if ($peminjaman->prasarana_id) {
            $prasaranaApprovals = $peminjaman->approvalWorkflow
                ->where('approval_type', 'prasarana')
                ->where('prasarana_id', $peminjaman->prasarana_id);
            $prasaranaApprovalSummary = $this->buildApprovalSummary($prasaranaApprovals, $globalStatus);
        }

        $saranaApprovals = $peminjaman->approvalWorkflow
            ->where('approval_type', 'sarana')
            ->groupBy('sarana_id');

        $peminjaman->items->each(function ($item) use ($saranaApprovals, $globalStatus) {
            $itemApprovals = $saranaApprovals->get($item->sarana_id, collect());
            $item->approval_summary = $this->buildApprovalSummary($itemApprovals, $globalStatus);
        });

        $serializedUnitOptions = [];
        if (
            $peminjaman->status === Peminjaman::STATUS_APPROVED &&
            (Auth::user()?->can('peminjaman.adjust_sarpras') ?? false)
        ) {
            $serializedUnitOptions = $peminjaman->items
                ->filter(fn ($item) => optional($item->sarana)->type === 'serialized')
                ->mapWithKeys(function ($item) use ($peminjaman) {
                    $activeAssignments = $item->units->where('status', 'active');
                    $assignedUnits = $activeAssignments->pluck('unit_id');

                    $unitCollection = SaranaUnit::where('sarana_id', $item->sarana_id)
                        ->orderBy('unit_code')
                        ->get();

                    $conflictingUnitIds = $this->getConflictingUnitIdsForItem($item, $unitCollection->pluck('id'));

                    $units = $unitCollection
                        ->filter(function ($unit) use ($assignedUnits, $conflictingUnitIds) {
                            return $assignedUnits->contains($unit->id) || !$conflictingUnitIds->contains($unit->id);
                        })
                        ->map(function ($unit) use ($assignedUnits, $conflictingUnitIds) {
                            $isAssignedHere = $assignedUnits->contains($unit->id);
                            $isConflict = $conflictingUnitIds->contains($unit->id);
                            $isAvailable = !$isConflict && $unit->unit_status === 'tersedia';

                            return [
                                'id' => $unit->id,
                                'unit_code' => $unit->unit_code,
                                'status' => $unit->unit_status,
                                'is_assigned_to_this' => $isAssignedHere,
                                'is_available' => $isAvailable,
                            ];
                        })
                        ->values();

                    return [$item->id => [
                        'max_selectable' => $item->qty_approved ?? $item->qty_requested,
                        'requested' => $item->qty_requested,
                        'approved' => $item->qty_approved,
                        'units' => $units->toArray(),
                        'selected_count' => $assignedUnits->count(),
                    ]];
                })
                ->toArray();
        }

        $approvalActionSummary = [
            'has_pending' => $peminjaman->approvalWorkflow->where('status', 'pending')->isNotEmpty(),
            'global' => [],
            'prasarana' => [],
            'sarana' => [],
        ];

        $peminjaman->approvalWorkflow->groupBy('approval_type')->each(function ($group, $type) use (&$approvalActionSummary) {
            $approvalActionSummary[$type] = $group->where('status', 'pending')->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'name' => optional($workflow->approver)->name ?? '-',
                    'level' => $workflow->approval_level,
                    'created_at' => optional($workflow->created_at)->format('d/m/Y H:i'),
                    'approver_id' => $workflow->approver_id,
                    'reference_id' => $workflow->sarana_id ?? $workflow->prasarana_id,
                ];
            })->values()->all();
        });

        return view('peminjaman.show', [
            'peminjaman' => $peminjaman,
            'pickupValidatorName' => $pickupValidatorName,
            'pickupValidatorUsername' => optional(User::find($peminjaman->pickup_validated_by))->username,
            'returnValidatorName' => $returnValidatorName,
            'returnValidatorUsername' => optional(User::find($peminjaman->return_validated_by))->username,
            'prasaranaApprovalSummary' => $prasaranaApprovalSummary,
            'approvalActionSummary' => $approvalActionSummary,
            'serializedUnitOptions' => $serializedUnitOptions,
            'overrideApprovals' => $overrideApprovals,
            'konflikMembers' => $konflikMembers,
            'statusBadge' => $statusBadge,
        ]);
    }

    public function assignUnits(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('view', $peminjaman);

        // Validasi permission admin
        if ($peminjaman->status !== 'approved' || !Auth::user()->can('peminjaman.adjust_sarpras')) {
            abort(403, 'Unauthorized');
        }

        $itemId = $request->input('item_id');

        if ($itemId) {
            $item = PeminjamanItem::where('peminjaman_id', $peminjaman->id)
                ->with('units.unit')
                ->findOrFail($itemId);

            if (optional($item->sarana)->type !== 'serialized') {
                abort(400, 'Item bukan sarana serialized.');
            }

            $selectedUnits = collect($request->input("unit_selection.$itemId", []))->map(fn ($id) => (int) $id)->filter()->values();

            $existingAssignments = PeminjamanItemUnit::where('peminjaman_item_id', $item->id)
                ->where('peminjaman_id', $peminjaman->id)
                ->get()
                ->keyBy('unit_id');

            $activeAssignments = $existingAssignments->filter(fn ($assignment) => $assignment->status === 'active');
            $activeUnitIds = $activeAssignments->keys();

            $unitsToDetach = $activeUnitIds->diff($selectedUnits);
            if ($unitsToDetach->isNotEmpty()) {
                foreach ($unitsToDetach as $unitId) {
                    $assignment = $existingAssignments->get($unitId);
                    if ($assignment) {
                        $assignment->update([
                            'status' => 'released',
                            'released_by' => Auth::id(),
                            'released_at' => now(),
                        ]);
                    }
                }

                SaranaUnit::whereIn('id', $unitsToDetach->all())->update(['unit_status' => 'tersedia']);
            }

            $unitsToAttach = $selectedUnits->diff($activeUnitIds);
            foreach ($unitsToAttach as $unitId) {
                $existingAssignment = $existingAssignments->get($unitId);

                if ($existingAssignment) {
                    if ($this->unitHasScheduleConflict($item, $unitId, $peminjaman)) {
                        return redirect()
                            ->route('peminjaman.show', $peminjaman)
                            ->with('error', 'Unit '.$existingAssignment->unit?->unit_code.' tidak dapat digunakan karena bentrok dengan peminjaman lain.');
                    }
                    $existingAssignment->update([
                        'status' => 'active',
                        'released_by' => null,
                        'released_at' => null,
                        'assigned_by' => Auth::id(),
                        'assigned_at' => now(),
                    ]);
                    continue;
                }

                $unit = SaranaUnit::where('id', $unitId)
                    ->where('sarana_id', $item->sarana_id)
                    ->where('unit_status', 'tersedia')
                    ->first();

                if (!$unit) {
                    continue;
                }

                if ($this->unitHasScheduleConflict($item, $unitId, $peminjaman)) {
                    return redirect()
                        ->route('peminjaman.show', $peminjaman)
                        ->with('error', 'Unit '.$unit->unit_code.' tidak dapat digunakan karena bentrok dengan peminjaman lain.');
                }

                PeminjamanItemUnit::create([
                    'peminjaman_id' => $peminjaman->id,
                    'peminjaman_item_id' => $item->id,
                    'unit_id' => $unitId,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                    'status' => 'active',
                ]);
            }

            $item->update([
                'qty_approved' => PeminjamanItemUnit::where('peminjaman_item_id', $item->id)
                    ->where('peminjaman_id', $peminjaman->id)
                    ->where('status', 'active')
                    ->count(),
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'unit_assigned',
                'model_type' => 'Peminjaman',
                'model_id' => $peminjaman->id,
                'description' => 'Admin assigned units for peminjaman item '.$item->id,
                'payload' => [
                    'item_id' => $item->id,
                    'unit_ids' => $selectedUnits,
                ],
                'created_at' => now(),
            ]);

            return redirect()->route('peminjaman.show', $peminjaman)->with('success', 'Unit peminjaman diperbarui.');
        }

        return redirect()->route('peminjaman.show', $peminjaman)->with('info', 'Tidak ada perubahan unit yang dilakukan.');
    }

    private function unitHasScheduleConflict(PeminjamanItem $item, int $unitId, Peminjaman $peminjaman): bool
    {
        return $this->getConflictingUnitIdsForItem($item, collect([$unitId]))->isNotEmpty();
    }

    private function getConflictingUnitIdsForItem(PeminjamanItem $item, Collection $unitIds): Collection
    {
        if ($unitIds->isEmpty()) {
            return collect();
        }

        return PeminjamanItemUnit::whereIn('unit_id', $unitIds)
            ->where('status', 'active')
            ->where('peminjaman_id', '!=', $item->peminjaman_id)
            ->whereHas('peminjamanItem.peminjaman', function ($query) {
                $query->whereNotIn('status', [
                    Peminjaman::STATUS_RETURNED,
                    Peminjaman::STATUS_CANCELLED,
                    Peminjaman::STATUS_REJECTED,
                ]);
            })
            ->whereHas('peminjamanItem.peminjaman', function ($query) use ($item) {
                $query->where(function ($q) use ($item) {
                    $q->whereBetween('start_date', [$item->peminjaman->start_date, $item->peminjaman->end_date])
                        ->orWhereBetween('end_date', [$item->peminjaman->start_date, $item->peminjaman->end_date])
                        ->orWhere(function ($q2) use ($item) {
                            $q2->where('start_date', '<=', $item->peminjaman->start_date)
                                ->where('end_date', '>=', $item->peminjaman->end_date);
                        });
                });
            })
            ->pluck('unit_id');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Peminjaman $peminjaman)
    {
        $this->authorize('update', $peminjaman);

        $currentUser = Auth::user();
        $canAdminEditApproved = $currentUser && ($currentUser->getRoleName() === 'admin' || $currentUser->hasPermission('peminjaman.approve'));

        if ($peminjaman->status !== Peminjaman::STATUS_PENDING && !($canAdminEditApproved && $peminjaman->status === Peminjaman::STATUS_APPROVED)) {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Peminjaman yang sudah diproses tidak dapat diedit.');
        }

        $prasarana = Prasarana::where('status', 'tersedia')->get();
        
        // Get all sarana and update their stats first
        $allSarana = Sarana::all();
        foreach ($allSarana as $s) {
            $s->updateStats();
        }
        
        // Then get sarana with available quantity
        $sarana = Sarana::where('jumlah_tersedia', '>', 0)->get();
        
        // If no sarana available, get all sarana for debugging
        if ($sarana->isEmpty()) {
            $sarana = Sarana::all();
            \Log::warning('No sarana with available quantity found in edit form. Showing all sarana for debugging.', [
                'total_sarana' => $sarana->count(),
                'peminjaman_id' => $peminjaman->id,
            ]);
        }
        
        $ukms = [];
        if (Auth::user()->user_type === 'mahasiswa') {
            $ukms = Ukm::orderBy('nama')->get(['id','nama']);
        }
        $maxDuration = SystemSetting::getValue('max_duration_days', 7);

        return view('peminjaman.edit', compact('peminjaman', 'prasarana', 'sarana', 'ukms', 'maxDuration'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('update', $peminjaman);

        $currentUser = Auth::user();
        $canAdminEditApproved = $currentUser && ($currentUser->getRoleName() === 'admin' || $currentUser->hasPermission('peminjaman.approve'));

        if ($peminjaman->status !== Peminjaman::STATUS_PENDING && !($canAdminEditApproved && $peminjaman->status === Peminjaman::STATUS_APPROVED)) {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Peminjaman yang sudah diproses tidak dapat diedit.');
        }

        $rules = [
            'event_name' => 'required|string|max:255',
            'loan_type' => 'required|in:sarana,prasarana,both',
            'prasarana_id' => 'nullable|exists:prasarana,id',
            'lokasi_custom' => 'nullable|string|max:150',
            'jumlah_peserta' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'sarana_items' => 'nullable|array',
            'sarana_items.*.sarana_id' => 'required_with:sarana_items|distinct|exists:sarana,id',
            'sarana_items.*.qty_requested' => 'required_with:sarana_items|integer|min:1',
            'sarana_items.*.notes' => 'nullable|string|max:500',
        ];
        if (Auth::user()->user_type === 'mahasiswa') {
            $rules['ukm_id'] = 'required|exists:ukm,id';
        } else {
            $rules['ukm_id'] = 'nullable|exists:ukm,id';
        }
        $validator = Validator::make($request->all(), $rules, [
            'ukm_id.required' => 'UKM wajib dipilih untuk pengajuan oleh mahasiswa.',
            'ukm_id.exists' => 'UKM tidak valid.',
        ]);

        $validator->after(function ($validator) use ($request, $peminjaman) {
            $this->validateLoanTypeSelection($request, $validator);
            $loanType = $request->input('loan_type');
            $requiresParticipants = in_array($loanType, ['prasarana', 'both'], true) || ($peminjaman->prasarana_id && $loanType !== 'sarana');
            if ($requiresParticipants && empty($request->input('jumlah_peserta'))) {
                $validator->errors()->add('jumlah_peserta', 'Jumlah peserta wajib diisi untuk peminjaman prasarana.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $this->normalizeBorrowingPayload($request, $validator->validated());
        $request->merge([
            'loan_type' => $validated['loan_type'],
            'prasarana_id' => $validated['prasarana_id'],
            'lokasi_custom' => $validated['lokasi_custom'],
            'sarana_items' => $validated['sarana_items'],
            'jumlah_peserta' => $validated['jumlah_peserta'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        // Check duration limit
        $maxDuration = SystemSetting::getValue('max_duration_days', 7);
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $duration = $startDate->diffInDays($endDate) + 1;

        if ($duration > $maxDuration) {
            return redirect()->back()
                ->with('error', "Durasi peminjaman tidak boleh lebih dari {$maxDuration} hari")
                ->withInput();
        }

        if (!empty($validated['prasarana_id']) && !empty($validated['jumlah_peserta'])) {
            $selectedPrasarana = Prasarana::find($validated['prasarana_id']);
            if ($selectedPrasarana && $selectedPrasarana->kapasitas !== null) {
                $kapasitas = (int) $selectedPrasarana->kapasitas;
                $peserta = (int) $validated['jumlah_peserta'];
                if ($kapasitas > 0 && $peserta > $kapasitas) {
                    return redirect()->back()
                        ->with('error', "Jumlah peserta ({$peserta}) melebihi kapasitas prasarana {$selectedPrasarana->name} ({$kapasitas}).")
                        ->withInput();
                }
            }
        }

        // Check for conflicts (excluding current peminjaman)
        $slotConflictService = app(SlotConflictService::class);
        $conflict = $slotConflictService->checkConflicts($request, $peminjaman->id);
        if ($conflict) {
            if ($lastConflict = $slotConflictService->getLastConflict()) {
                app(NotificationService::class)->notifyPriorityConflict(
                    $lastConflict['peminjaman'],
                    $peminjaman,
                    $lastConflict['type']
                );
            }
            return redirect()->back()
                ->with('error', $conflict)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Update peminjaman
            $peminjaman->update([
                'prasarana_id' => $validated['prasarana_id'],
                'lokasi_custom' => $validated['lokasi_custom'],
                'jumlah_peserta' => $validated['jumlah_peserta'] ?? null,
                'ukm_id' => $validated['ukm_id'] ?? null,
                'event_name' => $validated['event_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
            ]);

            // Update surat if provided
            if ($request->hasFile('surat')) {
                // Delete old surat
                $this->deletePublicFile($peminjaman->surat_path);

                // Upload new surat
                $suratPath = $this->storePublicFile($request->file('surat'), 'peminjaman/surat');
                $peminjaman->update(['surat_path' => $suratPath]);
            }

            // Update peminjaman items
            $peminjaman->items()->delete();
            if (!empty($validated['sarana_items'])) {
                foreach ($validated['sarana_items'] as $item) {
                    PeminjamanItem::create([
                        'peminjaman_id' => $peminjaman->id,
                        'sarana_id' => $item['sarana_id'],
                        'qty_requested' => $item['qty_requested'],
                        'qty_approved' => null,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            $peminjaman->load('items');
            $pendingConflicts = $slotConflictService->findPendingConflicts($peminjaman);
            $this->syncKonflikGroup($peminjaman, $pendingConflicts);

            DB::commit();

            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('success', 'Pengajuan peminjaman berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to update peminjaman', [
                'id' => $peminjaman->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui pengajuan peminjaman.')
                ->withInput();
        }
    }

    private function validateLoanTypeSelection(Request $request, $validator): void
    {
        $loanType = $request->input('loan_type');
        $lokasiCustom = $request->input('lokasi_custom');
        $lokasiCustom = is_string($lokasiCustom) ? trim($lokasiCustom) : $lokasiCustom;
        $prasaranaId = $request->input('prasarana_id');
        $saranaItems = collect($request->input('sarana_items', []))
            ->filter(function ($item) {
                return isset($item['sarana_id']) && $item['sarana_id'] !== null && $item['sarana_id'] !== '';
            });

        if (!in_array($loanType, ['sarana', 'prasarana', 'both'], true)) {
            $validator->errors()->add('loan_type', 'Jenis peminjaman tidak valid.');
            return;
        }

        if ($loanType === 'sarana') {
            if (empty($lokasiCustom)) {
                $validator->errors()->add('lokasi_custom', 'Lokasi acara wajib diisi untuk peminjaman sarana.');
            }

            if (!empty($prasaranaId)) {
                $validator->errors()->add('prasarana_id', 'Prasarana tidak boleh dipilih untuk peminjaman sarana.');
            }

            if ($saranaItems->isEmpty()) {
                $validator->errors()->add('sarana_items', 'Tambahkan minimal satu sarana yang ingin dipinjam.');
            }
        }

        if ($loanType === 'prasarana') {
            if (empty($prasaranaId)) {
                $validator->errors()->add('prasarana_id', 'Pilih prasarana yang ingin dipinjam.');
            }
        }

        if ($loanType === 'both') {
            if (empty($prasaranaId)) {
                $validator->errors()->add('prasarana_id', 'Pilih prasarana yang ingin dipinjam.');
            }

            if ($saranaItems->isEmpty()) {
                $validator->errors()->add('sarana_items', 'Tambahkan minimal satu sarana yang ingin dipinjam.');
            }
        }
    }

    private function normalizeBorrowingPayload(Request $request, array $validated): array
    {
        $loanType = $validated['loan_type'] ?? $request->input('loan_type');
        $prasaranaId = $validated['prasarana_id'] ?? null;
        $lokasiCustom = $validated['lokasi_custom'] ?? null;
        $lokasiCustom = is_string($lokasiCustom) ? trim($lokasiCustom) : $lokasiCustom;
        if ($lokasiCustom === '') {
            $lokasiCustom = null;
        }

        $startTime = $this->normalizeTimeValue($validated['start_time'] ?? $request->input('start_time'));
        $endTime = $this->normalizeTimeValue($validated['end_time'] ?? $request->input('end_time'));

        $jumlahPeserta = $validated['jumlah_peserta'] ?? $request->input('jumlah_peserta');
        if (is_string($jumlahPeserta) && $jumlahPeserta !== '') {
            $jumlahPeserta = (int) $jumlahPeserta;
        }
        if ($jumlahPeserta === '' || $jumlahPeserta === 0) {
            $jumlahPeserta = null;
        }

        $saranaItems = $validated['sarana_items'] ?? [];

        switch ($loanType) {
            case 'sarana':
                $prasaranaId = null;
                $validated['jumlah_peserta'] = null;
                break;
            case 'prasarana':
                $lokasiCustom = null;
                $saranaItems = [];
                $validated['jumlah_peserta'] = $jumlahPeserta;
                break;
            case 'both':
                $lokasiCustom = null;
                $validated['jumlah_peserta'] = $jumlahPeserta;
                break;
        }

        $validated['loan_type'] = $loanType;
        $validated['prasarana_id'] = $prasaranaId;
        $validated['lokasi_custom'] = $lokasiCustom;
        if (!array_key_exists('jumlah_peserta', $validated)) {
            $validated['jumlah_peserta'] = $jumlahPeserta;
        }
        $validated['start_time'] = $startTime;
        $validated['end_time'] = $endTime;
        $validated['sarana_items'] = $this->normalizeSaranaItems($saranaItems);

        return $validated;
    }

    private function buildApprovalSummary($workflows, string $defaultStatus = 'pending')
    {
        $collection = collect($workflows);
        if ($collection->isEmpty()) {
            $status = in_array($defaultStatus, ['approved', 'rejected'], true) ? $defaultStatus : 'pending';
            $label = match ($status) {
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default => 'Pending',
            };
            return [
                'status' => $status,
                'label' => $label,
                'badge_class' => 'status-' . $status,
                'approvers' => collect(),
            ];
        }

        $hasRejected = $collection->contains(fn ($wf) => $wf->status === 'rejected');
        $allApproved = $collection->every(fn ($wf) => $wf->status === 'approved');
        $hasApproved = $collection->contains(fn ($wf) => $wf->status === 'approved');

        if ($hasRejected) {
            $status = 'rejected';
        } elseif ($allApproved) {
            $status = 'approved';
        } elseif ($hasApproved) {
            $status = 'partially_approved';
        } else {
            $status = 'pending';
        }

        $label = match ($status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'partially_approved' => 'Partial',
            default => 'Pending',
        };

        $approvers = $collection->map(function ($wf) {
            return [
                'name' => optional($wf->approver)->name ?? '-',
                'status' => $wf->status,
                'badge_class' => 'status-' . $wf->status,
                'level' => $wf->approval_level,
                'updated_at' => optional($wf->approved_at ?? $wf->rejected_at ?? $wf->created_at)->format('d/m/Y H:i'),
            ];
        });

        $pendingApprovers = $approvers->filter(fn ($data) => $data['status'] === 'pending')->values();
        $approvedApprovers = $approvers->filter(fn ($data) => $data['status'] === 'approved')->values();
        $rejectedApprovers = $approvers->filter(fn ($data) => $data['status'] === 'rejected')->values();

        return [
            'status' => $status,
            'label' => $label,
            'badge_class' => 'status-' . $status,
            'approvers' => $approvers->values(),
            'pending_approvers' => $pendingApprovers,
            'approved_approvers' => $approvedApprovers,
            'rejected_approvers' => $rejectedApprovers,
            'counts' => [
                'total' => $approvers->count(),
                'pending' => $pendingApprovers->count(),
                'approved' => $approvedApprovers->count(),
                'rejected' => $rejectedApprovers->count(),
            ],
        ];
    }

    private function normalizeSaranaItems(array $items): array
    {
        return collect($items)
            ->filter(function ($item) {
                return isset($item['sarana_id']) && $item['sarana_id'] !== null && $item['sarana_id'] !== '';
            })
            ->map(function ($item) {
                return [
                    'sarana_id' => (int) $item['sarana_id'],
                    'qty_requested' => max(1, (int) ($item['qty_requested'] ?? 1)),
                    'notes' => isset($item['notes']) && $item['notes'] !== '' ? $item['notes'] : null,
                ];
            })
            ->groupBy('sarana_id')
            ->map(function ($group) {
                $notes = $group->pluck('notes')
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode('; ');

                return [
                    'sarana_id' => $group->first()['sarana_id'],
                    'qty_requested' => $group->sum('qty_requested'),
                    'notes' => $notes !== '' ? $notes : null,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeTimeValue($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::instance($value)->format('H:i');
        }

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Peminjaman $peminjaman)
    {
        $this->authorize('delete', $peminjaman);

        // Only allow deleting if status is pending
        if ($peminjaman->status !== 'pending') {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Peminjaman yang sudah diproses tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            // Delete surat file
            if ($peminjaman->surat_path) {
                Storage::disk('public')->delete($peminjaman->surat_path);
            }

            // Delete peminjaman (items will be deleted by cascade)
            $peminjaman->delete();

            DB::commit();

            return redirect()->route('peminjaman.index')
                ->with('success', 'Pengajuan peminjaman berhasil dibatalkan.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membatalkan pengajuan peminjaman.');
        }
    }

    /**
     * Cancel the specified peminjaman.
     */
    public function cancel(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('update', $peminjaman);

        $validator = Validator::make($request->all(), [
            'cancelled_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        // Only allow cancelling if status is pending or approved
        if (!in_array($peminjaman->status, ['pending', 'approved'])) {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Peminjaman tidak dapat dibatalkan pada status ini.');
        }

        $peminjaman->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_reason' => $request->cancelled_reason,
            'cancelled_at' => now(),
        ]);

        // Update kuota (keluar dari set aktif)
        app(UserQuotaService::class)->decrementIfInactive($peminjaman);

        app(PickupReturnService::class)->releaseSerializedUnits($peminjaman);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'peminjaman.cancelled',
            'model_type' => Peminjaman::class,
            'model_id' => $peminjaman->id,
            'description' => 'Pembatalan peminjaman oleh admin/petugas',
            'old_values' => ['status' => $peminjaman->getOriginal('status')],
            'new_values' => ['status' => $peminjaman->status, 'reason' => $peminjaman->cancelled_reason],
            'created_at' => now(),
        ]);

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('success', 'Peminjaman berhasil dibatalkan.');
    }

    /**
     * Validate pickup (petugas/admin only)
     */
    public function validatePickup(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('validate_pickup', $peminjaman);

        $request->validate([
            'foto_pickup' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $fotoPath = $peminjaman->foto_pickup_path; // keep existing by default
        if ($request->hasFile('foto_pickup')) {
            // replace old if any
            if (!empty($fotoPath)) {
                $this->deletePublicFile($fotoPath);
            }
            $fotoPath = $this->storePublicFile($request->file('foto_pickup'), 'peminjaman/foto/pickup');
        }

        app(PickupReturnService::class)->validatePickup($peminjaman, $fotoPath);

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('success', 'Pengambilan berhasil divalidasi.');
    }

    /**
     * Validate return (petugas/admin only)
     */
    public function validateReturn(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('validate_return', $peminjaman);

        $request->validate([
            'foto_return' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $fotoPath = $peminjaman->foto_return_path; // keep existing by default
        if ($request->hasFile('foto_return')) {
            if (!empty($fotoPath)) {
                $this->deletePublicFile($fotoPath);
            }
            $fotoPath = $this->storePublicFile($request->file('foto_return'), 'peminjaman/foto/return');
        }

        app(PickupReturnService::class)->validateReturn($peminjaman, $fotoPath);

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('success', 'Pengembalian berhasil divalidasi.');
    }

    /**
     * Upload pickup photo by peminjam (owner) without validating pickup.
     */
    public function uploadPickupPhoto(Request $request, Peminjaman $peminjaman)
    {
        if (Auth::id() !== $peminjaman->user_id) {
            abort(403);
        }

        // Only allow when status is approved (before picked up)
        if ($peminjaman->status !== Peminjaman::STATUS_APPROVED) {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Foto pengambilan hanya dapat diunggah saat status disetujui.');
        }

        $request->validate([
            'foto_pickup' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if (!empty($peminjaman->foto_pickup_path)) {
            $this->deletePublicFile($peminjaman->foto_pickup_path);
        }

        $fotoPath = $this->storePublicFile($request->file('foto_pickup'), 'peminjaman/foto/pickup');
        $peminjaman->update([
            'foto_pickup_path' => $fotoPath,
        ]);

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('success', 'Foto pengambilan berhasil diunggah.');
    }

    /**
     * Upload return photo by peminjam (owner) without validating return.
     */
    public function uploadReturnPhoto(Request $request, Peminjaman $peminjaman)
    {
        if (Auth::id() !== $peminjaman->user_id) {
            abort(403);
        }

        // Only allow when status is picked_up (before returned)
        if ($peminjaman->status !== Peminjaman::STATUS_PICKED_UP) {
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Foto pengembalian hanya dapat diunggah saat status sudah diambil.');
        }

        $request->validate([
            'foto_return' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if (!empty($peminjaman->foto_return_path)) {
            $this->deletePublicFile($peminjaman->foto_return_path);
        }

        $fotoPath = $this->storePublicFile($request->file('foto_return'), 'peminjaman/foto/return');
        $peminjaman->update([
            'foto_return_path' => $fotoPath,
        ]);

        return redirect()->route('peminjaman.show', $peminjaman)
            ->with('success', 'Foto pengembalian berhasil diunggah.');
    }

    /**
     * Approve a peminjaman (global approval simple version).
     */
    public function approve(Request $request, Peminjaman $peminjaman)
    {
        $this->authorize('approve', $peminjaman);

        Log::info('PeminjamanController@approve invoked', [
            'peminjaman_id' => $peminjaman->id,
            'approver_id' => Auth::id(),
            'approval_type' => $request->input('approval_type'),
            'sarpras_id' => $request->input('sarpras_id')
        ]);

        if ($peminjaman->status !== Peminjaman::STATUS_PENDING) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pengajuan berstatus pending yang dapat di-approve.'
                ], 400);
            }
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Hanya pengajuan berstatus pending yang dapat di-approve.');
        }

        $approvalType = $request->input('approval_type', 'global');
        $sarprasId = $request->input('sarpras_id');
        $notes = $request->input('notes');

        // Enforcement kuota aktif saat approve
        $maxActiveBorrowings = SystemSetting::getValue('max_active_borrowings', 3);
        $currentBorrowings = Peminjaman::where('user_id', $peminjaman->user_id)
            ->whereIn('status', [Peminjaman::STATUS_PENDING, Peminjaman::STATUS_APPROVED, Peminjaman::STATUS_PICKED_UP])
            ->count();
        if ($currentBorrowings >= $maxActiveBorrowings) {
            return response()->json([
                'success' => false,
                'message' => "Kuota peminjaman aktif pengguna telah tercapai (maksimal {$maxActiveBorrowings})."
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Use PeminjamanApprovalService for multi-approval
            $approvalService = app(\App\Services\PeminjamanApprovalService::class);
            
            if ($approvalType === 'global') {
                $approvalService->approveGlobal($peminjaman->id, Auth::id(), $notes);
            } elseif ($approvalType === 'sarana') {
                $approvalService->approveSpecificSarana($peminjaman->id, $sarprasId, Auth::id(), $notes);
            } elseif ($approvalType === 'prasarana') {
                $approvalService->approveSpecificPrasarana($peminjaman->id, $sarprasId, Auth::id(), $notes);
            } else {
                throw new \InvalidArgumentException('Invalid approval type');
            }

            // Check if all approvals are complete and update peminjaman status
            $approvalStatus = $peminjaman->approvalStatus;
            if ($approvalStatus && $approvalStatus->overall_status === 'approved') {
                $peminjaman->update([
                    'status' => Peminjaman::STATUS_APPROVED,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
                
                app(UserQuotaService::class)->incrementIfActive($peminjaman);

                $approvalService->syncAllPooledSarana($peminjaman->id, 'approve');
            }

            app(NotificationService::class)->notifyApproval($peminjaman);

            Log::info('PeminjamanController@approve success', [
                'peminjaman_id' => $peminjaman->id,
                'approver_id' => Auth::id(),
                'approval_type' => $approvalType,
                'sarpras_id' => $sarprasId,
                'peminjaman_status' => $peminjaman->status,
            ]);

            DB::commit();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Approval berhasil!'
                ]);
            }
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('success', 'Approval berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('PeminjamanController@approve failed', [
                'peminjaman_id' => $peminjaman->id,
                'approver_id' => Auth::id(),
                'approval_type' => $approvalType,
                'sarpras_id' => $sarprasId,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyetujui peminjaman: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('peminjaman.show', $peminjaman)
                ->with('error', 'Gagal menyetujui peminjaman.');
        }
    }

    /**
     * List of pending peminjaman (for approvers/admin).
     */
    public function pending(Request $request)
    {
        $this->authorize('viewAny', Peminjaman::class);

        $query = Peminjaman::with(['user', 'prasarana'])
            ->where('status', Peminjaman::STATUS_PENDING)
            ->orderBy('created_at', 'desc');

        // Non-admin: hanya milik sendiri jika tidak punya permission melihat semua
        if (!Auth::user()->hasPermissionTo('peminjaman.view')) {
            $query->where('user_id', Auth::id());
        }

        $peminjaman = $query->paginate(15);
        return view('peminjaman.pending', compact('peminjaman'));
    }
}

