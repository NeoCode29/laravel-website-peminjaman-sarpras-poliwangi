@extends('user-management.layout')

@section('title', 'Tambah Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key"></i> Tambah Permission</h2>
    <a href="{{ route('permission-management.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-plus"></i> Form Tambah Permission</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('permission-management.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Permission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="contoh: user.view, sarpras.create" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: kategori.aksi (contoh: user.view, sarpras.create)</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                               id="display_name" name="display_name" value="{{ old('display_name') }}" 
                               placeholder="contoh: Melihat daftar user" required>
                        @error('display_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select @error('category') is-invalid @enderror" 
                                id="category" name="category" required>
                            <option value="">Pilih Kategori</option>
                            <option value="user" {{ old('category') == 'user' ? 'selected' : '' }}>User Management</option>
                            <option value="sarpras" {{ old('category') == 'sarpras' ? 'selected' : '' }}>Sarana/Prasarana</option>
                            <option value="peminjaman" {{ old('category') == 'peminjaman' ? 'selected' : '' }}>Peminjaman</option>
                            <option value="report" {{ old('category') == 'report' ? 'selected' : '' }}>Report & Analytics</option>
                            <option value="system" {{ old('category') == 'system' ? 'selected' : '' }}>System Management</option>
                            <option value="notification" {{ old('category') == 'notification' ? 'selected' : '' }}>Notification</option>
                            <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Permission Aktif
                            </label>
                        </div>
                        <div class="form-text">Permission yang aktif dapat digunakan oleh role</div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3" 
                          placeholder="Deskripsi singkat tentang permission ini">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('permission-management.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-times"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Permission
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Permission Categories Guide -->
<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-info-circle"></i> Panduan Kategori Permission</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><span class="badge bg-primary">user</span> User Management</h6>
                <ul class="list-unstyled">
                    <li><code>user.view</code> - Melihat daftar user</li>
                    <li><code>user.create</code> - Menambah user baru</li>
                    <li><code>user.edit</code> - Mengedit profil user</li>
                    <li><code>user.delete</code> - Menghapus user</li>
                    <li><code>user.block</code> - Memblokir user</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6><span class="badge bg-success">sarpras</span> Sarana/Prasarana</h6>
                <ul class="list-unstyled">
                    <li><code>sarpras.view</code> - Melihat data sarpras</li>
                    <li><code>sarpras.create</code> - Menambah sarpras baru</li>
                    <li><code>sarpras.edit</code> - Mengedit data sarpras</li>
                    <li><code>sarpras.delete</code> - Menghapus sarpras</li>
                    <li><code>sarpras.status_update</code> - Mengubah status</li>
                </ul>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <h6><span class="badge bg-warning">peminjaman</span> Peminjaman</h6>
                <ul class="list-unstyled">
                    <li><code>peminjaman.view</code> - Melihat data peminjaman</li>
                    <li><code>peminjaman.create</code> - Membuat pengajuan</li>
                    <li><code>peminjaman.approve</code> - Approve pengajuan</li>
                    <li><code>peminjaman.reject</code> - Reject pengajuan</li>
                    <li><code>peminjaman.validate_pickup</code> - Validasi pengambilan</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6><span class="badge bg-info">system</span> System Management</h6>
                <ul class="list-unstyled">
                    <li><code>system.settings</code> - Mengatur setting sistem</li>
                    <li><code>system.backup</code> - Backup sistem</li>
                    <li><code>system.monitoring</code> - Monitoring sistem</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
