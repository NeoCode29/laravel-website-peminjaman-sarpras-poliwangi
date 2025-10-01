@extends('user-management.layout')

@section('title', 'Edit User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-edit"></i> Edit User: {{ $user->name }}</h2>
    <a href="{{ route('user-management.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-form"></i> Form Edit User</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('user-management.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Nomor Handphone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                               placeholder="08xxxxxxxxxx" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="user_type" class="form-label">Tipe User <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_type') is-invalid @enderror" 
                                id="user_type" name="user_type" required>
                            <option value="">Pilih Tipe User</option>
                            <option value="mahasiswa" {{ old('user_type', $user->user_type) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="staff" {{ old('user_type', $user->user_type) == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                        @error('user_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="blocked" {{ old('status', $user->status) == 'blocked' ? 'selected' : '' }}>Diblokir</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role_id') is-invalid @enderror" 
                                id="role_id" name="role_id" required>
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name ?? $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Informasi User</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Dibuat:</strong><br>
                                <small>{{ $user->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="col-md-3">
                                <strong>Terakhir Update:</strong><br>
                                <small>{{ $user->updated_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="col-md-3">
                                <strong>Profil Lengkap:</strong><br>
                                <span class="badge {{ $user->profile_completed ? 'bg-success' : 'bg-warning' }}">
                                    {{ $user->profile_completed ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong>Terakhir Login SSO:</strong><br>
                                <small>{{ $user->last_sso_login ? $user->last_sso_login->format('d/m/Y H:i') : '-' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('user-management.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
