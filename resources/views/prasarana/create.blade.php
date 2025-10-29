@extends('layouts.app')

@section('title', 'Tambah Prasarana')

@section('subtitle', 'Tambah data prasarana baru ke dalam sistem')

@section('header-actions')
<a href="{{ route('prasarana.index') }}" class="btn btn-secondary">
    Kembali
</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/prasarana.css') }}?v={{ filemtime(public_path('css/prasarana.css')) }}">
@endpush

@section('content')
<section class="detail-page prasarana-detail-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <form method="POST" action="{{ route('prasarana.store') }}" enctype="multipart/form-data" id="prasaranaForm" class="prasarana-create-form">
            @csrf

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Dasar</h3>
                    <div class="detail-block form-grid">
                        <div class="form-group">
                            <label for="name" class="form-label required">Nama Prasarana</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   class="form-input @error('name') form-input-error @enderror"
                                   placeholder="Masukkan nama prasarana"
                                   required>
                            @error('name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="kategori_id" class="form-label required">Kategori</label>
                            <select id="kategori_id" 
                                    name="kategori_id" 
                                    class="form-select @error('kategori_id') form-input-error @enderror"
                                    required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoriPrasarana as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group form-group--full">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea @error('description') form-input-error @enderror"
                                  placeholder="Masukkan deskripsi prasarana"
                                  rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Lokasi & Kapasitas</h3>
                    <div class="detail-block form-grid">
                        <div class="form-group">
                            <label for="lokasi" class="form-label required">Lokasi</label>
                            <input type="text" 
                                   id="lokasi" 
                                   name="lokasi" 
                                   value="{{ old('lokasi') }}" 
                                   class="form-input @error('lokasi') form-input-error @enderror"
                                   placeholder="Masukkan lokasi prasarana"
                                   required>
                            @error('lokasi')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="kapasitas" class="form-label">Kapasitas (Orang)</label>
                            <input type="number" 
                                   id="kapasitas" 
                                   name="kapasitas" 
                                   value="{{ old('kapasitas') }}" 
                                   class="form-input @error('kapasitas') form-input-error @enderror"
                                   placeholder="Masukkan kapasitas"
                                   min="1">
                            @error('kapasitas')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Gambar Prasarana</h3>
                <div class="detail-block">
                    <div class="form-group">
                        <label class="form-label">Upload Gambar</label>
                        <div class="uploader-dnd-area" id="imageUploadArea">
                            <div class="uploader-dnd-content">
                                <i class="fas fa-cloud-upload-alt uploader-icon"></i>
                                <p class="uploader-text">Drag & drop gambar di sini atau klik untuk memilih</p>
                                <p class="uploader-hint">Format: JPG, PNG, GIF. Maksimal 5MB per file.</p>
                            </div>
                            <input type="file" 
                                   id="images" 
                                   name="images[]" 
                                   multiple 
                                   accept="image/*" 
                                   class="uploader-native-input"
                                   onchange="handleImageUpload(this)">
                        </div>
                        @error('images')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        @error('images.*')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="imagePreview" class="uploader-preview-list" style="display: none;"></div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Status & Pengaturan</h3>
                <div class="detail-block">
                    <div class="form-group">
                        <label for="status" class="form-label required">Status Awal</label>
                        <select id="status" 
                                name="status" 
                                class="form-select @error('status') form-input-error @enderror"
                                required>
                            <option value="tersedia" {{ old('status', 'tersedia') == 'tersedia' ? 'selected' : '' }}>
                                Tersedia
                            </option>
                            <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>
                                Rusak
                            </option>
                            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>
                                Maintenance
                            </option>
                        </select>
                        @error('status')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Tindakan</h3>
            <div class="detail-actions">
                <a href="{{ route('prasarana.index') }}" class="btn btn-secondary btn-cancel">
                    <i class="fas fa-arrow-left"></i>
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Simpan Prasarana
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
// Image upload handling
function handleImageUpload(input) {
    const files = input.files;
    const preview = document.getElementById('imagePreview');
    const uploadArea = document.getElementById('imageUploadArea');
    
    if (files.length > 0) {
        preview.style.display = 'grid';
        preview.innerHTML = '';
        
        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'uploader-item';
                    previewItem.innerHTML = `
                        <div class="uploader-meta">
                            <div class="uploader-meta-info">
                                <div class="uploader-name">${file.name}</div>
                                <div class="uploader-size">${formatFileSize(file.size)}</div>
                            </div>
                            <button type="button" class="uploader-remove" onclick="removeImage(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <img src="${e.target.result}" alt="Preview" class="uploader-preview-image">
                    `;
                    preview.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            }
        });
        
        uploadArea.style.display = 'none';
    }
}

function removeImage(index) {
    const input = document.getElementById('images');
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    
    // Re-render preview
    handleImageUpload(input);
    
    // Show upload area if no files
    if (input.files.length === 0) {
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('imageUploadArea').style.display = 'block';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag and drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('imageUploadArea');
    const fileInput = document.getElementById('images');
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });
    
    // Handle dropped files
    uploadArea.addEventListener('drop', handleDrop, false);
    
    // Click to select files
    uploadArea.addEventListener('click', () => fileInput.click());
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        uploadArea.classList.add('uploader-dnd-area-active');
    }
    
    function unhighlight(e) {
        uploadArea.classList.remove('uploader-dnd-area-active');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        fileInput.files = files;
        handleImageUpload(fileInput);
    }
});

// Form validation
document.getElementById('prasaranaForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const kategoriId = document.getElementById('kategori_id').value;
    const lokasi = document.getElementById('lokasi').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('Nama prasarana harus diisi');
        document.getElementById('name').focus();
        return;
    }
    
    if (!kategoriId) {
        e.preventDefault();
        alert('Kategori harus dipilih');
        document.getElementById('kategori_id').focus();
        return;
    }
    
    if (!lokasi) {
        e.preventDefault();
        alert('Lokasi harus diisi');
        document.getElementById('lokasi').focus();
        return;
    }
    
    // Check file sizes
    const fileInput = document.getElementById('images');
    if (fileInput.files.length > 0) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        for (let file of fileInput.files) {
            if (file.size > maxSize) {
                e.preventDefault();
                alert(`File ${file.name} terlalu besar. Maksimal 5MB per file.`);
                return;
            }
        }
    }
});
</script>

<style>
.uploader-dnd-area {
    border: 2px dashed #cfd8dc;
    border-radius: 8px;
    background: #ffffff;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.uploader-dnd-area:hover {
    border-color: #007bff;
    background: #f8fbff;
}

.uploader-dnd-area-active {
    border-color: #007bff;
    background: #f8fbff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.15);
}

.uploader-dnd-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.uploader-icon {
    font-size: 48px;
    color: #666666;
}

.uploader-text {
    font-size: 16px;
    font-weight: 500;
    color: #333333;
    margin: 0;
}

.uploader-hint {
    font-size: 14px;
    color: #666666;
    margin: 0;
}

.uploader-native-input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.uploader-preview-list {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    margin-top: 16px;
}

.uploader-item {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 12px;
    position: relative;
}

.uploader-meta {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
    margin-bottom: 8px;
}

.uploader-meta-info {
    min-width: 0;
}

.uploader-name {
    font-size: 12px;
    font-weight: 500;
    color: #333333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.uploader-size {
    font-size: 11px;
    color: #666666;
}

.uploader-remove {
    width: 20px;
    height: 20px;
    border: none;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.uploader-preview-image {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 4px;
}
</style>
@endpush


