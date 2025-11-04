@extends('layouts.app')

@section('title', 'Manajemen Prasarana')
@section('subtitle', 'Kelola data prasarana dan fasilitas yang tersedia')

@section('header-actions')
@can('sarpras.create')
<a href="{{ route('prasarana.create') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i>
    Tambah Prasarana
</a>
@endcan
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/prasarana.css') }}?v={{ filemtime(public_path('css/prasarana.css')) }}">
@endpush

@section('content')
<div class="page-content prasarana-page">
    <div class="card card--headerless prasarana-list-card">
        <div class="card-main">
            <div class="filters-section">
                <form method="GET" action="{{ route('prasarana.index') }}" class="filters-form" novalidate>
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label" for="search">Pencarian</label>
                            <div class="search-input-wrapper">
                                <input type="text"
                                       id="search"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Cari nama prasarana..."
                                       class="search-input">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label" for="kategori_id">Kategori</label>
                            <select id="kategori_id" name="kategori_id" class="filters-select">
                                <option value="">Semua Kategori</option>
                                @foreach($kategoriPrasarana as $kategori)
                                    <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label" for="status">Status</label>
                            <select id="status" name="status" class="filters-select">
                                <option value="">Semua Status</option>
                                <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="rusak" {{ request('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label" for="lokasi">Lokasi</label>
                            <div class="search-input-wrapper">
                                <i class="fas fa-map-marker-alt search-icon" aria-hidden="true"></i>
                                <input type="text"
                                       id="lokasi"
                                       name="lokasi"
                                       value="{{ request('lokasi') }}"
                                       placeholder="Cari lokasi..."
                                       class="search-input"
                                       autocomplete="off">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-section">
                @if($prasarana->count() > 0)
                    <div class="table-wrapper" role="region" aria-live="polite">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Prasarana</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prasarana as $item)
                                <tr>
                                    <td>
                                        <div class="prasarana-info">
                                            <div class="prasarana-icon">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div class="prasarana-details">
                                                <div class="prasarana-name">{{ $item->name }}</div>
                                                @if($item->description)
                                                    <div class="prasarana-description">{{ Str::limit($item->description, 60) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-chip">
                                            <i class="fas fa-layer-group"></i>
                                            {{ $item->kategori->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="prasarana-meta">
                                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                            <span title="{{ $item->lokasi }}">{{ $item->lokasi ? Str::limit($item->lokasi, 40) : '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-capacity">
                                            <i class="fas fa-users"></i>
                                            {{ $item->kapasitas ? $item->kapasitas . ' orang' : 'Tidak ditentukan' }}
                                        </span>
                                    </td>
                                    <td>
                                        @switch($item->status)
                                            @case('tersedia')
                                                <span class="status-badge status-available">Tersedia</span>
                                                @break
                                            @case('rusak')
                                                <span class="status-badge status-damaged">Rusak</span>
                                                @break
                                            @case('maintenance')
                                                <span class="status-badge status-maintenance">Maintenance</span>
                                                @break
                                            @default
                                                <span class="status-badge status-available">Tersedia</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @can('sarpras.view')
                                            <a href="{{ route('prasarana.show', $item->id) }}" class="action-btn action-view" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('sarpras.edit')
                                            <a href="{{ route('prasarana.edit', $item->id) }}" class="action-btn action-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('sarpras.status_update')
                                            <button type="button"
                                                    class="action-btn {{ $item->status == 'tersedia' ? 'action-block' : 'action-unblock' }}"
                                                    onclick="toggleStatus({{ $item->id }}, '{{ $item->name }}', '{{ $item->status }}')"
                                                    title="{{ $item->status == 'tersedia' ? 'Set Rusak' : 'Set Tersedia' }}">
                                                <i class="fas fa-{{ $item->status == 'tersedia' ? 'times' : 'check' }}"></i>
                                            </button>
                                            @endcan
                                            @can('sarpras.delete')
                                            <button type="button"
                                                    class="action-btn action-delete"
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

                    <div class="pagination-section" aria-label="Navigasi halaman prasarana">
                        <div class="pagination-info">
                            <span class="pagination-text">Menampilkan {{ $prasarana->firstItem() }}-{{ $prasarana->lastItem() }} dari {{ $prasarana->total() }} prasarana</span>
                        </div>
                        <div class="pagination-controls">
                            <div class="pagination-wrapper">
                                {{ $prasarana->appends(request()->query())->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-container">
                            <div class="empty-icon">
                                <i class="fas fa-city"></i>
                            </div>
                            <h3 class="empty-title">Tidak Ada Data Prasarana</h3>
                            <p class="empty-description">
                                @if(request()->filled('search') || request()->filled('kategori_id') || request()->filled('status') || request()->filled('lokasi'))
                                    Tidak ada prasarana yang sesuai dengan filter yang dipilih.
                                @else
                                    Belum ada data prasarana yang tersedia.
                                @endif
                            </p>
                            @if(request()->filled('search') || request()->filled('kategori_id') || request()->filled('status') || request()->filled('lokasi'))
                                <a href="{{ route('prasarana.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Hapus Filter
                                </a>
                            @else
                                @can('sarpras.create')
                                <a href="{{ route('prasarana.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Tambah Prasarana Pertama
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
    <div id="deleteModal" class="dialog-backdrop" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle" aria-hidden="true" style="display: none;">
        <div class="dialog" tabindex="-1">
            <button type="button" class="dialog-close" aria-label="Tutup" onclick="closeDeleteModal()">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
            <div class="dialog-header">
                <i class="fas fa-exclamation-circle dialog-header__icon" aria-hidden="true"></i>
                <div class="dialog-header__content">
                    <h3 id="deleteModalTitle">Konfirmasi Hapus</h3>
                    <p class="dialog-header__subtitle">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>
            <div class="dialog-body">
                <p>Apakah Anda yakin ingin menghapus prasarana <strong id="deletePrasaranaName"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan dan akan mempengaruhi peminjaman yang menggunakan prasarana ini.</p>
            </div>
            <div class="dialog-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                <form id="deleteForm" method="POST" aria-label="Form hapus prasarana" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Status Toggle Modal -->
    <div id="statusModal" class="dialog-backdrop" role="dialog" aria-modal="true" aria-labelledby="statusModalTitle" aria-hidden="true" style="display: none;">
        <div class="dialog" tabindex="-1">
            <button type="button" class="dialog-close" aria-label="Tutup" onclick="closeStatusModal()">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
            <div class="dialog-header">
                <i class="fas fa-sync-alt dialog-header__icon" aria-hidden="true"></i>
                <div class="dialog-header__content">
                    <h3 id="statusModalTitle">Ubah Status Prasarana</h3>
                    <p class="dialog-header__subtitle">Pilih status terbaru untuk prasarana.</p>
                </div>
            </div>
            <div class="dialog-body">
                <p>Apakah Anda yakin ingin mengubah status prasarana <strong id="statusPrasaranaName"></strong>?</p>
                <div class="form-group">
                    <label class="form-label" for="newStatus">Status Baru</label>
                    <select id="newStatus" class="form-input">
                        <option value="tersedia">Tersedia</option>
                        <option value="rusak">Rusak</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
            <div class="dialog-footer">
                <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="updateStatus()">Update Status</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const modalFocusReturn = new Map();

function getOpenModals() {
    return document.querySelectorAll('.dialog-backdrop[aria-hidden="false"]');
}

function showModal(modal) {
    if (!modal) return;

    modalFocusReturn.set(modal.id, document.activeElement);
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');

    const dialog = modal.querySelector('.dialog');
    if (dialog) {
        dialog.focus();
    }

    document.body.classList.add('modal-open');
}

function hideModal(modal) {
    if (!modal) return;

    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');

    const previousFocus = modalFocusReturn.get(modal.id);
    if (previousFocus && typeof previousFocus.focus === 'function') {
        previousFocus.focus();
    }
    modalFocusReturn.delete(modal.id);

    if (getOpenModals().length === 0) {
        document.body.classList.remove('modal-open');
    }
}

function confirmDelete(id, name) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    const nameElement = document.getElementById('deletePrasaranaName');

    nameElement.textContent = name;
    form.action = `/prasarana/${id}`;

    showModal(modal);
}

function closeDeleteModal() {
    hideModal(document.getElementById('deleteModal'));
}

function toggleStatus(id, name, currentStatus) {
    const modal = document.getElementById('statusModal');
    const nameElement = document.getElementById('statusPrasaranaName');
    const statusSelect = document.getElementById('newStatus');

    nameElement.textContent = name;

    let newStatus = 'tersedia';
    if (currentStatus === 'tersedia') {
        newStatus = 'rusak';
    }
    statusSelect.value = newStatus;

    modal.dataset.prasaranaId = id;

    showModal(modal);
}

function closeStatusModal() {
    hideModal(document.getElementById('statusModal'));
}

function updateStatus() {
    const statusSelect = document.getElementById('newStatus');
    const newStatus = statusSelect.value;

    const modal = document.getElementById('statusModal');
    const prasaranaId = modal.dataset.prasaranaId;

    if (prasaranaId && newStatus) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/prasarana/${prasaranaId}/status`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        
        const statusField = document.createElement('input');
        statusField.type = 'hidden';
        statusField.name = 'status';
        statusField.value = newStatus;
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        form.appendChild(statusField);

        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking backdrop
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.dialog-backdrop[aria-hidden="false"]');
        if (openModal) {
            hideModal(openModal.id === 'deleteModal' ? document.getElementById('deleteModal') : document.getElementById('statusModal'));
        }
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
