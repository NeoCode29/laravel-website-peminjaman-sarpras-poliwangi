@extends('layouts.app')

@section('title', 'Edit Kategori Sarana')
@section('subtitle', 'Perbarui informasi kategori sarana')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/kategori-sarana.css') }}?v={{ filemtime(public_path('css/kategori-sarana.css')) }}">
@endpush



@section('content')
<section class="detail-page kategori-sarana-edit-page">
    <div class="card kategori-form-card">
        <div class="card-main">
            <form method="POST" action="{{ route('kategori-sarana.update', $kategoriSarana->id) }}" class="kategori-form user-create-form">
                @csrf
                @method('PUT')

                <div class="detail-card-grid">
                    <div class="form-section">
                        <h3 class="section-title">Informasi Kategori</h3>
                        <div class="detail-block form-grid form-grid--single">
                            <div class="form-group">
                                <label for="name" class="form-label required">Nama Kategori</label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $kategoriSarana->name) }}"
                                       class="form-input @error('name') is-invalid @enderror"
                                       placeholder="Masukkan nama kategori"
                                       required>
                                @error('name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group form-group--full">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea id="description"
                                          name="description"
                                          class="form-textarea @error('description') is-invalid @enderror"
                                          placeholder="Masukkan deskripsi kategori (opsional)"
                                          rows="4"
                                          maxlength="500">{{ old('description', $kategoriSarana->description) }}</textarea>
                                <div class="form-help">
                                    <span id="charCount">0</span>/500 karakter
                                </div>
                                @error('description')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group form-group--inline">
                                <div class="form-checkbox">
                                    <input type="checkbox"
                                           id="is_active"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $kategoriSarana->is_active ?? true) ? 'checked' : '' }}
                                           class="form-checkbox-input">
                                    <label for="is_active" class="form-checkbox-label">
                                        Kategori aktif
                                    </label>
                                </div>
                                <div class="form-help">Kategori aktif dapat digunakan untuk sarana baru</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Tindakan</h3>
                    <div class="detail-actions">
                        <a href="{{ route('kategori-sarana.index') }}" class="btn btn-secondary btn-cancel">
                            <i class="fas fa-times"></i>
                            Batal
                        </a>
                        <a href="{{ route('kategori-sarana.show', $kategoriSarana->id) }}" class="btn btn-secondary">
                            <i class="fas fa-eye"></i>
                            Lihat Detail
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Kategori
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
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    
    // Update character count
    function updateCharCount() {
        const currentLength = descriptionTextarea.value.length;
        charCount.textContent = currentLength;
        
        // Change color based on length
        if (currentLength > 450) {
            charCount.style.color = 'var(--danger-600)';
        } else if (currentLength > 400) {
            charCount.style.color = 'var(--warning-600)';
        } else {
            charCount.style.color = 'var(--text-tertiary)';
        }
    }
    
    // Set initial count
    updateCharCount();
    
    // Update on input
    descriptionTextarea.addEventListener('input', updateCharCount);
});
</script>
@endpush
