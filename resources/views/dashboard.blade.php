@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Selamat datang, ' . $user->name . '!')

@section('content')
<div class="dashboard-content">
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Selamat datang, {{ $user->name }}!</h2>
                            <p class="text-muted mb-0">
                                Role: <span class="badge badge-{{ $role->name === 'admin' ? 'danger' : 'primary' }}">{{ $role->display_name }}</span>
                            </p>
                        </div>
                        <div class="user-avatar">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card quick-action-card">
                <div class="card-body text-center">
                    <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Tambah Sarana</h5>
                    <p class="card-text text-muted">Tambah data sarana baru</p>
                    <a href="{{ route('sarana.create') }}" class="btn btn-primary">Tambah</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card quick-action-card">
                <div class="card-body text-center">
                    <i class="fas fa-list fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Daftar Sarana</h5>
                    <p class="card-text text-muted">Lihat semua data sarana</p>
                    <a href="{{ route('sarana.index') }}" class="btn btn-info">Lihat</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card quick-action-card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Manajemen User</h5>
                    <p class="card-text text-muted">Kelola data pengguna</p>
                    <a href="{{ route('user-management.index') }}" class="btn btn-success">Kelola</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card quick-action-card">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Laporan</h5>
                    <p class="card-text text-muted">Lihat laporan sistem</p>
                    <a href="#" class="btn btn-warning">Lihat</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-widget">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                    <h3 class="mb-1">156</h3>
                    <p class="text-muted mb-0">Total Sarana</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-widget">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                    <h3 class="mb-1">24</h3>
                    <p class="text-muted mb-0">Peminjaman Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-widget">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                    <h3 class="mb-1">89</h3>
                    <p class="text-muted mb-0">Total User</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-widget">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-warning mb-2"></i>
                    <h3 class="mb-1">12</h3>
                    <p class="text-muted mb-0">Menunggu Approval</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection