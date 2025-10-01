@extends('user-management.layout')

@section('title', 'Matrix Role vs Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size: 24px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
        📊 Matrix Role vs Permission
    </h2>
    <div style="display: flex; gap: var(--spacing-sm);">
        <a href="{{ route('role-management.index') }}" class="btn btn-info">
            🏷️ Kelola Role
        </a>
        <a href="{{ route('permission-management.index') }}" class="btn btn-warning">
            🔑 Kelola Permission
        </a>
    </div>
</div>

<!-- Matrix Table -->
<div class="card">
    <div class="card-header">
        <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
            📊 Matrix Role vs Permission
        </h5>
        <p class="text-muted" style="margin: 0; margin-top: var(--spacing-xs);">Klik checkbox untuk mengatur permission role. Perubahan akan disimpan otomatis.</p>
    </div>
    <div class="card-body">
        @if($roles->count() > 0 && $permissions->count() > 0)
            <div style="overflow-x: auto;">
                <table class="table" style="border: 1px solid var(--border-color);">
                    <thead style="background: #212529; color: white;">
                        <tr>
                            <th style="width: 200px; position: sticky; left: 0; background: #212529; z-index: 10; padding: 12px;">
                                🏷️ Role
                            </th>
                            @foreach($permissions as $category => $categoryPermissions)
                                <th colspan="{{ $categoryPermissions->count() }}" class="text-center" style="padding: 12px;">
                                    📁 {{ ucfirst($category) }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            <th style="position: sticky; left: 0; background: #212529; z-index: 10; padding: 12px;">Permission</th>
                            @foreach($permissions as $category => $categoryPermissions)
                                @foreach($categoryPermissions as $permission)
                                    <th class="text-center" style="width: 120px; padding: 12px;">
                                        <div style="display: flex; flex-direction: column; align-items: center;">
                                            <small style="font-weight: 600;">{{ $permission->display_name }}</small>
                                            <small style="color: #ccc;">{{ $permission->name }}</small>
                                        </div>
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td style="position: sticky; left: 0; background: #f8f9fa; z-index: 5; padding: 12px;">
                                    <div style="display: flex; flex-direction: column;">
                                        <strong>{{ $role->display_name }}</strong>
                                        <small class="text-muted">{{ $role->name }}</small>
                                        <small class="text-muted">{{ $role->users()->count() }} user</small>
                                    </div>
                                </td>
                                @foreach($permissions as $category => $categoryPermissions)
                                    @foreach($categoryPermissions as $permission)
                                        <td class="text-center" style="padding: 12px;">
                                            <div class="form-check" style="display: flex; justify-content: center;">
                                                <input class="form-check-input permission-checkbox" 
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

            <!-- Legend -->
            <div class="row" style="margin-top: var(--spacing-lg);">
                <div class="col-md-6">
                    <h6 style="font-size: 14px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                        ℹ️ Keterangan:
                    </h6>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: var(--spacing-xs);">✅ <strong>Centang:</strong> Role memiliki permission</li>
                        <li style="margin-bottom: var(--spacing-xs);">❌ <strong>Tidak Centang:</strong> Role tidak memiliki permission</li>
                        <li style="margin-bottom: var(--spacing-xs);"><strong>Otomatis:</strong> Perubahan disimpan otomatis saat checkbox diubah</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 style="font-size: 14px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                        📊 Statistik:
                    </h6>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: var(--spacing-xs);"><strong>Total Role:</strong> {{ $roles->count() }}</li>
                        <li style="margin-bottom: var(--spacing-xs);"><strong>Total Permission:</strong> {{ $permissions->flatten()->count() }}</li>
                        <li style="margin-bottom: var(--spacing-xs);"><strong>Kategori Permission:</strong> {{ $permissions->count() }}</li>
                    </ul>
                </div>
            </div>
        @else
            <div class="text-center" style="padding: var(--spacing-xxl) 0;">
                <div style="font-size: 64px; color: var(--text-secondary); margin-bottom: var(--spacing-md);">📊</div>
                <h5 class="text-muted" style="margin-bottom: var(--spacing-sm);">Tidak ada data untuk ditampilkan</h5>
                <p class="text-muted" style="margin-bottom: var(--spacing-lg);">Pastikan ada role dan permission yang tersedia.</p>
                <div style="margin-top: var(--spacing-md); display: flex; gap: var(--spacing-sm); justify-content: center;">
                    <a href="{{ route('role-management.create') }}" class="btn btn-primary">
                        🏷️ Buat Role
                    </a>
                    <a href="{{ route('permission-management.create') }}" class="btn btn-warning">
                        🔑 Buat Permission
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Bulk Actions -->
@if($roles->count() > 0 && $permissions->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
            ⚙️ Aksi Massal
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 style="font-size: 14px; font-weight: 500; color: var(--text-primary); margin-bottom: var(--spacing-md);">Atur Permission untuk Role Spesifik:</h6>
                <form id="bulkUpdateForm">
                    @csrf
                    <div class="form-group">
                        <label for="bulkRoleId" class="form-label">Pilih Role:</label>
                        <select class="form-control" id="bulkRoleId" name="role_id" required>
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pilih Permissions:</label>
                        @foreach($permissions as $category => $categoryPermissions)
                            <div class="card" style="margin-bottom: var(--spacing-sm);">
                                <div class="card-header" style="padding: var(--spacing-sm);">
                                    <h6 style="margin: 0; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: var(--spacing-sm);">
                                        <input type="checkbox" class="form-check-input category-checkbox" 
                                               data-category="{{ $category }}">
                                        📁 {{ ucfirst($category) }}
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: var(--spacing-sm);">
                                    <div class="row">
                                        @foreach($categoryPermissions as $permission)
                                            <div class="col-md-6" style="margin-bottom: var(--spacing-xs);">
                                                <div class="form-check">
                                                    <input class="form-check-input bulk-permission-checkbox" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->id }}"
                                                           data-category="{{ $category }}">
                                                    <label class="form-check-label">
                                                        {{ $permission->display_name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary">
                        💾 Update Permissions
                    </button>
                </form>
            </div>
            <div class="col-md-6">
                <h6 style="font-size: 14px; font-weight: 500; color: var(--text-primary); margin-bottom: var(--spacing-md);">Quick Actions:</h6>
                <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                    <button type="button" class="btn btn-success" onclick="selectAllPermissions()">
                        ☑️ Pilih Semua Permission
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearAllPermissions()">
                        ☐ Hapus Semua Permission
                    </button>
                    <button type="button" class="btn btn-info" onclick="loadRolePermissions()">
                        🔄 Muat Permission Role
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<!-- Role Permission Matrix JavaScript -->
<script src="{{ asset('js/role-permission-matrix.js') }}"></script>
@endpush
