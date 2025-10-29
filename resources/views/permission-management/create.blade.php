@extends('layouts.app')

@section('title', 'Tambah Permission')
@section('subtitle', 'Buat permission baru untuk sistem')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/prasarana.css') }}?v={{ filemtime(public_path('css/prasarana.css')) }}">
<link rel="stylesheet" href="{{ asset('css/components/permission-management.css') }}">
@endpush

@section('content')
<section class="detail-page prasarana-detail-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <form method="POST" action="{{ route('permission-management.store') }}" class="permission-form">
                @csrf
                <div class="detail-card-grid">
                    <div class="form-section">
                        <h3 class="section-title">Informasi Permission</h3>
                        <div class="detail-block form-grid">
                            <div class="form-group">
                                <label for="name" class="form-label required">Nama Permission</label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       class="form-input @error('name') form-input-error @enderror"
                                       placeholder="contoh: prasarana_create"
                                       required>
                                @error('name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">
                                    Format: huruf kecil, angka, dan underscore (contoh: prasarana_create)
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="display_name" class="form-label required">Nama Tampilan</label>
                                <input type="text" 
                                       id="display_name" 
                                       name="display_name" 
                                       value="{{ old('display_name') }}"
                                       class="form-input @error('display_name') form-input-error @enderror"
                                       placeholder="contoh: Tambah Prasarana"
                                       required>
                                @error('display_name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">
                                    Nama yang ditampilkan di interface
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="guard_name" class="form-label required">Guard Name</label>
                                <select id="guard_name" 
                                        name="guard_name" 
                                        class="form-select @error('guard_name') form-input-error @enderror"
                                        required>
                                    <option value="">Pilih Guard</option>
                                    <option value="web" {{ old('guard_name', 'web') === 'web' ? 'selected' : '' }}>Web</option>
                                    <option value="api" {{ old('guard_name') === 'api' ? 'selected' : '' }}>API</option>
                                </select>
                                @error('guard_name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">
                                    Guard untuk autentikasi (biasanya 'web')
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="category" class="form-label required">Kategori</label>
                                <select id="category" 
                                        name="category" 
                                        class="form-select @error('category') form-input-error @enderror"
                                        required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">
                                    Gunakan kategori untuk pengelompokan permission
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Deskripsi</h3>
                        <div class="detail-block">
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
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Tindakan</h3>
                    <div class="detail-actions">
                        <a href="{{ route('permission-management.index') }}" class="btn btn-secondary btn-cancel">
                            <i class="fas fa-arrow-left"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Permission
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

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