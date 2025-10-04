@extends('user-management.layout')

@section('title', 'Edit Sarana')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Sarana</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('sarana.update', $sarana->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Sarana <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $sarana->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kategori_id">Kategori <span class="text-danger">*</span></label>
                                <select class="form-control @error('kategori_id') is-invalid @enderror" 
                                        id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($kategori as $kat)
                                    <option value="{{ $kat->id }}" 
                                            {{ old('kategori_id', $sarana->kategori_id) == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Tipe Sarana <span class="text-danger">*</span></label>
                                <select class="form-control @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="serialized" 
                                            {{ old('type', $sarana->type) == 'serialized' ? 'selected' : '' }}>
                                        Serialized (Berunit)
                                    </option>
                                    <option value="pooled" 
                                            {{ old('type', $sarana->type) == 'pooled' ? 'selected' : '' }}>
                                        Pooled (Berbasis Stok)
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <strong>Serialized:</strong> Setiap unit memiliki nomor seri/kode unik<br>
                                    <strong>Pooled:</strong> Berbasis kuantitas tanpa nomor seri
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jumlah_total" id="jumlah_total_label">
                                    @if($sarana->type == 'serialized')
                                        Jumlah Total <span class="text-danger">*</span>
                                    @else
                                        Kapasitas Stok <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input type="number" class="form-control @error('jumlah_total') is-invalid @enderror" 
                                       id="jumlah_total" name="jumlah_total" 
                                       value="{{ old('jumlah_total', $sarana->jumlah_total) }}" 
                                       min="0" required>
                                @error('jumlah_total')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted" id="jumlah_total_help">
                                    @if($sarana->type == 'serialized')
                                        Maksimal unit yang dapat ditambahkan (saat ini: {{ $sarana->units->count() }} unit)
                                    @else
                                        Kapasitas stok total
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lokasi">Lokasi</label>
                                <input type="text" class="form-control @error('lokasi') is-invalid @enderror" 
                                       id="lokasi" name="lokasi" value="{{ old('lokasi', $sarana->lokasi) }}" 
                                       placeholder="Contoh: Ruang 101, Gedung A">
                                @error('lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="image">Gambar</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Format: JPG, PNG, GIF. Maksimal 2MB
                                    @if($sarana->image_url)
                                    <br><strong>Gambar saat ini:</strong> 
                                    <a href="{{ asset('storage/' . $sarana->image_url) }}" target="_blank">Lihat</a>
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Deskripsi sarana...">{{ old('description', $sarana->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="{{ route('sarana.show', $sarana->id) }}" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                        <a href="{{ route('sarana.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Current Image -->
        @if($sarana->image_url)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Gambar Saat Ini</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $sarana->image_url) }}" 
                     alt="{{ $sarana->name }}" 
                     class="img-fluid rounded" 
                     style="max-height: 200px;">
                <p class="mt-2 text-muted">
                    <small>Klik untuk melihat ukuran penuh</small>
                </p>
            </div>
        </div>
        @endif

        <!-- Statistik Saat Ini -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">Statistik Saat Ini</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $sarana->jumlah_total }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $sarana->jumlah_tersedia }}</h4>
                        <small class="text-muted">Tersedia</small>
                    </div>
                </div>
                
                @if($sarana->type == 'serialized')
                <div class="mt-3">
                    <div class="d-flex justify-content-between">
                        <small>Unit Terdaftar:</small>
                        <strong>{{ $sarana->units->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small>Sisa Unit:</small>
                        <strong>{{ $sarana->jumlah_total - $sarana->units->count() }}</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Informasi Tipe -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">Informasi Tipe</h5>
            </div>
            <div class="card-body">
                @if($sarana->type == 'serialized')
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Serialized (Berunit)</h6>
                    <ul class="mb-0">
                        <li>Setiap unit memiliki kode unik</li>
                        <li>Dapat dilacak per unit</li>
                        <li>Statistik otomatis dari unit</li>
                    </ul>
                </div>
                @else
                <div class="alert alert-success">
                    <h6><i class="fas fa-info-circle"></i> Pooled (Berbasis Stok)</h6>
                    <ul class="mb-0">
                        <li>Berbasis kuantitas</li>
                        <li>Tidak ada nomor seri</li>
                        <li>Statistik manual</li>
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const jumlahTotalInput = document.getElementById('jumlah_total');
    const label = document.getElementById('jumlah_total_label');
    const help = document.getElementById('jumlah_total_help');
    
    function updateJumlahTotalLabel() {
        const type = typeSelect.value;
        
        if (type === 'serialized') {
            label.innerHTML = 'Jumlah Total <span class="text-danger">*</span>';
            help.textContent = 'Maksimal unit yang dapat ditambahkan (saat ini: {{ $sarana->units->count() }} unit)';
        } else if (type === 'pooled') {
            label.innerHTML = 'Kapasitas Stok <span class="text-danger">*</span>';
            help.textContent = 'Kapasitas stok total';
        }
    }
    
    typeSelect.addEventListener('change', updateJumlahTotalLabel);
    updateJumlahTotalLabel();
});
</script>
@endsection
