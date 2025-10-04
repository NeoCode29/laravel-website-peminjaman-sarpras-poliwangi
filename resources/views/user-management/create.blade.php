@extends('layouts.app')

@section('title', 'Tambah User')
@section('subtitle', 'Buat akun pengguna baru untuk sistem')

@section('content')
<!-- Form Card -->
<div class="card">
    <!-- Card Header -->
    <div class="card-header">
        <div class="card-header-content">
            <div class="card-header-title">
                <h2 class="card-title">
                    <i class="fas fa-user-plus"></i>
                    Tambah User Baru
                </h2>
                <p class="card-subtitle">Lengkapi form di bawah ini untuk membuat akun pengguna baru</p>
            </div>
            <div class="card-header-actions">
                <a href="{{ route('user-management.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Daftar User
                </a>
            </div>
        </div>
    </div>

    <!-- Card Main -->
    <div class="card-main">
        <form id="createUserForm" method="POST" action="{{ route('user-management.store') }}" class="form-container">
                @csrf
                
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
                                   value="{{ old('name') }}"
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
                                   value="{{ old('username') }}"
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
                                   value="{{ old('email') }}"
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
                                   value="{{ old('phone') }}"
                                   class="form-input @error('phone') is-invalid @enderror"
                                   placeholder="Masukkan nomor handphone"
                                   minlength="10" 
                                   maxlength="15"
                                   required>
                            @error('phone')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password" class="form-label required">
                                Password
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-input @error('password') is-invalid @enderror"
                                       placeholder="Masukkan password minimal 8 karakter"
                                       required
                                       minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label required">
                                Konfirmasi Password
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="form-input @error('password_confirmation') is-invalid @enderror"
                                       placeholder="Ulangi password"
                                       required
                                       minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
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
                                <option value="mahasiswa" {{ old('user_type') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="staff" {{ old('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
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
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Mahasiswa Information Section -->
                <div class="form-section" id="mahasiswa-section" style="display: none;">
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
                                   value="{{ old('nim') }}"
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
                                   value="{{ old('angkatan') }}"
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
                                    <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
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
                                @if(old('prodi_id'))
                                    @foreach($prodi as $p)
                                        @if($p->jurusan_id == old('jurusan_id'))
                                            <option value="{{ $p->id }}" {{ old('prodi_id') == $p->id ? 'selected' : '' }}>
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
                <div class="form-section" id="staff-section" style="display: none;">
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
                                   value="{{ old('nip') }}"
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
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
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
                                    <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
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

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

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

// Load positions (all positions are available, not tied to units)
function loadPositions() {
    // Positions are not tied to specific units, so this function is not needed
    // All positions are already loaded in the select dropdown
}

// Form validation
document.getElementById('createUserForm').addEventListener('submit', function(e) {
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
    // Set initial state based on old input
    const userType = '{{ old("user_type") }}';
    if (userType) {
        document.getElementById('user_type').value = userType;
        toggleUserTypeFields();
    }
    
    // Set old values for prodi
    @if(old('prodi_id') && old('jurusan_id'))
        loadProdi();
    @endif
});

</script>
@endpush
