@extends('user-management.layout')

@section('title', 'Edit Kategori Sarana')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Kategori Sarana</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('kategori-sarana.update', $kategoriSarana->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $kategoriSarana->name) }}" 
                               placeholder="Contoh: Proyektor, Laptop, Sound System" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Deskripsi kategori sarana...">{{ old('description', $kategoriSarana->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="icon">Icon (Font Awesome)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i id="icon-preview" class="{{ $kategoriSarana->icon ?: 'fas fa-cube' }}"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" name="icon" value="{{ old('icon', $kategoriSarana->icon) }}" 
                                   placeholder="fas fa-cube, fas fa-laptop, fas fa-tv">
                        </div>
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Gunakan class Font Awesome. Contoh: fas fa-cube, fas fa-laptop, fas fa-tv
                        </small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="{{ route('kategori-sarana.show', $kategoriSarana->id) }}" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                        <a href="{{ route('kategori-sarana.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Current Icon Preview -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Icon Saat Ini</h5>
            </div>
            <div class="card-body text-center">
                @if($kategoriSarana->icon)
                <i class="{{ $kategoriSarana->icon }} fa-4x text-primary mb-3"></i>
                <p><code>{{ $kategoriSarana->icon }}</code></p>
                @else
                <i class="fas fa-cube fa-4x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada icon</p>
                @endif
            </div>
        </div>

        <!-- Informasi Kategori -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">Informasi Kategori</h5>
            </div>
            <div class="card-body">
                <p><strong>ID Kategori:</strong> #{{ $kategoriSarana->id }}</p>
                <p><strong>Jumlah Sarana:</strong> {{ $kategoriSarana->sarana->count() }}</p>
                <p><strong>Tanggal Dibuat:</strong> {{ $kategoriSarana->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Terakhir Diupdate:</strong> {{ $kategoriSarana->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Contoh Icon -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">Contoh Icon Font Awesome</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <i class="fas fa-cube fa-2x text-primary"></i>
                        <br><small>fas fa-cube</small>
                    </div>
                    <div class="col-6 mb-3">
                        <i class="fas fa-laptop fa-2x text-success"></i>
                        <br><small>fas fa-laptop</small>
                    </div>
                    <div class="col-6 mb-3">
                        <i class="fas fa-tv fa-2x text-info"></i>
                        <br><small>fas fa-tv</small>
                    </div>
                    <div class="col-6 mb-3">
                        <i class="fas fa-microphone fa-2x text-warning"></i>
                        <br><small>fas fa-microphone</small>
                    </div>
                    <div class="col-6 mb-3">
                        <i class="fas fa-chair fa-2x text-secondary"></i>
                        <br><small>fas fa-chair</small>
                    </div>
                    <div class="col-6 mb-3">
                        <i class="fas fa-table fa-2x text-dark"></i>
                        <br><small>fas fa-table</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');
    
    iconInput.addEventListener('input', function() {
        const iconClass = this.value.trim();
        if (iconClass) {
            iconPreview.className = iconClass;
        } else {
            iconPreview.className = 'fas fa-cube';
        }
    });
});
</script>
@endsection
