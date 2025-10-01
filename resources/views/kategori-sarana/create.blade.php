@extends('user-management.layout')

@section('title', 'Tambah Kategori Sarana')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Kategori Sarana Baru</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('kategori-sarana.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="Contoh: Proyektor, Laptop, Sound System" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Deskripsi kategori sarana...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="icon">Icon (Font Awesome)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i id="icon-preview" class="fas fa-cube"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" name="icon" value="{{ old('icon') }}" 
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
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="{{ route('kategori-sarana.index') }}" class="btn btn-secondary">
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
