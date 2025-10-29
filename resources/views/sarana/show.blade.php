@extends('layouts.app')

@section('title', 'Detail Sarana')
@section('subtitle', 'Informasi lengkap sarana')

@section('header-actions')
<a href="{{ route('sarana.index') }}" class="btn btn-secondary btn-cancel">
    <i class="fas fa-arrow-left"></i>
    Kembali
</a>
@can('sarpras.edit')
<a href="{{ route('sarana.edit', $sarana->id) }}" class="btn btn-primary">
    <i class="fas fa-edit"></i>
    Edit Sarana
</a>
@endcan
@can('sarpras.approval_assign')
<button type="button" class="btn btn-info" onclick="openAssignApproverModal()">
    <i class="fas fa-user-check"></i>
    Assign Approval
</button>
@endcan
@endsection

@section('content')
<section class="detail-page sarana-detail-page">
    <div class="card user-detail-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-tools sarana-detail-icon"></i>
                <div class="card-header__text">
                    <h2 class="card-title">{{ $sarana->name }}</h2>
                    <p class="card-subtitle">{{ $sarana->kategori->name }}</p>
                </div>
            </div>
            <div class="card-header__actions">
                <span class="sarana-type-badge sarana-type-{{ $sarana->type }}">
                    <i class="fas {{ $sarana->type === 'serialized' ? 'fa-layer-group' : 'fa-cubes' }}"></i>
                    {{ ucfirst($sarana->type) }}
                </span>
                <span class="sarana-meta-chip">
                    <i class="fas fa-user"></i>
                    {{ $sarana->creator->name ?? 'Tidak diketahui' }}
                </span>
                <span class="sarana-meta-chip">
                    <i class="fas fa-calendar-plus"></i>
                    {{ $sarana->created_at->format('d M Y H:i') }}
                </span>
            </div>
        </div>

        <div class="card-main">
            @php
                $jumlahDipinjam = $sarana->peminjamanItems()
                    ->whereHas('peminjaman', function($query) {
                        $query->whereIn('status', ['approved', 'picked_up']);
                    })
                    ->sum('qty_approved');
            @endphp

            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $sarana->lokasi ?: 'Lokasi belum diisi' }}
                </div>
                <div class="chip">
                    <i class="fas fa-cubes"></i>
                    {{ $sarana->jumlah_total }} Total Unit
                </div>
                <div class="chip">
                    <i class="fas fa-check-circle"></i>
                    {{ $sarana->jumlah_tersedia }} Tersedia
                </div>
                <div class="chip">
                    <i class="fas fa-hand-holding"></i>
                    {{ $jumlahDipinjam }} Dipinjam
                </div>
                <div class="chip">
                    <i class="fas fa-clock"></i>
                    Diperbarui {{ $sarana->updated_at->diffForHumans() }}
                </div>
            </div>

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Dasar</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Sarana</span>
                            <span class="detail-value">{{ $sarana->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Kategori</span>
                            <span class="detail-value">
                                <span class="category-badge">
                                    <i class="fas fa-bookmark"></i>
                                    {{ $sarana->kategori->name }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe</span>
                            <span class="detail-value">
                                <span class="sarana-type-badge sarana-type-{{ $sarana->type }}">
                                    <i class="fas {{ $sarana->type === 'serialized' ? 'fa-layer-group' : 'fa-cubes' }}"></i>
                                    {{ ucfirst($sarana->type) }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Lokasi</span>
                            <span class="detail-value">{{ $sarana->lokasi ?: '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Dibuat Oleh</span>
                            <span class="detail-value">{{ $sarana->creator->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tanggal Dibuat</span>
                            <span class="detail-value">{{ $sarana->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Terakhir Diupdate</span>
                            <span class="detail-value">{{ $sarana->updated_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="detail-row detail-row--stacked">
                            <span class="detail-label">Deskripsi</span>
                            <span class="detail-value detail-value--multiline">{{ $sarana->description ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                @if($sarana->image_url)
                <div class="form-section">
                    <h3 class="section-title">Gambar Sarana</h3>
                    <div class="detail-block image-block">
                        <img src="{{ Storage::url($sarana->image_url) }}" alt="{{ $sarana->name }}" class="sarana-image">
                    </div>
                </div>
                @endif
            </div>

            <div class="form-section">
                <h3 class="section-title">Statistik Kuantitas</h3>
                <div class="detail-block">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->jumlah_total }}</div>
                                <div class="stat-label">Total Unit</div>
                            </div>
                        </div>
                        <div class="stat-card stat-success">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->jumlah_tersedia }}</div>
                                <div class="stat-label">Tersedia</div>
                            </div>
                        </div>
                        <div class="stat-card stat-warning">
                            <div class="stat-icon"><i class="fas fa-hand-holding"></i></div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $jumlahDipinjam }}</div>
                                <div class="stat-label">Dipinjam</div>
                            </div>
                        </div>
                        <div class="stat-card stat-danger">
                            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->jumlah_rusak }}</div>
                                <div class="stat-label">Rusak</div>
                            </div>
                        </div>
                        <div class="stat-card stat-info">
                            <div class="stat-icon"><i class="fas fa-tools"></i></div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->jumlah_maintenance }}</div>
                                <div class="stat-label">Maintenance</div>
                            </div>
                        </div>
                        <div class="stat-card stat-danger">
                            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                            <div class="stat-content">
                                <div class="stat-value">{{ $sarana->jumlah_hilang }}</div>
                                <div class="stat-label">Hilang</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($sarana->type == 'serialized')
            <div class="form-section">
                <h3 class="section-title">Manajemen Unit Serialized</h3>
                <div class="detail-block">
                    <div class="unit-management-header">
                        <div class="unit-management-info">
                            <p>Sarana ini menggunakan sistem serialized dengan unit-unit yang memiliki nomor seri unik.</p>
                        </div>
                        @can('sarpras.unit_manage')
                        <div class="unit-management-actions">
                            <a href="{{ route('sarana.units', $sarana->id) }}" class="btn btn-primary">
                                <i class="fas fa-cogs"></i>
                                Kelola Unit
                            </a>
                        </div>
                        @endcan
                    </div>

                    @if($sarana->units()->count() > 0)
                        <div class="units-table-wrapper">
                            <table class="units-table">
                                <thead>
                                    <tr>
                                        <th>Unit Code</th>
                                        <th>Status</th>
                                        <th>Peminjam</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Dibuat</th>
                                        <th>Diupdate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sarana->units()->orderBy('unit_code')->get() as $unit)
                                        @php
                                            $borrowing = $unit->peminjamanItemUnits()
                                                ->whereHas('peminjaman', function($query) {
                                                    $query->whereIn('status', ['approved', 'picked_up']);
                                                })
                                                ->with('peminjaman.user')
                                                ->first();

                                            $isBorrowed = $borrowing !== null;
                                        @endphp
                                        <tr>
                                            <td class="unit-code">{{ $unit->unit_code }}</td>
                                            <td>
                                                @if($isBorrowed)
                                                    <span class="badge badge-warning">Dipinjam</span>
                                                @else
                                                    @switch($unit->unit_status)
                                                        @case('tersedia')
                                                            <span class="badge badge-status_active">Tersedia</span>
                                                            @break
                                                        @case('rusak')
                                                            <span class="badge badge-status_blocked">Rusak</span>
                                                            @break
                                                        @case('maintenance')
                                                            <span class="badge badge-overtime">Maintenance</span>
                                                            @break
                                                        @case('hilang')
                                                            <span class="badge badge-status_blocked">Hilang</span>
                                                            @break
                                                    @endswitch
                                                @endif
                                            </td>
                                            <td>
                                                @if($isBorrowed)
                                                    {{ $borrowing->peminjaman->user->name ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($isBorrowed)
                                                    {{ optional($borrowing->peminjaman->end_date)->format('d M Y') ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $unit->created_at->format('d M Y') }}</td>
                                            <td>{{ $unit->updated_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-box-open empty-state__icon"></i>
                            <h4 class="empty-state__title">Belum Ada Unit</h4>
                            <p class="empty-state__description">
                                Sarana ini belum memiliki unit yang terdaftar.
                                @can('sarpras.unit_manage')
                                    Klik "Kelola Unit" untuk menambahkan unit pertama.
                                @endcan
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <div class="form-section">
                <h3 class="section-title">Peminjaman Terkini</h3>
                <div class="detail-block">
                    @php
                        $recentBorrowings = $sarana->peminjamanItems()
                            ->with(['peminjaman.user', 'peminjaman'])
                            ->whereHas('peminjaman', function($query) {
                                $query->whereIn('status', ['approved', 'picked_up']);
                            })
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp

                    @if($recentBorrowings->count() > 0)
                        <div class="borrowings-list">
                            @foreach($recentBorrowings as $item)
                                <div class="borrowing-item">
                                    <div class="borrowing-info">
                                        <div class="borrowing-user">{{ $item->peminjaman->user->name }}</div>
                                        <div class="borrowing-event">{{ $item->peminjaman->event_name }}</div>
                                        <div class="borrowing-dates">
                                            {{ $item->peminjaman->start_date->format('d M Y') }}
                                            &ndash;
                                            {{ $item->peminjaman->end_date->format('d M Y') }}
                                        </div>
                                    </div>
                                    <div class="borrowing-status">
                                        @switch($item->peminjaman->status)
                                            @case('approved')
                                                <span class="badge badge-status_active">Disetujui</span>
                                                @break
                                            @case('picked_up')
                                                <span class="badge badge-in_progress">Diambil</span>
                                                @break
                                        @endswitch
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-calendar-times empty-state__icon"></i>
                            <h4 class="empty-state__title">Tidak Ada Peminjaman Aktif</h4>
                            <p class="empty-state__description">
                                Sarana ini tidak sedang dipinjam oleh siapapun.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            @can('sarpras.approval_assign')
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user-check"></i>
                    Approvers
                </h3>
                <div class="detail-block">
                    @if($sarana->approvers->count() > 0)
                        <div class="approvers-list">
                            @foreach($sarana->approvers as $approver)
                                <div class="approver-item">
                                    <div class="approver-info">
                                        <div class="approver-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="approver-details">
                                            <strong>{{ $approver->approver->name }}</strong>
                                            <small>{{ $approver->approver->email }}</small>
                                        </div>
                                    </div>
                                    <div class="approver-level">
                                        <span class="badge badge-level-{{ $approver->approval_level }}">
                                            Level {{ $approver->approval_level }}
                                        </span>
                                    </div>
                                    <div class="approver-status">
                                        <span class="badge {{ $approver->is_active ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $approver->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </div>
                                    <div class="approver-actions">
                                        <button class="btn btn-sm btn-warning" onclick="editApprover({{ $approver->id }}, {{ $approver->approval_level }}, {{ $approver->is_active ? 'true' : 'false' }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteApprover({{ $approver->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-user-slash empty-state__icon"></i>
                            <p class="empty-state__description">Belum ada approver yang ditetapkan untuk sarana ini.</p>
                        </div>
                    @endif
                </div>
            </div>
            @endcan
        </div>
    </div>
</section>
@endsection

@can('sarpras.approval_assign')
<!-- Modal Assign Approver -->
<div id="assignApproverModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-user-check"></i>
                Assign Approver untuk {{ $sarana->name }}
            </h3>
            <button type="button" class="close" onclick="closeAssignApproverModal()">&times;</button>
        </div>
        <form action="{{ route('approval-assignment.sarana.store') }}" method="POST" onsubmit="return validateAssignForm(this)">
            @csrf
            <input type="hidden" name="sarana_id" value="{{ $sarana->id }}">
            <div class="modal-body">
                <div class="form-group">
                    <label for="approver_id">Approver <span class="required">*</span></label>
                    @php
                        $assignedApproverIds = $sarana->approvers->pluck('approver_id')->all();
                    @endphp
                    <select name="approver_id" id="approver_id" required>
                        <option value="">Pilih Approver</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ in_array($user->id, $assignedApproverIds) ? 'disabled' : '' }} {{ old('approver_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('approver_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="approval_level">Level Approval <span class="required">*</span></label>
                    <select name="approval_level" id="approval_level" required>
                        <option value="">Pilih Level</option>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('approval_level') == $i ? 'selected' : '' }}>
                                Level {{ $i }} {{ $i == 1 ? '(Primary)' : ($i == 2 ? '(Secondary)' : ($i == 3 ? '(Tertiary)' : '')) }}
                            </option>
                        @endfor
                    </select>
                    @error('approval_level')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignApproverModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Approver -->
<div id="editApproverModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-edit"></i>
                Edit Approver
            </h3>
            <button type="button" class="close" onclick="closeEditApproverModal()">&times;</button>
        </div>
        <form id="editApproverForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_approval_level">Level Approval <span class="required">*</span></label>
                    <select name="approval_level" id="edit_approval_level" required>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">
                                Level {{ $i }} {{ $i == 1 ? '(Primary)' : ($i == 2 ? '(Secondary)' : ($i == 3 ? '(Tertiary)' : '')) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_is_active">Status</label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <label for="edit_is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditApproverModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/sarana.css') }}?v={{ filemtime(public_path('css/sarana.css')) }}">
<style>
.sarana-detail-icon {
    font-size: 24px;
    color: #4b5563;
}

.card-header__text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.card-header__title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
}

.card-subtitle {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.card-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.sarana-type-badge,
.sarana-meta-chip,
.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.sarana-type-badge {
    border: 1px solid transparent;
}

.sarana-type-serialized {
    background: #e0f2fe;
    border-color: #bae6fd;
    color: #0369a1;
}

.sarana-type-pooled {
    background: #ede9fe;
    border-color: #ddd6fe;
    color: #5b21b6;
}

.sarana-meta-chip {
    background: #f5f7fa;
    border: 1px solid #e5e7eb;
    color: #4b5563;
    font-weight: 500;
}

.category-badge {
    background: #fff7ed;
    border: 1px solid #fde7c7;
    color: #c2410c;
}

.summary-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f5f7fa;
    border: 1px solid #e0e0e0;
    color: #1f2937;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 13px;
}

.chip i {
    color: #6b7280;
}

.image-block {
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f9fafb;
}

.basic-info-block {
    padding: 0;
}

.basic-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    padding: 18px;
}

.basic-info-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 14px;
    border-radius: 10px;
    border: 1px solid #f1f5f9;
    background: #fcfdff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.basic-info-item--full {
    grid-column: 1 / -1;
}

.basic-info-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.basic-info-value {
    font-size: 14px;
    color: #111827;
    line-height: 1.5;
}

.sarana-image {
    max-width: 100%;
    max-height: 320px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
}

.stat-card .stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #64748b;
    font-size: 18px;
}

.stat-card.stat-success {
    background: #ecfdf5;
    border-color: #d1fae5;
}

.stat-card.stat-success .stat-icon {
    background: #bbf7d0;
    color: #047857;
}

.stat-card.stat-warning {
    background: #fff7ed;
    border-color: #ffedd5;
}

.stat-card.stat-warning .stat-icon {
    background: #fed7aa;
    color: #c2410c;
}

.stat-card.stat-info {
    background: #eff6ff;
    border-color: #dbeafe;
}

.stat-card.stat-info .stat-icon {
    background: #bfdbfe;
    color: #1d4ed8;
}

.stat-card.stat-danger {
    background: #fef2f2;
    border-color: #fee2e2;
}

.stat-card.stat-danger .stat-icon {
    background: #fecaca;
    color: #b91c1c;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 22px;
    font-weight: 600;
    color: #111827;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.unit-management-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
}

.unit-management-info p {
    margin: 0;
    color: #4b5563;
    font-size: 14px;
}

.detail-block {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-row--stacked {
    align-items: flex-start;
    flex-direction: column;
    gap: 6px;
}

.detail-label {
    font-size: 13px;
    color: #6b7280;
    min-width: 140px;
}

.detail-value {
    font-size: 14px;
    color: #1f2937;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
    text-align: right;
}

.detail-value--multiline {
    justify-content: flex-start;
    text-align: left;
    width: 100%;
}

.detail-value span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.units-table-wrapper {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.units-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 640px;
}

.units-table th,
.units-table td {
    padding: 12px;
    border-bottom: 1px solid #eef2f7;
    color: #1f2937;
    text-align: left;
}

.units-table th {
    background: #f8fafc;
    font-weight: 600;
}

.unit-code {
    font-family: "SFMono-Regular", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 32px 16px;
    border-radius: 10px;
    border: 1px dashed #e5e7eb;
    background: #f9fafb;
    color: #6b7280;
}

.empty-state__icon {
    font-size: 40px;
    color: #cbd5f5;
    margin-bottom: 12px;
}

.empty-state__title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 6px;
}

.empty-state__description {
    font-size: 14px;
    margin: 0;
}

.borrowings-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.borrowing-item {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f9fafb;
}

.borrowing-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    color: #1f2937;
}

.borrowing-user {
    font-weight: 600;
}

.borrowing-event {
    font-size: 13px;
    color: #4b5563;
}

.borrowing-dates {
    font-size: 12px;
    color: #6b7280;
}

.approvers-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.approver-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
}

.approver-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.approver-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #4b5563;
    display: flex;
    align-items: center;
    justify-content: center;
}

.approver-details strong {
    font-size: 14px;
    color: #1f2937;
}

.approver-details small {
    font-size: 12px;
    color: #6b7280;
}

.approver-level,
.approver-status {
    min-width: 80px;
}

.approver-actions {
    display: flex;
    gap: 8px;
}

.badge-level-1 { background: #dc3545; color: #fff; }
.badge-level-2 { background: #fd7e14; color: #fff; }
.badge-level-3 { background: #ffc107; color: #1f2937; }
.badge-level-4 { background: #20c997; color: #fff; }
.badge-level-5 { background: #17a2b8; color: #fff; }
.badge-level-6 { background: #6f42c1; color: #fff; }
.badge-level-7 { background: #e83e8c; color: #fff; }
.badge-level-8 { background: #6c757d; color: #fff; }
.badge-level-9 { background: #343a40; color: #fff; }
.badge-level-10 { background: #000; color: #fff; }

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: #fff;
    border-radius: 12px;
    width: min(520px, 92%);
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header,
.modal-footer {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
    border-bottom: none;
    justify-content: flex-end;
}

.modal-body {
    padding: 20px;
}

.close {
    background: none;
    border: none;
    font-size: 20px;
    color: #6b7280;
    cursor: pointer;
}

.close:hover {
    color: #111827;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    font-weight: 600;
    font-size: 13px;
    color: #374151;
    margin-bottom: 6px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.error-message {
    color: #dc2626;
    font-size: 12px;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .card-header__actions {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
        gap: 6px;
    }

    .summary-chips {
        flex-direction: column;
        align-items: stretch;
    }

    .unit-management-header,
    .borrowing-item,
    .approver-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .approver-actions {
        width: 100%;
        justify-content: flex-end;
    }

    .units-table {
        min-width: 0;
    }
}

@media (max-width: 768px) {
    .card-header__title {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .card-title {
        font-size: 20px;
    }

    .card-subtitle {
        font-size: 13px;
    }

    .chip {
        width: 100%;
        justify-content: flex-start;
    }

    .detail-card-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        gap: 6px;
    }

    .detail-label {
        min-width: 0;
    }

    .detail-value {
        justify-content: flex-start;
        text-align: left;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stat-card {
        flex-direction: row;
        align-items: center;
        gap: 12px;
    }

    .stat-card .stat-icon {
        width: 44px;
        height: 44px;
    }

    .unit-management-header {
        gap: 12px;
    }

    .approver-actions {
        justify-content: flex-start;
    }

    .modal-content {
        width: 94%;
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/sarana.js') }}"></script>
<script>
function openAssignApproverModal() {
    document.getElementById('assignApproverModal').classList.add('show');
}

function closeAssignApproverModal() {
    document.getElementById('assignApproverModal').classList.remove('show');
}

function editApprover(id, level, isActive) {
    const modal = document.getElementById('editApproverModal');
    const form = document.getElementById('editApproverForm');
    
    form.action = `/approval-assignment/sarana/${id}`;
    document.getElementById('edit_approval_level').value = level;
    document.getElementById('edit_is_active').checked = isActive === 'true';
    
    modal.classList.add('show');
}

function closeEditApproverModal() {
    document.getElementById('editApproverModal').classList.remove('show');
}

function deleteApprover(id) {
    if (confirm('Apakah Anda yakin ingin menghapus approver ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/approval-assignment/sarana/${id}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function validateAssignForm(form) {
    const approverId = form.approver_id.value;
    const approvalLevel = form.approval_level.value;
    
    if (!approverId) {
        alert('Pilih approver terlebih dahulu');
        return false;
    }
    
    if (!approvalLevel) {
        alert('Pilih level approval terlebih dahulu');
        return false;
    }
    
    return true;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const assignModal = document.getElementById('assignApproverModal');
    const editModal = document.getElementById('editApproverModal');
    
    if (event.target === assignModal) {
        closeAssignApproverModal();
    }
    
    if (event.target === editModal) {
        closeEditApproverModal();
    }
}
</script>
@endpush
