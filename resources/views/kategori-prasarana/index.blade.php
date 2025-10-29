@extends('layouts.app')

@section('title', 'Manajemen Kategori Prasarana')
@section('subtitle', 'Kelola kategori untuk mengelompokkan prasarana')

@section('header-actions')
@can('sarpras.create')
<a href="{{ route('kategori-prasarana.create') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i>
    Tambah Kategori
</a>
@endcan
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kategori-prasarana.css') }}?v={{ filemtime(public_path('css/kategori-prasarana.css')) }}">
@endpush


@section('content')
<div class="page-content kategori-prasarana-page">
    <div class="card card--headerless">
        <div class="card-main">
            <div class="filters-section">
                <form method="GET" action="{{ route('kategori-prasarana.index') }}" class="filters-form">
                    <div class="filters-grid">
                        <div class="filters-group">
                            <label class="filters-label">Pencarian</label>
                            <div class="search-input-wrapper">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Cari nama kategori..." 
                                       class="search-input">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>
                        
                        <div class="filters-group">
                            <label class="filters-label">Status</label>
                            <select name="status" class="filters-select">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-section">
            @if($kategoriPrasarana->count() > 0)
                <!-- Table Wrapper -->
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="table-head" style="width: 200px;">Nama Kategori</th>
                                <th class="table-head" style="width: 300px;">Deskripsi</th>
                                <th class="table-head" style="width: 100px;">Jumlah Prasarana</th>
                                <th class="table-head" style="width: 100px;">Status</th>
                                <th class="table-head" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kategoriPrasarana as $index => $item)
                            <tr>
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
                                                {{ Str::limit($item->description, 100) }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="table-body">
                                    <span class="badge badge-status_active">{{ $item->prasarana_count ?? 0 }}</span>
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
                                        <a href="{{ route('kategori-prasarana.show', $item->id) }}" 
                                           class="action-button action-button-view" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('sarpras.edit')
                                        <a href="{{ route('kategori-prasarana.edit', $item->id) }}" 
                                           class="action-button action-button-edit" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('sarpras.delete')
                                        <button type="button" 
                                                class="action-button action-button-delete" 
                                                onclick="confirmDelete({{ $item->id }}, '{{ $item->name }}')"
                                                title="Hapus">
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

                <!-- Pagination -->
                <div class="pagination-section">
                    <div class="pagination-info">
                        <span class="pagination-text">
                            Menampilkan {{ $kategoriPrasarana->firstItem() }}-{{ $kategoriPrasarana->lastItem() }} dari {{ $kategoriPrasarana->total() }} kategori
                        </span>
                    </div>
                    <div class="pagination-controls">
                        <div class="pagination-wrapper">
                            {{ $kategoriPrasarana->appends(request()->query())->links('pagination.custom') }}
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-container">
                        <i class="fas fa-building empty-state-icon"></i>
                        <h3 class="empty-state-title">Tidak Ada Data Kategori</h3>
                        <p class="empty-state-description">
                            @if(request()->filled('search') || request()->filled('status'))
                                Tidak ada kategori yang sesuai dengan filter yang dipilih.
                            @else
                                Belum ada data kategori prasarana yang tersedia. 
                                @can('sarpras.create')
                                    Klik tombol "Tambah Kategori" untuk menambahkan kategori pertama.
                                @endcan
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                            <a href="{{ route('kategori-prasarana.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hapus Filter
                            </a>
                        @else
                            @can('sarpras.create')
                            <a href="{{ route('kategori-prasarana.create') }}" class="btn btn-primary">
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3>Konfirmasi Hapus</h3>
        </div>
        <div class="dialog-body">
            <p>Apakah Anda yakin ingin menghapus kategori <strong id="deleteCategoryName"></strong>?</p>
            <p class="text-danger">Tindakan ini tidak dapat dibatalkan dan akan mempengaruhi prasarana yang menggunakan kategori ini.</p>
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
<script src="{{ asset('js/kategori-prasarana.js') }}"></script>
<script>
function confirmDelete(id, name) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    const nameElement = document.getElementById('deleteCategoryName');
    
    nameElement.textContent = name;
    form.action = `/kategori-prasarana/${id}`;
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