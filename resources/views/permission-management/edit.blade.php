@extends('user-management.layout')

@section('title', 'Edit Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key"></i> Edit Permission</h2>
    <a href="{{ route('permission-management.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-edit"></i> Form Edit Permission</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('permission-management.update', $permission->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Permission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $permission->name) }}" 
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
                               id="display_name" name="display_name" value="{{ old('display_name', $permission->display_name) }}" 
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
                            <option value="user" {{ old('category', $permission->category) == 'user' ? 'selected' : '' }}>User Management</option>
                            <option value="sarpras" {{ old('category', $permission->category) == 'sarpras' ? 'selected' : '' }}>Sarana/Prasarana</option>
                            <option value="peminjaman" {{ old('category', $permission->category) == 'peminjaman' ? 'selected' : '' }}>Peminjaman</option>
                            <option value="report" {{ old('category', $permission->category) == 'report' ? 'selected' : '' }}>Report & Analytics</option>
                            <option value="system" {{ old('category', $permission->category) == 'system' ? 'selected' : '' }}>System Management</option>
                            <option value="notification" {{ old('category', $permission->category) == 'notification' ? 'selected' : '' }}>Notification</option>
                            <option value="other" {{ old('category', $permission->category) == 'other' ? 'selected' : '' }}>Lainnya</option>
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
                                   value="1" {{ old('is_active', $permission->is_active) ? 'checked' : '' }}>
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
                          placeholder="Deskripsi singkat tentang permission ini">{{ old('description', $permission->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('permission-management.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-times"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Permission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
