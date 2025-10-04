@extends('layouts.app')

@section('title', 'Edit User')
@section('subtitle', 'Perbarui informasi pengguna yang sudah ada')

@section('content')
<!-- Form Card -->
<div class="card">
    <!-- Card Header -->
    <div class="card-header">
        <div class="card-header-content">
            <div class="card-header-title">
                <h2 class="card-title">
                    <i class="fas fa-user-edit"></i>
                    Edit User: {{ $user->name }}
                </h2>
                <p class="card-subtitle">Perbarui informasi pengguna di bawah ini</p>
            </div>
            <div class="card-header-actions">
                <!-- Tombol kembali dihapus sesuai permintaan -->
            </div>
        </div>
    </div>

    <!-- Card Main -->
    <div class="card-main">
        <form id="editUserForm" method="POST" action="{{ route('user-management.update', $user->id) }}" class="form-container">
            @csrf
            @method('PUT')
            
            <!-- Basic Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-info-circle"></i>
                    Informasi Dasar
                </h3>
                
                <div class="form-grid">
                    <!-- Nama Lengkap -->
                    <div class="form-group">
                        <label for="name" class="form-label required">
                            Nama Lengkap
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}"
                               class="form-input @error('name') is-invalid @enderror"
                               placeholder="Masukkan nama lengkap"
                               required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label required">
                            Username
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="{{ old('username', $user->username) }}"
                               class="form-input @error('username') is-invalid @enderror"
                               placeholder="Masukkan username unik"
                               required>
                        @error('username')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label required">
                            Email
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}"
                               class="form-input @error('email') is-invalid @enderror"
                               placeholder="Masukkan alamat email"
                               required>
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nomor Handphone -->
                    <div class="form-group">
                        <label for="phone" class="form-label required">
                            Nomor Handphone
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $user->phone) }}"
                               class="form-input @error('phone') is-invalid @enderror"
                               placeholder="Masukkan nomor handphone"
                               minlength="10" 
                               maxlength="15"
                               required>
                        @error('phone')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- User Type and Role Section -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-user-tag"></i>
                    Tipe User dan Role
                </h3>
                
                <div class="form-grid">
                    <!-- Tipe User -->
                    <div class="form-group">
                        <label for="user_type" class="form-label required">
                            Tipe User
                        </label>
                        <select id="user_type" 
                                name="user_type" 
                                class="form-select @error('user_type') is-invalid @enderror"
                                required
                                onchange="toggleUserTypeFields()">
                            <option value="">Pilih tipe user</option>
                            <option value="mahasiswa" {{ old('user_type', $user->user_type) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="staff" {{ old('user_type', $user->user_type) == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                        @error('user_type')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div class="form-group">
                        <label for="role_id" class="form-label required">
                            Role
                        </label>
                        <select id="role_id" 
                                name="role_id" 
                                class="form-select @error('role_id') is-invalid @enderror"
                                required>
                            <option value="">Pilih role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name ?? $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status" class="form-label required">
                            Status
                        </label>
                        <select id="status" 
                                name="status" 
                                class="form-select @error('status') is-invalid @enderror"
                                required>
                            <option value="">Pilih status</option>
                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="blocked" {{ old('status', $user->status) == 'blocked' ? 'selected' : '' }}>Diblokir</option>
                        </select>
                        @error('status')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Mahasiswa Information Section -->
            <div class="form-section" id="mahasiswa-section" style="display: {{ old('user_type', $user->user_type) == 'mahasiswa' ? 'block' : 'none' }};">
                <h3 class="form-section-title">
                    <i class="fas fa-graduation-cap"></i>
                    Informasi Mahasiswa
                </h3>
                
                <div class="form-grid">
                    <!-- NIM -->
                    <div class="form-group">
                        <label for="nim" class="form-label required">
                            NIM
                        </label>
                        <input type="text" 
                               id="nim" 
                               name="nim" 
                               value="{{ old('nim', $user->student->nim ?? '') }}"
                               class="form-input @error('nim') is-invalid @enderror"
                               placeholder="Masukkan NIM (10-15 digit)"
                               minlength="10" 
                               maxlength="15">
                        @error('nim')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Angkatan -->
                    <div class="form-group">
                        <label for="angkatan" class="form-label required">
                            Angkatan
                        </label>
                        <input type="number" 
                               id="angkatan" 
                               name="angkatan" 
                               value="{{ old('angkatan', $user->student->angkatan ?? '') }}"
                               class="form-input @error('angkatan') is-invalid @enderror"
                               placeholder="Masukkan tahun angkatan"
                               min="2000" 
                               max="2030">
                        @error('angkatan')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jurusan -->
                    <div class="form-group">
                        <label for="jurusan_id" class="form-label required">
                            Jurusan
                        </label>
                        <select id="jurusan_id" 
                                name="jurusan_id" 
                                class="form-select @error('jurusan_id') is-invalid @enderror"
                                onchange="loadProdi()">
                            <option value="">Pilih jurusan</option>
                            @foreach($jurusan as $j)
                                <option value="{{ $j->id }}" {{ old('jurusan_id', $user->student->jurusan_id ?? '') == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_jurusan }}
                                </option>
                            @endforeach
                        </select>
                        @error('jurusan_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Prodi -->
                    <div class="form-group">
                        <label for="prodi_id" class="form-label required">
                            Program Studi
                        </label>
                        <select id="prodi_id" 
                                name="prodi_id" 
                                class="form-select @error('prodi_id') is-invalid @enderror">
                            <option value="">Pilih program studi</option>
                            @if(old('prodi_id', $user->student->prodi_id ?? ''))
                                @foreach($prodi as $p)
                                    @if($p->jurusan_id == old('jurusan_id', $user->student->jurusan_id ?? ''))
                                        <option value="{{ $p->id }}" {{ old('prodi_id', $user->student->prodi_id ?? '') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_prodi }}
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @error('prodi_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Staff Information Section -->
            <div class="form-section" id="staff-section" style="display: {{ old('user_type', $user->user_type) == 'staff' ? 'block' : 'none' }};">
                <h3 class="form-section-title">
                    <i class="fas fa-briefcase"></i>
                    Informasi Staff
                </h3>
                
                <div class="form-grid">
                    <!-- NIP -->
                    <div class="form-group">
                        <label for="nip" class="form-label">
                            NIP
                        </label>
                        <input type="text" 
                               id="nip" 
                               name="nip" 
                               value="{{ old('nip', $user->staffEmployee->nip ?? '') }}"
                               class="form-input @error('nip') is-invalid @enderror"
                               placeholder="Masukkan NIP (opsional, minimal 8 digit)"
                               minlength="8" 
                               maxlength="20">
                        @error('nip')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Unit -->
                    <div class="form-group">
                        <label for="unit_id" class="form-label required">
                            Unit
                        </label>
                        <select id="unit_id" 
                                name="unit_id" 
                                class="form-select @error('unit_id') is-invalid @enderror">
                            <option value="">Pilih unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $user->staffEmployee->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Posisi -->
                    <div class="form-group">
                        <label for="position_id" class="form-label required">
                            Posisi/Jabatan
                        </label>
                        <select id="position_id" 
                                name="position_id" 
                                class="form-select @error('position_id') is-invalid @enderror">
                            <option value="">Pilih posisi</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}" {{ old('position_id', $user->staffEmployee->position_id ?? '') == $pos->id ? 'selected' : '' }}>
                                    {{ $pos->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('position_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- User Information Display -->
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
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    <i class="fas fa-times"></i>
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Toggle user type fields
function toggleUserTypeFields() {
    const userType = document.getElementById('user_type').value;
    const mahasiswaSection = document.getElementById('mahasiswa-section');
    const staffSection = document.getElementById('staff-section');
    
    // Hide all sections first
    mahasiswaSection.style.display = 'none';
    staffSection.style.display = 'none';
    
    // Show relevant section
    if (userType === 'mahasiswa') {
        mahasiswaSection.style.display = 'block';
        // Make mahasiswa fields required
        document.getElementById('nim').required = true;
        document.getElementById('angkatan').required = true;
        document.getElementById('jurusan_id').required = true;
        document.getElementById('prodi_id').required = true;
        // Make staff fields not required
        document.getElementById('nip').required = false;
        document.getElementById('unit_id').required = false;
        document.getElementById('position_id').required = false;
    } else if (userType === 'staff') {
        staffSection.style.display = 'block';
        // Make staff fields required (except NIP which is optional)
        document.getElementById('nip').required = false;
        document.getElementById('unit_id').required = true;
        document.getElementById('position_id').required = true;
        // Make mahasiswa fields not required
        document.getElementById('nim').required = false;
        document.getElementById('angkatan').required = false;
        document.getElementById('jurusan_id').required = false;
        document.getElementById('prodi_id').required = false;
    } else {
        // Make all fields not required
        document.getElementById('nim').required = false;
        document.getElementById('angkatan').required = false;
        document.getElementById('jurusan_id').required = false;
        document.getElementById('prodi_id').required = false;
        document.getElementById('nip').required = false;
        document.getElementById('unit_id').required = false;
        document.getElementById('position_id').required = false;
    }
}

// Load prodi based on jurusan
function loadProdi() {
    const jurusanId = document.getElementById('jurusan_id').value;
    const prodiSelect = document.getElementById('prodi_id');
    
    // Clear existing options
    prodiSelect.innerHTML = '<option value="">Pilih program studi</option>';
    
    if (jurusanId) {
        // Fetch prodi data via AJAX
        fetch(`/api/jurusan/${jurusanId}/prodi`)
            .then(response => response.json())
            .then(data => {
                data.forEach(prodi => {
                    const option = document.createElement('option');
                    option.value = prodi.id;
                    option.textContent = prodi.nama_prodi;
                    prodiSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading prodi:', error);
                alert('Gagal memuat data program studi');
            });
    }
}

// Form validation
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    const userType = document.getElementById('user_type').value;
    
    if (userType === 'mahasiswa') {
        const nim = document.getElementById('nim').value;
        const angkatan = document.getElementById('angkatan').value;
        const jurusan = document.getElementById('jurusan_id').value;
        const prodi = document.getElementById('prodi_id').value;
        
        if (!nim || !angkatan || !jurusan || !prodi) {
            e.preventDefault();
            alert('Mohon lengkapi semua field informasi mahasiswa');
            return false;
        }
    } else if (userType === 'staff') {
        const unit = document.getElementById('unit_id').value;
        const position = document.getElementById('position_id').value;
        
        if (!unit || !position) {
            e.preventDefault();
            alert('Mohon lengkapi semua field informasi staff yang wajib');
            return false;
        }
    }
});

// Initialize form on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial state based on user type
    const userType = '{{ $user->user_type }}';
    if (userType) {
        document.getElementById('user_type').value = userType;
        toggleUserTypeFields();
    }
    
    // Load prodi if jurusan is already selected
    const jurusanId = document.getElementById('jurusan_id').value;
    if (jurusanId) {
        loadProdi();
    }
});

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
    
    .form-actions {
        flex-direction: column;
        gap: 12px;
    }
    
    .form-actions .btn {
        width: 100%;
        min-width: unset;
    }
}
</style>
@endpush