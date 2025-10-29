@extends('layouts.app')

@section('title', 'Edit Role')
@section('subtitle', 'Ubah data role')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/prasarana.css') }}?v={{ filemtime(public_path('css/prasarana.css')) }}">
<link rel="stylesheet" href="{{ asset('css/components/role-management.css') }}">
@endpush

@section('content')
<section class="detail-page prasarana-detail-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <form method="POST" action="{{ route('role-management.update', $role->id) }}" class="role-form">
                @csrf
                @method('PUT')
                <div class="detail-card-grid">
                    <div class="form-section">
                        <h3 class="section-title">Informasi Role</h3>
                        <div class="detail-block form-grid">
                            <div class="form-group">
                                <label for="name" class="form-label required">Nama Role</label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $role->name) }}"
                                       class="form-input @error('name') form-input-error @enderror"
                                       placeholder="contoh: admin"
                                       required>
                                @error('name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">
                                    Format: huruf kecil, angka, dan underscore (contoh: admin, petugas)
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="display_name" class="form-label required">Nama Tampilan</label>
                                <input type="text" 
                                       id="display_name" 
                                       name="display_name" 
                                       value="{{ old('display_name', $role->display_name) }}"
                                       class="form-input @error('display_name') form-input-error @enderror"
                                       placeholder="contoh: Administrator"
                                       required>
                                @error('display_name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">
                                    Nama yang ditampilkan di interface
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="guard_name" class="form-label required">Guard Name</label>
                                <select id="guard_name" 
                                        name="guard_name" 
                                        class="form-select @error('guard_name') form-input-error @enderror"
                                        required>
                                    <option value="">Pilih Guard</option>
                                    <option value="web" {{ old('guard_name', $role->guard_name) === 'web' ? 'selected' : '' }}>Web</option>
                                    <option value="api" {{ old('guard_name', $role->guard_name) === 'api' ? 'selected' : '' }}>API</option>
                                </select>
                                @error('guard_name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">
                                    Guard untuk autentikasi (biasanya 'web')
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="is_active" class="form-label">Status</label>
                                <div class="form-check">
                                    <input type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label for="is_active" class="form-check-label">
                                        Aktif
                                    </label>
                                </div>
                                <div class="form-help">
                                    Role aktif dapat digunakan oleh user
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Deskripsi</h3>
                        <div class="detail-block">
                            <div class="form-group">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea id="description" 
                                          name="description" 
                                          class="form-textarea @error('description') form-input-error @enderror"
                                          placeholder="Deskripsi role..."
                                          rows="4">{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <div class="form-help">
                                    Deskripsi optional untuk menjelaskan fungsi role
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">
                            Permission
                        </h3>
                        <div class="detail-block">
                            <p class="section-description">
                                Pilih permission yang akan diberikan kepada role ini
                            </p>
                    
                    @if($permissions->count() > 0)
                        <div class="permissions-grid">
                            @foreach($permissions as $category => $categoryPermissions)
                            <div class="permission-category">
                                <div class="category-header">
                                    <h4 class="category-title">
                                        <input type="checkbox" 
                                               class="category-checkbox" 
                                               data-category="{{ $category }}"
                                               id="category_{{ $category }}">
                                        <label for="category_{{ $category }}" class="category-label">
                                            {{ ucfirst($category) }}
                                        </label>
                                    </h4>
                                </div>
                                <div class="permissions-list">
                                    @foreach($categoryPermissions as $permission)
                                    <div class="permission-item">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input permission-checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}"
                                                   data-category="{{ $category }}"
                                                   id="permission_{{ $permission->id }}"
                                                   {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label for="permission_{{ $permission->id }}" class="form-check-label">
                                                <div class="permission-name">{{ $permission->display_name }}</div>
                                                <div class="permission-code">{{ $permission->name }}</div>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-permissions">
                            <div class="empty-state-container">
                                <i class="fas fa-key empty-state-icon"></i>
                                <h4 class="empty-state-title">Tidak Ada Permission</h4>
                                <p class="empty-state-description">
                                    Belum ada permission yang tersedia. 
                                    <a href="{{ route('permission-management.create') }}">Buat permission</a> terlebih dahulu.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="form-section">
                    <h3 class="section-title">Tindakan</h3>
                    <div class="detail-actions">
                        <a href="{{ route('role-management.index') }}" class="btn btn-secondary btn-cancel">
                            <i class="fas fa-arrow-left"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Role
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
document.addEventListener('DOMContentLoaded', function() {
    // Category checkbox functionality
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    
    // Initialize category checkboxes based on individual permissions
    categoryCheckboxes.forEach(categoryCheckbox => {
        const category = categoryCheckbox.getAttribute('data-category');
        const categoryPermissions = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);
        const checkedPermissions = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]:checked`);
        
        categoryCheckbox.checked = categoryPermissions.length === checkedPermissions.length;
    });
    
    categoryCheckboxes.forEach(categoryCheckbox => {
        categoryCheckbox.addEventListener('change', function() {
            const category = this.getAttribute('data-category');
            const categoryPermissions = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);
            
            categoryPermissions.forEach(permissionCheckbox => {
                permissionCheckbox.checked = this.checked;
            });
        });
    });
    
    // Individual permission checkbox functionality
    permissionCheckboxes.forEach(permissionCheckbox => {
        permissionCheckbox.addEventListener('change', function() {
            const category = this.getAttribute('data-category');
            const categoryCheckbox = document.querySelector(`.category-checkbox[data-category="${category}"]`);
            const categoryPermissions = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);
            const checkedPermissions = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]:checked`);
            
            // Update category checkbox based on individual permissions
            categoryCheckbox.checked = categoryPermissions.length === checkedPermissions.length;
        });
    });
    
    // Form validation
    const form = document.querySelector('.role-form');
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const displayName = document.getElementById('display_name').value.trim();
        const guardName = document.getElementById('guard_name').value;
        
        if (!name || !displayName || !guardName) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi.');
            return;
        }
        
        // Validate name format
        const nameRegex = /^[a-z][a-z0-9_]*$/;
        if (!nameRegex.test(name)) {
            e.preventDefault();
            alert('Format nama role tidak valid. Gunakan huruf kecil, angka, dan underscore');
            document.getElementById('name').focus();
            return;
        }
    });
});
</script>
@endpush