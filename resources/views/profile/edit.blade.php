@extends('layouts.app')

@section('title', 'Edit Profil')
@section('subtitle', 'Perbarui informasi akun Anda')

@section('header-actions')
    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>
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
                    <i class="fas fa-user-tag"></i>
                    {{ $user->role->display_name ?? $user->role->name ?? '-' }}
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="profile-form" onsubmit="return validateProfileForm(this)">
                @csrf
                @method('PUT')

                <div class="detail-card-grid">
                    <div class="form-section">
                        <h2 class="section-title">Informasi Akun</h2>
                        <div class="detail-block form-grid">
                            <div class="form-group">
                                <label class="form-label" for="name">Nama <span class="required"></span></label>
                                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="form-input" required />
                                @error('name')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Email <span class="required"></span></label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="form-input" required />
                                @error('email')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">No. Handphone <span class="required"></span></label>
                                <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="form-input" required />
                                @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <div class="form-value">
                                    <span class="badge-role">{{ $user->role->display_name ?? $user->role->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($user->user_type === 'mahasiswa')
                        <div class="form-section">
                            <h2 class="section-title">Data Mahasiswa</h2>
                            <div class="detail-block form-grid">
                                <div class="form-group">
                                    <label class="form-label">NIM</label>
                                    <div class="form-value">{{ $user->student->nim ?? $user->username }}</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="jurusan_id">Jurusan <span class="required"></span></label>
                                    <select id="jurusan_id" name="jurusan_id" class="form-select" required onchange="syncProdiOptions()">
                                        <option value="">Pilih Jurusan</option>
                                        @foreach($jurusans as $jurusan)
                                            <option value="{{ $jurusan->id }}" {{ old('jurusan_id', optional($user->student)->jurusan_id) == $jurusan->id ? 'selected' : '' }}>{{ $jurusan->nama_jurusan }}</option>
                                        @endforeach
                                    </select>
                                    @error('jurusan_id')<div class="form-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="prodi_id">Program Studi <span class="required"></span></label>
                                    <select id="prodi_id" name="prodi_id" class="form-select" required>
                                        <option value="">Pilih Program Studi</option>
                                        @foreach($prodis as $prodi)
                                            <option value="{{ $prodi->id }}" data-jurusan="{{ $prodi->jurusan_id }}" {{ old('prodi_id', optional($user->student)->prodi_id) == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                                        @endforeach
                                    </select>
                                    @error('prodi_id')<div class="form-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    @elseif($user->user_type === 'staff')
                        <div class="form-section">
                            <h2 class="section-title">Data Staff</h2>
                            <div class="detail-block form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="nip">NIP</label>
                                    <input id="nip" name="nip" type="text" value="{{ old('nip', optional($user->staffEmployee)->nip) }}" class="form-input" />
                                    @error('nip')<div class="form-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="unit_id">Unit <span class="required"></span></label>
                                    <select id="unit_id" name="unit_id" class="form-select" required>
                                        <option value="">Pilih Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id', optional($user->staffEmployee)->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')<div class="form-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="position_id">Posisi <span class="required"></span></label>
                                    <select id="position_id" name="position_id" class="form-select" required>
                                        <option value="">Pilih Posisi</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id', optional($user->staffEmployee)->position_id) == $position->id ? 'selected' : '' }}>{{ $position->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('position_id')<div class="form-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="detail-block">
                    <div class="form-actions">
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

@push('scripts')
<script>
function validateProfileForm(form) {
    if (!form.name.value.trim()) { alert('Nama harus diisi'); form.name.focus(); return false; }
    if (!form.email.value.trim()) { alert('Email harus diisi'); form.email.focus(); return false; }
    if (!form.phone.value.trim()) { alert('No. Handphone harus diisi'); form.phone.focus(); return false; }
    if (form.phone.value.length < 10 || form.phone.value.length > 15) { alert('No. Handphone 10-15 digit'); form.phone.focus(); return false; }
    return true;
}

function syncProdiOptions() {
    var jurusanId = document.getElementById('jurusan_id').value;
    var prodiSelect = document.getElementById('prodi_id');
    Array.from(prodiSelect.options).forEach(function(opt) {
        if (!opt.value) return;
        var show = !jurusanId || opt.getAttribute('data-jurusan') === jurusanId;
        opt.style.display = show ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('jurusan_id')) {
        syncProdiOptions();
    }
});
</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ filemtime(public_path('css/profile.css')) }}">
@endpush
@endsection
