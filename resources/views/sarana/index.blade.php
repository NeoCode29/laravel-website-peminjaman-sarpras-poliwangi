@extends('layouts.app')

@section('title', 'Daftar Sarana')
@section('subtitle', 'Kelola data sarana dan unit yang tersedia')

@section('header-actions')
@can('sarpras.create')
<a href="{{ route('sarana.create') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i>
    Tambah Sarana
</a>
@endcan
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sarana.css') }}?v={{ filemtime(public_path('css/sarana.css')) }}">
@endpush

@section('content')
<div class="page-content">
    <div class="card card--headerless">
        <div class="card-main">
        <!-- Filters Section -->
        <div class="filters-section">
            <form method="GET" action="{{ route('sarana.index') }}" class="filters-form">
                <div class="filters-grid">
                    <div class="filters-group">
                        <label class="filters-label">Pencarian</label>
                        <div class="search-input-wrapper">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Cari nama sarana..." 
                                   class="search-input">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                    </div>
                    
                    <div class="filters-group">
                        <label class="filters-label">Kategori</label>
                        <select name="kategori_id" class="filters-select">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoriSarana as $kategori)
                                <option value="{{ $kategori->id }}" 
                                        {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filters-group">
                        <label class="filters-label">Tipe</label>
                        <select name="type" class="filters-select">
                            <option value="">Semua Tipe</option>
                            <option value="serialized" {{ request('type') == 'serialized' ? 'selected' : '' }}>
                                Serialized
                            </option>
                            <option value="pooled" {{ request('type') == 'pooled' ? 'selected' : '' }}>
                                Pooled
                            </option>
                        </select>
                    </div>
                    
                    <div class="filters-group">
                        <label class="filters-label">Status</label>
                        <select name="status" class="filters-select">
                            <option value="">Semua Status</option>
                            <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>
                                Tersedia
                            </option>
                            <option value="rusak" {{ request('status') == 'rusak' ? 'selected' : '' }}>
                                Rusak
                            </option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>
                                Maintenance
                            </option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            @if($sarana->count() > 0)
                <!-- Table Wrapper -->
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="table-head" style="width: 60px;">No</th>
                                <th class="table-head" style="width: 200px;">Nama Sarana</th>
                                <th class="table-head" style="width: 120px;">Kategori</th>
                                <th class="table-head" style="width: 100px;">Tipe</th>
                                <th class="table-head" style="width: 100px;">Total Unit</th>
                                <th class="table-head" style="width: 100px;">Tersedia</th>
                                <th class="table-head" style="width: 100px;">Dipinjam</th>
                                <th class="table-head" style="width: 100px;">Rusak</th>
                                <th class="table-head" style="width: 120px;">Lokasi</th>
                                <th class="table-head" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $startNumber = method_exists($sarana, 'firstItem') ? $sarana->firstItem() : 1;
                            @endphp
                            @foreach($sarana as $item)
                            <tr>
                                <td class="table-body table-number">
                                    {{ $startNumber + $loop->index }}
                                </td>
                                <td class="table-body">
                                    <div class="user-info">
                                        <div class="user-details">
                                            <div class="user-name">{{ $item->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-body">
                                    <span class="badge badge-role">{{ $item->kategori->name }}</span>
                                </td>
                                <td class="table-body">
                                    @if($item->type == 'serialized')
                                        <span class="badge badge-in_progress">Serialized</span>
                                    @else
                                        <span class="badge badge-done">Pooled</span>
                                    @endif
                                </td>
                                <td class="table-body">
                                    <strong>{{ $item->jumlah_total }}</strong>
                                </td>
                                <td class="table-body">
                                    <span class="badge badge-status_active">{{ $item->jumlah_tersedia }}</span>
                                </td>
                                <td class="table-body">
                                    @php
                                        $jumlah_dipinjam = $item->peminjamanItems()
                                            ->whereHas('peminjaman', function($query) {
                                                $query->whereIn('status', ['approved', 'picked_up']);
                                            })
                                            ->sum('qty_approved');
                                    @endphp
                                    @if($jumlah_dipinjam > 0)
                                        <span class="badge badge-warning">{{ $jumlah_dipinjam }}</span>
                                    @else
                                        <span class="badge badge-status_active">0</span>
                                    @endif
                                </td>
                                <td class="table-body">
                                    @if($item->jumlah_rusak > 0)
                                        <span class="badge badge-status_blocked">{{ $item->jumlah_rusak }}</span>
                                    @else
                                        <span class="badge badge-status_active">0</span>
                                    @endif
                                </td>
                                <td class="table-body">
                                    {{ $item->lokasi ?? '-' }}
                                </td>
                                <td class="table-body">
                                    <div class="action-buttons">
                                        @can('sarpras.view')
                                        <a href="{{ route('sarana.show', $item->id) }}" 
                                           class="action-button action-button-view" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('sarpras.edit')
                                        <a href="{{ route('sarana.edit', $item->id) }}" 
                                           class="action-button action-button-edit" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        
                                        @if($item->type == 'serialized')
                                            @can('sarpras.unit_manage')
                                            <a href="{{ route('sarana.units', $item->id) }}" 
                                               class="action-button action-button-reset" 
                                               title="Kelola Unit">
                                                <i class="fas fa-cogs"></i>
                                            </a>
                                            @endcan
                                        @endif
                                        
                                        @can('sarpras.delete')
                                        <button type="button" 
                                                class="action-button action-button-delete" 
                                                onclick="confirmDelete({{ $item->id }})"
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
                            Menampilkan {{ $sarana->firstItem() }}-{{ $sarana->lastItem() }} dari {{ $sarana->total() }} sarana
                        </span>
                    </div>
                    <div class="pagination-controls">
                        <div class="pagination-wrapper">
                            {{ $sarana->appends(request()->query())->links('pagination.custom') }}
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-container">
                        <i class="fas fa-tools empty-state-icon"></i>
                        <h3 class="empty-state-title">Tidak Ada Data Sarana</h3>
                        <p class="empty-state-description">
                            @if(request()->filled('search') || request()->filled('kategori_id') || request()->filled('type') || request()->filled('status'))
                                Tidak ada sarana yang sesuai dengan filter yang dipilih.
                            @else
                                Belum ada data sarana yang tersedia. 
                                @can('sarpras.create')
                                    Klik tombol "Tambah Sarana" untuk menambahkan data sarana pertama.
                                @endcan
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('kategori_id') || request()->filled('type') || request()->filled('status'))
                            <a href="{{ route('sarana.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hapus Filter
                            </a>
                        @else
                            @can('sarpras.create')
                            <a href="{{ route('sarana.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Tambah Sarana Pertama
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
            <p>Apakah Anda yakin ingin menghapus sarana ini? Tindakan ini tidak dapat dibatalkan.</p>
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
<script src="{{ asset('js/sarana.js') }}"></script>
<script>
function confirmDelete(id) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    
    form.action = `/sarana/${id}`;
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

<style>
/* Badge Warning untuk Status Dipinjam */
.badge-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Badge Warning untuk Status Dipinjam */
.badge-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Action Buttons - Sesuai Style Guide */
.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
}

.action-button {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid #e0e0e0;
    background: #ffffff;
    color: #333333;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    font-size: 14px;
}

.action-button:hover {
    background: #f5f5f5;
    border-color: #cccccc;
    transform: translateY(-1px);
}

/* Action Button Variants */
.action-button-view:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

.action-button-edit:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

.action-button-reset:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

.action-button-delete {
    background: #fff5f5;
    border-color: #fed7d7;
    color: #e53e3e;
}

.action-button-delete:hover {
    background: #fed7d7;
    border-color: #feb2b2;
    color: #c53030;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(229, 62, 62, 0.2);
}

/* Mobile Responsive untuk Action Buttons */
@media (max-width: 768px) {
    .action-buttons {
        gap: 4px;
    }
    
    .action-button {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}
</style>
@endpush
