@extends('layouts.app')

@section('title', 'Detail Role')
@section('subtitle', 'Informasi lengkap role')

@section('content')
<div class="role-management-container">
    <!-- Single Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-content">
                <div class="card-header-title">
                    <h1 class="title">
                        <i class="fas fa-users-cog title-icon"></i>
                        Detail Role
                    </h1>
                    <p class="subtitle">Informasi lengkap role: {{ $role->display_name }}</p>
                </div>
                <div class="card-header-actions">
                    <a href="{{ route('role-management.edit', $role->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Role
                    </a>
                    <a href="{{ route('role-management.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-main">
            <!-- Role Details -->
            <div class="role-details-section">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label class="detail-label">Nama Role</label>
                        <div class="detail-value">
                            <code class="role-code">{{ $role->name }}</code>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Nama Tampilan</label>
                        <div class="detail-value">{{ $role->display_name }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Guard Name</label>
                        <div class="detail-value">{{ $role->guard_name }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Status</label>
                        <div class="detail-value">
                            @if($role->is_active)
                                <span class="badge badge-status-active">Aktif</span>
                            @else
                                <span class="badge badge-status-inactive">Tidak Aktif</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Jumlah User</label>
                        <div class="detail-value">
                            <span class="user-count">{{ $role->users->count() }}</span>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Jumlah Permission</label>
                        <div class="detail-value">
                            <span class="permission-count">{{ $role->permissions->count() }}</span>
                        </div>
                    </div>
                </div>
                
                @if($role->description)
                <div class="detail-item full-width">
                    <label class="detail-label">Deskripsi</label>
                    <div class="detail-value">
                        <div class="role-description">{{ $role->description }}</div>
                    </div>
                </div>
                @endif
                
                <div class="detail-item full-width">
                    <label class="detail-label">Tanggal Dibuat</label>
                    <div class="detail-value">{{ $role->created_at->format('d M Y H:i') }}</div>
                </div>
                
                <div class="detail-item full-width">
                    <label class="detail-label">Terakhir Diupdate</label>
                    <div class="detail-value">{{ $role->updated_at->format('d M Y H:i') }}</div>
                </div>
            </div>
            
            <!-- Permissions Section -->
            @if($role->permissions->count() > 0)
            <div class="permissions-section">
                <h3 class="section-title">
                    <i class="fas fa-key"></i>
                    Permission yang Dimiliki
                </h3>
                
                <div class="permissions-grid">
                    @foreach($role->permissions->groupBy('category') as $category => $categoryPermissions)
                    <div class="permission-category">
                        <div class="category-header">
                            <h4 class="category-title">{{ ucfirst($category) }}</h4>
                            <span class="category-count">{{ $categoryPermissions->count() }} permission</span>
                        </div>
                        <div class="permissions-list">
                            @foreach($categoryPermissions as $permission)
                            <div class="permission-item">
                                <div class="permission-info">
                                    <div class="permission-name">{{ $permission->display_name }}</div>
                                    <div class="permission-code">{{ $permission->name }}</div>
                                </div>
                                <div class="permission-actions">
                                    <a href="{{ route('permission-management.show', $permission->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                        Lihat
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="empty-permissions">
                <div class="empty-state-container">
                    <i class="fas fa-key empty-state-icon"></i>
                    <h4 class="empty-state-title">Belum Ada Permission</h4>
                    <p class="empty-state-description">
                        Role ini belum memiliki permission apapun.
                    </p>
                </div>
            </div>
            @endif
            
            <!-- Users Section -->
            @if($users->count() > 0)
            <div class="users-section">
                <h3 class="section-title">
                    <i class="fas fa-users"></i>
                    User dengan Role Ini ({{ $users->total() }})
                </h3>
                
                <div class="simple-users-list">
                    @foreach($users as $user)
                    <div class="simple-user-item">
                        <div class="simple-user-info">
                            <span class="simple-user-name">{{ $user->name }}</span>
                            <span class="simple-user-email">{{ $user->email }}</span>
                            <span class="simple-user-type">{{ ucfirst($user->user_type) }}</span>
                        </div>
                        <a href="{{ route('user-management.show', $user->id) }}" 
                           class="simple-user-link" title="Lihat User">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
                
                <!-- Users Pagination -->
                @if($users->hasPages())
                <div class="pagination-section">
                    <div class="pagination-info">
                        <p class="pagination-text">
                            Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} user
                        </p>
                    </div>
                    <div class="pagination-controls">
                        <div class="pagination-nav">
                            <ul class="pagination-list">
                                {{-- Previous Page Link --}}
                                @if ($users->onFirstPage())
                                    <li class="pagination-item">
                                        <span class="pagination-link pagination-link-disabled">
                                            <i class="fas fa-chevron-left pagination-icon"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="pagination-item">
                                        <a href="{{ $users->previousPageUrl() }}" class="pagination-link">
                                            <i class="fas fa-chevron-left pagination-icon"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                    @if ($page == $users->currentPage())
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
                                @if ($users->hasMorePages())
                                    <li class="pagination-item">
                                        <a href="{{ $users->nextPageUrl() }}" class="pagination-link">
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
            <div class="empty-users">
                <div class="empty-state-container">
                    <i class="fas fa-users empty-state-icon"></i>
                    <h4 class="empty-state-title">Belum Ada User</h4>
                    <p class="empty-state-description">
                        Belum ada user yang menggunakan role ini.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/role-management.css') }}">
@endpush