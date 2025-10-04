@extends('layouts.app')

@section('title', 'Tambah Permission')
@section('subtitle', 'Buat permission baru untuk sistem')

@section('content')
<div class="permission-management-container">
    <!-- Single Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-content">
                <div class="card-header-title">
                    <h1 class="title">
                        <i class="fas fa-plus title-icon"></i>
                        Tambah Permission
                    </h1>
                    <p class="subtitle">Buat permission baru untuk mengatur akses pengguna</p>
                </div>
                <div class="card-header-actions">
                    <a href="{{ route('permission-management.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-main">
            <!-- Form Section -->
            <form method="POST" action="{{ route('permission-management.store') }}" class="permission-form">
                @csrf
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nama Permission <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               class="form-input @error('name') form-input-error @enderror"
                               placeholder="contoh: user.create"
                               required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">
                            Format: domain.action (huruf kecil, angka, dan underscore)
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_name" class="form-label">
                            Nama Tampilan <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="display_name" 
                               name="display_name" 
                               value="{{ old('display_name') }}"
                               class="form-input @error('display_name') form-input-error @enderror"
                               placeholder="contoh: Buat User"
                               required>
                        @error('display_name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">
                            Nama yang ditampilkan di interface
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">
                            Kategori <span class="required">*</span>
                        </label>
                        <select id="category" 
                                name="category" 
                                class="form-select @error('category') form-input-error @enderror"
                                required>
                            <option value="">Pilih Kategori</option>
                            <option value="user" {{ old('category') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="sarpras" {{ old('category') === 'sarpras' ? 'selected' : '' }}>Sarana/Prasarana</option>
                            <option value="peminjaman" {{ old('category') === 'peminjaman' ? 'selected' : '' }}>Peminjaman</option>
                            <option value="report" {{ old('category') === 'report' ? 'selected' : '' }}>Laporan</option>
                            <option value="system" {{ old('category') === 'system' ? 'selected' : '' }}>Sistem</option>
                            <option value="notification" {{ old('category') === 'notification' ? 'selected' : '' }}>Notifikasi</option>
                        </select>
                        @error('category')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-help">
                            Kategori untuk mengelompokkan permission
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="is_active" class="form-label">Status</label>
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="form-check-input">
                            <label for="is_active" class="form-check-label">
                                Aktif
                            </label>
                        </div>
                        <div class="form-help">
                            Permission aktif dapat digunakan oleh role
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-textarea @error('description') form-input-error @enderror"
                              placeholder="Deskripsi permission..."
                              rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-help">
                        Deskripsi optional untuk menjelaskan fungsi permission
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('permission-management.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/permission-management.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate display name from name
    const nameInput = document.getElementById('name');
    const displayNameInput = document.getElementById('display_name');
    
    nameInput.addEventListener('input', function() {
        if (!displayNameInput.value || displayNameInput.value === '') {
            const value = this.value
                .split('.')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
            displayNameInput.value = value;
        }
    });
    
    // Form validation
    const form = document.querySelector('.permission-form');
    form.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        const displayName = displayNameInput.value.trim();
        const category = document.getElementById('category').value;
        
        if (!name || !displayName || !category) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi.');
            return;
        }
        
        // Validate name format
        const nameRegex = /^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/;
        if (!nameRegex.test(name)) {
            e.preventDefault();
            alert('Format nama permission tidak valid. Gunakan format: domain.action (huruf kecil, angka, dan underscore)');
            nameInput.focus();
            return;
        }
    });
});
</script>
@endpush