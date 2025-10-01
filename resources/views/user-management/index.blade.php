@extends('user-management.layout')

@section('title', 'Daftar User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users"></i> Daftar User</h2>
    <a href="{{ route('user-management.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah User
    </a>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-filter"></i> Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('user-management.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <label for="search" class="form-label">Pencarian</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nama, username, atau email">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Diblokir</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="user_type" class="form-label">Tipe User</label>
                    <select class="form-select" id="user_type" name="user_type">
                        <option value="">Semua Tipe</option>
                        <option value="mahasiswa" {{ request('user_type') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="staff" {{ request('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="role_id" class="form-label">Role</label>
                    <select class="form-select" id="role_id" name="role_id">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name ?? $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-table"></i> Data User ({{ $users->total() }} total)</h5>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tipe</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terakhir Login</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->phone)
                                        <br><small class="text-muted">{{ $user->phone }}</small>
                                    @endif
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge user-type-badge 
                                        {{ $user->user_type == 'mahasiswa' ? 'bg-info' : 'bg-warning' }}">
                                        {{ $user->user_type_display }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->role)
                                        <span class="badge bg-secondary">
                                            {{ $user->role->display_name ?? $user->role->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->status == 'active')
                                        <span class="badge bg-success status-badge">Aktif</span>
                                    @elseif($user->status == 'inactive')
                                        <span class="badge bg-secondary status-badge">Tidak Aktif</span>
                                    @else
                                        <span class="badge bg-danger status-badge">Diblokir</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->last_sso_login)
                                        {{ $user->last_sso_login->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('user-management.show', $user->id) }}" 
                                           class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('user-management.edit', $user->id) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($user->status == 'active')
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#blockModal{{ $user->id }}" 
                                                    title="Blokir">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @elseif($user->status == 'blocked')
                                            <form action="{{ route('user-management.unblock', $user->id) }}" 
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" 
                                                        title="Aktifkan" 
                                                        onclick="return confirm('Aktifkan user ini?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('user-management.destroy', $user->id) }}" 
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    title="Nonaktifkan" 
                                                    onclick="return confirm('Nonaktifkan user ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Block Modal -->
                                    <div class="modal fade" id="blockModal{{ $user->id }}" tabindex="-1">
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada user ditemukan</h5>
                <p class="text-muted">Coba ubah filter pencarian atau tambah user baru.</p>
            </div>
        @endif
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
        
        document.querySelectorAll('input[type="datetime-local"]').forEach(input => {
            input.value = datetimeString;
        });
    });
</script>
@endsection
