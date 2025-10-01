@extends('layouts.dashboard-modular')

@section('title', 'Dashboard Peminjam')
@section('subtitle', 'Selamat datang, ' . $user->name . '!')

@section('breadcrumb')
    <div class="breadcrumb-item">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">
            <i class="fas fa-home"></i>
            Dashboard
        </a>
    </div>
    <div class="breadcrumb-item">
        <span>Peminjam</span>
    </div>
@endsection

@section('content')
<div class="dashboard-content">
    <!-- Main content area - kosong untuk customisasi -->
</div>
@endsection