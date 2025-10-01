@extends('user-management.layout')

@section('title', 'Daftar Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size: 24px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
        🏷️ Daftar Role
    </h2>
    <a href="{{ route('role-management.create') }}" class="btn btn-primary">
        ➕ Tambah Role
    </a>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
            🔍 Filter
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('role-management.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search" class="form-label">Pencarian</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Nama role atau deskripsi">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        🔍 Cari
                    </button>
                    <a href="{{ route('role-management.index') }}" class="btn btn-secondary">
                        ❌ Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Role List -->
<div class="card">
    <div class="card-header">
        <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
            📋 Daftar Role ({{ $roles->total() }} role)
        </h5>
    </div>
    <div class="card-body">
        @if($roles->count() > 0)
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Role</th>
                            <th>Display Name</th>
                            <th>Deskripsi</th>
                            <th>Permissions</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                </td>
                                <td>{{ $role->display_name }}</td>
                                <td>{{ Str::limit($role->description, 50) }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $role->permissions->count() }} permissions</span>
                                </td>
                                <td>
                                    @if($role->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>{{ $role->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('role-management.show', $role->id) }}" 
                                           class="btn btn-sm btn-info" title="Lihat Detail">
                                            👁️
                                        </a>
                                        <a href="{{ route('role-management.edit', $role->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            ✏️
                                        </a>
                                        <form action="{{ route('role-management.toggle-status', $role->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $role->is_active ? 'btn-secondary' : 'btn-success' }}" 
                                                    title="{{ $role->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    onclick="return confirm('Yakin ingin {{ $role->is_active ? 'nonaktifkan' : 'aktifkan' }} role ini?')">
                                                {{ $role->is_active ? '⏸️' : '▶️' }}
                                            </button>
                                        </form>
                                        @if($role->users()->count() == 0)
                                            <form action="{{ route('role-management.destroy', $role->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                                        onclick="return confirm('Yakin ingin menghapus role ini?')">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                @if ($roles->onFirstPage())
                    <span class="disabled">« Previous</span>
                @else
                    <a href="{{ $roles->previousPageUrl() }}">« Previous</a>
                @endif

                @foreach ($roles->getUrlRange(1, $roles->lastPage()) as $page => $url)
                    @if ($page == $roles->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($roles->hasMorePages())
                    <a href="{{ $roles->nextPageUrl() }}">Next »</a>
                @else
                    <span class="disabled">Next »</span>
                @endif
            </div>
        @else
            <div class="text-center" style="padding: var(--spacing-xxl) 0;">
                <div style="font-size: 48px; color: var(--text-secondary); margin-bottom: var(--spacing-md);">🏷️</div>
                <h5 class="text-muted" style="margin-bottom: var(--spacing-sm);">Tidak ada role ditemukan</h5>
                <p class="text-muted" style="margin-bottom: var(--spacing-lg);">Mulai dengan membuat role pertama Anda.</p>
                <a href="{{ route('role-management.create') }}" class="btn btn-primary">
                    ➕ Tambah Role
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
