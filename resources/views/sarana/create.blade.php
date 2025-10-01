@extends('user-management.layout')

@section('title', 'Tambah Sarana')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Sarana Baru</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('sarana.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Sarana <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
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
                                    <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
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
                                    <option value="serialized" {{ old('type') == 'serialized' ? 'selected' : '' }}>Serialized (Berunit)</option>
                                    <option value="pooled" {{ old('type') == 'pooled' ? 'selected' : '' }}>Pooled (Berbasis Stok)</option>
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
                                <label for="jumlah_total">Jumlah Total <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('jumlah_total') is-invalid @enderror" 
                                       id="jumlah_total" name="jumlah_total" value="{{ old('jumlah_total', 0) }}" 
                                       min="0" required>
                                @error('jumlah_total')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Untuk serialized: maksimal unit yang dapat ditambahkan<br>
                                    Untuk pooled: kapasitas stok total
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lokasi">Lokasi</label>
                                <input type="text" class="form-control @error('lokasi') is-invalid @enderror" 
                                       id="lokasi" name="lokasi" value="{{ old('lokasi') }}" 
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
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Deskripsi sarana...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="{{ route('sarana.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Informasi Tipe Sarana</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Serialized (Berunit)</h6>
                    <ul class="mb-0">
                        <li>Setiap unit memiliki kode/nomor seri unik</li>
                        <li>Dapat dilacak per unit individual</li>
                        <li>Contoh: Proyektor dengan nomor seri PROJ-001, PROJ-002</li>
                        <li>Statistik dihitung otomatis dari unit yang terdaftar</li>
                    </ul>
                </div>
                
                <div class="alert alert-success">
                    <h6><i class="fas fa-info-circle"></i> Pooled (Berbasis Stok)</h6>
                    <ul class="mb-0">
                        <li>Berbasis kuantitas tanpa nomor seri</li>
                        <li>Dapat dipinjam dalam jumlah tertentu</li>
                        <li>Contoh: Kursi, meja, sound system</li>
                        <li>Statistik dihitung manual berdasarkan stok</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const jumlahTotalInput = document.getElementById('jumlah_total');
    
    function updateJumlahTotalLabel() {
        const type = typeSelect.value;
        const label = document.querySelector('label[for="jumlah_total"]');
        
        if (type === 'serialized') {
            label.innerHTML = 'Jumlah Total <span class="text-danger">*</span>';
            jumlahTotalInput.placeholder = 'Maksimal unit yang dapat ditambahkan';
        } else if (type === 'pooled') {
            label.innerHTML = 'Kapasitas Stok <span class="text-danger">*</span>';
            jumlahTotalInput.placeholder = 'Kapasitas stok total';
        }
    }
    
    typeSelect.addEventListener('change', updateJumlahTotalLabel);
    updateJumlahTotalLabel();
});
</script>
@endsection
