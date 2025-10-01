@extends('user-management.layout')

@section('title', 'Detail Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size: 24px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
        🏷️ Detail Role: {{ $role->display_name }}
    </h2>
    <div style="display: flex; gap: var(--spacing-sm);">
        <a href="{{ route('role-management.edit', $role->id) }}" class="btn btn-warning">
            ✏️ Edit
        </a>
        <a href="{{ route('role-management.index') }}" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- Role Information -->
    <div class="col-md-8">
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <div class="card-header">
                <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
                    ℹ️ Informasi Role
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nama Role:</strong><br>
                        <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{ $role->name }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Display Name:</strong><br>
                        {{ $role->display_name }}
                    </div>
                </div>
                <hr style="border: none; border-top: 1px solid var(--border-color); margin: var(--spacing-md) 0;">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        @if($role->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-secondary">Tidak Aktif</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Dibuat:</strong><br>
                        {{ $role->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                @if($role->description)
                    <hr style="border: none; border-top: 1px solid var(--border-color); margin: var(--spacing-md) 0;">
                    <strong>Deskripsi:</strong><br>
                    <p style="margin-top: var(--spacing-sm);">{{ $role->description }}</p>
                @endif
            </div>
        </div>

        <!-- Permissions -->
        <div class="card">
            <div class="card-header">
                <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
                    🔑 Permissions ({{ $role->permissions->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($role->permissions->count() > 0)
                    <div class="row">
                        @foreach($role->permissions->groupBy('category') as $category => $permissions)
                            <div class="col-md-6" style="margin-bottom: var(--spacing-md);">
                                <h6 style="color: #1976d2; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                                    📁 {{ ucfirst($category) }}
                                    <span class="badge badge-info">{{ $permissions->count() }}</span>
                                </h6>
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    @foreach($permissions as $permission)
                                        <li style="margin-bottom: var(--spacing-xs);">
                                            ✅
                                            <strong>{{ $permission->display_name }}</strong>
                                            @if($permission->description)
                                                <br><small class="text-muted">{{ $permission->description }}</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center" style="padding: var(--spacing-xxl) 0;">
                        <div style="font-size: 48px; color: var(--text-secondary); margin-bottom: var(--spacing-md);">🔑</div>
                        <h6 class="text-muted" style="margin-bottom: var(--spacing-md);">Role ini belum memiliki permission</h6>
                        <a href="{{ route('role-management.edit', $role->id) }}" class="btn btn-primary">
                            ➕ Tambah Permission
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Users with this role -->
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <div class="card-header">
                <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
                    👥 User dengan Role Ini
                </h5>
            </div>
            <div class="card-body">
                @if($role->users()->count() > 0)
                    <p class="text-muted" style="margin-bottom: var(--spacing-md);">Ada {{ $role->users()->count() }} user yang menggunakan role ini:</p>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @foreach($role->users()->limit(5)->get() as $user)
                            <li style="margin-bottom: var(--spacing-xs);">
                                👤
                                <a href="{{ route('user-management.show', $user->id) }}" style="text-decoration: none; color: var(--text-primary);">
                                    {{ $user->name }}
                                </a>
                                <small class="text-muted">({{ $user->username }})</small>
                            </li>
                        @endforeach
                    </ul>
                    @if($role->users()->count() > 5)
                        <small class="text-muted">... dan {{ $role->users()->count() - 5 }} user lainnya</small>
                    @endif
                @else
                    <div class="text-center" style="padding: var(--spacing-lg) 0;">
                        <div style="font-size: 32px; color: var(--text-secondary); margin-bottom: var(--spacing-sm);">👤</div>
                        <p class="text-muted" style="margin: 0;">Belum ada user yang menggunakan role ini</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
                    ⚡ Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                    <a href="{{ route('role-management.edit', $role->id) }}" class="btn btn-warning">
                        ✏️ Edit Role
                    </a>
                    <a href="{{ route('role-permission-matrix.index') }}" class="btn btn-info">
                        📊 Matrix Permission
                    </a>
                    @if($role->users()->count() == 0)
                        <form action="{{ route('role-management.destroy', $role->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Yakin ingin menghapus role ini?')">
                                🗑️ Hapus Role
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
