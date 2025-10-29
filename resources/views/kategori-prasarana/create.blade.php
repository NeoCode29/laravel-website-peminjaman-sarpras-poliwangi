@extends('layouts.app')

@section('title', 'Tambah Kategori Prasarana')

@section('header-actions')
<a href="{{ route('kategori-prasarana.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i>
    Kembali
</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/prasarana.css') }}?v={{ filemtime(public_path('css/prasarana.css')) }}">
<link rel="stylesheet" href="{{ asset('css/kategori-prasarana.css') }}?v={{ filemtime(public_path('css/kategori-prasarana.css')) }}">
@endpush

@section('content')
<section class="detail-page prasarana-detail-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <form action="{{ route('kategori-prasarana.store') }}" method="POST" class="kategori-form" id="kategoriPrasaranaCreateForm">
                @csrf

                <div class="detail-card-grid">
                    <div class="form-section">
                        <h3 class="section-title">Informasi Kategori</h3>
                        <div class="detail-block form-grid">
                            <div class="form-group">
                                <label for="name" class="form-label required">Nama Kategori</label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       class="form-input @error('name') form-input-error @enderror"
                                       placeholder="Masukkan nama kategori prasarana"
                                       required>
                                @error('name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea id="description"
                                          name="description"
                                          class="form-input form-textarea @error('description') form-input-error @enderror"
                                          placeholder="Masukkan deskripsi kategori prasarana"
                                          rows="4">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Preview Kategori</h3>
                        <div class="detail-block preview-block">
                            <div class="preview-item">
                                <span class="preview-label">Nama Kategori</span>
                                <span class="preview-value" id="previewName">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Deskripsi</span>
                                <span class="preview-value" id="previewDescription">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Status</span>
                                <span class="preview-value">
                                    <span class="status-badge status-approved">Aktif</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Tindakan</h3>
                    <div class="detail-actions">
                        <a href="{{ route('kategori-prasarana.index') }}" class="btn btn-secondary btn-cancel">
                            <i class="fas fa-arrow-left"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Kategori
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
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const previewName = document.getElementById('previewName');
    const previewDescription = document.getElementById('previewDescription');
    
    // Update preview when inputs change
    function updatePreview() {
        const name = nameInput.value.trim();
        const description = descriptionInput.value.trim();
        
        previewName.textContent = name || '-';
        previewDescription.textContent = description || '-';
    }
    
    // Add event listeners
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    
    // Initial preview update
    updatePreview();
    
    // Form validation
    const form = document.getElementById('kategoriPrasaranaCreateForm');
    form.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();

        if (!name) {
            e.preventDefault();
            nameInput.focus();
            showAlert('Nama kategori harus diisi!', 'error');
            return;
        }
        
        if (name.length < 3) {
            e.preventDefault();
            nameInput.focus();
            showAlert('Nama kategori minimal 3 karakter!', 'error');
            return;
        }
        
        if (name.length > 100) {
            e.preventDefault();
            nameInput.focus();
            showAlert('Nama kategori maksimal 100 karakter!', 'error');
            return;
        }
    });
    
    // Show alert function
    function showAlert(message, type) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} fade-in`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} alert-icon"></i>
            <div>
                <strong>${type === 'error' ? 'Error!' : 'Berhasil!'}</strong> ${message}
            </div>
        `;
        
        // Insert alert after content header
        const contentHeader = document.querySelector('.content-header');
        contentHeader.insertAdjacentElement('afterend', alert);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alert.parentElement) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
});
</script>
@endpush