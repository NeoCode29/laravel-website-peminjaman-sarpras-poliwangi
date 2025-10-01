@extends('user-management.layout')

@section('title', 'Detail User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user"></i> Detail User: {{ $user->name }}</h2>
    <div>
        <a href="{{ route('user-management.edit', $user->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('user-management.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- User Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Informasi User</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nama Lengkap:</strong></td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td>{{ $user->username }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nomor Handphone:</strong></td>
                                <td>{{ $user->phone ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tipe User:</strong></td>
                                <td>
                                    <span class="badge {{ $user->user_type == 'mahasiswa' ? 'bg-info' : 'bg-warning' }}">
                                        {{ $user->user_type_display }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($user->status == 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif($user->status == 'inactive')
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Diblokir</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>
                                    @if($user->role)
                                        <span class="badge bg-secondary">
                                            {{ $user->role->display_name ?? $user->role->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Profil Lengkap:</strong></td>
                                <td>
                                    <span class="badge {{ $user->profile_completed ? 'bg-success' : 'bg-warning' }}">
                                        {{ $user->profile_completed ? 'Ya' : 'Tidak' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SSO Information -->
        @if($user->sso_id)
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-sign-in-alt"></i> Informasi SSO</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>SSO ID:</strong></td>
                                <td>{{ $user->sso_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Provider:</strong></td>
                                <td>{{ $user->sso_provider }}</td>
                            </tr>
                            <tr>
                                <td><strong>Terakhir Login SSO:</strong></td>
                                <td>{{ $user->last_sso_login ? $user->last_sso_login->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        @if($user->sso_data)
                            <strong>Data SSO:</strong>
                            <pre class="bg-light p-2 rounded">{{ json_encode($user->sso_data, JSON_PRETTY_PRINT) }}</pre>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Actions & Timeline -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-bolt"></i> Aksi Cepat</h5>
            </div>
            <div class="card-body">
                @if($user->status == 'active')
                    <button type="button" class="btn btn-danger btn-sm w-100 mb-2" 
                            data-bs-toggle="modal" data-bs-target="#blockModal">
                        <i class="fas fa-ban"></i> Blokir User
                    </button>
                @elseif($user->status == 'blocked')
                    <form action="{{ route('user-management.unblock', $user->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm w-100 mb-2" 
                                onclick="return confirm('Aktifkan user ini?')">
                            <i class="fas fa-check"></i> Aktifkan User
                        </button>
                    </form>
                @endif

                <form action="{{ route('user-management.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 mb-2" 
                            onclick="return confirm('Nonaktifkan user ini?')">
                        <i class="fas fa-trash"></i> Nonaktifkan User
                    </button>
                </form>

                <a href="{{ route('user-management.edit', $user->id) }}" class="btn btn-warning btn-sm w-100">
                    <i class="fas fa-edit"></i> Edit User
                </a>
            </div>
        </div>

        <!-- User Timeline -->
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6>User Dibuat</h6>
                            <small class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    
                    @if($user->profile_completed_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6>Profil Diselesaikan</h6>
                            <small class="text-muted">{{ $user->profile_completed_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    @endif

                    @if($user->password_changed_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6>Password Diubah</h6>
                            <small class="text-muted">{{ $user->password_changed_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    @endif

                    @if($user->last_sso_login)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6>Login SSO Terakhir</h6>
                            <small class="text-muted">{{ $user->last_sso_login->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    @endif

                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary"></div>
                        <div class="timeline-content">
                            <h6>Terakhir Update</h6>
                            <small class="text-muted">{{ $user->updated_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block Modal -->
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('user-management.block', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Blokir User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Blokir user <strong>{{ $user->name }}</strong>?</p>
                    <div class="mb-3">
                        <label for="blocked_until" class="form-label">Sampai Tanggal</label>
                        <input type="datetime-local" class="form-control" 
                               id="blocked_until" name="blocked_until" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan (Opsional)</label>
                        <textarea class="form-control" id="reason" name="reason" 
                                  rows="3" placeholder="Alasan memblokir user"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Blokir</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Set default datetime untuk block modal
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        now.setHours(now.getHours() + 1); // Default 1 jam dari sekarang
        const datetimeString = now.toISOString().slice(0, 16);
        
        document.getElementById('blocked_until').value = datetimeString;
    });
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
}

.timeline-content small {
    color: #6c757d;
}
</style>
@endsection
