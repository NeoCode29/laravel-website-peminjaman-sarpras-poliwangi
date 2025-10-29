@extends('layouts.app')

@section('title', 'Convert Marking ke Peminjaman')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/marking.css') }}?v={{ filemtime(public_path('css/marking.css')) }}">
@endpush
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('marking.show', $marking->id) }}" class="breadcrumb-item">Detail Marking</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item">Konversi ke Pengajuan</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-header-content">
            <div class="card-header-title">
                <h1 class="card-title">
                    <i class="fas fa-exchange-alt card-title-icon"></i>
                    Konversi Marking ke Pengajuan
                </h1>
                <p class="card-subtitle">Konversi marking "{{ $marking->event_name }}" menjadi pengajuan formal</p>
            </div>
            <div class="card-header-actions">
                <a href="{{ route('marking.show', $marking->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Marking
                </a>
            </div>
        </div>
    </div>
    
    <div class="card-main">
        <!-- Marking Information Preview -->
        <div class="form-card">
            <h3 class="form-section-title">
                <i class="fas fa-info-circle"></i>
                Informasi Marking
            </h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <label class="info-label">Nama Acara</label>
                    <div class="info-value">{{ $marking->event_name }}</div>
                </div>
                
                <div class="info-item">
                    <label class="info-label">Jumlah Peserta</label>
                    <div class="info-value">
                        <span class="badge badge-info">{{ $marking->jumlah_peserta }} orang</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <label class="info-label">Lokasi</label>
                    <div class="info-value">
                        @if($marking->prasarana)
                            {{ $marking->prasarana->name }}
                        @elseif($marking->lokasi_custom)
                            {{ $marking->lokasi_custom }}
                        @endif
                    </div>
                </div>
                
                <div class="info-item">
                    <label class="info-label">Tanggal & Waktu</label>
                    <div class="info-value">
                        <strong>{{ \Carbon\Carbon::parse($marking->start_datetime)->format('d/m/Y') }}</strong><br>
                        <small>{{ \Carbon\Carbon::parse($marking->start_datetime)->format('H:i') }} - {{ \Carbon\Carbon::parse($marking->end_datetime)->format('H:i') }}</small>
                    </div>
                </div>
                
                @if($marking->ukm)
                <div class="info-item">
                    <label class="info-label">UKM</label>
                    <div class="info-value">
                        <span class="badge badge-primary">{{ $marking->ukm->nama }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Conversion Form -->
        <form method="POST" action="{{ route('marking.convert', $marking->id) }}" id="convertForm" enctype="multipart/form-data">
            @csrf
            
            <div class="form-card">
                <h3 class="form-section-title">
                    <i class="fas fa-file-upload"></i>
                    Upload Surat Pengajuan
                </h3>
                <p class="form-help">Upload surat pengajuan formal untuk melengkapi konversi marking</p>
                
                <div class="form-group">
                    <label for="surat_path" class="form-label">Surat Pengajuan <span class="text-danger">*</span></label>
                    <div class="file-input-wrapper">
                        <input type="file" id="surat_path" name="surat_path" 
                               class="form-input @error('surat_path') form-input-error @enderror"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <label for="surat_path" class="file-input-label">
                            <i class="fas fa-upload"></i>
                            Pilih File Surat
                        </label>
                    </div>
                    <small class="form-help">Format yang diterima: PDF, JPG, PNG (Maksimal 5MB)</small>
                    @error('surat_path')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Sarana Selection -->
            <div class="form-card">
                <h3 class="form-section-title">
                    <i class="fas fa-list"></i>
                    Pilih Sarana yang Dibutuhkan
                </h3>
                <p class="form-help">Pilih sarana yang akan digunakan untuk acara ini</p>
                
                @if($marking->items->count() > 0)
                    <div class="sarana-conversion-list">
                        @foreach($marking->items as $item)
                        <div class="sarana-conversion-item">
                            <div class="sarana-conversion-info">
                                <div class="sarana-conversion-name">{{ $item->sarana->name }}</div>
                                <div class="sarana-conversion-category">{{ $item->sarana->kategori->name }}</div>
                            </div>
                            <div class="sarana-conversion-actions">
                                <label class="sarana-conversion-checkbox">
                                    <input type="checkbox" name="sarana_ids[]" value="{{ $item->sarana->id }}" checked>
                                    <span class="sarana-conversion-label">Gunakan</span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-container">
                        <i class="fas fa-list empty-state-icon"></i>
                        <h3 class="empty-state-title">Tidak Ada Sarana</h3>
                        <p class="empty-state-description">
                            Tidak ada sarana yang direncanakan dalam marking ini.
                        </p>
                    </div>
                @endif
            </div>
            
            <!-- Additional Information -->
            <div class="form-card">
                <h3 class="form-section-title">
                    <i class="fas fa-comment"></i>
                    Informasi Tambahan
                </h3>
                
                <div class="form-group">
                    <label for="notes" class="form-label">Catatan Tambahan</label>
                    <textarea id="notes" name="notes" rows="3" 
                              class="form-input form-textarea @error('notes') form-input-error @enderror"
                              placeholder="Catatan tambahan untuk pengajuan">{{ old('notes', $marking->notes) }}</textarea>
                    @error('notes')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Conversion Warning -->
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Perhatian:</strong> Setelah dikonversi, marking ini akan dihapus dan diganti dengan pengajuan formal. 
                    Pastikan semua informasi sudah benar sebelum melanjutkan.
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('marking.show', $marking->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-exchange-alt"></i>
                    Konversi ke Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// File input styling
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('surat_path');
    const fileLabel = document.querySelector('.file-input-label');
    
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fileName = this.files[0].name;
                fileLabel.innerHTML = `<i class="fas fa-file"></i> ${fileName}`;
            } else {
                fileLabel.innerHTML = '<i class="fas fa-upload"></i> Pilih File Surat';
            }
        });
    }
    
    // Form validation
    const form = document.getElementById('convertForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('surat_path');
            const saranaCheckboxes = document.querySelectorAll('input[name="sarana_ids[]"]:checked');
            
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Silakan pilih file surat pengajuan');
                fileInput.focus();
                return;
            }
            
            if (saranaCheckboxes.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal satu sarana');
                return;
            }
            
            // File size validation (5MB)
            const file = fileInput.files[0];
            if (file && file.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('Ukuran file terlalu besar. Maksimal 5MB');
                fileInput.focus();
                return;
            }
        });
    }
});
</script>
@endpush
