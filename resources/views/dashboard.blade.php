@extends('user-management.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Selamat Datang, {{ Auth::user()->name }}!</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Informasi Akun</h4>
                        <table class="table">
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td>{{ Auth::user()->username }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ Auth::user()->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ Auth::user()->phone ?? 'Belum diisi' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tipe User:</strong></td>
                                <td>{{ Auth::user()->getUserTypeDisplayAttribute() }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-{{ Auth::user()->status === 'active' ? 'success' : 'danger' }}">
                                        {{ Auth::user()->getStatusDisplayAttribute() }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>{{ Auth::user()->getRoleDisplayName() }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Quick Actions</h4>
                        <div class="btn-group d-grid gap-2">
                            @if(Auth::user()->hasPermission('user.view'))
                                <a href="{{ route('user-management.index') }}" class="btn btn-primary">
                                    Manajemen User
                                </a>
                            @endif
                            
                            @if(Auth::user()->hasPermission('role.view'))
                                <a href="{{ route('role-management.index') }}" class="btn btn-info">
                                    Manajemen Role
                                </a>
                            @endif
                            
                            @if(Auth::user()->hasPermission('permission.view'))
                                <a href="{{ route('permission-management.index') }}" class="btn btn-warning">
                                    Manajemen Permission
                                </a>
                            @endif
                            
                            @if(Auth::user()->hasPermission('role.view'))
                                <a href="{{ route('role-permission-matrix.index') }}" class="btn btn-secondary">
                                    Role Permission Matrix
                                </a>
                            @endif
                            
                            
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->hasPermission('user.view'))
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistik User</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">{{ \App\Models\User::count() }}</h5>
                                <p class="card-text">Total User</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">{{ \App\Models\User::where('status', 'active')->count() }}</h5>
                                <p class="card-text">User Aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">{{ \App\Models\User::where('user_type', 'mahasiswa')->count() }}</h5>
                                <p class="card-text">Mahasiswa</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">{{ \App\Models\User::where('user_type', 'staff')->count() }}</h5>
                                <p class="card-text">Staff</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
