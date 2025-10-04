@extends('layouts.app')

@section('title', 'Matrix Role vs Permission')
@section('subtitle', 'Kelola permission untuk setiap role')

@section('content')
<div class="role-permission-matrix-container">
    <!-- Single Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-content">
                <div class="card-header-title">
                    <h1 class="title">
                        <i class="fas fa-table title-icon"></i>
                        Matrix Role vs Permission
                    </h1>
                    <p class="subtitle">Kelola permission untuk setiap role dalam sistem</p>
                </div>
                <div class="card-header-actions">
                    <a href="{{ route('role-management.index') }}" class="btn btn-primary">
                        <i class="fas fa-users-cog"></i>
                        Kelola Role
                    </a>
                    <a href="{{ route('permission-management.index') }}" class="btn btn-secondary">
                        <i class="fas fa-key"></i>
                        Kelola Permission
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-main">

            @if($roles->count() > 0 && $permissions->count() > 0)
                <!-- Matrix Table -->
                <div class="matrix-section">
                    <div class="matrix-header">
                        <h3 class="section-title">
                            <i class="fas fa-table"></i>
                            Matrix Role vs Permission
                        </h3>
                        <p class="section-description">
                            Klik checkbox untuk mengatur permission role. Perubahan akan disimpan otomatis.
                        </p>
                    </div>
                    
                    <div class="matrix-wrapper">
                        <table class="matrix-table">
                            <thead>
                                <tr class="category-row">
                                    <th class="role-header">
                                        <i class="fas fa-users-cog"></i>
                                        Role
                                    </th>
                                    @foreach($permissions as $category => $categoryPermissions)
                                        <th colspan="{{ $categoryPermissions->count() }}" class="category-header">
                                            <i class="fas fa-folder"></i>
                                            {{ ucfirst($category) }}
                                        </th>
                                    @endforeach
                                </tr>
                                <tr class="permission-row">
                                    <th class="permission-label">Permission</th>
                                    @foreach($permissions as $category => $categoryPermissions)
                                        @foreach($categoryPermissions as $permission)
                                            <th class="permission-header">
                                                <div class="permission-info">
                                                    <div class="permission-name">{{ $permission->display_name }}</div>
                                                    <div class="permission-code">{{ $permission->name }}</div>
                                                </div>
                                            </th>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr class="role-row">
                                    <td class="role-cell">
                                        <div class="role-info">
                                            <div class="role-name">{{ $role->display_name }}</div>
                                            <div class="role-code">{{ $role->name }}</div>
                                            <div class="role-users">{{ $role->users()->count() }} user</div>
                                        </div>
                                    </td>
                                    @foreach($permissions as $category => $categoryPermissions)
                                        @foreach($categoryPermissions as $permission)
                                            <td class="permission-cell">
                                                <div class="permission-checkbox-wrapper">
                                                    <input class="permission-checkbox" 
                                                           type="checkbox" 
                                                           data-role-id="{{ $role->id }}" 
                                                           data-permission-id="{{ $permission->id }}"
                                                           {{ $role->hasPermissionTo($permission) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Legend & Statistics -->
                <div class="legend-section">
                    <div class="legend-grid">
                        <div class="legend-item">
                            <h4 class="legend-title">
                                <i class="fas fa-info-circle"></i>
                                Keterangan
                            </h4>
                            <ul class="legend-list">
                                <li><i class="fas fa-check text-success"></i> <strong>Centang:</strong> Role memiliki permission</li>
                                <li><i class="fas fa-times text-danger"></i> <strong>Tidak Centang:</strong> Role tidak memiliki permission</li>
                                <li><i class="fas fa-save text-info"></i> <strong>Otomatis:</strong> Perubahan disimpan otomatis saat checkbox diubah</li>
                            </ul>
                        </div>
                        <div class="legend-item">
                            <h4 class="legend-title">
                                <i class="fas fa-chart-bar"></i>
                                Statistik
                            </h4>
                            <ul class="legend-list">
                                <li><strong>Total Role:</strong> {{ $roles->count() }}</li>
                                <li><strong>Total Permission:</strong> {{ $permissions->flatten()->count() }}</li>
                                <li><strong>Kategori Permission:</strong> {{ $permissions->count() }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-container">
                        <i class="fas fa-table empty-state-icon"></i>
                        <h3 class="empty-state-title">Tidak Ada Data</h3>
                        <p class="empty-state-description">
                            Belum ada role atau permission yang tersedia. Buat role dan permission terlebih dahulu.
                        </p>
                        <div class="empty-state-actions">
                            <a href="{{ route('role-management.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Buat Role
                            </a>
                            <a href="{{ route('permission-management.create') }}" class="btn btn-secondary">
                                <i class="fas fa-key"></i>
                                Buat Permission
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

            <!-- Bulk Actions -->
            @if($roles->count() > 0 && $permissions->count() > 0)
            <div class="bulk-actions-section">
                <div class="card">
                    <div class="card-header">
                        <h3 class="section-title">
                            <i class="fas fa-cogs"></i>
                            Aksi Massal
                        </h3>
                    </div>
                    <div class="card-main">
                        <div class="bulk-actions-grid">
                            <div class="bulk-form-section">
                                <h4 class="subsection-title">Atur Permission untuk Role Spesifik</h4>
                                <form id="bulkUpdateForm" class="bulk-form">
                                    @csrf
                                    <div class="form-group">
                                        <label for="bulkRoleId" class="form-label">Pilih Role:</label>
                                        <select class="form-select" id="bulkRoleId" name="role_id" required>
                                            <option value="">Pilih Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Pilih Permissions:</label>
                                        <div class="permissions-grid">
                                            @foreach($permissions as $category => $categoryPermissions)
                                                <div class="permission-category">
                                                    <div class="category-header">
                                                        <h5 class="category-title">
                                                            <input type="checkbox" class="category-checkbox" 
                                                                   data-category="{{ $category }}"
                                                                   id="bulk_category_{{ $category }}">
                                                            <label for="bulk_category_{{ $category }}" class="category-label">
                                                                {{ ucfirst($category) }}
                                                            </label>
                                                        </h5>
                                                    </div>
                                                    <div class="permissions-list">
                                                        @foreach($categoryPermissions as $permission)
                                                            <div class="permission-item">
                                                                <div class="form-check">
                                                                    <input class="form-check-input bulk-permission-checkbox" 
                                                                           type="checkbox" 
                                                                           name="permissions[]" 
                                                                           value="{{ $permission->id }}"
                                                                           data-category="{{ $category }}"
                                                                           id="bulk_permission_{{ $permission->id }}">
                                                                    <label for="bulk_permission_{{ $permission->id }}" class="form-check-label">
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
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i>
                                            Update Permissions
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="quick-actions-section">
                                <h4 class="subsection-title">Quick Actions</h4>
                                <div class="quick-actions-list">
                                    <button type="button" class="btn btn-success" onclick="selectAllPermissions()">
                                        <i class="fas fa-check-square"></i>
                                        Pilih Semua Permission
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearAllPermissions()">
                                        <i class="fas fa-square"></i>
                                        Hapus Semua Permission
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="loadRolePermissions()">
                                        <i class="fas fa-sync"></i>
                                        Muat Permission Role
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/role-permission-matrix.css') }}">
@endpush

@push('scripts')
<!-- Role Permission Matrix JavaScript -->
<script src="{{ asset('js/role-permission-matrix.js') }}"></script>
@endpush
