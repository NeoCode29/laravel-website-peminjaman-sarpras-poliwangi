@extends('layouts.app')

@section('title', 'Detail Prasarana')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/prasarana.css') }}?v={{ filemtime(public_path('css/prasarana.css')) }}">
@endpush

@section('header-actions')
<a href="{{ route('prasarana.index') }}" class="btn btn-secondary btn-cancel">
    <i class="fas fa-arrow-left"></i>
    Kembali
</a>
@can('sarpras.edit')
<a href="{{ route('prasarana.edit', $prasarana->id) }}" class="btn btn-primary">
    <i class="fas fa-edit"></i>
    Edit Prasarana
</a>
@endcan
@endsection

@section('content')
<section class="detail-page prasarana-detail-page">
    <div class="card user-detail-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-building detail-icon"></i>
                <div class="card-header__text">
                    <h2 class="card-title">{{ $prasarana->name }}</h2>
                    <p class="card-subtitle">{{ $prasarana->kategori->name ?? 'Kategori tidak diketahui' }}</p>
                </div>
            </div>
            <div class="card-header__actions">
                <span class="prasarana-meta-chip">
                    <i class="fas fa-user"></i>
                    {{ $prasarana->createdBy->name ?? 'Sistem' }}
                </span>
                <span class="prasarana-meta-chip">
                    <i class="fas fa-calendar-plus"></i>
                    {{ $prasarana->created_at->format('d M Y H:i') }}
                </span>
                <span class="prasarana-status-chip {{ $prasarana->status }}">
                    <i class="fas fa-circle"></i>
                    {{ ucfirst($prasarana->status) }}
                </span>
            </div>
        </div>

        <div class="card-main">
            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $prasarana->lokasi ?? 'Lokasi belum diisi' }}
                </div>
                <div class="chip">
                    <i class="fas fa-users"></i>
                    {{ $prasarana->kapasitas ? $prasarana->kapasitas . ' orang' : 'Kapasitas belum diisi' }}
                </div>
                <div class="chip">
                    <i class="fas fa-check-circle"></i>
                    Status: {{ ucfirst($prasarana->status) }}
                </div>
                <div class="chip">
                    <i class="fas fa-history"></i>
                    Diperbarui {{ $prasarana->updated_at->diffForHumans() }}
                </div>
            </div>

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Prasarana</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Prasarana</span>
                            <span class="detail-value">{{ $prasarana->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Kategori</span>
                            <span class="detail-value">
                                <span class="category-chip">
                                    <i class="fas fa-layer-group"></i>
                                    {{ $prasarana->kategori->name ?? '-' }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Lokasi</span>
                            <span class="detail-value detail-value--start">{{ $prasarana->lokasi ?? '-' }}</span>
                        </div>
                        <div class="detail-row detail-row--stacked">
                            <span class="detail-label">Deskripsi</span>
                            <span class="detail-value detail-value--start">
                                {{ $prasarana->description ?? 'Tidak ada deskripsi' }}
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Dibuat</span>
                            <span class="detail-value">
                                <div class="user-meta">
                                    <i class="fas fa-user-circle"></i>
                                    <div>
                                        <div class="user-name">{{ $prasarana->createdBy->name ?? 'Sistem' }}</div>
                                        <div class="user-meta-date">{{ $prasarana->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Terakhir Diupdate</span>
                            <span class="detail-value">{{ $prasarana->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                @if($prasarana->images && $prasarana->images->count() > 0)
                <div class="form-section">
                    <h3 class="section-title">Galeri Prasarana</h3>
                    <div class="detail-block image-gallery-block">
                        <div class="images-gallery">
                            @foreach($prasarana->images as $image)
                                <div class="image-gallery-item">
                                    <img src="{{ Storage::url($image->image_url) }}"
                                         alt="Gambar Prasarana"
                                         class="gallery-image"
                                         onclick="openImageModal('{{ Storage::url($image->image_url) }}', '{{ $prasarana->name }}')">
                                    <div class="image-overlay">
                                        <button class="image-action-btn" onclick="openImageModal('{{ Storage::url($image->image_url) }}', '{{ $prasarana->name }}')">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                        @can('sarpras.edit')
                                        <button class="image-action-btn image-delete-btn" onclick="confirmDeleteImage({{ $image->id }}, '{{ $prasarana->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="form-section">
                <h3 class="section-title">Status & Pengaturan</h3>
                <div class="detail-block">
                    <div class="status-grid">
                        <div class="status-card">
                            <div class="status-icon status-{{ $prasarana->status }}">
                                <i class="fas fa-{{ $prasarana->status === 'tersedia' ? 'check-circle' : ($prasarana->status === 'rusak' ? 'times-circle' : 'tools') }}"></i>
                            </div>
                            <div class="status-content">
                                <div class="status-label">Status</div>
                                <div class="status-value">{{ ucfirst($prasarana->status) }}</div>
                            </div>
                        </div>
                        <div class="status-card">
                            <div class="status-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="status-content">
                                <div class="status-label">Kapasitas</div>
                                <div class="status-value">{{ $prasarana->kapasitas ? $prasarana->kapasitas . ' orang' : 'Tidak ditentukan' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($peminjamanHistory && $peminjamanHistory->count() > 0)
            <div class="form-section">
                <h3 class="section-title">Riwayat Peminjaman</h3>
                <div class="detail-block detail-block--table">
                    <div class="table-wrapper table-wrapper--history">
                        <table class="data-table history-table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Peminjam</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($peminjamanHistory as $peminjaman)
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-details">
                                                <div class="user-name">{{ $peminjaman->event_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name">{{ $peminjaman->user->name }}</div>
                                                <div class="user-email">{{ $peminjaman->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div class="date-text">{{ $peminjaman->start_date->format('d M Y') }}</div>
                                            <div class="time-text">{{ $peminjaman->start_time }} - {{ $peminjaman->end_time }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $peminjaman->status }}">{{ ucfirst($peminjaman->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @can('peminjaman.view')
                                            <a href="{{ route('peminjaman.show', $peminjaman->id) }}" class="action-btn action-view" title="Lihat Detail Peminjaman">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($peminjamanHistory->hasPages())
                    <div class="pagination-section pagination-section--history">
                        <div class="pagination-info">
                            <p class="pagination-text">
                                Menampilkan {{ $peminjamanHistory->firstItem() }}-{{ $peminjamanHistory->lastItem() }} dari {{ $peminjamanHistory->total() }} riwayat
                            </p>
                        </div>
                        <div class="pagination-controls">
                            <div class="pagination-wrapper">
                                {{ $peminjamanHistory->onEachSide(1)->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="form-section">
                <h3 class="section-title">Riwayat Peminjaman</h3>
                <div class="detail-block">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times empty-icon"></i>
                        <h4 class="empty-title">Belum Ada Peminjaman</h4>
                        <p class="empty-description">Prasarana ini belum pernah dipinjam.</p>
                    </div>
                </div>
            </div>
            @endif

            @can('sarpras.approval_assign')
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user-check"></i>
                    Approvers
                </h3>
                <div class="detail-block approvers-block">
                    <div class="approvers-header">
                        <div class="approvers-info">
                            <p class="approvers-description">Kelola daftar approver yang bertanggung jawab untuk prasarana ini.</p>
                        </div>
                        <div class="approvers-actions">
                            <button type="button" class="btn btn-primary" onclick="openAssignApproverModal()">
                                <i class="fas fa-user-plus"></i>
                                Assign Approver
                            </button>
                        </div>
                    </div>
                    <div class="approvers-content">
                        @if($prasarana->approvers->count() > 0)
                            <div class="approvers-list">
                                @foreach($prasarana->approvers as $approver)
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
                                            <span class="badge badge-level-{{ $approver->approval_level }}">Level {{ $approver->approval_level }}</span>
                                        </div>
                                        <div class="approver-status">
                                            <span class="badge {{ $approver->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $approver->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </div>
                                        <div class="approver-actions">
                                            <button class="btn btn-sm btn-warning" onclick="openEditApproverModal({{ $approver->id }}, {{ $approver->approval_level }}, {{ $approver->is_active ? 'true' : 'false' }})">
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
                            <div class="empty-approvers">
                                <i class="fas fa-user-slash"></i>
                                <p>Belum ada approver yang ditetapkan untuk prasarana ini</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endcan
        </div>
    </div>

    @can('sarpras.approval_assign')
    <!-- Modal Assign Approver -->
    <div id="assignApproverModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-user-check"></i>
                    Assign Approver untuk {{ $prasarana->name }}
                </h3>
                <button type="button" class="close" onclick="closeAssignApproverModal()">&times;</button>
            </div>
            <form action="{{ route('approval-assignment.prasarana.store') }}" method="POST" onsubmit="return validateAssignForm(this)">
                @csrf
                <input type="hidden" name="prasarana_id" value="{{ $prasarana->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approver_id">Approver <span class="required">*</span></label>
                        @php
                            $assignedApproverIds = $prasarana->approvers->pluck('approver_id')->all();
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
                                    Level {{ $i }} {{ $i == 1 ? '(Primary)' : ($i == 2 ? '(Secondary)' : ($i == 3 ? '(Tertiary)' : '') ) }}
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
                                    Level {{ $i }} {{ $i == 1 ? '(Primary)' : ($i == 2 ? '(Secondary)' : ($i == 3 ? '(Tertiary)' : '') ) }}
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
</div>

<!-- Image Modal -->
<div id="imageModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog image-dialog">
        <div class="dialog-header">
            <h3 id="imageModalTitle">Gambar Prasarana</h3>
            <button type="button" class="dialog-close" onclick="closeImageModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="dialog-body">
            <img id="modalImage" src="" alt="Gambar Prasarana" class="modal-image">
        </div>
    </div>
</div>

<!-- Delete Image Confirmation Modal -->
<div id="deleteImageModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Konfirmasi Hapus Gambar</h3>
        </div>
        <div class="dialog-body">
            <p>Apakah Anda yakin ingin menghapus gambar dari prasarana <strong id="deleteImagePrasaranaName"></strong>?</p>
            <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteImageModal()">Batal</button>
            <form id="deleteImageForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus Gambar</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@can('sarpras.approval_assign')
<script>
function openAssignApproverModal() {
    document.getElementById('assignApproverModal').classList.add('show');
}

function closeAssignApproverModal() {
    document.getElementById('assignApproverModal').classList.remove('show');
}

function openEditApproverModal(id, level, isActive) {
    const modal = document.getElementById('editApproverModal');
    const form = document.getElementById('editApproverForm');
    form.action = `/approval-assignment/prasarana/${id}`;
    document.getElementById('edit_approval_level').value = level;
    document.getElementById('edit_is_active').checked = (isActive === true || isActive === 'true');
    modal.classList.add('show');
}

function closeEditApproverModal() {
    document.getElementById('editApproverModal').classList.remove('show');
}

function deleteApprover(id) {
    if (confirm('Apakah Anda yakin ingin menghapus approver ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/approval-assignment/prasarana/${id}`;

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

window.addEventListener('click', function(event) {
    const assignModal = document.getElementById('assignApproverModal');
    const editModal = document.getElementById('editApproverModal');
    if (event.target === assignModal) closeAssignApproverModal();
    if (event.target === editModal) closeEditApproverModal();
});

document.addEventListener('DOMContentLoaded', function() {
    const hasAssignErrors = Boolean({{ ($errors->has('approver_id') || $errors->has('approval_level')) ? 'true' : 'false' }});
    if (hasAssignErrors) {
        openAssignApproverModal();
    }
});
</script>

<style>
.approvers-actions { display: flex; justify-content: flex-end; margin-bottom: 1rem; }
.approvers-content { margin-top: 1rem; }
.approvers-list { display: flex; flex-direction: column; gap: 0.75rem; }
.approver-item { display: flex; align-items: center; padding: 1rem; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; gap: 1rem; }
.approver-info { display: flex; align-items: center; gap: 0.75rem; flex: 1; }
.approver-avatar { width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6c757d; }
.approver-details strong { display: block; color: #212529; font-size: 0.9rem; }
.approver-details small { color: #6c757d; font-size: 0.8rem; }
.approver-level { min-width: 80px; }
.approver-status { min-width: 80px; }
.approver-actions { display: flex; gap: 0.5rem; }
.empty-approvers { text-align: center; padding: 2rem; color: #6c757d; }
.empty-approvers i { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
.badge-level-1 { background: #dc3545; color: #fff; }
.badge-level-2 { background: #fd7e14; color: #fff; }
.badge-level-3 { background: #ffc107; color: #212529; }
.badge-level-4 { background: #20c997; color: #fff; }
.badge-level-5 { background: #17a2b8; color: #fff; }
.badge-level-6 { background: #6f42c1; color: #fff; }
.badge-level-7 { background: #e83e8c; color: #fff; }
.badge-level-8 { background: #6c757d; color: #fff; }
.badge-level-9 { background: #343a40; color: #fff; }
.badge-level-10 { background: #000; color: #fff; }

.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
.modal.show { display: flex; align-items: center; justify-content: center; }
.modal-content { background: #fff; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e9ecef; }
.modal-header h3 { margin: 0; color: #212529; display: flex; align-items: center; gap: 0.5rem; }
.close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6c757d; }
.close:hover { color: #212529; }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem; border-top: 1px solid #e9ecef; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #212529; }
.required { color: #dc3545; }
.form-group select { width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.9rem; }
.checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
.error-message { color: #dc3545; font-size: 0.8rem; margin-top: 0.25rem; }

@media (max-width: 768px) {
  .approvers-actions { justify-content: stretch; }
  .approver-item { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
  .approver-info { width: 100%; }
  .approver-level, .approver-status { min-width: auto; }
  .approver-actions { width: 100%; justify-content: flex-end; }
}
</style>
@endcan
@endpush

@push('scripts')
<script>
function openImageModal(imageSrc, title) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('imageModalTitle');

    modalImage.src = imageSrc;
    modalTitle.textContent = title;
    modal.style.display = 'flex';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
}

function confirmDeleteImage(imageId, prasaranaName) {
    const modal = document.getElementById('deleteImageModal');
    const form = document.getElementById('deleteImageForm');
    const nameElement = document.getElementById('deleteImagePrasaranaName');

    nameElement.textContent = prasaranaName;
    form.action = `/prasarana/images/${imageId}`;
    modal.style.display = 'flex';
}

function closeDeleteImageModal() {
    const modal = document.getElementById('deleteImageModal');
    modal.style.display = 'none';
}

document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

document.getElementById('deleteImageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteImageModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
        closeDeleteImageModal();
    }
});
</script>

<style>
.form-value {
    padding: 10px 12px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    color: #333333;
    min-height: 40px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.images-gallery {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
}

.image-gallery-item {
    position: relative;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s ease;
}

.image-gallery-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.gallery-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    transition: transform 0.2s ease;
}

.image-gallery-item:hover .gallery-image {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.image-gallery-item:hover .image-overlay {
    opacity: 1;
}

.image-action-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    color: #333333;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.image-action-btn:hover {
    background: #ffffff;
    transform: scale(1.1);
}

.image-delete-btn {
    background: rgba(255, 68, 68, 0.9);
    color: white;
}

.image-delete-btn:hover {
    background: #ff4444;
}

.image-dialog {
    max-width: 90vw;
    max-height: 90vh;
    width: auto;
    height: auto;
}

.modal-image {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
}

.dialog-close {
    position: absolute;
    top: 16px;
    right: 20px;
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: #333333;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.dialog-close:hover {
    background: #ffffff;
    transform: scale(1.1);
}

.date-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.date-text {
    font-size: 14px;
    font-weight: 500;
    color: #333333;
}

.time-text {
    font-size: 12px;
    color: #666666;
}

.text-muted {
    color: #666666;
    font-style: italic;
}
</style>
@endpush
