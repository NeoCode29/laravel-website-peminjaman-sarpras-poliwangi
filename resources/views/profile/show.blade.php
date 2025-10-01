@extends('profile.layout')

@section('title', 'Profil Saya')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        Profil Saya
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit mr-1"></i>
                            Edit Profil
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="200"><strong>Nama Lengkap</strong></td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Username</strong></td>
                                        <td>{{ $user->username }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nomor Handphone</strong></td>
                                        <td>{{ $user->phone ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipe User</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $user->user_type === 'mahasiswa' ? 'info' : 'success' }}">
                                                {{ $user->user_type_display }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                                {{ $user->status_display }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Role</strong></td>
                                        <td>{{ $user->getRoleDisplayName() }}</td>
                                    </tr>
                                    @if($user->user_type === 'mahasiswa' && $user->student)
                                        <tr>
                                            <td><strong>NIM</strong></td>
                                            <td>{{ $user->student->nim }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Angkatan</strong></td>
                                            <td>{{ $user->student->angkatan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jurusan</strong></td>
                                            <td>{{ $user->student->jurusan->nama_jurusan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Program Studi</strong></td>
                                            <td>{{ $user->student->prodi->nama_prodi ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status Mahasiswa</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $user->student->status_mahasiswa === 'aktif' ? 'success' : 'warning' }}">
                                                    {{ $user->student->status_display }}
                                                </span>
                                            </td>
                                        </tr>
                                    @elseif($user->user_type === 'staff' && $user->staffEmployee)
                                        <tr>
                                            <td><strong>NIP</strong></td>
                                            <td>{{ $user->staffEmployee->nip ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Unit</strong></td>
                                            <td>{{ $user->staffEmployee->unit->nama ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Posisi</strong></td>
                                            <td>{{ $user->staffEmployee->position->nama ?? '-' }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Profil Lengkap</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $user->profile_completed ? 'success' : 'warning' }}">
                                                {{ $user->profile_completed ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if($user->profile_completed_at)
                                        <tr>
                                            <td><strong>Profil Diselesaikan</strong></td>
                                            <td>{{ $user->profile_completed_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Terdaftar Sejak</strong></td>
                                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-circle fa-8x text-muted"></i>
                                </div>
                                <h5>{{ $user->name }}</h5>
                                <p class="text-muted">{{ $user->getRoleDisplayName() }}</p>
                                
                                @if($user->isSsoUser())
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <small>Akun SSO Poliwangi</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
