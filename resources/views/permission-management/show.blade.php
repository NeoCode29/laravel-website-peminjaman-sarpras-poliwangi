@extends('layouts.app')

@section('title', 'Detail Permission')
@section('subtitle', 'Informasi lengkap permission')

@section('header-actions')
<a href="{{ route('permission-management.edit', $permission->id) }}" class="btn btn-secondary btn-cancel">
    <i class="fas fa-edit"></i>
    Edit Permission
</a>
<a href="{{ route('permission-management.index') }}" class="btn btn-secondary btn-cancel">
    <i class="fas fa-list"></i>
    Daftar Permission
</a>
@endsection

@section('content')
<section class="detail-page prasarana-detail-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-key"></i> {{ $permission->name }}
                </div>
                <div class="chip">
                    <i class="fas fa-layer-group"></i> {{ ucfirst($permission->category) }}
                </div>
                <div class="chip">
                    <i class="fas fa-calendar-plus"></i> {{ $permission->created_at->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="detail-card-grid">
                <div class="form-section">
                    <h3 class="section-title">Informasi Permission</h3>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Permission</span>
                            <span class="detail-value">
                                <code class="detail-code">{{ $permission->name }}</code>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nama Tampilan</span>
                            <span class="detail-value">{{ $permission->display_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Kategori</span>
                            <span class="detail-value">
                                <span class="detail-chip">
                                    <i class="fas fa-layer-group"></i>
                                    {{ ucfirst($permission->category) }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Guard</span>
                            <span class="detail-value">{{ $permission->guard_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                @if($permission->is_active)
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
                            <span class="detail-label">Jumlah Role</span>
                            <span class="detail-value">
                                <span class="detail-chip">
                                    <i class="fas fa-users"></i>
                                    {{ $permission->roles->count() }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Deskripsi & Riwayat</h3>
                    <div class="detail-block">
                        <div class="detail-row detail-row-start">
                            <span class="detail-label">Deskripsi</span>
                            <span class="detail-value detail-value-start">{{ $permission->description ?? 'Tidak ada deskripsi' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tanggal Dibuat</span>
                            <span class="detail-value">{{ $permission->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Terakhir Diperbarui</span>
                            <span class="detail-value">{{ $permission->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Role yang Memiliki Permission</h3>
                <div class="detail-block">
                    @if($roles->count() > 0)
                    <div class="simple-roles-list">
                        @foreach($roles as $role)
                        <div class="simple-role-item">
                            <div class="simple-role-info">
                                <span class="simple-role-name">{{ $role->display_name }}</span>
                                <span class="simple-role-code">{{ $role->name }}</span>
                            </div>
                            <a href="{{ route('role-management.show', $role->id) }}" class="simple-role-link" title="Lihat Role">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>

                    @if($roles->hasPages())
                    <div class="detail-pagination">
                        <div class="pagination-info">
                            Menampilkan {{ $roles->firstItem() }} - {{ $roles->lastItem() }} dari {{ $roles->total() }} role
                        </div>
                        <div class="pagination-controls">
                            {{ $roles->links() }}
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="detail-empty">
                        <div class="empty-state">
                            <i class="fas fa-users empty-state-icon"></i>
                            <p class="empty-state-text">Permission ini belum digunakan oleh role manapun.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Tindakan</h3>
                <div class="detail-actions">
                    <a href="{{ route('permission-management.edit', $permission->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Permission
                    </a>
                    <a href="{{ route('permission-management.index') }}" class="btn btn-secondary btn-cancel">
                        <i class="fas fa-list"></i>
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/user-management-create.css') }}?v={{ filemtime(public_path('css/components/user-management-create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/prasarana.css') }}?v={{ filemtime(public_path('css/prasarana.css')) }}">
<link rel="stylesheet" href="{{ asset('css/components/permission-management.css') }}">
@endpush