@extends('layouts.app')

@section('title', 'Daftar Role')
@section('subtitle', 'Kelola role dan permission pengguna')

@section('content')
<div class="role-management-container">
    <!-- Single Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-content">
                <div class="card-header-title">
                    <h1 class="title">
                        <i class="fas fa-users-cog title-icon"></i>
                        Daftar Role
                    </h1>
                    <p class="subtitle">Kelola role dan permission pengguna sistem</p>
                </div>
                <div class="card-header-actions">
                    <a href="{{ route('role-management.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Role
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-main">
            <!-- Filters Section -->
            <form method="GET" action="{{ route('role-management.index') }}" class="filters-form">
                <div class="filters-grid">
                    <div class="form-group">
                        <label class="form-label">Cari Role</label>
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
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Guard Name</label>
                        <select name="guard_name" class="form-select">
                            <option value="">Semua Guard</option>
                            <option value="web" {{ request('guard_name') === 'web' ? 'selected' : '' }}>Web</option>
                            <option value="api" {{ request('guard_name') === 'api' ? 'selected' : '' }}>API</option>
                        </select>
                    </div>
                </div>
            </form>
            @if($roles->count() > 0)
                <!-- Table Section -->
                <div class="table-section">
                    <!-- Table Wrapper -->
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Role</th>
                                    <th>Guard</th>
                                    <th>Status</th>
                                    <th>Jumlah User</th>
                                    <th>Permissions</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-details">
                                                <div class="user-name">{{ $role->name }}</div>
                                                @if($role->description)
                                                    <div class="user-email">{{ Str::limit($role->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-role">{{ $role->guard_name }}</span>
                                    </td>
                                    <td>
                                        @if($role->is_active)
                                            <span class="badge badge-status-active">Aktif</span>
                                        @else
                                            <span class="badge badge-status-inactive">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="user-count">{{ $role->users_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="permission-count">{{ $role->permissions->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('role-management.show', $role->id) }}" 
                                               class="action-btn action-btn-view" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('role-management.edit', $role->id) }}" 
                                               class="action-btn action-btn-edit" 
                                               title="Edit Role">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($role->is_active)
                                                <button type="button" 
                                                        class="action-btn action-btn-toggle-inactive" 
                                                        onclick="toggleRoleStatus({{ $role->id }})"
                                                        title="Nonaktifkan Role">
                                                    <i class="fas fa-toggle-on"></i>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        class="action-btn action-btn-toggle-active" 
                                                        onclick="toggleRoleStatus({{ $role->id }})"
                                                        title="Aktifkan Role">
                                                    <i class="fas fa-toggle-off"></i>
                                                </button>
                                            @endif
                                            @if(!in_array($role->name, ['admin', 'super_admin']) && $role->users_count == 0)
                                                <button type="button" 
                                                        class="action-btn action-btn-delete" 
                                                        onclick="deleteRole({{ $role->id }})"
                                                        title="Hapus Role">
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
                @if($roles->hasPages())
                <div class="pagination-section">
                    <div class="pagination-info">
                        <p class="pagination-text">
                            Menampilkan {{ $roles->firstItem() }} - {{ $roles->lastItem() }} dari {{ $roles->total() }} role
                        </p>
                    </div>
                    <div class="pagination-controls">
                        <div class="pagination-nav">
                            <ul class="pagination-list">
                                {{-- Previous Page Link --}}
                                @if ($roles->onFirstPage())
                                    <li class="pagination-item">
                                        <span class="pagination-link pagination-link-disabled">
                                            <i class="fas fa-chevron-left pagination-icon"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="pagination-item">
                                        <a href="{{ $roles->previousPageUrl() }}" class="pagination-link">
                                            <i class="fas fa-chevron-left pagination-icon"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($roles->getUrlRange(1, $roles->lastPage()) as $page => $url)
                                    @if ($page == $roles->currentPage())
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
                                @if ($roles->hasMorePages())
                                    <li class="pagination-item">
                                        <a href="{{ $roles->nextPageUrl() }}" class="pagination-link">
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
                        <i class="fas fa-users-cog empty-state-icon"></i>
                        <h3 class="empty-state-title">Tidak Ada Role</h3>
                        <p class="empty-state-description">
                            Belum ada role yang tersedia. Klik tombol "Tambah Role" untuk membuat role pertama.
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
<link rel="stylesheet" href="{{ asset('css/components/role-management.css') }}">
@endpush

@push('scripts')
<script>
// Toggle Role Status
function toggleRoleStatus(roleId) {
    const action = event.target.closest('.action-button-toggle-active') ? 'aktifkan' : 'nonaktifkan';
    showConfirmationModal(
        `Apakah Anda yakin ingin ${action} role ini?`,
        () => {
            fetch(`/role-management/${roleId}/toggle-status`, {
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
                    alert(data.message || 'Terjadi kesalahan saat mengubah status role.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengubah status role.');
            });
        }
    );
}

// Delete Role
function deleteRole(roleId) {
    showConfirmationModal(
        'Apakah Anda yakin ingin menghapus role ini secara permanen? Tindakan ini tidak dapat dibatalkan dan akan menghapus role dari database.',
        () => {
            fetch(`/role-management/${roleId}`, {
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
                    alert(data.message || 'Terjadi kesalahan saat menghapus role.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus role.');
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