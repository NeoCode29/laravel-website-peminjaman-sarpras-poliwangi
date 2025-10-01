@extends('user-management.layout')

@section('title', 'Daftar Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size: 24px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
        🔑 Daftar Permission
    </h2>
    <a href="{{ route('permission-management.create') }}" class="btn btn-primary">
        ➕ Tambah Permission
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
        <form method="GET" action="{{ route('permission-management.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search" class="form-label">Pencarian</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Nama permission atau deskripsi">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        🔍 Cari
                    </button>
                    <a href="{{ route('permission-management.index') }}" class="btn btn-secondary">
                        ❌ Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Permission List -->
<div class="card">
    <div class="card-header">
        <h5 style="font-size: 16px; font-weight: 500; color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
            📋 Daftar Permission ({{ $permissions->total() }} permission)
        </h5>
    </div>
    <div class="card-body">
        @if($permissions->count() > 0)
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Permission</th>
                            <th>Display Name</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $permission)
                            <tr>
                                <td>
                                    <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{ $permission->name }}</code>
                                </td>
                                <td>{{ $permission->display_name }}</td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($permission->category) }}</span>
                                </td>
                                <td>{{ Str::limit($permission->description, 50) }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ $permission->roles->count() }} roles</span>
                                </td>
                                <td>
                                    @if($permission->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('permission-management.show', $permission->id) }}" 
                                           class="btn btn-sm btn-info" title="Lihat Detail">
                                            👁️
                                        </a>
                                        <a href="{{ route('permission-management.edit', $permission->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            ✏️
                                        </a>
                                        <form action="{{ route('permission-management.toggle-status', $permission->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $permission->is_active ? 'btn-secondary' : 'btn-success' }}" 
                                                    title="{{ $permission->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    onclick="return confirm('Yakin ingin {{ $permission->is_active ? 'nonaktifkan' : 'aktifkan' }} permission ini?')">
                                                {{ $permission->is_active ? '⏸️' : '▶️' }}
                                            </button>
                                        </form>
                                        @if($permission->roles()->count() == 0)
                                            <form action="{{ route('permission-management.destroy', $permission->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                                        onclick="return confirm('Yakin ingin menghapus permission ini?')">
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
                @if ($permissions->onFirstPage())
                    <span class="disabled">« Previous</span>
                @else
                    <a href="{{ $permissions->previousPageUrl() }}">« Previous</a>
                @endif

                @foreach ($permissions->getUrlRange(1, $permissions->lastPage()) as $page => $url)
                    @if ($page == $permissions->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($permissions->hasMorePages())
                    <a href="{{ $permissions->nextPageUrl() }}">Next »</a>
                @else
                    <span class="disabled">Next »</span>
                @endif
            </div>
        @else
            <div class="text-center" style="padding: var(--spacing-xxl) 0;">
                <div style="font-size: 48px; color: var(--text-secondary); margin-bottom: var(--spacing-md);">🔑</div>
                <h5 class="text-muted" style="margin-bottom: var(--spacing-sm);">Tidak ada permission ditemukan</h5>
                <p class="text-muted" style="margin-bottom: var(--spacing-lg);">Mulai dengan membuat permission pertama Anda.</p>
                <a href="{{ route('permission-management.create') }}" class="btn btn-primary">
                    ➕ Tambah Permission
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
