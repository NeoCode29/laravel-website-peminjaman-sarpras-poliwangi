@extends('layouts.app')

@section('title', 'Daftar User')
@section('subtitle', 'Kelola data pengguna sistem')

@section('header-actions')
<a href="{{ route('user-management.create') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i>
    Tambah User
</a>
@endsection

@section('content')
<div class="page-content">
    <!-- User List Card -->
    <div class="card card--headerless">
        <!-- Card Header -->
        <div class="card-header" aria-hidden="true"></div>

        <!-- Card Main -->
        <div class="card-main">
            <!-- Filters Section -->
            <div class="filters-section">
                <form id="filterForm" method="GET" action="{{ route('user-management.index') }}" class="filters-form">
                    <div class="filters-grid">
                        <!-- Search Input -->
                        <div class="filter-group">
                            <label for="search" class="filter-label">Pencarian</label>
                            <div class="search-input-wrapper">
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Cari nama, username, atau email..."
                                       class="search-input">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="filter-group">
                            <label for="status" class="filter-label">Status</label>
                            <select id="status" name="status" class="filter-select">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Diblokir</option>
                            </select>
                        </div>

                        <!-- User Type Filter -->
                        <div class="filter-group">
                            <label for="user_type" class="filter-label">Tipe User</label>
                            <select id="user_type" name="user_type" class="filter-select">
                                <option value="">Semua Tipe</option>
                                <option value="mahasiswa" {{ request('user_type') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="staff" {{ request('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                        </div>

                        <!-- Role Filter -->
                        <div class="filter-group">
                            <label for="role_id" class="filter-label">Role</label>
                            <select id="role_id" name="role_id" class="filter-select">
                                <option value="">Semua Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name ?? $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Table Section -->
            <div class="table-section">
                @if($users->count() > 0)
                    <!-- Table Wrapper -->
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Nomor Handphone</th>
                                    <th>Tipe User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name">{{ $user->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-email">{{ $user->email }}</div>
                                        </td>
                                        <td>
                                            <div class="user-phone">{{ $user->phone ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="user-type-badge user-type-{{ $user->user_type }}">
                                                {{ $user->user_type_display }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="role-badge">
                                                {{ $user->getRoleDisplayName() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->isBlocked())
                                                <span class="status-badge status-blocked">
                                                    Diblokir
                                                </span>
                                            @else
                                                <span class="status-badge status-{{ $user->status }}">
                                                    {{ $user->status_display }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('user-management.show', $user->id) }}" 
                                                   class="action-btn action-view" 
                                                   title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('user-management.edit', $user->id) }}" 
                                                   class="action-btn action-edit" 
                                                   title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @can('user.block')
                                                    @if($user->isBlocked())
                                                        <button type="button" 
                                                                class="action-btn action-unblock" 
                                                                onclick="unblockUser({{ $user->id }})"
                                                                title="Aktifkan User">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @elseif($user->status === 'active')
                                                        <button type="button" 
                                                                class="action-btn action-block" 
                                                                onclick="blockUser({{ $user->id }})"
                                                                title="Blokir User">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    @endif
                                                @endcan
                                                <button type="button" 
                                                        class="action-btn action-delete" 
                                                        onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                                        title="Hapus User"
                                                        data-user-id="{{ $user->id }}"
                                                        data-user-name="{{ $user->name }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
                                Menampilkan {{ $users->firstItem() }}-{{ $users->lastItem() }} dari {{ $users->total() }} user
                            </span>
                        </div>
                        <div class="pagination-controls">
                            <div class="pagination-wrapper">
                                {{ $users->appends(request()->query())->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="empty-title">Tidak ada user ditemukan</h3>
                        <p class="empty-description">
                            @if(request()->filled('search') || request()->filled('status') || request()->filled('user_type') || request()->filled('role_id'))
                                Tidak ada user yang sesuai dengan filter yang dipilih.
                            @else
                                Belum ada user yang terdaftar dalam sistem.
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('user_type') || request()->filled('role_id'))
                            <a href="{{ route('user-management.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hapus Filter
                            </a>
                        @else
                            <a href="{{ route('user-management.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Tambah User Pertama
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal">
    <div class="modal-backdrop" onclick="closeModal()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Konfirmasi Aksi</h3>
            <button type="button" class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="modalMessage"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
            <button type="button" class="btn btn-danger" id="confirmAction">
                <i class="fas fa-trash"></i>
                <span id="confirmButtonText">Ya, Hapus</span>
            </button>
        </div>
    </div>
</div>

<!-- Block User Modal -->
<div id="blockUserModal" class="modal">
    <div class="modal-backdrop" onclick="closeBlockModal()"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Blokir User</h3>
            <button type="button" class="modal-close" onclick="closeBlockModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Pilih durasi blokir untuk user ini:</p>
            <form id="blockUserForm">
                <div class="form-group">
                    <label for="blockDuration" class="form-label">Durasi Blokir (Hari)</label>
                    <select id="blockDuration" name="block_duration" class="form-select" required>
                        <option value="">Pilih durasi...</option>
                        <option value="1">1 Hari</option>
                        <option value="3">3 Hari</option>
                        <option value="7">7 Hari</option>
                        <option value="14">14 Hari</option>
                        <option value="30">30 Hari</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeBlockModal()">Batal</button>
            <button type="button" class="btn btn-warning" id="confirmBlock">
                <i class="fas fa-ban"></i>
                Blokir User
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Page Header Adjustment */
.content-back-button {
    display: none !important;
}

/* Import Poppins Font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

/* Base Styles */
* {
    box-sizing: border-box;
}

body {
    font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f8f9fa;
    color: #333333;
    line-height: 1.5;
}

/* Card Styles */
.card {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 16px;
    transition: box-shadow 0.2s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Card Header */
.card--headerless > .card-header {
    display: none;
}

/* Card Main */
.card-main {
    padding: 20px;
}

/* Filters Section */
.filters-section {
    margin-bottom: 24px;
}

.filters-form {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e0e0e0;
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-label {
    font-size: 14px;
    font-weight: 500;
    line-height: 1.4;
    color: #333333;
    margin: 0;
}

.search-input-wrapper {
    position: relative;
}

.search-input {
    width: 100%;
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 10px 12px 10px 36px;
    font-size: 14px;
    font-weight: 400;
    color: #333333;
    height: 40px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-input:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
}

.search-input:hover {
    border-color: #cccccc;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666666;
    font-size: 14px;
}

.filter-select {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 10px 36px 10px 12px;
    min-width: 120px;
    height: 40px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    font-size: 14px;
    color: #333333;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.filter-select:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
}

.filter-select:hover {
    border-color: #cccccc;
    background-color: #fcfcfc;
}


/* Table Section */
.table-section {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    overflow: hidden;
}


.table-wrapper {
    overflow-x: auto;
    min-width: 100%;
    -webkit-overflow-scrolling: touch;
}

.data-table {
    border-collapse: collapse;
    width: 100%;
    min-width: 800px;
}

.data-table thead {
    background: #f5f7fa;
}

.data-table th {
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #333333;
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
    white-space: nowrap;
    min-width: 120px;
}

.data-table th:nth-child(1) { min-width: 200px; } /* Nama */
.data-table th:nth-child(2) { min-width: 180px; } /* Email */
.data-table th:nth-child(3) { min-width: 140px; } /* Nomor Handphone */
.data-table th:nth-child(4) { min-width: 120px; } /* Tipe User */
.data-table th:nth-child(5) { min-width: 120px; } /* Role */
.data-table th:nth-child(6) { min-width: 100px; } /* Status */
.data-table th:nth-child(7) { min-width: 150px; } /* Aksi */

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: top;
    min-width: 120px;
}

.data-table td:nth-child(1) { min-width: 200px; } /* Nama */
.data-table td:nth-child(2) { min-width: 180px; } /* Email */
.data-table td:nth-child(3) { min-width: 140px; } /* Nomor Handphone */
.data-table td:nth-child(4) { min-width: 120px; } /* Tipe User */
.data-table td:nth-child(5) { min-width: 120px; } /* Role */
.data-table td:nth-child(6) { min-width: 100px; } /* Status */
.data-table td:nth-child(7) { min-width: 150px; } /* Aksi */

.data-table tbody tr:hover {
    background-color: #fafafa;
}

/* User Info */
.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666666;
    font-size: 16px;
    flex-shrink: 0;
}

.user-details {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 500;
    color: #333333;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-email {
    font-size: 12px;
    color: #666666;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-username {
    font-size: 12px;
    color: #999999;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-phone {
    font-size: 14px;
    color: #333333;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Badges */
.user-type-badge, .role-badge, .status-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
}

.user-type-mahasiswa {
    background: #e1f5fe;
    color: #0277bd;
}

.user-type-staff {
    background: #f3e5f5;
    color: #7b1fa2;
}

.status-active {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-inactive {
    background: #fff3e0;
    color: #f57c00;
}

.status-blocked {
    background: #ffebee;
    color: #c62828;
}


/* Form Styles */
.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

.form-select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background-color: #fff;
    transition: border-color 0.2s ease;
}

.form-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.role-badge {
    background: #f1f5f9;
    color: #333333;
}

/* Last Login */
.last-login {
    font-size: 12px;
}

.last-login-date {
    color: #333333;
    font-weight: 500;
}

.last-login-time {
    color: #666666;
}

.no-login {
    color: #999999;
    font-style: italic;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
}

.action-btn {
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

.action-btn:hover {
    background: #f5f5f5;
    border-color: #cccccc;
    transform: translateY(-1px);
}

.action-view:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

.action-edit:hover {
    background: #e7f1ff;
    border-color: #cfe3ff;
    color: #0d6efd;
}

.action-block:hover {
    background: #fff7e6;
    border-color: #ffe7b5;
    color: #b26a00;
}

.action-unblock:hover {
    background: #eaf7ee;
    border-color: #cfead6;
    color: #2e7d32;
}

.action-delete {
    background: #fff5f5;
    border-color: #fed7d7;
    color: #e53e3e;
}

.action-delete:hover {
    background: #fed7d7;
    border-color: #feb2b2;
    color: #c53030;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(229, 62, 62, 0.2);
}

.action-delete:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(229, 62, 62, 0.3);
}

/* Pagination */
.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-top: 16px;
    padding: 20px;
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
    border-radius: 0 0 8px 8px;
}

.pagination-info {
    flex: 1;
}

.pagination-text {
    font-size: 14px;
    color: #666666;
    font-weight: 400;
}

.pagination-controls {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 48px 20px;
}

.empty-icon {
    color: #6c757d;
    margin-bottom: 16px;
}

.empty-icon i {
    font-size: 48px;
}

.empty-title {
    font-size: 20px;
    font-weight: 500;
    color: #333333;
    margin-bottom: 8px;
}

.empty-description {
    font-size: 14px;
    color: #666666;
    margin-bottom: 24px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Buttons */
.btn {
    border: none;
    border-radius: 6px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    line-height: 1;
}

.btn-primary {
    background: #333333;
    color: #ffffff;
}

.btn-primary:hover {
    background: #555555;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #ffffff;
    color: #333333;
    border: 1px solid #e0e0e0;
}

.btn-secondary:hover {
    background: #f5f5f5;
    border-color: #cccccc;
}

.btn-outline {
    background: transparent;
    color: #666666;
    border: 1px solid #e0e0e0;
}

.btn-outline:hover {
    background: #f5f5f5;
    color: #333333;
}

.btn-danger {
    background: #dc3545;
    color: #ffffff;
    border: 1px solid #dc3545;
}

.btn-danger:hover {
    background: #c82333;
    border-color: #bd2130;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.btn-danger:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(220, 53, 69, 0.4);
}

.btn-danger:disabled {
    background: #6c757d;
    border-color: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-success {
    background: #28a745;
    color: #ffffff;
    border: 1px solid #28a745;
}

.btn-success:hover {
    background: #218838;
    border-color: #1e7e34;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.btn-success:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(40, 167, 69, 0.4);
}

.btn-success:disabled {
    background: #6c757d;
    border-color: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Spinner animation */
.fa-spinner.fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
}

.modal-dialog {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    width: min(520px, calc(100% - 32px));
    position: relative;
    z-index: 1001;
}

.modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #333333;
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: transparent;
    border: none;
    color: #666666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.modal-close:hover {
    background: #f5f5f5;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 16px 20px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .filters-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .filters-section {
        margin-bottom: 20px;
    }

    .filters-form {
        padding: 16px;
        gap: 16px;
    }

    .filters-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .filter-group {
        gap: 8px;
    }

    .search-input,
    .filter-select {
        width: 100%;
    }

    .detail-card-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .pagination-section {
        flex-direction: column;
        gap: 16px;
        text-align: center;
        padding: 16px;
    }
    
    .pagination-info {
        order: 2;
    }
    
    .pagination-controls {
        order: 1;
    }
    
    .table-wrapper {
        margin: 0 -16px;
        padding: 0 16px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .data-table {
        min-width: 900px;
    }
    
    .data-table th,
    .data-table td {
        font-size: 13px;
        padding: 8px;
    }
    
    .data-table th:nth-child(1),
    .data-table td:nth-child(1) { min-width: 180px; } /* Nama */
    .data-table th:nth-child(2),
    .data-table td:nth-child(2) { min-width: 160px; } /* Email */
    .data-table th:nth-child(3),
    .data-table td:nth-child(3) { min-width: 120px; } /* Nomor Handphone */
    .data-table th:nth-child(4),
    .data-table td:nth-child(4) { min-width: 100px; } /* Tipe User */
    .data-table th:nth-child(5),
    .data-table td:nth-child(5) { min-width: 100px; } /* Role */
    .data-table th:nth-child(6),
    .data-table td:nth-child(6) { min-width: 80px; } /* Status */
    .data-table th:nth-child(7),
    .data-table td:nth-child(7) { min-width: 140px; } /* Aksi */
}

@media (max-width: 480px) {
    .card-header, .card-main {
        padding: 16px;
    }
    
    .filters-form {
        padding: 16px;
    }
    
    .empty-state {
        padding: 32px 16px;
    }
    
    .empty-icon i {
        font-size: 36px;
    }
    
    .empty-title {
        font-size: 18px;
    }
    
    .empty-description {
        font-size: 13px;
    }
    
    .pagination-section {
        padding: 12px;
        gap: 12px;
    }
    
    .pagination-text {
        font-size: 13px;
    }
    
    .table-wrapper {
        margin: 0 -12px;
        padding: 0 12px;
    }
    
    .data-table {
        min-width: 800px;
    }
    
    .data-table th,
    .data-table td {
        font-size: 12px;
        padding: 6px;
    }
    
    .data-table th:nth-child(1),
    .data-table td:nth-child(1) { min-width: 160px; } /* Nama */
    .data-table th:nth-child(2),
    .data-table td:nth-child(2) { min-width: 140px; } /* Email */
    .data-table th:nth-child(3),
    .data-table td:nth-child(3) { min-width: 100px; } /* Nomor Handphone */
    .data-table th:nth-child(4),
    .data-table td:nth-child(4) { min-width: 80px; } /* Tipe User */
    .data-table th:nth-child(5),
    .data-table td:nth-child(5) { min-width: 80px; } /* Role */
    .data-table th:nth-child(6),
    .data-table td:nth-child(6) { min-width: 70px; } /* Status */
    .data-table th:nth-child(7),
    .data-table td:nth-child(7) { min-width: 120px; } /* Aksi */
    
    .user-info {
        gap: 8px;
    }
    
    .user-avatar {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
    
    .user-name {
        font-size: 13px;
    }
    
    .user-email {
        font-size: 11px;
    }
    
    .user-phone {
        font-size: 12px;
    }
    
    .action-buttons {
        gap: 4px;
    }
    
    .action-btn {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}

/* Pagination Styles */
.pagination-nav {
    display: flex;
    justify-content: center;
    align-items: center;
}

.pagination {
    display: flex;
    gap: 4px;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
}

.pagination-item {
    display: flex;
}

.pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 6px;
    border: 1px solid #e0e0e0;
    background: #ffffff;
    color: #333333;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    cursor: pointer;
}

.pagination-link:hover:not(.disabled) {
    background: #f5f5f5;
    border-color: #cccccc;
}

.pagination-link:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
    border-color: #007bff;
}

.pagination-link.active {
    background: #333333;
    color: #ffffff;
    border-color: #333333;
}

.pagination-link.disabled {
    background: #f5f5f5;
    color: #999999;
    cursor: not-allowed;
    opacity: 0.5;
}

.pagination-link.disabled:hover {
    background: #f5f5f5;
    border-color: #e0e0e0;
}

.pagination-number {
    min-width: 32px;
}

.pagination-dots {
    background: transparent;
    border: none;
    color: #999999;
    cursor: default;
    min-width: 32px;
}

.pagination-dots:hover {
    background: transparent;
}

.pagination i {
    font-size: 12px;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Responsive pagination */
@media (max-width: 768px) {
    .pagination {
        gap: 3px;
    }
    
    .pagination-link {
        min-width: 28px;
        height: 28px;
        padding: 4px;
        font-size: 13px;
    }
    
    .pagination-number {
        min-width: 28px;
    }
}

@media (max-width: 480px) {
    .pagination {
        gap: 2px;
    }
    
    .pagination-link {
        min-width: 24px;
        height: 24px;
        padding: 3px;
        font-size: 12px;
    }
    
    .pagination-number {
        min-width: 24px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Show flash messages
document.addEventListener('DOMContentLoaded', function() {
    // Filter form auto-submit
    const filterForm = document.getElementById('filterForm');
    const filterSelects = filterForm.querySelectorAll('select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
});


// Modal functions
let currentAction = null;
let currentUserId = null;

function showModal(message, action, userId = null) {
    currentAction = action;
    currentUserId = userId;
    document.getElementById('modalMessage').textContent = message;
    
    // Update button text and icon based on action
    const confirmBtn = document.getElementById('confirmAction');
    const confirmText = document.getElementById('confirmButtonText');
    const confirmIcon = confirmBtn.querySelector('i');
    
    switch (action) {
        case 'delete':
            confirmBtn.className = 'btn btn-danger';
            confirmIcon.className = 'fas fa-trash';
            confirmText.textContent = 'Ya, Hapus';
            break;
        case 'unblock':
            confirmBtn.className = 'btn btn-success';
            confirmIcon.className = 'fas fa-check';
            confirmText.textContent = 'Ya, Aktifkan';
            break;
        default:
            confirmBtn.className = 'btn btn-danger';
            confirmIcon.className = 'fas fa-trash';
            confirmText.textContent = 'Ya, Hapus';
    }
    
    document.getElementById('confirmationModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmationModal').style.display = 'none';
    currentAction = null;
    currentUserId = null;
}

function blockUser(userId) {
    currentUserId = userId;
    document.getElementById('blockUserModal').style.display = 'flex';
}

function unblockUser(userId) {
    showModal('Apakah Anda yakin ingin mengaktifkan kembali user ini?', 'unblock', userId);
}

function closeBlockModal() {
    document.getElementById('blockUserModal').style.display = 'none';
    document.getElementById('blockUserForm').reset();
    currentUserId = null;
}

function deleteUser(userId, userName = '') {
    const message = userName 
        ? `Apakah Anda yakin ingin menghapus user "${userName}"? Tindakan ini tidak dapat dibatalkan.`
        : 'Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.';
    
    showModal(message, 'delete', userId);
}

// Confirm action
document.getElementById('confirmAction').addEventListener('click', function() {
    if (currentAction && currentUserId) {
        let url = '';
        let method = 'POST';
        
        switch (currentAction) {
            case 'block':
                url = `/user-management/${currentUserId}/block`;
                break;
            case 'unblock':
                url = `/user-management/${currentUserId}/unblock`;
                break;
            case 'delete':
                url = `/user-management/${currentUserId}`;
                method = 'DELETE';
                break;
        }
        
        // Show loading state
        const confirmBtn = document.getElementById('confirmAction');
        const originalHTML = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        confirmBtn.disabled = true;
        
        // Use fetch API for better control
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const token = csrfToken ? csrfToken.getAttribute('content') : '';
        
        // Prepare form data
        const formData = new FormData();
        formData.append('_token', token);
        
        if (method === 'DELETE') {
            formData.append('_method', 'DELETE');
        }
        
        // Make the request
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => {
            if (response.ok) {
                // Reload the page to show updated data
                window.location.reload();
            } else {
                throw new Error('Network response was not ok');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback to form submission
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.style.display = 'none';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = token;
            form.appendChild(csrfInput);
            
            // Add method field for DELETE
            if (method === 'DELETE') {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        });
    }
    
    closeModal();
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Close modal on backdrop click
document.querySelector('.modal-backdrop').addEventListener('click', closeModal);

// Block user confirmation
document.getElementById('confirmBlock').addEventListener('click', function() {
    const duration = document.getElementById('blockDuration').value;
    
    if (!duration) {
        alert('Pilih durasi blokir terlebih dahulu.');
        return;
    }
    
    if (currentUserId) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/user-management/${currentUserId}/block`;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add block duration
        const durationInput = document.createElement('input');
        durationInput.type = 'hidden';
        durationInput.name = 'block_duration';
        durationInput.value = duration;
        form.appendChild(durationInput);
        
        document.body.appendChild(form);
        form.submit();
    }
    
    closeBlockModal();
});
</script>
@endpush

