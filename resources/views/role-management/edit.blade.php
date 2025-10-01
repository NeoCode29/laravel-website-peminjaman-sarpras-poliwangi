@extends('user-management.layout')

@section('title', 'Edit Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size: 24px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
        🏷️ Edit Role: {{ $role->display_name }}
    </h2>
    <a href="{{ route('role-management.index') }}" class="btn btn-secondary">
        ← Kembali
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
            ✏️ Form Edit Role
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('role-management.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Role <span style="color: #dc3545;">*</span></label>
                        <input type="text" class="form-control @error('name') error @enderror" 
                               id="name" name="name" value="{{ old('name', $role->name) }}" 
                               placeholder="contoh: admin, peminjam" required>
                        @error('name')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Nama role harus unik dan tidak boleh ada spasi</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="display_name" class="form-label">Display Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" class="form-control @error('display_name') error @enderror" 
                               id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" 
                               placeholder="contoh: Admin (Petugas Sarpras)" required>
                        @error('display_name')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') error @enderror" 
                          id="description" name="description" rows="3" 
                          placeholder="Deskripsi singkat tentang role ini">{{ old('description', $role->description) }}</textarea>
                @error('description')
                    <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                           value="1" {{ old('is_active', $role->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Role Aktif
                    </label>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Role yang aktif dapat digunakan oleh user</div>
            </div>

            <!-- Permissions Section -->
            <div style="margin-bottom: var(--spacing-lg);">
                <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                    🔑 Permissions
                </h5>
                <p class="text-muted" style="margin-bottom: var(--spacing-md);">Pilih permissions yang akan diberikan ke role ini</p>
                
                @if($permissions->count() > 0)
                    @foreach($permissions as $category => $categoryPermissions)
                        <div class="card" style="margin-bottom: var(--spacing-md);">
                            <div class="card-header">
                                <h6 style="font-size: 14px; font-weight: 500; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: var(--spacing-sm);">
                                    📁 {{ ucfirst($category) }}
                                    <span class="badge badge-info" style="margin-left: var(--spacing-sm);">{{ $categoryPermissions->count() }} permissions</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($categoryPermissions as $permission)
                                        <div class="col-md-6 col-lg-4" style="margin-bottom: var(--spacing-sm);">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="permission_{{ $permission->id }}" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}"
                                                       {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    <strong>{{ $permission->display_name }}</strong>
                                                    @if($permission->description)
                                                        <br><small class="text-muted">{{ $permission->description }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning">
                        ⚠️ Tidak ada permission yang tersedia.
                    </div>
                @endif
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--spacing-sm);">
                <a href="{{ route('role-management.index') }}" class="btn btn-secondary">
                    ❌ Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    💾 Update Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
