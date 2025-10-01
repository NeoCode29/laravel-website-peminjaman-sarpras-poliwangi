@extends('layouts.app')

@section('title', 'Role & Permission')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-shield me-2"></i>
                Role & Permission
            </h1>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-gray-300 mb-3"></i>
                <h4 class="text-muted">Manajemen Role & Permission</h4>
                <p class="text-muted">Halaman manajemen role dan permission sedang dalam pengembangan. Fitur lengkap akan segera tersedia.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
