@extends('layouts.app')

@section('title', 'Assign Global Approver')
@section('subtitle', 'Kelola global approver yang dapat menyetujui semua peminjaman')

@section('header-actions')
<button type="button" class="btn btn-primary" onclick="openAddModal()">
    <i class="fas fa-plus"></i>
    Tambah Global Approver
</button>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/approval-assignment-global.css') }}?v={{ filemtime(public_path('css/approval-assignment-global.css')) }}">
@endpush

@section('content')
<div class="page-content approval-assignment-page">
    @if(session('success'))
        <div class="alert alert-success fade-in" role="alert">
            <i class="fas fa-check-circle alert-icon"></i>
            <div>
                <strong>Berhasil!</strong> {{ session('success') }}
                </div>
</div>
            <button type="button" class="close" onclick="closeAlert(this)" aria-label="Tutup">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger fade-in" role="alert">
            <i class="fas fa-exclamation-circle alert-icon"></i>
            <div>
                <strong>Terjadi Kesalahan!</strong> {{ session('error') }}
            </div>
            <button type="button" class="close" onclick="closeAlert(this)" aria-label="Tutup">&times;</button>
        </div>
    @endif

    <div class="card card--headerless">
        <div class="card-header" aria-hidden="true"></div>

        <div class="card-main">
            <div class="table-section">
                @if($globalApprovers->count() > 0)
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Approver</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($globalApprovers as $index => $approver)
                                    <tr>
                                        <td>{{ $globalApprovers->firstItem() + $index }}</td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name">{{ $approver->approver->name }}</div>
                                                    <div class="user-email">{{ $approver->approver->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="approval-level-badge level-{{ $approver->approval_level }}">
                                                {{ $approver->level_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $approver->is_active ? 'active' : 'inactive' }}">
                                                {{ $approver->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="created-at">
                                                <span class="created-date">{{ $approver->created_at->format('d/m/Y') }}</span>
                                                <span class="created-time">{{ $approver->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="action-btn action-edit" onclick="openEditModal({{ $approver->id }}, {{ $approver->approval_level }}, {{ $approver->is_active ? 'true' : 'false' }})" aria-label="Edit Approver">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="action-btn action-delete" onclick="deleteApprover({{ $approver->id }})" aria-label="Hapus Approver">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-section">
                        <div class="pagination-info">
                            <span class="pagination-text">
                                Menampilkan {{ $globalApprovers->firstItem() }}-{{ $globalApprovers->lastItem() }} dari {{ $globalApprovers->total() }} approver
                            </span>
                        </div>
                        <div class="pagination-controls">
                            {{ $globalApprovers->appends(request()->query())->links('pagination.custom') }}
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-users-slash"></i>
                        </div>
                        <h3 class="empty-title">Belum ada global approver</h3>
                        <p class="empty-description">
                            Tambah approver global untuk memastikan setiap peminjaman dapat diproses dengan cepat.
                        </p>
                        <button type="button" class="btn btn-primary" onclick="openAddModal()">
                            <i class="fas fa-plus"></i>
                            Tambah Global Approver
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="addModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true" style="display: none;">
        <div class="modal-backdrop" onclick="closeModal('addModal')"></div>
        <div class="modal-dialog">
            <form action="{{ route('approval-assignment.global.store') }}" method="POST" onsubmit="return validateForm(this)">
                @csrf
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-plus"></i>
                        Tambah Global Approver
                    </h3>
                    <button type="button" class="modal-close" onclick="closeModal('addModal')" aria-label="Tutup">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approver_id" class="form-label">Approver <span class="required">*</span></label>
                        <select name="approver_id" id="approver_id" class="form-control" required>
                            <option value="">Pilih Approver</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('approver_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('approver_id')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="approval_level" class="form-label">Level Approval <span class="required">*</span></label>
                        <select name="approval_level" id="approval_level" class="form-control" required>
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

                    <label class="checkbox-label" for="is_active">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                        Aktif
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true" style="display: none;">
        <div class="modal-backdrop" onclick="closeModal('editModal')"></div>
        <div class="modal-dialog">
            <form id="editForm" method="POST" onsubmit="return validateForm(this)">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-edit"></i>
                        Edit Global Approver
                    </h3>
                    <button type="button" class="modal-close" onclick="closeModal('editModal')" aria-label="Tutup">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_approval_level" class="form-label">Level Approval <span class="required">*</span></label>
                        <select name="approval_level" id="edit_approval_level" class="form-control" required>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">
                                    Level {{ $i }} {{ $i == 1 ? '(Primary)' : ($i == 2 ? '(Secondary)' : ($i == 3 ? '(Tertiary)' : '')) }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <label class="checkbox-label" for="edit_is_active">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1">
                        Aktif
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function openAddModal() {
    const modal = document.getElementById('addModal');
    if (!modal) return;
    modal.style.display = 'flex';
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    const firstInput = modal.querySelector('select, input');
    if (firstInput) {
        firstInput.focus();
    }
}

function openEditModal(id, level, isActive) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editForm');
    if (!modal || !form) return;

    form.action = `/approval-assignment/global/${id}`;
    document.getElementById('edit_approval_level').value = level;
    document.getElementById('edit_is_active').checked = Boolean(isActive);

    modal.style.display = 'flex';
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    const firstInput = modal.querySelector('select, input');
    if (firstInput) {
        firstInput.focus();
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    modal.style.display = 'none';
}

function closeAlert(element) {
    const alert = element.closest('.alert');
    if (!alert) return;
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    setTimeout(() => alert.remove(), 300);
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        alert('Mohon lengkapi semua field yang wajib diisi');
    }

    return isValid;
}

function deleteApprover(id) {
    if (confirm('Apakah Anda yakin ingin menghapus global approver ini?')) {
        const form = document.getElementById('deleteForm');
        if (!form) return;
        form.action = `/approval-assignment/global/${id}`;
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert.fade-in');
    alerts.forEach(alert => {
        setTimeout(() => closeAlert(alert.querySelector('.close') || alert), 5000);
    });

    document.querySelectorAll('.modal').forEach(modal => {
        const backdrop = modal.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', () => closeModal(modal.id));
        }
    });
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => closeModal(modal.id));
    }
});
</script>
@endpush