@extends('layouts.app')

@section('title', 'Detail User')
@section('subtitle', 'Informasi lengkap pengguna')

@section('content')
<!-- User Detail Card -->
<section class="detail-page">
    <div class="card user-detail-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-user-circle user-detail-icon"></i>
                <h2 class="card-title">{{ $user->name }}</h2>
                @if($user->status === 'active')
                    <span class="status-badge status-approved">Aktif</span>
                @elseif($user->status === 'inactive')
                    <span class="status-badge status-pending">Tidak Aktif</span>
                @elseif($user->status === 'blocked')
                    <span class="status-badge status-rejected">Diblokir</span>
                @endif
            </div>
            <div class="card-header__actions">
                <span class="user-type-badge user-type-{{ $user->user_type }}">
                    <i class="fas fa-{{ $user->user_type == 'mahasiswa' ? 'graduation-cap' : 'briefcase' }}"></i>
                    {{ ucfirst($user->user_type) }}
                </span>
                @if($user->role)
                    <span class="role-badge">
                        <i class="fas fa-user-tag"></i>
                        {{ optional($user->role)->display_name ?? optional($user->role)->name }}
                    </span>
                @endif
            </div>
        </div>
        <div class="card-main">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                    <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                    <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-user"></i> {{ $user->username }}
                </div>
                <div class="chip">
                    <i class="fas fa-envelope"></i> {{ $user->email }}
                </div>
                <div class="chip">
                    <i class="fas fa-calendar-plus"></i> {{ $user->created_at->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Dasar</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Lengkap</span>
                            <span class="detail-value">{{ $user->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Username</span>
                            <span class="detail-value">{{ $user->username }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value">{{ $user->email }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nomor Handphone</span>
                            <span class="detail-value">{{ $user->phone ?? 'Tidak diisi' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe User</span>
                            <span class="detail-value">
                                <span class="user-type-badge user-type-{{ $user->user_type }}">
                                    <i class="fas fa-{{ $user->user_type == 'mahasiswa' ? 'graduation-cap' : 'briefcase' }}"></i>
                                    {{ ucfirst($user->user_type) }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Role & Status</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Role</span>
                            <span class="detail-value">
                                @if($user->role)
                                    <span class="role-badge">
                                        <i class="fas fa-user-tag"></i>
                                        {{ optional($user->role)->display_name ?? optional($user->role)->name }}
                                    </span>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                @if($user->status == 'active')
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check-circle"></i>
                                        Aktif
                                    </span>
                                @elseif($user->status == 'inactive')
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-pause-circle"></i>
                                        Tidak Aktif
                                    </span>
                                @elseif($user->status == 'blocked')
                                    <span class="status-badge status-rejected">
                                        <i class="fas fa-ban"></i>
                                        Diblokir
                                    </span>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status Profil</span>
                            <span class="detail-value">
                                @if($user->profile_completed)
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check-circle"></i>
                                        Lengkap
                                    </span>
                                @else
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Belum Lengkap
                                    </span>
                                @endif
                            </span>
                        </div>
                        @if($user->isBlocked())
                        <div class="detail-row">
                            <span class="detail-label">Status Blokir</span>
                            <span class="detail-value">
                                <span class="status-badge status-rejected">
                                    <i class="fas fa-ban"></i>
                                    Diblokir hingga {{ $user->blocked_until->format('d/m/Y H:i') }}
                                </span>
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($user->user_type == 'mahasiswa' && $user->student)
            <div class="form-section">
                <h3 class="section-title">Informasi Mahasiswa</h3>
                <div class="detail-block">
                    <div class="detail-row">
                        <span class="detail-label">NIM</span>
                        <span class="detail-value">{{ $user->student->nim }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Angkatan</span>
                        <span class="detail-value">{{ $user->student->angkatan }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Jurusan</span>
                        <span class="detail-value">{{ $user->student->jurusan->nama_jurusan }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Program Studi</span>
                        <span class="detail-value">{{ $user->student->prodi->nama_prodi }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Semester</span>
                        <span class="detail-value">{{ $user->student->semester ?? 'Tidak diisi' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status Mahasiswa</span>
                        <span class="detail-value">
                            @if($user->student->status_mahasiswa == 'aktif')
                                <span class="status-badge status-approved">
                                    <i class="fas fa-check-circle"></i>
                                    {{ ucfirst($user->student->status_mahasiswa) }}
                                </span>
                            @else
                                <span class="status-badge status-pending">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ ucfirst($user->student->status_mahasiswa) }}
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @endif

            @if($user->user_type == 'staff' && $user->staffEmployee)
            <div class="form-section">
                <h3 class="section-title">Informasi Staff</h3>
                <div class="detail-block">
                    <div class="detail-row">
                        <span class="detail-label">NIP</span>
                        <span class="detail-value">{{ $user->staffEmployee->nip }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Unit</span>
                        <span class="detail-value">{{ $user->staffEmployee->unit->nama }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Posisi/Jabatan</span>
                        <span class="detail-value">{{ $user->staffEmployee->position->nama }}</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-section">
                <h3 class="section-title">Informasi Akun</h3>
                <div class="detail-block">
                    <div class="detail-row">
                        <span class="detail-label">Tanggal Dibuat</span>
                        <span class="detail-value">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Terakhir Diperbarui</span>
                        <span class="detail-value">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($user->profile_completed_at)
                    <div class="detail-row">
                        <span class="detail-label">Profil Diselesaikan</span>
                        <span class="detail-value">{{ $user->profile_completed_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
                @if($user->sso_id)
                <div class="detail-block detail-block-inline">
                    <div class="detail-row">
                        <span class="detail-label">SSO ID</span>
                        <span class="detail-value">{{ $user->sso_id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SSO Provider</span>
                        <span class="detail-value">{{ ucfirst($user->sso_provider) }}</span>
                    </div>
                    @if($user->last_sso_login)
                    <div class="detail-row">
                        <span class="detail-label">Login SSO Terakhir</span>
                        <span class="detail-value">{{ $user->last_sso_login->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            @if($user->role && $user->role->permissions)
            <div class="form-section">
                <h3 class="section-title">Permission yang Dimiliki</h3>
                <div class="permissions-grid">
                    @foreach($user->role->permissions->groupBy('category') as $category => $permissions)
                        <div class="permission-card">
                            <h4 class="permission-card-title">{{ ucfirst($category) }}</h4>
                            <div class="permission-list">
                                @foreach($permissions as $permission)
                                <div class="permission-item">
                                    <i class="fas fa-check-circle permission-icon"></i>
                                    <span class="permission-name">{{ $permission->display_name ?? $permission->name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="form-section">
                <h3 class="section-title">Tindakan</h3>
                <div class="detail-actions">
                    @can('user.edit')
                    <a href="{{ route('user-management.edit', $user->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit User
                    </a>
                    @endcan

                    @can('user.block')
                    @if($user->status != 'blocked')
                    <button type="button" class="btn btn-warning" onclick="showBlockModal()">
                        <i class="fas fa-ban"></i>
                        Blokir User
                    </button>
                    @else
                    <form method="POST" action="{{ route('user-management.unblock', $user->id) }}" class="inline-form">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin membuka blokir user ini?')">
                            <i class="fas fa-unlock"></i>
                            Buka Blokir
                        </button>
                    </form>
                    @endif
                    @endcan

                    @can('user.delete')
                    <form method="POST" action="{{ route('user-management.destroy', $user->id) }}" class="inline-form" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                            Hapus User
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            @can('user.edit')
                @if(!$user->isSsoUser())
                <div class="form-section">
                    <h3 class="section-title">Atur Ulang Password</h3>
                    <div class="detail-block">
                        <form method="POST" action="{{ route('user-management.update-password', $user->id) }}" class="password-reset-form">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="new_password" class="form-label required">Password Baru</label>
                                <div class="password-input-wrapper">
                                    <input type="password"
                                           id="new_password"
                                           name="password"
                                           class="form-input"
                                           placeholder="Masukkan password minimal 8 karakter"
                                           minlength="8"
                                           required>
                                    <button type="button" class="password-toggle" data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new_password_confirmation" class="form-label required">Konfirmasi Password Baru</label>
                                <div class="password-input-wrapper">
                                    <input type="password"
                                           id="new_password_confirmation"
                                           name="password_confirmation"
                                           class="form-input"
                                           placeholder="Ulangi password baru"
                                           minlength="8"
                                           required>
                                    <button type="button" class="password-toggle" data-target="new_password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="form-hint">Password harus mengandung huruf besar, huruf kecil, dan angka.</p>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i>
                                    Simpan Password Baru
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @else
                <div class="form-section">
                    <h3 class="section-title">Atur Ulang Password</h3>
                    <div class="detail-block">
                        <div class="alert alert-info" style="margin: 0;">
                            <i class="fas fa-info-circle"></i>
                            Password untuk akun SSO dikelola oleh penyedia SSO.
                        </div>
                    </div>
                </div>
                @endif
            @endcan
        </div>
    </div>
</section>

<!-- Block User Modal -->
<div id="blockModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-ban"></i>
                Blokir User
            </h3>
            <button type="button" class="modal-close" onclick="closeBlockModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Berapa lama user <strong>{{ $user->name }}</strong> akan diblokir?</p>
            <form method="POST" action="{{ route('user-management.block', $user->id) }}" id="blockForm">
                @csrf
                <div class="form-group">
                    <label for="block_duration" class="form-label required">Durasi Blokir (hari)</label>
                    <select id="block_duration" name="block_duration" class="form-select" required>
                        <option value="">Pilih durasi blokir</option>
                        <option value="1">1 Hari</option>
                        <option value="3">3 Hari</option>
                        <option value="7">7 Hari</option>
                        <option value="14">14 Hari</option>
                        <option value="30">30 Hari</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeBlockModal()">
                <i class="fas fa-times"></i>
                Batal
            </button>
            <button type="submit" form="blockForm" class="btn btn-warning">
                <i class="fas fa-ban"></i>
                Blokir User
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Show block modal
function showBlockModal() {
    document.getElementById('blockModal').style.display = 'flex';
}

// Close block modal
function closeBlockModal() {
    document.getElementById('blockModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('blockModal');
    if (event.target === modal) {
        closeBlockModal();
    }
}

// Toggle password visibility for admin reset form
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.password-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-target');
            const field = document.getElementById(targetId);
            const icon = button.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
/* Page */
.detail-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.user-detail-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
    font-size: 24px;
    font-weight: 600;
    color: #333333;
    margin: 0;
}

.card-header__title .card-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #333333;
}

.card-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: flex-end;
}

.card-main {
    padding: 20px;
}

.user-detail-icon {
    font-size: 24px;
    color: #4b5563;
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
    color: #333333;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 13px;
}

.chip i {
    color: #6b7280;
}

.detail-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333333;
    margin: 0;
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

.detail-block-inline {
    margin-top: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #f9fafb;
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

.detail-value span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.detail-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.detail-actions .btn {
    min-width: 140px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-weight: 500;
    border-radius: 6px;
}

.inline-form {
    display: inline;
}

/* Badges */
.user-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.user-type-mahasiswa {
    background: #e3f2fd;
    color: #1976d2;
    border: 1px solid #bbdefb;
}

.user-type-staff {
    background: #f3e5f5;
    color: #7b1fa2;
    border: 1px solid #e1bee7;
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
}

.status-badge.status-approved {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-badge.status-pending {
    background: #fff3e0;
    color: #f57c00;
}

.status-badge.status-rejected {
    background: #ffebee;
    color: #c62828;
}

/* Permissions */
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 16px;
}

.permission-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.permission-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #333333;
    margin: 0;
    padding-bottom: 8px;
    border-bottom: 1px solid #e0e0e0;
}

.permission-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.permission-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #555555;
}

.permission-icon {
    color: #28a745;
    font-size: 12px;
}

.permission-name {
    font-size: 13px;
    color: #4b5563;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--card-padding, 20px);
    border-bottom: 1px solid #f0f0f0;
}

.modal-title {
    font-size: var(--heading-h2-size, 20px);
    font-weight: var(--heading-h2-weight, 500);
    color: var(--text-primary, #333333);
    display: flex;
    align-items: center;
    gap: var(--spacing-xs, 8px);
}

.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    color: #666666;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.modal-close:hover {
    background-color: #f5f5f5;
}

.modal-body {
    padding: var(--card-padding, 20px);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-sm, 12px);
    padding: var(--card-padding, 20px);
    border-top: 1px solid #f0f0f0;
}

/* Responsive */
@media (max-width: 768px) {
    .detail-card-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        text-align: left;
    }

    .detail-value {
        justify-content: flex-start;
        text-align: left;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px;
    }

    .card-header__actions {
        justify-content: flex-start;
    }

    .card-main {
        padding: 16px;
    }

    .detail-block,
    .detail-block-inline {
        padding: 12px;
        gap: 10px;
    }

    .detail-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .permissions-grid {
        grid-template-columns: 1fr;
    }

    .modal-content {
        width: 95%;
        margin: 24px;
    }
}
</style>
@endpush