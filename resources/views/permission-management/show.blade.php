@extends('user-management.layout')

@section('title', 'Detail Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key"></i> Detail Permission</h2>
    <div>
        <a href="{{ route('permission-management.edit', $permission->id) }}" class="btn btn-warning me-2">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('permission-management.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- Permission Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Informasi Permission</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nama Permission:</strong><br>
                        <code>{{ $permission->name }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Display Name:</strong><br>
                        {{ $permission->display_name }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Kategori:</strong><br>
                        <span class="badge bg-info">{{ ucfirst($permission->category) }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        @if($permission->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Tidak Aktif</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Dibuat:</strong><br>
                        {{ $permission->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="col-md-6">
                        <strong>Terakhir Update:</strong><br>
                        {{ $permission->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                @if($permission->description)
                    <hr>
                    <strong>Deskripsi:</strong><br>
                    <p class="mt-2">{{ $permission->description }}</p>
                @endif
            </div>
        </div>

        <!-- Roles using this permission -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user-tag"></i> Role yang Menggunakan Permission Ini ({{ $permission->roles->count() }})</h5>
            </div>
            <div class="card-body">
                @if($permission->roles->count() > 0)
                    <div class="row">
                        @foreach($permission->roles as $role)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-user-tag"></i> {{ $role->display_name }}
                                            @if($role->is_active)
                                                <span class="badge bg-success ms-2">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary ms-2">Tidak Aktif</span>
                                            @endif
                                        </h6>
                                        <p class="card-text text-muted mb-2">{{ $role->description }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-users"></i> {{ $role->users()->count() }} user
                                        </small>
                                        <div class="mt-2">
                                            <a href="{{ route('role-management.show', $role->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Lihat Role
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Permission ini belum digunakan oleh role manapun</h6>
                        <a href="{{ route('role-permission-matrix.index') }}" class="btn btn-primary">
                            <i class="fas fa-table"></i> Atur di Matrix Permission
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-bolt"></i> Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('permission-management.edit', $permission->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Permission
                    </a>
                    <a href="{{ route('role-permission-matrix.index') }}" class="btn btn-info">
                        <i class="fas fa-table"></i> Matrix Permission
                    </a>
                    @if($permission->roles()->count() == 0)
                        <form action="{{ route('permission-management.destroy', $permission->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Yakin ingin menghapus permission ini?')">
                                <i class="fas fa-trash"></i> Hapus Permission
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Permission Usage Stats -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Statistik Penggunaan</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $permission->roles->count() }}</h4>
                        <small class="text-muted">Role</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $permission->roles->sum(function($role) { return $role->users()->count(); }) }}</h4>
                        <small class="text-muted">Total User</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Permission ini digunakan oleh {{ $permission->roles->count() }} role 
                        dengan total {{ $permission->roles->sum(function($role) { return $role->users()->count(); }) }} user.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
