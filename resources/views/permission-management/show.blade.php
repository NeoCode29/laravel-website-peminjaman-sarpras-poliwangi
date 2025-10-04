@extends('layouts.app')

@section('title', 'Detail Permission')
@section('subtitle', 'Informasi lengkap permission')

@section('content')
<div class="permission-management-container">
    <!-- Single Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-content">
                <div class="card-header-title">
                    <h1 class="title">
                        <i class="fas fa-key title-icon"></i>
                        Detail Permission
                    </h1>
                    <p class="subtitle">Informasi lengkap permission: {{ $permission->display_name }}</p>
                </div>
                <div class="card-header-actions">
                    <a href="{{ route('permission-management.edit', $permission->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Permission
                    </a>
                    <a href="{{ route('permission-management.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-main">
            <!-- Permission Details -->
            <div class="permission-details-section">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label class="detail-label">Nama Permission</label>
                        <div class="detail-value">
                            <code class="permission-code">{{ $permission->name }}</code>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Nama Tampilan</label>
                        <div class="detail-value">{{ $permission->display_name }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Kategori</label>
                        <div class="detail-value">
                            <span class="badge badge-category">{{ ucfirst($permission->category) }}</span>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Status</label>
                        <div class="detail-value">
                            @if($permission->is_active)
                                <span class="badge badge-status-active">Aktif</span>
                            @else
                                <span class="badge badge-status-inactive">Tidak Aktif</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Guard Name</label>
                        <div class="detail-value">{{ $permission->guard_name }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Jumlah Role</label>
                        <div class="detail-value">
                            <span class="role-count">{{ $permission->roles->count() }}</span>
                        </div>
                    </div>
                </div>
                
                @if($permission->description)
                <div class="detail-item full-width">
                    <label class="detail-label">Deskripsi</label>
                    <div class="detail-value">
                        <div class="permission-description">{{ $permission->description }}</div>
                    </div>
                </div>
                @endif
                
                <div class="detail-item full-width">
                    <label class="detail-label">Tanggal Dibuat</label>
                    <div class="detail-value">{{ $permission->created_at->format('d M Y H:i') }}</div>
                </div>
                
                <div class="detail-item full-width">
                    <label class="detail-label">Terakhir Diupdate</label>
                    <div class="detail-value">{{ $permission->updated_at->format('d M Y H:i') }}</div>
                </div>
            </div>
            
            <!-- Roles Section -->
            @if($roles->count() > 0)
            <div class="roles-section">
                <h3 class="section-title">
                    <i class="fas fa-users"></i>
                    Role yang Memiliki Permission Ini ({{ $roles->total() }})
                </h3>
                
                <div class="simple-roles-list">
                    @foreach($roles as $role)
                    <div class="simple-role-item">
                        <div class="simple-role-info">
                            <span class="simple-role-name">{{ $role->display_name }}</span>
                            <span class="simple-role-code">{{ $role->name }}</span>
                        </div>
                        <a href="{{ route('role-management.show', $role->id) }}" 
                           class="simple-role-link" title="Lihat Role">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
                
                <!-- Roles Pagination -->
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
            </div>
            @else
            <div class="empty-roles">
                <div class="empty-state-container">
                    <i class="fas fa-users empty-state-icon"></i>
                    <h4 class="empty-state-title">Belum Ada Role</h4>
                    <p class="empty-state-description">
                        Permission ini belum digunakan oleh role manapun.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/permission-management.css') }}">
@endpush