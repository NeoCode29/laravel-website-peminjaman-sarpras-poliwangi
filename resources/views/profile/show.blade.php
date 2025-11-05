@extends('layouts.app')

@section('title', 'Profil Saya')
@section('subtitle', 'Informasi akun dan identitas')

@section('header-actions')
    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
        <i class="fas fa-edit"></i>
        Edit Profil
    </a>
    @if(!$user->isSsoUser())
    <a href="{{ route('profile.password.edit') }}" class="btn btn-outline-secondary">
        <i class="fas fa-key"></i>
        Ubah Password
    </a>
    @endif
@endsection

@section('content')
<section class="detail-page profile-page">
    <div class="card">
        <div class="card-main">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                    <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                    <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            <div class="summary-chips">
                <div class="chip">
                    <i class="fas fa-user"></i>
                    {{ $user->username }}
                </div>
                <div class="chip">
                    <i class="fas fa-envelope"></i>
                    {{ $user->email }}
                </div>
                <div class="chip">
                    <i class="fas fa-calendar-plus"></i>
                    {{ $user->created_at->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="detail-card-grid two-column">
                <div class="form-section">
                    <h2 class="section-title">Informasi Akun</h2>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Nama Lengkap</span>
                            <span class="detail-value">{{ $user->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value">{{ $user->email }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nomor Handphone</span>
                            <span class="detail-value">{{ $user->phone ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Username</span>
                            <span class="detail-value">{{ $user->username }}</span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">Role &amp; Status</h2>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">Tipe Pengguna</span>
                            <span class="detail-value">
                                <span class="badge-role">{{ ucfirst($user->user_type) }}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Role</span>
                            <span class="detail-value">
                                <span class="badge-role">{{ $user->role->display_name ?? $user->role->name ?? '-' }}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status Akun</span>
                            <span class="detail-value">
                                @if($user->status === 'active')
                                    <span class="status-badge status-approved"><i class="fas fa-check-circle"></i> Aktif</span>
                                @elseif($user->status === 'inactive')
                                    <span class="status-badge status-pending"><i class="fas fa-pause-circle"></i> Tidak Aktif</span>
                                @elseif($user->status === 'blocked')
                                    <span class="status-badge status-rejected"><i class="fas fa-ban"></i> Diblokir</span>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status Profil</span>
                            <span class="detail-value">
                                @if($user->profile_completed)
                                    <span class="status-badge status-approved"><i class="fas fa-check-circle"></i> Lengkap</span>
                                @else
                                    <span class="status-badge status-pending"><i class="fas fa-exclamation-triangle"></i> Belum Lengkap</span>
                                @endif
                            </span>
                        </div>
                        @if($user->isBlocked())
                        <div class="detail-row">
                            <span class="detail-label">Diblokir Hingga</span>
                            <span class="detail-value">
                                <span class="status-badge status-rejected"><i class="fas fa-ban"></i> {{ optional($user->blocked_until)->format('d/m/Y H:i') }}</span>
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($user->user_type === 'mahasiswa')
                <div class="form-section">
                    <h2 class="section-title">Data Mahasiswa</h2>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">NIM</span>
                            <span class="detail-value">{{ $user->student->nim ?? $user->username }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Angkatan</span>
                            <span class="detail-value">{{ $user->student->angkatan ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jurusan</span>
                            <span class="detail-value">{{ $user->student->jurusan->nama_jurusan ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Program Studi</span>
                            <span class="detail-value">{{ $user->student->prodi->nama_prodi ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @elseif($user->user_type === 'staff')
                <div class="form-section">
                    <h2 class="section-title">Data Staff</h2>
                    <div class="detail-block">
                        <div class="detail-row">
                            <span class="detail-label">NIP</span>
                            <span class="detail-value">{{ $user->staffEmployee->nip ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Unit</span>
                            <span class="detail-value">{{ $user->staffEmployee->unit->nama ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Posisi</span>
                            <span class="detail-value">{{ $user->staffEmployee->position->nama ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ filemtime(public_path('css/profile.css')) }}">
@endpush
@endsection
