@extends('layouts.app')

@section('title', 'Detail Role')
@section('subtitle', 'Informasi lengkap role')

@section('content')
<section class="detail-page">
    <div class="card role-detail-card">
        <div class="card-header">
            <div class="card-header__title">
                <i class="fas fa-users-cog role-detail-icon"></i>
                <h2 class="card-title">{{ $role->display_name ?? $role->name }}</h2>
                @if($role->is_active)
                    <span class="status-badge status-approved">
                        <i class="fas fa-check-circle"></i>
                        Aktif
                    </span>
                @else
                    <span class="status-badge status-pending">
                        <i class="fas fa-pause-circle"></i>
                        Tidak Aktif
                    </span>
                @endif
            </div>
            <div class="card-header__actions">
                <span class="guard-badge">
                    <i class="fas fa-shield-alt"></i>
                    {{ strtoupper($role->guard_name) }} Guard
                </span>
            </div>
        </div>

        <div class="card-main">
            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-hashtag"></i> {{ $role->name }}
                </div>
                <div class="chip">
                    <i class="fas fa-user"></i> {{ $role->users->count() }} User
                </div>
                <div class="chip">
                    <i class="fas fa-key"></i> {{ $role->permissions->count() }} Permission
                </div>
                <div class="chip">
                    <i class="fas fa-calendar-plus"></i> {{ $role->created_at->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Role</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Role</span>
                            <span class="detail-value">
                                <span class="code-badge">{{ $role->name }}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nama Tampilan</span>
                            <span class="detail-value">{{ $role->display_name ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Guard Name</span>
                            <span class="detail-value">{{ strtoupper($role->guard_name) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Terakhir Diperbarui</span>
                            <span class="detail-value">{{ $role->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Statistik</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                @if($role->is_active)
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check-circle"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-pause-circle"></i>
                                        Tidak Aktif
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah User</span>
                            <span class="detail-value">
                                <span class="stat-badge">
                                    <i class="fas fa-user"></i>
                                    {{ $role->users->count() }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah Permission</span>
                            <span class="detail-value">
                                <span class="stat-badge">
                                    <i class="fas fa-key"></i>
                                    {{ $role->permissions->count() }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tanggal Dibuat</span>
                            <span class="detail-value">{{ $role->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($role->description)
            <div class="form-section">
                <h3 class="section-title">Deskripsi</h3>
                <div class="detail-block">
                    <p class="detail-text">{{ $role->description }}</p>
                </div>
            </div>
            @endif

            <div class="form-section">
                <h3 class="section-title">Permission yang Dimiliki</h3>
                @if($role->permissions->count() > 0)
                <div class="permissions-grid">
                    @foreach($role->permissions->groupBy('category') as $category => $categoryPermissions)
                    <div class="permission-card">
                        <h4 class="permission-card-title">{{ ucfirst($category) }}</h4>
                        <div class="permission-list">
                            @foreach($categoryPermissions as $permission)
                            <div class="permission-item">
                                <i class="fas fa-check-circle permission-icon"></i>
                                <div class="permission-info">
                                    <span class="permission-name">{{ $permission->display_name ?? $permission->name }}</span>
                                    <span class="permission-code">{{ $permission->name }}</span>
                                </div>
                                <a href="{{ route('permission-management.show', $permission->id) }}" class="permission-link" title="Lihat Permission">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="detail-block detail-empty-block">
                    <div class="empty-state">
                        <i class="fas fa-key empty-state-icon"></i>
                        <p class="empty-state-text">Role ini belum memiliki permission apapun.</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="form-section">
                <h3 class="section-title">User dengan Role Ini</h3>
                @if($users->count() > 0)
                <div class="detail-block">
                    <div class="simple-users-list">
                        @foreach($users as $user)
                        <div class="simple-user-item">
                            <div class="simple-user-info">
                                <span class="simple-user-name">{{ $user->name }}</span>
                                <span class="simple-user-email">{{ $user->email }}</span>
                                <span class="simple-user-type">{{ ucfirst($user->user_type) }}</span>
                            </div>
                            <a href="{{ route('user-management.show', $user->id) }}" class="simple-user-link" title="Lihat User">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>

                    @if($users->hasPages())
                    <div class="detail-pagination">
                        <div class="pagination-info">
                            Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} user
                        </div>
                        <div class="pagination-controls">
                            {{ $users->links() }}
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="detail-block detail-empty-block">
                    <div class="empty-state">
                        <i class="fas fa-users empty-state-icon"></i>
                        <p class="empty-state-text">Belum ada user yang menggunakan role ini.</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="form-section">
                <h3 class="section-title">Tindakan</h3>
                <div class="detail-actions">
                    <a href="{{ route('role-management.edit', $role->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Role
                    </a>
                    <a href="{{ route('role-management.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i>
                        Daftar Role
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/role-management.css') }}">
<style>
/* Page */
.detail-page {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.role-detail-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.card-header__title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: 600;
    color: #333333;
}

.card-header__title .card-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #333333;
}

.card-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: flex-end;
}

.card-main {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.role-detail-icon {
    font-size: 24px;
    color: #4b5563;
}

.guard-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #eef2ff;
    color: #4f46e5;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.summary-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f5f7fa;
    border: 1px solid #e0e0e0;
    color: #333333;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 13px;
}

.chip i {
    color: #6b7280;
}

.detail-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333333;
    margin: 0;
}

.detail-block {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detail-block.detail-empty-block {
    align-items: center;
    text-align: center;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-label {
    font-size: 13px;
    color: #6b7280;
    min-width: 140px;
}

.detail-value {
    font-size: 14px;
    color: #1f2937;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
    text-align: right;
}

.detail-value span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.detail-text {
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
    margin: 0;
}

.code-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 6px;
    background: #f3f4f6;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #4b5563;
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    background: #e0f2fe;
    color: #0284c7;
    font-size: 12px;
    font-weight: 600;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.status-approved {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-badge.status-pending {
    background: #fff3e0;
    color: #f57c00;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 16px;
}

.permission-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.permission-card-title {
    font-size: 15px;
    font-weight: 600;
    color: #333333;
    margin: 0;
}

.permission-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.permission-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.permission-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.permission-name {
    font-size: 13px;
    color: #374151;
}

.permission-code {
    font-size: 12px;
    color: #6b7280;
    font-family: 'Courier New', monospace;
}

.permission-icon {
    color: #28a745;
    font-size: 12px;
}

.permission-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid #d0d7de;
    color: #1a56db;
    transition: transform 0.2s ease, background-color 0.2s ease;
}

.permission-link:hover {
    background-color: #e7f1ff;
    transform: scale(1.05);
}

.detail-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.detail-actions .btn {
    min-width: 160px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-weight: 500;
    border-radius: 6px;
}

.detail-pagination {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pagination-info {
    font-size: 13px;
    color: #666666;
}

.pagination-controls {
    display: flex;
    justify-content: flex-end;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.empty-state-icon {
    font-size: 32px;
    color: #6c757d;
}

.empty-state-text {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.simple-users-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.simple-user-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.simple-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.simple-user-name {
    font-weight: 600;
    color: #1f2937;
}

.simple-user-email {
    font-size: 13px;
    color: #6b7280;
}

.simple-user-type {
    font-size: 12px;
    color: #2563eb;
    background: #e0f2fe;
    padding: 4px 10px;
    border-radius: 999px;
    font-weight: 600;
}

.simple-user-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #eef2ff;
    color: #4338ca;
}

.simple-user-link:hover {
    background: #c7d2fe;
}

/* Responsive */
@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .card-header__actions {
        justify-content: flex-start;
    }

    .detail-card-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        text-align: left;
    }

    .detail-value {
        justify-content: flex-start;
        text-align: left;
    }

    .permissions-grid {
        grid-template-columns: 1fr;
    }

    .detail-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .detail-actions .btn {
        width: 100%;
        min-width: unset;
    }
}
</style>
@endpush