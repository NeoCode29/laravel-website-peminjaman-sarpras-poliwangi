@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history me-2"></i>
                Log Aktivitas
            </h1>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-gray-300 mb-3"></i>
                <h4 class="text-muted">Audit Log</h4>
                <p class="text-muted">Halaman log aktivitas sedang dalam pengembangan. Fitur lengkap akan segera tersedia.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
