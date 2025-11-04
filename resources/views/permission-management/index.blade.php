@extends('layouts.app')

@section('title', 'Daftar Permission')
@section('subtitle', 'Kelola permission sistem')

@section('header-actions')
<a href="{{ route('permission-management.create') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i>
    Tambah Permission
</a>
@endsection

@section('content')
<div class="permission-management-container">
    <!-- Single Card -->
    <div class="card">
        <div class="card-header" aria-hidden="true"></div>

        <div class="card-main">
            <!-- Filters Section -->
            <form method="GET" action="{{ route('permission-management.index') }}" class="filters-form">
                <div class="filters-grid">
                    <div class="form-group">
                        <label class="form-label">Cari Permission</label>
                            <div class="search-input-wrapper">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Cari berdasarkan nama, display name, atau deskripsi..."
                                   class="search-input">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-select">
                                <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                            </select>
                        </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                                <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                    </div>
                </div>
            </form>

            @if($permissions->count() > 0)
                <!-- Table Section -->
                <div class="table-section">
                    <!-- Table Wrapper -->
            <div class="table-wrapper">
                        <table class="data-table">
                    <thead>
                        <tr>
                                    <th>Nama Permission</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Jumlah Role</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                        </tr>
                    </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td>
                                        <div class="permission-info">
                                            <div class="permission-details">
                                                <div class="permission-name">{{ $permission->display_name }}</div>
                                                <div class="permission-code">{{ $permission->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-category">{{ ucfirst($permission->category) }}</span>
                                    </td>
                                    <td>
                                        @if($permission->is_active)
                                            <span class="badge badge-status-active">Aktif</span>
                                        @else
                                            <span class="badge badge-status-inactive">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="role-count">{{ $permission->roles_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <div class="permission-description">
                                            {{ Str::limit($permission->description, 50) ?: '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('permission-management.show', $permission->id) }}" 
                                               class="action-btn action-btn-view" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('permission-management.edit', $permission->id) }}" 
                                               class="action-btn action-btn-edit" 
                                               title="Edit Permission">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($permission->is_active)
                                                <button type="button" 
                                                        class="action-btn action-btn-toggle-inactive" 
                                                        onclick="togglePermissionStatus({{ $permission->id }})"
                                                        title="Nonaktifkan Permission">
                                                    <i class="fas fa-toggle-on"></i>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        class="action-btn action-btn-toggle-active" 
                                                        onclick="togglePermissionStatus({{ $permission->id }})"
                                                        title="Aktifkan Permission">
                                                    <i class="fas fa-toggle-off"></i>
                                                </button>
                                            @endif
                                            @if($permission->roles->count() == 0)
                                                <button type="button" 
                                                        class="action-btn action-btn-delete" 
                                                        onclick="deletePermission({{ $permission->id }})"
                                                        title="Hapus Permission">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Pagination -->
                @if($permissions->hasPages())
                <div class="pagination-section">
                <div class="pagination-info">
                        <p class="pagination-text">
                            Menampilkan {{ $permissions->firstItem() }} - {{ $permissions->lastItem() }} dari {{ $permissions->total() }} permission
                        </p>
                </div>
                <div class="pagination-controls">
                    <div class="pagination-nav">
                            <ul class="pagination-list">
                                {{-- Previous Page Link --}}
                                @if ($permissions->onFirstPage())
                                    <li class="pagination-item">
                                        <span class="pagination-link pagination-link-disabled">
                                            <i class="fas fa-chevron-left pagination-icon"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="pagination-item">
                                        <a href="{{ $permissions->previousPageUrl() }}" class="pagination-link">
                                            <i class="fas fa-chevron-left pagination-icon"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($permissions->getUrlRange(1, $permissions->lastPage()) as $page => $url)
                                    @if ($page == $permissions->currentPage())
                                        <li class="pagination-item">
                                            <span class="pagination-link pagination-link-active">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="pagination-item">
                                            <a href="{{ $url }}" class="pagination-link pagination-number">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($permissions->hasMorePages())
                                    <li class="pagination-item">
                                        <a href="{{ $permissions->nextPageUrl() }}" class="pagination-link">
                                            <i class="fas fa-chevron-right pagination-icon"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="pagination-item">
                                        <span class="pagination-link pagination-link-disabled">
                                            <i class="fas fa-chevron-right pagination-icon"></i>
                                        </span>
                                    </li>
                                @endif
                        </ul>
                        </div>
                    </div>
                </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-container">
                        <i class="fas fa-key empty-state-icon"></i>
                        <h3 class="empty-state-title">Tidak Ada Permission</h3>
                        <p class="empty-state-description">
                            Belum ada permission yang tersedia. Klik tombol "Tambah Permission" untuk membuat permission pertama.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="dialog-backdrop" style="display: none;">
    <div class="dialog">
        <div class="dialog-header">
            <h3 class="dialog-title">Konfirmasi</h3>
        </div>
        <div class="dialog-body">
            <p id="confirmationMessage">Apakah Anda yakin ingin melakukan aksi ini?</p>
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-secondary" onclick="closeConfirmationModal()">Batal</button>
            <button type="button" class="btn btn-danger" id="confirmButton">Ya, Lanjutkan</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/permission-management.css') }}">
<style>
.content-back-button {
    display: none !important;
}
</style>
@endpush

@push('scripts')
<script>
// Toggle Permission Status
function togglePermissionStatus(permissionId) {
    const action = event.target.closest('.action-btn-toggle-active') ? 'aktifkan' : 'nonaktifkan';
    showConfirmationModal(
        `Apakah Anda yakin ingin ${action} permission ini?`,
        () => {
            fetch(`/permission-management/${permissionId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan saat mengubah status permission.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengubah status permission.');
            });
        }
    );
}

// Delete Permission
function deletePermission(permissionId) {
    showConfirmationModal(
        'Apakah Anda yakin ingin menghapus permission ini secara permanen? Tindakan ini tidak dapat dibatalkan dan akan menghapus permission dari database.',
        () => {
            fetch(`/permission-management/${permissionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan saat menghapus permission.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus permission.');
            });
        }
    );
}

// Confirmation Modal
function showConfirmationModal(message, onConfirm) {
    document.getElementById('confirmationMessage').textContent = message;
    document.getElementById('confirmationModal').style.display = 'flex';
    
    document.getElementById('confirmButton').onclick = () => {
        closeConfirmationModal();
        onConfirm();
    };
}

function closeConfirmationModal() {
    document.getElementById('confirmationModal').style.display = 'none';
}

// Close modal when clicking backdrop
document.getElementById('confirmationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfirmationModal();
    }
});

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.filters-form');
    const filterSelects = filterForm.querySelectorAll('select');
    const searchInput = filterForm.querySelector('input[name="search"]');
    
    // Auto-submit on select change
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
    
    // Auto-submit on search with debounce
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterForm.submit();
        }, 500); // Wait 500ms after user stops typing
    });
    
    // Submit on Enter key in search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterForm.submit();
        }
    });
});
</script>
@endpush