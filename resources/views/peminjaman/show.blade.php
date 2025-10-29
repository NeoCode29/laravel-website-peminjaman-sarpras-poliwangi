@extends('layouts.app')

@section('title', 'Detail Peminjaman')
@section('subtitle', 'Informasi lengkap peminjaman')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/peminjaman.css') }}?v={{ filemtime(public_path('css/peminjaman.css')) }}">
<style>
/* Page */
.detail-page { display: flex; flex-direction: column; gap: 24px; }
.user-detail-card { border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); background: #ffffff; }
.card-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 20px; border-bottom: 1px solid #f0f0f0; }
.card-header__title { display: flex; align-items: center; gap: 12px; font-size: 24px; font-weight: 600; color: #333333; margin: 0; }
.card-header__title .card-title { margin: 0; font-size: 24px; font-weight: 600; color: #333333; }
.detail-page__icon { font-size: 24px; color: #4b5563; }
.card-header__actions, .detail-header-tags { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: flex-end; }
.card-main { padding: 20px; display: flex; flex-direction: column; gap: 24px; background: #ffffff; }

/* Chips */
.summary-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; }
.chip { display: inline-flex; align-items: center; gap: 8px; background: #f5f7fa; border: 1px solid #e0e0e0; color: #333333; border-radius: 999px; padding: 6px 12px; font-size: 13px; }
.chip i { color: #6b7280; }
.chip--outline { background: transparent; border-color: #d0d5dd; color: #1f2937; }

/* Status badges */
.status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; text-transform: capitalize; line-height: 1; }
.status-badge.status-approved { background: #e8f5e8; color: #2e7d32; }
.status-badge.status-pending { background: #fff3e0; color: #f57c00; }
.status-badge.status-rejected { background: #ffebee; color: #c62828; }
.status-badge.status-returned { background: #e0f2fe; color: #0369a1; }

/* Sections */
.form-section { display: flex; flex-direction: column; gap: 12px; }
.section-title { font-size: 16px; font-weight: 600; color: #333333; margin: 0; }
.detail-block { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.detail-block + .detail-block { margin-top: 12px; }
.detail-list { display: flex; flex-direction: column; gap: 12px; }

/* Detail rows */
.detail-row { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding-bottom: 8px; border-bottom: 1px dashed #e5e7eb; }
.detail-row:last-child { border-bottom: none; padding-bottom: 0; }
.detail-label { font-size: 13px; color: #6b7280; min-width: 140px; }
.detail-value { font-size: 14px; color: #1f2937; display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-wrap: wrap; text-align: right; }
.detail-value span { display: inline-flex; align-items: center; gap: 6px; }
.detail-row--wide { flex-direction: column; align-items: flex-start; gap: 12px; }
.detail-row--wide .detail-value { justify-content: flex-start; text-align: left; width: 100%; }
.empty-state { text-align: center; color: #6b7280; padding: 16px; font-size: 13px; }

/* Detail actions */
.detail-actions { display: flex; flex-wrap: wrap; gap: 12px; }
.detail-actions .btn { min-width: 140px; height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500; border-radius: 6px; }

/* Media */
.media-gallery { display: flex; flex-wrap: wrap; gap: 12px; }
.media-gallery__item { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; width: 180px; background: #f9fafb; }
.media-gallery__item img { width: 100%; height: auto; display: block; }

/* Unit chips */
.unit-chip-list { display: flex; flex-wrap: wrap; gap: 8px; }
.unit-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; font-size: 12px; font-weight: 500; }
.unit-chip--released { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.unit-chip__note { font-size: 11px; color: inherit; opacity: 0.85; }

/* Table */
.table-section { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
.table { width: 100%; border-collapse: collapse; font-size: 14px; }
.table thead { background: #f9fafb; color: #4b5563; }
.table th, .table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #e5e7eb; }
.table tbody tr:last-child td { border-bottom: none; }

/* Action task cards */
.action-task-stack { display: flex; flex-direction: column; gap: 16px; }
.action-task-card { border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; display: flex; flex-direction: column; }
.action-task-card__header { display: flex; gap: 12px; align-items: center; padding: 16px; border-bottom: 1px solid #e5e7eb; }
.action-task-card__icon { width: 36px; height: 36px; border-radius: 999px; background: #eef2ff; display: flex; align-items: center; justify-content: center; color: #4f46e5; font-size: 16px; }
.action-task-card__heading { display: flex; flex-direction: column; gap: 4px; }
.action-task-card__title { font-size: 15px; font-weight: 600; color: #1f2937; }
.action-task-card__detail { font-size: 12px; color: #6b7280; }
.action-task-card__body { padding: 16px; font-size: 13px; color: #4b5563; display: flex; flex-direction: column; gap: 12px; }
.action-task-card__meta { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.action-task-card__footer { padding: 16px; display: flex; flex-wrap: wrap; gap: 12px; border-top: 1px solid #e5e7eb; }
.action-task-card__footer .btn { display: inline-flex; align-items: center; gap: 8px; font-weight: 500; }
.action-task-card__form--inline { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
.action-task-card__form--upload { gap: 12px; }
.action-task-card__file-trigger { display: inline-flex; gap: 8px; align-items: center; justify-content: center; padding: 10px 14px; border: 1px dashed #cbd5f5; border-radius: 8px; cursor: pointer; background: #ffffff; color: #4f46e5; font-size: 13px; font-weight: 500; }
.action-task-card__file-trigger input[type="file"] { display: none; }
.action-task-card__file-name { font-size: 12px; color: #6b7280; }
.action-task-card__note { font-size: 12px; color: #6b7280; margin: 0; }
.action-task-card__description--warning { color: #ef4444; }
.action-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; }
.action-btn--success { background: #0ea5e9; color: #ffffff; }
.action-btn--secondary { background: #e0e7ff; color: #3730a3; }
.action-btn--neutral { background: #f3f4f6; color: #1f2937; }
.action-btn--primary { background: #2563eb; color: #ffffff; }
.action-btn--danger-outline { background: #ffffff; color: #b91c1c; border: 1px solid #f87171; }
.action-btn--validate { background: #28a745; color: #ffffff; padding: 12px 24px; font-size: 14px; font-weight: 500; border-radius: 6px; box-shadow: 0 6px 14px rgba(40, 167, 69, 0.18); transition: background 0.2s ease, transform 0.2s ease; }
.action-btn--validate:hover { background: #218838; transform: translateY(-1px); }
.action-btn--sm { padding: 6px 12px; font-size: 12px; }

/* Modals */
.modal-overlay { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.38); opacity: 0; pointer-events: none; transition: opacity 0.24s ease; z-index: 1100; padding: 16px; }
.modal-overlay.active { opacity: 1; pointer-events: auto; }
.modal-card { background: #ffffff; border-radius: 8px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16); width: min(520px, 95vw); display: flex; flex-direction: column; transform: translateY(16px); opacity: 0; transition: transform 0.24s ease, opacity 0.24s ease; }
.modal-overlay.active .modal-card { transform: translateY(0); opacity: 1; }
.modal-card__header, .modal-card__footer { padding: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; background: #ffffff; }
.modal-card__header { border-bottom: 1px solid #f0f0f0; }
.modal-card__footer { border-top: 1px solid #f0f0f0; justify-content: flex-end; flex-wrap: wrap; }
.modal-card__body { padding: 20px; display: flex; flex-direction: column; gap: 16px; background: #ffffff; }
.modal-card__title { font-size: 16px; font-weight: 600; color: #333333; margin: 0; }
.modal-card__subtitle { font-size: 13px; color: #666666; margin: 4px 0 0; }
.modal-card__note { font-size: 13px; color: #4b5563; margin: 0; }
.modal-card__close { background: transparent; border: none; color: #666666; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; transition: background 0.2s ease, color 0.2s ease; }
.modal-card__close:hover { background: #f5f5f5; color: #333333; }
.modal-card__footer .btn { min-width: 140px; display: inline-flex; align-items: center; gap: 8px; }
html.modal-open, body.modal-open { overflow: hidden; }

/* Responsive */
@media (max-width: 768px) {
    .card-header { flex-direction: column; align-items: flex-start; padding: 16px; }
    .card-header__actions, .detail-header-tags { justify-content: flex-start; }
    .card-main { padding: 16px; }
    .detail-row { flex-direction: column; align-items: flex-start; gap: 6px; text-align: left; }
    .detail-value { justify-content: flex-start; text-align: left; }
    .detail-actions { flex-direction: column; align-items: stretch; }
    .media-gallery__item { width: 100%; }
    .action-task-card__header, .action-task-card__body, .action-task-card__footer { padding: 14px; }
}
</style>
@endpush

@section('content')
@php
    $authUserName = Auth::user()->name ?? 'Pengguna';
@endphp
<section class="detail-page">
    <div class="card user-detail-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-calendar-check detail-page__icon"></i>
                <h2 class="card-title" data-event-name="{{ $peminjaman->event_name }}">{{ $peminjaman->event_name }}</h2>
                <span class="status-badge {{ $statusBadge['class'] ?? ('status-' . $peminjaman->status) }}">
                    {{ $statusBadge['label'] ?? ucfirst(str_replace('_',' ', $peminjaman->status)) }}
                </span>
            </div>
            <div class="card-header__actions detail-header-tags">
                <span class="chip chip--outline">
                    <i class="fas fa-hashtag"></i>
                    ID {{ $peminjaman->id }}
                </span>
                @if($peminjaman->loan_type)
                    <span class="chip chip--outline">
                        <i class="fas fa-layer-group"></i>
                        {{ ucfirst($peminjaman->loan_type) }}
                    </span>
                @endif
            </div>
        </div>
        <div class="card-main">
            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-calendar"></i>
                    {{ \Carbon\Carbon::parse($peminjaman->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($peminjaman->end_date)->format('d/m/Y') }}
                </div>
                <div class="chip">
                    <i class="fas fa-clock"></i>
                    @if($peminjaman->start_time)
                        {{ \Carbon\Carbon::parse($peminjaman->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($peminjaman->end_time)->format('H:i') }}
                    @else
                        -
                    @endif
                </div>
                <div class="chip">
                    <i class="fas fa-location-dot"></i>
                    {{ $peminjaman->prasarana->name ?? ($peminjaman->lokasi_custom ?? '-') }}
                </div>
                @if($peminjaman->konflik)
                    <div class="chip chip--override" title="Sedang konflik dengan peminjaman lain">
                        <i class="fas fa-undo"></i>
                        Konflik aktif
                    </div>
                @endif
            </div>

            @php
                $serializedNeededActions = [];
            @endphp

            <div class="form-section">
                <h3 class="section-title">Informasi Umum</h3>
                <div class="detail-block">
                    <div class="detail-row">
                        <span class="detail-label">Peminjam</span>
                        <span class="detail-value">{{ $peminjaman->user->name ?? '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Periode</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($peminjaman->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($peminjaman->end_date)->format('d/m/Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Waktu</span>
                        <span class="detail-value">
                            @if($peminjaman->start_time)
                                {{ \Carbon\Carbon::parse($peminjaman->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($peminjaman->end_time)->format('H:i') }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Lokasi</span>
                        <span class="detail-value">{{ $peminjaman->prasarana->name ?? ($peminjaman->lokasi_custom ?? '-') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Jumlah Peserta</span>
                        <span class="detail-value">{{ $peminjaman->jumlah_peserta ? number_format($peminjaman->jumlah_peserta) : '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Surat</span>
                        <span class="detail-value">
                            @if($peminjaman->surat_path)
                                <a class="link" href="{{ Storage::disk('public')->url($peminjaman->surat_path) }}" target="_blank">Lihat Surat</a>
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Status & Kontrol</h3>
                <div class="detail-block">
                    <div class="detail-row">
                        <span class="detail-label">Status Pengajuan</span>
                        <span class="detail-value">
                            <span class="status-badge {{ $statusBadge['class'] ?? 'status-' . $peminjaman->status }}">{{ $statusBadge['label'] ?? ucfirst(str_replace('_',' ', $peminjaman->status)) }}</span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Dibuat Oleh</span>
                        <span class="detail-value">{{ $peminjaman->user->name ?? '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tanggal Pengajuan</span>
                        <span class="detail-value">{{ $peminjaman->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Terakhir Diperbarui</span>
                        <span class="detail-value">{{ $peminjaman->updated_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                @php
                    $adminCanEditApproved = Auth::user() && (Auth::user()->getRoleName() === 'admin' || Auth::user()->hasPermission('peminjaman.approve'));
                @endphp
                <div class="detail-actions">
                    @can('update', $peminjaman)
                        @if($peminjaman->isPending() || ($peminjaman->isApproved() && $adminCanEditApproved))
                            <a href="{{ route('peminjaman.edit', $peminjaman->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                                Edit Peminjaman
                            </a>
                        @endif
                    @endcan

                    @can('cancel', $peminjaman)
                        @if($peminjaman->isPending() || $peminjaman->isApproved())
                            <button type="button" class="btn btn-danger" onclick="toggleCancelModal(true)">
                                <i class="fas fa-ban"></i>
                                Batalkan Peminjaman
                            </button>
                        @endif
                    @endcan
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Detail Prasarana</h3>
                <div class="detail-block">
                    <div class="detail-row"><span class="detail-label">Nama</span><span class="detail-value">{{ $peminjaman->prasarana->name ?? '-' }}</span></div>
                    <div class="detail-row"><span class="detail-label">Lokasi</span><span class="detail-value">{{ $peminjaman->prasarana->lokasi ?? ($peminjaman->lokasi_custom ?? '-') }}</span></div>
                    <div class="detail-row"><span class="detail-label">Status Approval</span><span class="detail-value">
                        @if($prasaranaApprovalSummary)
                            <span class="status-badge {{ $prasaranaApprovalSummary['badge_class'] ?? 'status-pending' }}">
                                {{ $prasaranaApprovalSummary['label'] ?? 'Pending' }}
                            </span>
                        @else
                            <span class="status-badge status-approved">Diijinkan</span>
                        @endif
                    </span></div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Pengambilan</h3>
                <div class="detail-block">
                    <div class="detail-row"><span class="detail-label">Divalidasi Oleh</span><span class="detail-value">{{ $pickupValidatorName ?? '-' }}</span></div>
                    <div class="detail-row"><span class="detail-label">Waktu Pengambilan</span><span class="detail-value">{{ $peminjaman->pickup_validated_at ? \Carbon\Carbon::parse($peminjaman->pickup_validated_at)->format('d/m/Y H:i') : '-' }}</span></div>
                    <div class="detail-row detail-row--wide">
                        <span class="detail-label">Dokumentasi</span>
                        <span class="detail-value">
                            @if($peminjaman->foto_pickup_path)
                                <div class="media-gallery">
                                    <div class="media-gallery__item">
                                        <a href="{{ Storage::disk('public')->url($peminjaman->foto_pickup_path) }}" target="_blank" rel="noopener">
                                            <img src="{{ Storage::disk('public')->url($peminjaman->foto_pickup_path) }}" alt="Foto Pengambilan">
                                        </a>
                                    </div>
                                </div>
                            @else
                                <span class="empty-state">Tidak ada foto pengambilan.</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Pengembalian</h3>
                <div class="detail-block">
                    <div class="detail-row"><span class="detail-label">Divalidasi Oleh</span><span class="detail-value">{{ $returnValidatorName ?? '-' }}</span></div>
                    <div class="detail-row"><span class="detail-label">Waktu Pengembalian</span><span class="detail-value">{{ $peminjaman->return_validated_at ? \Carbon\Carbon::parse($peminjaman->return_validated_at)->format('d/m/Y H:i') : '-' }}</span></div>
                    <div class="detail-row detail-row--wide">
                        <span class="detail-label">Dokumentasi</span>
                        <span class="detail-value">
                            @if($peminjaman->foto_return_path)
                                <div class="media-gallery">
                                    <div class="media-gallery__item">
                                        <a href="{{ Storage::disk('public')->url($peminjaman->foto_return_path) }}" target="_blank" rel="noopener">
                                            <img src="{{ Storage::disk('public')->url($peminjaman->foto_return_path) }}" alt="Foto Pengembalian">
                                        </a>
                                    </div>
                                </div>
                            @else
                                <span class="empty-state">Tidak ada foto pengembalian.</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Detail Sarana</h3>
                <div class="detail-block detail-list">
                    @forelse($peminjaman->items as $item)
                        @php
                            $summary = $item->approval_summary ?? null;
                            $status = $summary['status'] ?? 'pending';
                            $statusMap = [
                                'approved' => ['label' => 'Diijinkan', 'class' => 'status-approved'],
                                'rejected' => ['label' => 'Tidak Diijinkan', 'class' => 'status-rejected'],
                                'partially_approved' => ['label' => 'Diijinkan Sebagian', 'class' => 'status-pending'],
                                'pending' => ['label' => 'Menunggu Approve', 'class' => 'status-pending'],
                            ];
                            $mapped = $statusMap[$status] ?? [
                                'label' => $summary['label'] ?? 'Menunggu Approve',
                                'class' => $summary['badge_class'] ?? 'status-pending',
                            ];
                            $serialized = ($item->sarana->type ?? '') === 'serialized';
                            $serializedBundle = $serialized ? ($serializedUnitOptions[$item->id] ?? null) : null;
                            $unitMax = $serializedBundle['max_selectable'] ?? $item->approved_quantity;
                            $unitList = $serializedBundle['units'] ?? [];
                            $activeAssignments = $serialized ? $item->units->where('status', 'active') : collect();
                            $releasedAssignments = $serialized ? $item->units->where('status', 'released') : collect();
                            $displayAssignments = $activeAssignments->map(function ($assignment) {
                                return [
                                    'unit_code' => optional($assignment->unit)->unit_code ?? '-',
                                    'status' => 'active',
                                ];
                            })->concat($releasedAssignments->map(function ($assignment) {
                                return [
                                    'unit_code' => optional($assignment->unit)->unit_code ?? '-',
                                    'status' => 'released',
                                    'released_at' => optional($assignment->released_at)->format('d/m/Y H:i'),
                                ];
                            }));
                            $selectedCount = $activeAssignments->count();

                            if($serialized && $serializedBundle){
                                $serializedNeededActions[] = [
                                    'item_id' => $item->id,
                                    'sarana_name' => $item->sarana->name ?? '-',
                                    'unit_max' => $unitMax,
                                    'units' => $unitList,
                                    'selected_units' => $activeAssignments->map(function ($assignment) {
                                        return [
                                            'unit_code' => optional($assignment->unit)->unit_code ?? '-',
                                        ];
                                    })->toArray(),
                                    'selected_count' => $selectedCount,
                                ];
                            }
                        @endphp

                        <div class="detail-row">
                            <span class="detail-label">{{ $item->sarana->name ?? '-' }}</span>
                            <span class="detail-value">
                                <span>{{ $item->approved_quantity }} unit</span>
                                <span class="status-badge {{ $mapped['class'] }}">{{ $mapped['label'] }}</span>
                            </span>
                        </div>

                        @if($serialized)
                            <div class="detail-row detail-row--wide">
                                <span class="detail-label">Unit yang dipilih</span>
                                <span class="detail-value">
                                    @if($displayAssignments->isNotEmpty())
                                        <div class="unit-chip-list">
                                            @foreach($displayAssignments as $assignedUnit)
                                                <span class="unit-chip {{ $assignedUnit['status'] === 'released' ? 'unit-chip--released' : '' }}">
                                                    <span>{{ $assignedUnit['unit_code'] ?? '-' }}</span>
                                                    @if(($assignedUnit['status'] ?? '') === 'released')
                                                        <span class="unit-chip__note">dikembalikan{{ $assignedUnit['released_at'] ? ' · '.$assignedUnit['released_at'] : '' }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="empty-state">Belum ada unit yang dipilih.</span>
                                    @endif
                                </span>
                            </div>
                        @endif
                    @empty
                        <p class="empty-state">Tidak ada sarana yang dipinjam.</p>
                    @endforelse
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Aksi Yang Diperlukan</h3>

                @php
                    $authUser = Auth::user();
                    $authUserId = $authUser?->id;
                    $isOwner = $authUserId !== null && $authUserId === ($peminjaman->user_id ?? null);
                    $userCanUploadPickup = $isOwner && $peminjaman->status === 'approved';
                    $userCanUploadReturn = $isOwner && $peminjaman->status === 'picked_up';
                    $adminCanValidatePickup = ($authUser?->can('validate_pickup', $peminjaman) ?? false) && $peminjaman->status === 'approved';
                    $adminCanValidateReturn = ($authUser?->can('validate_return', $peminjaman) ?? false) && $peminjaman->status === 'picked_up';
                    $hasApprovalActions = ($approvalActionSummary['has_pending'] ?? false) && $authUser;
                    $hasSerializedActions = !empty($serializedNeededActions);
                    $hasSarprasActions = $userCanUploadPickup || $userCanUploadReturn || $adminCanValidatePickup || $adminCanValidateReturn || $hasSerializedActions;
                @endphp

                @php
                    $approvalTypeMeta = [
                        'global' => ['label' => 'Persetujuan Global', 'icon' => 'fas fa-globe'],
                        'prasarana' => ['label' => 'Persetujuan Prasarana', 'icon' => 'fas fa-building'],
                        'sarana' => ['label' => 'Persetujuan Sarana', 'icon' => 'fas fa-box'],
                    ];
                    $pendingApprovalTasks = $hasApprovalActions
                        ? collect($approvalActionSummary ?? [])
                            ->except('has_pending')
                            ->flatMap(function ($entries, $typeKey) use ($approvalTypeMeta) {
                                return collect($entries)->map(function ($entry, $index) use ($typeKey, $approvalTypeMeta) {
                                    return [
                                        'type' => $typeKey,
                                        'meta' => $approvalTypeMeta[$typeKey] ?? ['label' => ucfirst($typeKey), 'icon' => 'fas fa-tasks'],
                                        'entry' => $entry,
                                        'index' => $index,
                                    ];
                                });
                            })
                        : collect();
                    $pendingApprovalTasks = $pendingApprovalTasks
                        ->filter(function ($task) use ($authUserId) {
                            $approverId = $task['entry']['approver_id'] ?? null;
                            if (is_null($approverId)) {
                                return true;
                            }
                            if ($authUserId && (int) $approverId === (int) $authUserId) {
                                return true;
                            }
                            return false;
                        })
                        ->values();
                    $canApprove = $authUser?->can('approve', $peminjaman) || $authUser?->hasPermission('peminjaman.approve_specific');
                    $canReject = $authUser?->can('reject', $peminjaman);
                    $requesterName = $peminjaman->user->name ?? 'peminjam';
                @endphp

                @if($hasApprovalActions || $hasSarprasActions)
                    <div class="action-task-stack">
                        @foreach($pendingApprovalTasks as $task)
                            @php
                                $entry = $task['entry'];
                                $typeKey = $task['type'];
                                $typeMeta = $task['meta'];
                                $waitedFor = null;
                                $rawCreatedAt = $entry['created_at'] ?? null;

                                if($rawCreatedAt){
                                    try {
                                        $createdAtCarbon = $rawCreatedAt instanceof \Carbon\Carbon
                                            ? $rawCreatedAt
                                            : \Carbon\Carbon::parse($rawCreatedAt);
                                    } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                                        try {
                                            $createdAtCarbon = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $rawCreatedAt);
                                        } catch (\Throwable $e2) {
                                            $createdAtCarbon = null;
                                        }
                                    }

                                    if(isset($createdAtCarbon)) {
                                        $waitedFor = $createdAtCarbon->diffForHumans();
                                    }
                                }
                            @endphp
                            <div class="action-task-card action-task-card--approval">
                                <div class="action-task-card__header">
                                    <div class="action-task-card__icon">
                                        <i class="{{ $typeMeta['icon'] ?? 'fas fa-tasks' }}" aria-hidden="true"></i>
                                    </div>
                                    <div class="action-task-card__heading">
                                        <div class="action-task-card__title">{{ $typeMeta['label'] ?? ucfirst($typeKey) }}</div>
                                        <div class="action-task-card__detail">{{ $entry['name'] ?? '-' }} · Level {{ $entry['level'] ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="action-task-card__body">
                                    <p class="action-task-card__description">Keputusan ini menentukan penggunaan {{ strtolower($typeMeta['label'] ?? $typeKey) }} untuk acara {{ $peminjaman->event_name }}.</p>
                                    @if($waitedFor)
                                        <div class="action-task-card__meta"><i class="fas fa-clock" aria-hidden="true"></i> Menunggu sejak {{ $waitedFor }}</div>
                                    @endif
                                </div>
                                <div class="action-task-card__footer">
                                    @if($canApprove)
                                        <form method="POST" action="{{ route('peminjaman.approve', $peminjaman) }}" class="action-task-card__form action-task-card__form--inline js-approval-submit" data-action="approve" data-approval-type="{{ $typeKey }}" data-approval-type-label="{{ $typeMeta['label'] ?? ucfirst($typeKey) }}" data-approval-label="{{ $entry['name'] ?? '-' }}" data-event-name="{{ $peminjaman->event_name }}">
                                            @csrf
                                            <input type="hidden" name="approval_type" value="{{ $typeKey }}">
                                            @if(isset($entry['reference_id']))
                                                <input type="hidden" name="sarpras_id" value="{{ $entry['reference_id'] }}">
                                            @endif
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-check"></i>
                                                Setujui
                                            </button>
                                        </form>
                                    @endif
                                    @if($canReject)
                                        <button
                                            type="button"
                                            class="btn btn-danger"
                                            data-reject-open
                                            data-approval-type="{{ $typeKey }}"
                                            data-approval-type-label="{{ $typeMeta['label'] ?? ucfirst($typeKey) }}"
                                            data-approval-label="{{ $entry['name'] ?? '-' }}"
                                            data-approval-level="{{ $entry['level'] ?? '-' }}"
                                            data-reference-id="{{ $entry['reference_id'] ?? '' }}"
                                            data-event-name="{{ $peminjaman->event_name }}"
                                            data-requester-name="{{ $requesterName }}"
                                        >
                                            <i class="fas fa-times"></i>
                                            Tolak
                                        </button>
                                    @endif
                                    @if(!$canApprove && !$canReject)
                                        <p class="action-task-card__note">Tidak ada aksi yang tersedia untuk akun Anda.</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if($userCanUploadPickup)
                            <div class="action-task-card action-task-card--upload">
                                <div class="action-task-card__header">
                                    <div class="action-task-card__icon"><i class="fas fa-camera" aria-hidden="true"></i></div>
                                    <div class="action-task-card__heading">
                                        <div class="action-task-card__title">Unggah Foto Pengambilan</div>
                                        <div class="action-task-card__detail">Unggah dokumentasi saat sarana diambil (JPG/PNG · maks 5 MB).</div>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('peminjaman.upload_pickup_photo', $peminjaman) }}" enctype="multipart/form-data" class="action-task-card__body action-task-card__form action-task-card__form--upload" data-file-input-wrapper>
                                    @csrf
                                    <label class="action-task-card__file-trigger">
                                        <input type="file" name="foto_pickup" accept=".jpg,.jpeg,.png" required data-file-input>
                                        <span><i class="fas fa-upload" aria-hidden="true"></i> Pilih Foto Pengambilan</span>
                                    </label>
                                    <span class="action-task-card__file-name" data-file-name>Belum ada file yang dipilih.</span>
                                    <div class="action-task-card__footer">
                                        <button type="submit" class="action-btn action-btn--secondary">Unggah</button>
                                    </div>
                                    <p class="action-task-card__note">Pastikan foto jelas dan memuat seluruh sarana.</p>
                                </form>
                            </div>
                        @endif

                        @if($userCanUploadReturn)
                            <div class="action-task-card action-task-card--upload">
                                <div class="action-task-card__header">
                                    <div class="action-task-card__icon"><i class="fas fa-undo" aria-hidden="true"></i></div>
                                    <div class="action-task-card__heading">
                                        <div class="action-task-card__title">Unggah Foto Pengembalian</div>
                                        <div class="action-task-card__detail">Unggah dokumentasi saat sarana dikembalikan (JPG/PNG · maks 5 MB).</div>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('peminjaman.upload_return_photo', $peminjaman) }}" enctype="multipart/form-data" class="action-task-card__body action-task-card__form action-task-card__form--upload" data-file-input-wrapper>
                                    @csrf
                                    <label class="action-task-card__file-trigger">
                                        <input type="file" name="foto_return" accept=".jpg,.jpeg,.png" required data-file-input>
                                        <span><i class="fas fa-upload" aria-hidden="true"></i> Pilih Foto Pengembalian</span>
                                    </label>
                                    <span class="action-task-card__file-name" data-file-name>Belum ada file yang dipilih.</span>
                                    <div class="action-task-card__footer">
                                        <button type="submit" class="action-btn action-btn--neutral">Unggah</button>
                                    </div>
                                    <p class="action-task-card__note">Foto harus menampilkan kondisi sarana saat dikembalikan.</p>
                                </form>
                            </div>
                        @endif

                        @if($hasSerializedActions)
                            @foreach($serializedNeededActions as $serializedAction)
                                <div class="action-task-card action-task-card--units">
                                    <div class="action-task-card__header">
                                        <div class="action-task-card__icon"><i class="fas fa-list-check" aria-hidden="true"></i></div>
                                        <div class="action-task-card__heading">
                                            <div class="action-task-card__title">Sesuaikan Unit "{{ $serializedAction['sarana_name'] }}"</div>
                                            <div class="action-task-card__detail">Pilih maksimal {{ $serializedAction['unit_max'] }} unit sesuai kebutuhan peminjaman.</div>
                                        </div>
                                    </div>
                                    <div class="action-task-card__body">
                                        @if($serializedAction['selected_count'] > 0)
                                            <p class="action-task-card__description">Unit dipilih: {{ $serializedAction['selected_count'] }} / {{ $serializedAction['unit_max'] }}</p>
                                        @else
                                            <p class="action-task-card__description action-task-card__description--warning">Belum ada unit yang dipilih.</p>
                                        @endif
                                    </div>
                                    <div class="action-task-card__footer">
                                        <button type="button" class="action-btn action-btn--secondary" data-unit-modal-trigger data-item-id="{{ $serializedAction['item_id'] }}">Sesuaikan Unit</button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if($adminCanValidatePickup)
                            <div class="action-task-card action-task-card--validate">
                                <div class="action-task-card__header">
                                    <div class="action-task-card__icon"><i class="fas fa-check-double" aria-hidden="true"></i></div>
                                    <div class="action-task-card__heading">
                                        <div class="action-task-card__title">Validasi Pengambilan</div>
                                        <div class="action-task-card__detail">Konfirmasi bahwa sarana telah diambil oleh peminjam sesuai jadwal.</div>
                                    </div>
                                </div>
                                <div class="action-task-card__body">
                                    <p class="action-task-card__description">Gunakan fitur ini setelah mengecek bukti pengambilan dan memastikan jumlah sarana yang dibawa sesuai permohonan.</p>
                                </div>
                                <form method="POST" action="{{ route('peminjaman.validate_pickup', $peminjaman) }}" class="action-task-card__footer action-task-card__form">
                                    @csrf
                                    <button type="submit" class="action-btn action-btn--validate">Validasi Pengambilan</button>
                                </form>
                                <p class="action-task-card__note">Periksa kesesuaian data dan dokumentasi sebelum validasi.</p>
                            </div>
                        @endif

                        @if($adminCanValidateReturn)
                            <div class="action-task-card action-task-card--validate">
                                <div class="action-task-card__header">
                                    <div class="action-task-card__icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></div>
                                    <div class="action-task-card__heading">
                                        <div class="action-task-card__title">Validasi Pengembalian</div>
                                        <div class="action-task-card__detail">Konfirmasi bahwa sarana telah dikembalikan lengkap dan sesuai kondisi.</div>
                                    </div>
                                </div>
                                <div class="action-task-card__body">
                                    <p class="action-task-card__description">Pastikan seluruh item dicek ulang, termasuk jumlah dan kondisi fisik, sebelum menandai peminjaman sebagai selesai.</p>
                                </div>
                                <form method="POST" action="{{ route('peminjaman.validate_return', $peminjaman) }}" class="action-task-card__footer action-task-card__form">
                                    @csrf
                                    <button type="submit" class="action-btn action-btn--validate">Validasi Pengembalian</button>
                                </form>
                                <p class="action-task-card__note">Pastikan seluruh sarana telah diverifikasi sebelum menyelesaikan validasi.</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if(!$hasApprovalActions && !$hasSarprasActions)
                    <div class="action-empty-state">
                        <i class="fas fa-check"></i>
                        <p>Tidak ada aksi persetujuan yang perlu Anda proses saat ini.</p>
                    </div>
                @endif
            </div>

            <div class="form-section">
                <h3 class="section-title">Status Approval</h3>
                <div class="table-section">
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tipe</th>
                                    <th>Approver</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($peminjaman->approvalWorkflow ?? []) as $wf)
                                    <tr>
                                        <td>{{ str_replace('_', ' ', $wf->approval_type) }}</td>
                                        <td>{{ $wf->approver->name ?? '-' }}</td>
                                        <td>{{ $wf->approval_level }}</td>
                                        <td>
                                            <span class="status-badge status-{{ $wf->status }}">{{ ucfirst($wf->status) }}</span>
                                        </td>
                                        <td>
                                            @if($wf->approved_at) {{ \Carbon\Carbon::parse($wf->approved_at)->format('d/m/Y H:i') }} @endif
                                            @if($wf->rejected_at) {{ \Carbon\Carbon::parse($wf->rejected_at)->format('d/m/Y H:i') }} @endif
                                        </td>
                                        <td>{{ $wf->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted">Belum ada workflow approval.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>

    @if($peminjaman->konflik)
        <div class="form-section">
            <h3 class="section-title">Konflik Peminjaman</h3>
            <div class="override-timeline">
                <div class="override-event">
                    <div class="override-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="override-content">
                        <div class="override-header">
                            <span class="override-badge">Konflik ID</span>
                            <span class="override-meta">{{ $peminjaman->konflik }}</span>
                        </div>
                        <div class="override-body">
                            <p class="override-text">Peminjaman ini berbenturan dengan peminjaman lain pada slot/prasarana yang sama.</p>
                            @if(($konflikMembers ?? collect())->isNotEmpty())
                                <table class="table mt-2">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama Pengaju</th>
                                            <th>Status</th>
                                            <th>Diajukan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($konflikMembers as $member)
                                            <tr>
                                                <td><a href="{{ route('peminjaman.show', $member->id) }}">#{{ $member->id }}</a></td>
                                                <td>{{ $member->user->name ?? '-' }}</td>
                                                <td><span class="status-badge status-{{ $member->status }}">{{ ucfirst(str_replace('_',' ', $member->status)) }}</span></td>
                                                <td>{{ $member->created_at?->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="override-notes">Belum ada peminjaman lain dalam konflik ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>

<div class="modal-overlay" id="rejectDecisionModal" data-reject-modal aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="rejectDecisionTitle">
        <form method="POST" action="{{ route('peminjaman.reject', $peminjaman) }}" id="rejectDecisionForm" class="js-approval-submit" data-action="reject">
            @csrf
            <input type="hidden" name="approval_type" id="rejectApprovalType">
            <input type="hidden" name="sarpras_id" id="rejectReferenceId">
            <div class="modal-card__header">
                <div>
                    <h5 class="modal-card__title" id="rejectDecisionTitle">Tolak Keputusan</h5>
                    <p class="modal-card__subtitle" id="rejectDecisionSubtitle"></p>
                </div>
                <button type="button" class="modal-card__close" data-reject-close aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-card__body">
                <p class="modal-card__note" id="rejectDecisionDescription"></p>
                <div class="form-group mb-0">
                    <label for="rejectDecisionReason" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea id="rejectDecisionReason" name="reason" class="form-control js-reject-input" rows="4" placeholder="Contoh: Jadwal sarana berbenturan dengan kegiatan lain." required></textarea>
                </div>
            </div>
            <div class="modal-card__footer">
                <button type="button" class="btn btn-secondary" data-reject-close>
                    <i class="fas fa-times"></i>
                    Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-paper-plane"></i>
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Batalkan Peminjaman -->
<div class="modal-overlay" id="cancelModalOverlay" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-labelledby="cancelModalTitle" aria-modal="true">
        <form method="POST" action="{{ route('peminjaman.cancel', $peminjaman) }}" id="cancelPeminjamanForm">
            @csrf
            @method('PATCH')
            <div class="modal-card__header">
                <h5 class="modal-card__title" id="cancelModalTitle">Batalkan Peminjaman</h5>
                <button type="button" class="modal-card__close" aria-label="Tutup" data-cancel-modal-close>&times;</button>
            </div>
            <div class="modal-card__body">
                <p class="modal-card__note">Pengajuan ini akan dibatalkan. Mohon pastikan alasan pembatalan diisi dengan jelas.</p>
                <div class="form-group">
                    <label for="cancel_reason" class="form-label">Alasan Pembatalan <span class="text-danger">*</span></label>
                    <textarea name="cancelled_reason" id="cancel_reason" class="form-control" rows="3" required placeholder="Contoh: Jadwal acara berubah sehingga sarpras tidak diperlukan."></textarea>
                </div>
            </div>
            <div class="modal-card__footer">
                <button type="button" class="btn btn-secondary" data-cancel-modal-close>
                    <i class="fas fa-times"></i>
                    Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-ban"></i>
                    Konfirmasi Pembatalan
                </button>
            </div>
        </form>
    </div>
</div>

@if(!empty($serializedNeededActions))
    @foreach($serializedNeededActions as $serializedAction)
    <div class="unit-modal-overlay" id="unitModal-{{ $serializedAction['item_id'] }}" aria-hidden="true">
        <div class="unit-modal" role="dialog" aria-labelledby="unitModalTitle-{{ $serializedAction['item_id'] }}">
            <div class="unit-modal__header">
                <h5 class="unit-modal__title" id="unitModalTitle-{{ $serializedAction['item_id'] }}">Sesuaikan Unit "{{ $serializedAction['sarana_name'] }}"</h5>
                <button type="button" class="unit-modal__close" data-unit-modal-close="{{ $serializedAction['item_id'] }}" aria-label="Tutup">&times;</button>
            </div>
            <div class="unit-modal__body">
                <p class="unit-modal__description">Pilih maksimal {{ $serializedAction['unit_max'] }} unit sesuai kebutuhan peminjaman.</p>
                <form method="POST" action="{{ route('peminjaman.assign_units', $peminjaman->id) }}" class="unit-assign-form" data-max-selectable="{{ $serializedAction['unit_max'] }}">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $serializedAction['item_id'] }}">
                    <input type="hidden" data-initial-selected value="{{ $serializedAction['selected_count'] }}">
                    <input type="hidden" data-initial-max value="{{ $serializedAction['unit_max'] }}">
                    <div class="unit-modal__checkbox-wrapper">
                        <div class="unit-checkbox-list">
                            @foreach($serializedAction['units'] as $unit)
                                @php
                                    $isActive = $unit['is_assigned_to_this'];
                                    $isAvailable = $unit['status'] === 'tersedia' || $isActive;
                                    $statusLabel = $isActive
                                        ? 'Dipilih untuk peminjaman ini'
                                        : ($isAvailable ? ucfirst($unit['status']) : 'Digunakan di jadwal lain');
                                @endphp
                                <label class="unit-checkbox{{ $isActive ? ' unit-checkbox--selected' : '' }}{{ !$isAvailable ? ' unit-checkbox--disabled' : '' }}">
                                    <input type="checkbox" name="unit_selection[{{ $serializedAction['item_id'] }}][]" value="{{ $unit['id'] }}" {{ $isActive ? 'checked' : '' }} {{ !$isAvailable ? 'disabled' : '' }}>
                                    <span class="unit-checkbox__content">
                                        <span class="unit-checkbox__code">{{ $unit['unit_code'] }}</span>
                                        <span class="unit-checkbox__status">{{ $statusLabel }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="sarpras-item__assign-actions">
                        <span class="sarpras-item__assign-note">Dipilih: <span data-selected-counter>{{ $serializedAction['selected_count'] }}</span> / {{ $serializedAction['unit_max'] }}</span>
                        <button type="submit" class="action-btn action-btn--primary action-btn--sm">Simpan</button>
                    </div>
                    <p class="sarpras-item__assign-warning" data-selection-warning>Jumlah pilihan tidak boleh melebihi {{ $serializedAction['unit_max'] }} unit.</p>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endif

@endsection

@push('scripts')
<div class="modal-overlay decision-modal-overlay" id="decisionModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="decisionModalTitle">
        <div class="modal-card__header">
            <h5 class="modal-card__title" id="decisionModalTitle">Konfirmasi Keputusan</h5>
            <button type="button" class="modal-card__close" data-decision-close aria-label="Tutup">&times;</button>
        </div>
        <div class="modal-card__body">
            <p class="modal-card__note" data-decision-message></p>
            <p class="modal-card__subtitle" data-decision-note></p>
        </div>
        <div class="modal-card__footer">
            <button type="button" class="btn btn-secondary" data-decision-close>
                <i class="fas fa-times"></i>
                Batal
            </button>
            <button type="button" class="btn btn-primary" data-decision-confirm>
                <i class="fas fa-check"></i>
                Ya, lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
function updateScrollLock(){
    const htmlEl = document.documentElement;
    const bodyEl = document.body;
    const activeOverlays = document.querySelectorAll('.modal-overlay.active, .unit-modal-overlay.active, .decision-modal-overlay.active, [data-reject-modal].active');
    const hasActive = activeOverlays.length > 0;
    if(hasActive){
        const scrollBarWidth = window.innerWidth - htmlEl.clientWidth;
        htmlEl.classList.add('modal-open');
        bodyEl.classList.add('modal-open');
        bodyEl.style.paddingRight = scrollBarWidth > 0 ? `${scrollBarWidth}px` : '';
    } else {
        htmlEl.classList.remove('modal-open');
        bodyEl.classList.remove('modal-open');
        bodyEl.style.paddingRight = '';
    }
}

window.addEventListener('resize', () => {
    if(document.documentElement.classList.contains('modal-open')){
        updateScrollLock();
    }
});

const modalOverlay = document.getElementById('cancelModalOverlay');
const cancelForm = document.getElementById('cancelPeminjamanForm');
const cancelReasonField = document.getElementById('cancel_reason');

function toggleCancelModal(show) {
    if (!modalOverlay) return;
    if (show) {
        modalOverlay.classList.add('active');
        modalOverlay.setAttribute('aria-hidden', 'false');
        if (cancelReasonField) {
            setTimeout(() => cancelReasonField.focus(), 50);
        }
    } else {
        modalOverlay.classList.remove('active');
        modalOverlay.setAttribute('aria-hidden', 'true');
    }
    updateScrollLock();
}

(function(){
    if(cancelForm){
        cancelForm.addEventListener('submit', function(e){
            if(!cancelReasonField.value.trim()){
                e.preventDefault();
                alert('Alasan pembatalan wajib diisi.');
                cancelReasonField.focus();
                return false;
            }
            if(!confirm('Yakin ingin membatalkan peminjaman ini?')){
                e.preventDefault();
                return false;
            }
        });
    }

    if(modalOverlay){
        modalOverlay.addEventListener('click', function(e){
            if(e.target === modalOverlay){
                toggleCancelModal(false);
            }
        });

        const closeButtons = modalOverlay.querySelectorAll('[data-cancel-modal-close]');
        closeButtons.forEach(btn => btn.addEventListener('click', () => toggleCancelModal(false)));
    }

    window.addEventListener('keydown', function(e){
        if(e.key === 'Escape' && modalOverlay?.classList.contains('active')){
            toggleCancelModal(false);
        }
    });
})();

if(typeof updateScrollLock === 'function'){ updateScrollLock(); }

(function(){
    const accordionButtons = document.querySelectorAll('[data-accordion-target]');
    accordionButtons.forEach(button => {
        const targetId = button.getAttribute('data-accordion-target');
        const target = document.getElementById(targetId);
        if(!target) return;

        button.addEventListener('click', () => {
            const isHidden = target.hasAttribute('hidden');
            if(isHidden){
                target.removeAttribute('hidden');
                button.classList.add('is-open');
                button.setAttribute('aria-expanded', 'true');
            } else {
                target.setAttribute('hidden', '');
                button.classList.remove('is-open');
                button.setAttribute('aria-expanded', 'false');
            }
        });
    });
})();

(function(){
    const syncBodyScroll = () => {
        updateScrollLock();
    };

    const openModal = (modal) => {
        if(!modal) return;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        syncBodyScroll();
        const firstCheckbox = modal.querySelector('input[type="checkbox"]:not([disabled])');
        if(firstCheckbox){
            firstCheckbox.focus({ preventScroll: true });
        }
    };

    const closeModal = (modal) => {
        if(!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        syncBodyScroll();
    };

    const triggers = document.querySelectorAll('[data-unit-modal-trigger]');
    triggers.forEach(trigger => {
        const itemId = trigger.getAttribute('data-item-id');
        const modal = document.getElementById(`unitModal-${itemId}`);
        if(!modal) return;

        const closeButtons = modal.querySelectorAll(`[data-unit-modal-close="${itemId}"]`);

        trigger.addEventListener('click', () => openModal(modal));

        closeButtons.forEach(btn => btn.addEventListener('click', () => closeModal(modal)));

        modal.addEventListener('click', (event) => {
            if(event.target === modal){
                closeModal(modal);
            }
        });
    });

    window.addEventListener('keydown', (event) => {
        if(event.key !== 'Escape') return;
        const activeModal = document.querySelector('.unit-modal-overlay.active');
        if(activeModal){
            closeModal(activeModal);
        }
    });
})();

(function(){
    const forms = document.querySelectorAll('.unit-assign-form');
    forms.forEach(form => {
        const maxSelectable = parseInt(form.dataset.maxSelectable || '0', 10);
        const counterEls = form.querySelectorAll('[data-selected-counter]');
        const warningEl = form.querySelector('[data-selection-warning]');
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');

        const updateState = () => {
            let selectedCount = 0;
            checkboxes.forEach(cb => {
                if(cb.checked) selectedCount++;
            });

            counterEls.forEach(el => el.textContent = selectedCount.toString());

            const overLimit = selectedCount > maxSelectable;
            if (warningEl) {
                warningEl.style.display = overLimit ? 'block' : 'none';
            }

            if (overLimit) {
                checkboxes.forEach(cb => {
                    if(!cb.checked) {
                        cb.disabled = true;
                        cb.closest('.unit-checkbox')?.classList.add('disabled');
                    }
                });
            } else {
                checkboxes.forEach(cb => {
                    if(cb.dataset.initialDisabled !== 'true'){ 
                        cb.disabled = false;
                        cb.closest('.unit-checkbox')?.classList.remove('disabled');
                    }
                });
            }
        };

        checkboxes.forEach(cb => {
            cb.dataset.initialDisabled = cb.disabled ? 'true' : 'false';
            cb.addEventListener('change', updateState);
        });

        form.addEventListener('submit', function(e){
            const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            if(selectedCount > maxSelectable){
                e.preventDefault();
                alert(`Tidak boleh memilih lebih dari ${maxSelectable} unit.`);
                return false;
            }
        });

        updateState();
    });
})();

const decisionModalOverlay = document.getElementById('decisionModal');
const decisionMessage = decisionModalOverlay?.querySelector('[data-decision-message]');
const decisionNote = decisionModalOverlay?.querySelector('[data-decision-note]');
const decisionConfirmBtn = decisionModalOverlay?.querySelector('[data-decision-confirm]');
const decisionCloseButtons = decisionModalOverlay?.querySelectorAll('[data-decision-close]');
let pendingDecisionForm = null;

function openDecisionModal(message, note, onConfirm){
    if(!decisionModalOverlay) return;
    pendingDecisionForm = onConfirm;
    if(decisionMessage) decisionMessage.textContent = message;
    if(decisionNote) decisionNote.textContent = note || '';
    decisionModalOverlay.classList.add('active');
    decisionModalOverlay.setAttribute('aria-hidden', 'false');
    updateScrollLock();
    decisionConfirmBtn?.focus();
}

function closeDecisionModal(){
    if(!decisionModalOverlay) return;
    decisionModalOverlay.classList.remove('active');
    decisionModalOverlay.setAttribute('aria-hidden', 'true');
    updateScrollLock();
    pendingDecisionForm = null;
}

decisionCloseButtons?.forEach(btn => btn.addEventListener('click', closeDecisionModal));

decisionModalOverlay?.addEventListener('click', (event) => {
    if(event.target === decisionModalOverlay){
        closeDecisionModal();
    }
});

decisionConfirmBtn?.addEventListener('click', () => {
    if(typeof pendingDecisionForm === 'function'){
        pendingDecisionForm();
    }
    closeDecisionModal();
});

window.addEventListener('keydown', (event) => {
    if(event.key === 'Escape' && decisionModalOverlay?.classList.contains('active')){
        closeDecisionModal();
    }
});

function registerApprovalForms(scope=document){
    const approvalForms = scope.querySelectorAll('.js-approval-submit');
    approvalForms.forEach(form => {
        if(form.dataset.listenerAttached === 'true') return;
        form.dataset.listenerAttached = 'true';

        form.addEventListener('submit', function(event){
            event.preventDefault();

            const action = form.dataset.action;
            const label = form.dataset.approvalLabel || 'item ini';
            const typeLabel = form.dataset.approvalTypeLabel || form.dataset.approvalType || 'approval';
            const eventName = form.dataset.eventName || document.querySelector('[data-event-name]')?.dataset.eventName || '';
            const reasonInput = form.querySelector('.js-reject-input');

            if(action === 'reject'){
                const reason = reasonInput?.value.trim();
                if(!reason){
                    reasonInput?.classList.add('approval-reject-field__input--error');
                    reasonInput?.focus();
                    return;
                }
                reasonInput.classList.remove('approval-reject-field__input--error');
            }

            const messageLines = [];
            if(action === 'approve'){
                messageLines.push(`Setujui ${label}?`);
            } else {
                messageLines.push(`Tolak ${label}?`);
            }
            messageLines.push(`Jenis keputusan: ${typeLabel}`);
            if(eventName){
                messageLines.push(`Untuk acara: ${eventName}`);
            }

            const noteText = action === 'approve'
                ? 'Keputusan ini akan mengabari peminjam dan melanjutkan proses sarpras.'
                : 'Alasan penolakan akan dikirimkan kepada peminjam dan admin terkait.';

            openDecisionModal(messageLines.join('\n'), noteText, () => form.submit());
        });
    });
}

registerApprovalForms();
(function(){
    const wrappers = document.querySelectorAll('[data-file-input-wrapper]');
    wrappers.forEach(wrapper => {
        const input = wrapper.querySelector('[data-file-input]');
        const fileNameTarget = wrapper.querySelector('[data-file-name]');
        if(!input || !fileNameTarget) return;

        const updateFileName = () => {
            const file = input.files && input.files[0];
            fileNameTarget.textContent = file ? file.name : 'Belum ada file yang dipilih.';
        };

        updateFileName();
        input.addEventListener('change', updateFileName);
    });
})();

(function(){
    const modalOverlay = document.getElementById('rejectDecisionModal');
    if(!modalOverlay) return;

    const rejectForm = modalOverlay.querySelector('#rejectDecisionForm');
    const approvalTypeInput = modalOverlay.querySelector('#rejectApprovalType');
    const referenceInput = modalOverlay.querySelector('#rejectReferenceId');
    const subtitleEl = modalOverlay.querySelector('#rejectDecisionSubtitle');
    const descriptionEl = modalOverlay.querySelector('#rejectDecisionDescription');
    const reasonField = modalOverlay.querySelector('#rejectDecisionReason');
    const titleEl = modalOverlay.querySelector('#rejectDecisionTitle');

    const openModal = () => {
        modalOverlay.classList.add('active');
        modalOverlay.setAttribute('aria-hidden', 'false');
        if(reasonField){
            reasonField.value = reasonField.value || '';
            reasonField.focus({ preventScroll: true });
        }
        updateScrollLock();
    };

    const closeModal = () => {
        modalOverlay.classList.remove('active');
        modalOverlay.setAttribute('aria-hidden', 'true');
        updateScrollLock();
    };

    document.querySelectorAll('[data-reject-open]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const typeKey = trigger.getAttribute('data-approval-type') || '';
            const typeLabel = trigger.getAttribute('data-approval-type-label') || 'Persetujuan';
            const approvalLabel = trigger.getAttribute('data-approval-label') || 'item ini';
            const approvalLevel = trigger.getAttribute('data-approval-level') || '-';
            const referenceId = trigger.getAttribute('data-reference-id') || '';
            const requesterName = trigger.getAttribute('data-requester-name') || 'peminjam';
            const eventName = trigger.getAttribute('data-event-name') || '';

            if(titleEl){
                titleEl.textContent = `Tolak ${typeLabel}`;
            }
            if(subtitleEl){
                subtitleEl.textContent = `${approvalLabel} · Level ${approvalLevel}`;
            }
            if(descriptionEl){
                const parts = [`Berikan alasan penolakan agar ${requesterName} memahami keputusan Anda.`];
                if(eventName){
                    parts.push(`Acara: ${eventName}`);
                }
                descriptionEl.textContent = parts.join(' ');
            }

            if(approvalTypeInput){
                approvalTypeInput.value = typeKey;
            }
            if(referenceInput){
                if(referenceId){
                    referenceInput.value = referenceId;
                } else {
                    referenceInput.value = '';
                }
            }

            if(rejectForm){
                rejectForm.dataset.approvalType = typeKey;
                rejectForm.dataset.approvalTypeLabel = typeLabel;
                rejectForm.dataset.approvalLabel = approvalLabel;
                rejectForm.dataset.eventName = eventName;
            }

            if(reasonField){
                reasonField.value = '';
            }

            openModal();
        });
    });

    modalOverlay.querySelectorAll('[data-reject-close]').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    modalOverlay.addEventListener('click', (event) => {
        if(event.target === modalOverlay){
            closeModal();
        }
    });

    window.addEventListener('keydown', (event) => {
        if(event.key === 'Escape' && modalOverlay.classList.contains('active')){
            closeModal();
        }
    });
})();

function cancelAction(e){
    const reason = prompt('Alasan pembatalan?');
    if(!reason){ e.preventDefault(); return false; }
    var input = document.getElementById('cancelled_reason_input');
    if(input){ input.value = reason; }
    return confirm('Yakin ingin membatalkan peminjaman ini?');
}
</script>
@endpush