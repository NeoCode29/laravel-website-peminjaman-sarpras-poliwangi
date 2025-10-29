@extends('layouts.app')

@section('title', 'Edit Sarana')
@section('subtitle', 'Ubah data sarana: ' . $sarana->name)

@section('header-actions')
<a href="{{ route('sarana.index') }}" class="btn btn-secondary btn-cancel">
    <i class="fas fa-arrow-left"></i>
    Kembali
</a>
<a href="{{ route('sarana.show', $sarana->id) }}" class="btn btn-secondary">
    <i class="fas fa-eye"></i>
    Lihat Detail
</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/sarana.css') }}?v={{ filemtime(public_path('css/sarana.css')) }}">
@endpush

@section('content')
<section class="detail-page edit-sarana-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <form action="{{ route('sarana.update', $sarana->id) }}" method="POST" enctype="multipart/form-data" id="saranaForm" class="user-create-form user-edit-form sarana-edit-form">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h3 class="section-title">Informasi Dasar</h3>
                
                <div class="detail-block form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label required">Nama Sarana</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $sarana->name) }}" 
                               class="form-input @error('name') is-invalid @enderror"
                               placeholder="Masukkan nama sarana"
                               required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori_id" class="form-label required">Kategori</label>
                        <select id="kategori_id" 
                                name="kategori_id" 
                                class="form-select @error('kategori_id') is-invalid @enderror"
                                required>
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoriSarana as $kategori)
                                <option value="{{ $kategori->id }}" 
                                        {{ old('kategori_id', $sarana->kategori_id) == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="detail-block form-grid">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-input @error('description') is-invalid @enderror"
                                  placeholder="Masukkan deskripsi sarana"
                                  rows="3">{{ old('description', $sarana->description) }}</textarea>
                        @error('description')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <input type="text" 
                               id="lokasi" 
                               name="lokasi" 
                               value="{{ old('lokasi', $sarana->lokasi) }}" 
                               class="form-input @error('lokasi') is-invalid @enderror"
                               placeholder="Masukkan lokasi penyimpanan">
                        @error('lokasi')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="section-title">Tipe dan Kuantitas</h3>
                
                <div class="detail-block">
                    <div class="form-group">
                        <label class="form-label required">Tipe Sarana</label>
                        <div class="option-list">
                            <label class="option-item">
                                <input type="radio" 
                                       id="type_serialized" 
                                       name="type" 
                                       value="serialized" 
                                       {{ old('type', $sarana->type) == 'serialized' ? 'checked' : '' }}
                                       onchange="toggleTypeFields()">
                                <span>Serialized (Unit dengan nomor seri)</span>
                            </label>
                            <label class="option-item">
                                <input type="radio" 
                                       id="type_pooled" 
                                       name="type" 
                                       value="pooled" 
                                       {{ old('type', $sarana->type) == 'pooled' ? 'checked' : '' }}
                                       onchange="toggleTypeFields()">
                                <span>Pooled (Berdasarkan stok/kuantitas)</span>
                            </label>
                        </div>
                        @error('type')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah_total" class="form-label required">Jumlah Total</label>
                        <input type="number" 
                               id="jumlah_total" 
                               name="jumlah_total" 
                               value="{{ old('jumlah_total', $sarana->jumlah_total) }}" 
                               class="form-input @error('jumlah_total') is-invalid @enderror"
                               placeholder="Masukkan jumlah total"
                               min="1"
                               required>
                        <small class="form-help" id="jumlah_total_help">
                            Untuk tipe serialized: jumlah unit yang akan dibuat. Untuk tipe pooled: kapasitas stok maksimal.
                        </small>
                        @error('jumlah_total')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="section-title">Gambar Sarana</h3>
                
                <div class="detail-block">
                    <div class="form-group">
                        <label for="image" class="form-label">Upload Gambar</label>
                        <div class="file-input-wrapper">
                            <input type="file" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*"
                                   class="file-input-native @error('image') is-invalid @enderror"
                                   onchange="previewImage(this)">
                            <label for="image" class="file-input-label">
                                <i class="fas fa-upload"></i>
                                Pilih Gambar
                            </label>
                        </div>
                        <small class="form-help">Format yang didukung: JPG, PNG, GIF. Maksimal 5MB.</small>
                        @error('image')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($sarana->image_url)
                        <div class="image-preview" id="currentImagePreview">
                            <img src="{{ Storage::url($sarana->image_url) }}" alt="Current Image" class="preview-image">
                            <div class="image-preview-actions">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeExistingImage()">
                                    <i class="fas fa-times"></i>
                                    Hapus Gambar Saat Ini
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="remove_current_image" id="remove_current_image" value="0">
                    @endif

                    <div id="imagePreview" class="image-preview" style="display: none;">
                        <img id="previewImg" src="" alt="Preview" class="preview-image">
                        <div class="image-preview-actions">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()">
                                <i class="fas fa-times"></i>
                                Hapus Gambar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($sarana->type == 'serialized')
            <div class="form-section">
                <h3 class="section-title">Informasi Unit Serialized</h3>
                
                <div class="detail-block">
                    <p>Sarana ini memiliki {{ $sarana->units()->count() }} unit dengan nomor seri unik.</p>
                    <div class="unit-stats">
                        <div class="unit-stat-item">
                            <span class="unit-stat-label">Total Unit</span>
                            <span class="unit-stat-value">{{ $sarana->units()->count() }}</span>
                        </div>
                        <div class="unit-stat-item">
                            <span class="unit-stat-label">Tersedia</span>
                            <span class="unit-stat-value">{{ $sarana->units()->where('unit_status', 'tersedia')->count() }}</span>
                                <span class="unit-stat-label">Total Unit:</span>
                                <span class="unit-stat-value">{{ $sarana->units()->count() }}</span>
                            </div>
                            <div class="unit-stat-item">
                                <span class="unit-stat-label">Tersedia:</span>
                                <span class="unit-stat-value">{{ $sarana->units()->where('unit_status', 'tersedia')->count() }}</span>
                            </div>
                            <div class="unit-stat-item">
                                <span class="unit-stat-label">Rusak:</span>
                                <span class="unit-stat-value">{{ $sarana->units()->where('unit_status', 'rusak')->count() }}</span>
                            </div>
                            <div class="unit-stat-item">
                                <span class="unit-stat-label">Maintenance:</span>
                                <span class="unit-stat-value">{{ $sarana->units()->where('unit_status', 'maintenance')->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="form-actions">
                <a href="{{ route('sarana.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Sarana
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/sarana.js') }}"></script>
<script>
function toggleTypeFields() {
    const serializedRadio = document.getElementById('type_serialized');
    const pooledRadio = document.getElementById('type_pooled');
    const jumlahTotalHelp = document.getElementById('jumlah_total_help');
    
    if (serializedRadio.checked) {
        jumlahTotalHelp.textContent = 'Jumlah unit yang akan dibuat dengan nomor seri unik.';
    } else if (pooledRadio.checked) {
        jumlahTotalHelp.textContent = 'Kapasitas stok maksimal untuk sarana pooled.';
    }
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const input = document.getElementById('image');
    const preview = document.getElementById('imagePreview');
    
    input.value = '';
    preview.style.display = 'none';
}

function removeExistingImage() {
    const removeInput = document.getElementById('remove_current_image');
    const currentPreview = document.getElementById('currentImagePreview');

    if (removeInput && currentPreview) {
        removeInput.value = '1';
        currentPreview.style.display = 'none';
    }
}

// Initialize type fields on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleTypeFields();
});

// Form validation
document.getElementById('saranaForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const kategoriId = document.getElementById('kategori_id').value;
    const type = document.querySelector('input[name="type"]:checked');
    const jumlahTotal = document.getElementById('jumlah_total').value;
    
    if (!name) {
        e.preventDefault();
        alert('Nama sarana harus diisi!');
        document.getElementById('name').focus();
        return;
    }
    
    if (!kategoriId) {
        e.preventDefault();
        alert('Kategori harus dipilih!');
        document.getElementById('kategori_id').focus();
        return;
    }
    
    if (!type) {
        e.preventDefault();
        alert('Tipe sarana harus dipilih!');
        return;
    }
    
    if (!jumlahTotal || parseInt(jumlahTotal) < 1) {
        e.preventDefault();
        alert('Jumlah total harus minimal 1!');
        document.getElementById('jumlah_total').focus();
        return;
    }
    
    // Check if serialized and jumlah_total is less than existing units
    @if($sarana->type == 'serialized' && $sarana->units()->count() > 0)
    const existingUnits = {{ $sarana->units()->count() }};
    if (type.value === 'serialized' && parseInt(jumlahTotal) < existingUnits) {
        e.preventDefault();
        alert(`Jumlah total tidak boleh kurang dari ${existingUnits} unit yang sudah ada!`);
        document.getElementById('jumlah_total').focus();
        return;
    }
    @endif
});
</script>

<style>
.form-section-title {
    font-size: 16px;
    font-weight: 500;
    color: #333333;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e0e0e0;
}

.form-help {
    font-size: 12px;
    color: #666666;
    margin-top: 4px;
    display: block;
}

.image-preview {
    margin-top: 12px;
    text-align: center;
}

.preview-image {
    max-width: 200px;
    max-height: 200px;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    margin-bottom: 8px;
}

.current-image {
    margin-bottom: 16px;
}

.option-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 4px;
}

.option-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.option-item input[type="radio"] {
    margin: 0;
}

.option-item label {
    margin: 0;
    font-weight: 400;
    cursor: pointer;
}

.unit-management-info {
    margin-top: 16px;
}

.unit-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin-top: 12px;
}

.unit-stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

.unit-stat-label {
    font-size: 14px;
    color: #666666;
}

.unit-stat-value {
    font-size: 14px;
    font-weight: 600;
    color: #333333;
}
</style>
@endpush
