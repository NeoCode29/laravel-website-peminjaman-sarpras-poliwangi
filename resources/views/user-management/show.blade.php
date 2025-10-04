@extends('layouts.app')

@section('title', 'Detail User')
@section('subtitle', 'Informasi lengkap pengguna')

@section('content')
<!-- User Detail Card -->
<div class="card">
    <!-- Card Header -->
    <div class="card-header">
        <div class="card-header-content">
            <div class="card-header-title">
                <h2 class="card-title">
                    <i class="fas fa-user"></i>
                    Detail User: {{ $user->name }}
                </h2>
                <p class="card-subtitle">Informasi lengkap pengguna dalam sistem</p>
            </div>
            <div class="card-header-actions">
                <a href="{{ route('user-management.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Daftar User
                </a>
                @can('user.edit')
                <a href="{{ route('user-management.edit', $user->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    Edit User
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Card Main -->
    <div class="card-main">
        <!-- Basic Information Section -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-info-circle"></i>
                Informasi Dasar
            </h3>
            
            <div class="form-grid">
                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="form-display">
                        {{ $user->name }}
                    </div>
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="form-display">
                        {{ $user->username }}
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="form-display">
                        {{ $user->email }}
                    </div>
                </div>

                <!-- Nomor Handphone -->
                <div class="form-group">
                    <label class="form-label">Nomor Handphone</label>
                    <div class="form-display">
                        {{ $user->phone ?? 'Tidak diisi' }}
                    </div>
                </div>

                <!-- Tipe User -->
                <div class="form-group">
                    <label class="form-label">Tipe User</label>
                    <div class="form-display">
                        <span class="user-type-badge user-type-{{ $user->user_type }}">
                            <i class="fas fa-{{ $user->user_type == 'mahasiswa' ? 'graduation-cap' : 'briefcase' }}"></i>
                            {{ ucfirst($user->user_type) }}
                        </span>
                    </div>
                </div>

                <!-- Role -->
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <div class="form-display">
                        <span class="role-badge">
                            <i class="fas fa-user-tag"></i>
                            {{ $user->role->display_name ?? $user->role->name }}
                        </span>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="form-display">
                        @if($user->status == 'active')
                            <span class="status-badge status-success">
                                <i class="fas fa-check-circle"></i>
                                Aktif
                            </span>
                        @elseif($user->status == 'inactive')
                            <span class="status-badge status-warning">
                                <i class="fas fa-pause-circle"></i>
                                Tidak Aktif
                            </span>
                        @elseif($user->status == 'blocked')
                            <span class="status-badge status-danger">
                                <i class="fas fa-ban"></i>
                                Diblokir
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Mahasiswa Information Section -->
        @if($user->user_type == 'mahasiswa' && $user->student)
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-graduation-cap"></i>
                Informasi Mahasiswa
            </h3>
            
            <div class="form-grid">
                <!-- NIM -->
                <div class="form-group">
                    <label class="form-label">NIM</label>
                    <div class="form-display">
                        {{ $user->student->nim }}
                    </div>
                </div>

                <!-- Angkatan -->
                <div class="form-group">
                    <label class="form-label">Angkatan</label>
                    <div class="form-display">
                        {{ $user->student->angkatan }}
                    </div>
                </div>

                <!-- Jurusan -->
                <div class="form-group">
                    <label class="form-label">Jurusan</label>
                    <div class="form-display">
                        {{ $user->student->jurusan->nama_jurusan }}
                    </div>
                </div>

                <!-- Program Studi -->
                <div class="form-group">
                    <label class="form-label">Program Studi</label>
                    <div class="form-display">
                        {{ $user->student->prodi->nama_prodi }}
                    </div>
                </div>

                <!-- Semester -->
                <div class="form-group">
                    <label class="form-label">Semester</label>
                    <div class="form-display">
                        {{ $user->student->semester ?? 'Tidak diisi' }}
                    </div>
                </div>

                <!-- Status Mahasiswa -->
                <div class="form-group">
                    <label class="form-label">Status Mahasiswa</label>
                    <div class="form-display">
                        <span class="status-badge status-{{ $user->student->status_mahasiswa == 'aktif' ? 'success' : 'warning' }}">
                            <i class="fas fa-{{ $user->student->status_mahasiswa == 'aktif' ? 'check-circle' : 'exclamation-triangle' }}"></i>
                            {{ ucfirst($user->student->status_mahasiswa) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Staff Information Section -->
        @if($user->user_type == 'staff' && $user->staffEmployee)
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-briefcase"></i>
                Informasi Staff
            </h3>
            
            <div class="form-grid">
                <!-- NIP -->
                <div class="form-group">
                    <label class="form-label">NIP</label>
                    <div class="form-display">
                        {{ $user->staffEmployee->nip }}
                    </div>
                </div>

                <!-- Unit -->
                <div class="form-group">
                    <label class="form-label">Unit</label>
                    <div class="form-display">
                        {{ $user->staffEmployee->unit->nama }}
                    </div>
                </div>

                <!-- Posisi/Jabatan -->
                <div class="form-group">
                    <label class="form-label">Posisi/Jabatan</label>
                    <div class="form-display">
                        {{ $user->staffEmployee->position->nama }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Account Information Section -->
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-info"></i>
                Informasi Akun
            </h3>
            
            <div class="form-grid">
                <!-- Created At -->
                <div class="form-group">
                    <label class="form-label">Tanggal Dibuat</label>
                    <div class="form-display">
                        {{ $user->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>

                <!-- Last Updated -->
                <div class="form-group">
                    <label class="form-label">Terakhir Diperbarui</label>
                    <div class="form-display">
                        {{ $user->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>

                <!-- Profile Completed -->
                <div class="form-group">
                    <label class="form-label">Status Profil</label>
                    <div class="form-display">
                        @if($user->profile_completed)
                            <span class="status-badge status-success">
                                <i class="fas fa-check-circle"></i>
                                Lengkap
                            </span>
                        @else
                            <span class="status-badge status-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Belum Lengkap
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Profile Completed At -->
                @if($user->profile_completed_at)
                <div class="form-group">
                    <label class="form-label">Profil Diselesaikan</label>
                    <div class="form-display">
                        {{ $user->profile_completed_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                @endif

                <!-- Blocked Status -->
                @if($user->isBlocked())
                <div class="form-group">
                    <label class="form-label">Status Blokir</label>
                    <div class="form-display">
                        <span class="status-badge status-danger">
                            <i class="fas fa-ban"></i>
                            Diblokir hingga {{ $user->blocked_until->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
                @endif

                <!-- SSO Information -->
                @if($user->sso_id)
                <div class="form-group">
                    <label class="form-label">SSO ID</label>
                    <div class="form-display">
                        {{ $user->sso_id }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">SSO Provider</label>
                    <div class="form-display">
                        {{ ucfirst($user->sso_provider) }}
                    </div>
                </div>

                @if($user->last_sso_login)
                <div class="form-group">
                    <label class="form-label">Login SSO Terakhir</label>
                    <div class="form-display">
                        {{ $user->last_sso_login->format('d/m/Y H:i') }}
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>

        <!-- Permissions Section -->
        @if($user->role && $user->role->permissions)
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-key"></i>
                Permission yang Dimiliki
            </h3>
            
            <div class="permissions-grid">
                @foreach($user->role->permissions->groupBy('category') as $category => $permissions)
                <div class="permission-category">
                    <h4 class="permission-category-title">{{ ucfirst($category) }}</h4>
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

        <!-- Action Buttons -->
        <div class="form-actions">
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
            <form method="POST" action="{{ route('user-management.unblock', $user->id) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin membuka blokir user ini?')">
                    <i class="fas fa-unlock"></i>
                    Buka Blokir
                </button>
            </form>
            @endif
            @endcan

            @can('user.delete')
            <form method="POST" action="{{ route('user-management.destroy', $user->id) }}" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
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
</div>

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
</script>
@endpush

@push('styles')
<style>
/* Form Display Styles */
.form-display {
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    color: var(--text-primary);
    min-height: 38px;
    display: flex;
    align-items: center;
}

/* User Type Badge */
.user-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.user-type-mahasiswa {
    background-color: #e3f2fd;
    color: #1976d2;
    border: 1px solid #bbdefb;
}

.user-type-staff {
    background-color: #f3e5f5;
    color: #7b1fa2;
    border: 1px solid #e1bee7;
}

/* Role Badge */
.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    background-color: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

/* Status Badge Styles */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Permissions Grid */
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.permission-category {
    background-color: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
}

.permission-category-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 12px;
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
    padding: 4px 0;
}

.permission-icon {
    color: #28a745;
    font-size: 12px;
}

.permission-name {
    font-size: 13px;
    color: var(--text-secondary);
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    color: #666;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.modal-close:hover {
    background-color: #f0f0f0;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px;
    border-top: 1px solid #e0e0e0;
}

/* Form Actions Styling */
.form-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.form-actions .btn {
    min-width: 140px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.form-actions .btn i {
    font-size: 14px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .permissions-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 12px;
    }
    
    .form-actions .btn {
        width: 100%;
        min-width: unset;
    }
    
    .modal-content {
        width: 95%;
        margin: 20px;
    }
}
</style>
@endpush