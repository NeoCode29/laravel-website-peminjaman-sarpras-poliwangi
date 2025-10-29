@extends('layouts.app')

@section('title', 'Daftar Kategori Sarana')
@section('subtitle', 'Kelola kategori sarana dan status ketersediaannya')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kategori-sarana.css') }}?v={{ filemtime(public_path('css/kategori-sarana.css')) }}">
@endpush

@section('header-actions')
    @can('sarpras.create')
    <a href="{{ route('kategori-sarana.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Tambah Kategori
    </a>
    @endcan
@endsection

@section('content')
<div class="page-content">
    <div class="card card--headerless">
        <div class="card-main">
            <div class="filters-section">
                <form method="GET" action="{{ route('kategori-sarana.index') }}" class="filters-form">
                    <div class="filters-grid">
                        <div class="filters-group">
                            <label class="filters-label" for="search">Pencarian</label>
                            <div class="search-input-wrapper">
                                <input type="text"
                                       id="search"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Cari nama kategori..."
                                       class="search-input">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>

                        <div class="filters-group">
                            <label class="filters-label" for="status">Status</label>
                            <select id="status" name="status" class="filters-select">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-section">
                @if($kategoriSarana->count() > 0)
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="table-head" style="width: 60px;">No</th>
                                    <th class="table-head" style="width: 220px;">Nama Kategori</th>
                                    <th class="table-head" style="width: 320px;">Deskripsi</th>
                                    <th class="table-head" style="width: 120px;">Jumlah Sarana</th>
                                    <th class="table-head" style="width: 120px;">Status</th>
                                    <th class="table-head" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $startNumber = method_exists($kategoriSarana, 'firstItem') ? $kategoriSarana->firstItem() : 1;
                                @endphp
                                @foreach($kategoriSarana as $item)
                                <tr>
                                    <td class="table-body table-number">{{ $startNumber + $loop->index }}</td>
                                    <td class="table-body">
                                        <div class="user-info">
                                            <div class="user-details">
                                                <div class="user-name">{{ $item->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="table-body">
                                        <div class="description-cell">
                                            @if($item->description)
                                                <div class="description-text" title="{{ $item->description }}">
                                                    {{ Str::limit($item->description, 120) }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="table-body">
                                        <span class="badge badge-role">{{ $item->sarana_count ?? 0 }}</span>
                                    </td>
                                    <td class="table-body">
                                        @if($item->is_active ?? true)
                                            <span class="badge badge-status_active">Aktif</span>
                                        @else
                                            <span class="badge badge-status_blocked">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td class="table-body">
                                        <div class="action-buttons">
                                            @can('sarpras.view')
                                            <a href="{{ route('kategori-sarana.show', $item->id) }}" class="action-button action-button-view" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan

                                            @can('sarpras.edit')
                                            <a href="{{ route('kategori-sarana.edit', $item->id) }}" class="action-button action-button-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan

                                            @can('sarpras.delete')
                                            <button type="button" class="action-button action-button-delete" onclick="confirmDelete({{ $item->id }}, '{{ $item->name }}')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-section">
                        <div class="pagination-info">
                            <span class="pagination-text">
                                Menampilkan {{ $kategoriSarana->firstItem() }}-{{ $kategoriSarana->lastItem() }} dari {{ $kategoriSarana->total() }} kategori
                            </span>
                        </div>
                        <div class="pagination-controls">
                            <div class="pagination-wrapper">
                                {{ $kategoriSarana->appends(request()->query())->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-container">
                            <i class="fas fa-tags empty-state-icon"></i>
                            <h3 class="empty-state-title">Tidak Ada Data Kategori</h3>
                            <p class="empty-state-description">
                                @if(request()->filled('search') || request()->filled('status'))
                                    Tidak ada kategori yang sesuai dengan filter yang dipilih.
                                @else
                                    Belum ada kategori sarana yang tersedia.
                                    @can('sarpras.create')
                                        Klik tombol "Tambah Kategori" untuk membuat kategori pertama.
                                    @endcan
                                @endif
                            </p>
                            @if(request()->filled('search') || request()->filled('status'))
                                <a href="{{ route('kategori-sarana.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Hapus Filter
                                </a>
                            @else
                                @can('sarpras.create')
                                <a href="{{ route('kategori-sarana.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Tambah Kategori Pertama
                                </a>
                                @endcan
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Konfirmasi Hapus</h3>
        </div>
        <div class="dialog-body">
            <p>Apakah Anda yakin ingin menghapus kategori <strong id="deleteCategoryName"></strong>?</p>
            <p class="text-danger">Tindakan ini tidak dapat dibatalkan dan akan mempengaruhi sarana yang menggunakan kategori ini.</p>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    const nameElement = document.getElementById('deleteCategoryName');
    
    nameElement.textContent = name;
    form.action = `/kategori-sarana/${id}`;
    modal.style.display = 'flex';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
}

// Close modal when clicking backdrop
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

// Auto-submit form when filter changes
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.filters-form');
    const filterSelects = document.querySelectorAll('.filters-select');
    
    // Auto-submit when select changes
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
    
    // Auto-submit when search input changes (with debounce)
    const searchInput = document.querySelector('.search-input');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 500); // 500ms delay
        });
    }
});
</script>
@endpush
