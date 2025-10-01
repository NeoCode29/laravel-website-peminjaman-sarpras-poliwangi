@extends('layouts.dashboard-modular')

@section('title', 'Dashboard Admin')
@section('subtitle', 'Panel kontrol sistem peminjaman sarana dan prasarana')

@section('breadcrumb')
    <div class="breadcrumb-item">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">
            <i class="fas fa-home"></i>
            Dashboard
        </a>
    </div>
    <div class="breadcrumb-item">
        <span>Admin</span>
    </div>
@endsection

@section('content')
<div class="dashboard-content">
    <!-- Main content area - kosong untuk customisasi -->
</div>
@endsection