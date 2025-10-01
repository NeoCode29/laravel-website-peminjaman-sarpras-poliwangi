@extends('profile.layout')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Profil
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Kembali
                        </a>
                    </div>
                </div>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Informasi Dasar</h5>
                                <div class="form-group">
                                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">Nomor Handphone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                                           placeholder="08xxxxxxxxxx" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5>Informasi {{ $user->user_type === 'mahasiswa' ? 'Akademik' : 'Kepegawaian' }}</h5>
                                
                                @if($user->user_type === 'mahasiswa')
                                    <div class="form-group">
                                        <label for="jurusan_id">Jurusan <span class="text-danger">*</span></label>
                                        <select class="form-control @error('jurusan_id') is-invalid @enderror" 
                                                id="jurusan_id" name="jurusan_id" required>
                                            <option value="">Pilih Jurusan</option>
                                            @foreach($jurusans as $jurusan)
                                                <option value="{{ $jurusan->id }}" 
                                                        {{ old('jurusan_id', $user->student->jurusan_id ?? '') == $jurusan->id ? 'selected' : '' }}>
                                                    {{ $jurusan->nama_jurusan }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('jurusan_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="prodi_id">Program Studi <span class="text-danger">*</span></label>
                                        <select class="form-control @error('prodi_id') is-invalid @enderror" 
                                                id="prodi_id" name="prodi_id" required>
                                            <option value="">Pilih Program Studi</option>
                                            @if($user->student && $user->student->prodi)
                                                <option value="{{ $user->student->prodi->id }}" selected>
                                                    {{ $user->student->prodi->nama_prodi }}
                                                </option>
                                            @endif
                                        </select>
                                        @error('prodi_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label for="nip">NIP</label>
                                        <input type="text" class="form-control @error('nip') is-invalid @enderror" 
                                               id="nip" name="nip" value="{{ old('nip', $user->staffEmployee->nip ?? '') }}" 
                                               placeholder="Opsional">
                                        @error('nip')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="unit_id">Unit <span class="text-danger">*</span></label>
                                        <select class="form-control @error('unit_id') is-invalid @enderror" 
                                                id="unit_id" name="unit_id" required>
                                            <option value="">Pilih Unit</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" 
                                                        {{ old('unit_id', $user->staffEmployee->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('unit_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="position_id">Posisi <span class="text-danger">*</span></label>
                                        <select class="form-control @error('position_id') is-invalid @enderror" 
                                                id="position_id" name="position_id" required>
                                            <option value="">Pilih Posisi</option>
                                            @foreach($positions as $position)
                                                <option value="{{ $position->id }}" 
                                                        {{ old('position_id', $user->staffEmployee->position_id ?? '') == $position->id ? 'selected' : '' }}>
                                                    {{ $position->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('position_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle jurusan change for mahasiswa
    $('#jurusan_id').change(function() {
        var jurusanId = $(this).val();
        var prodiSelect = $('#prodi_id');
        
        prodiSelect.html('<option value="">Loading...</option>');
        
        if (jurusanId) {
            $.ajax({
                url: '{{ route("profile.get-prodis") }}',
                type: 'GET',
                data: { jurusan_id: jurusanId },
                success: function(data) {
                    prodiSelect.html('<option value="">Pilih Program Studi</option>');
                    $.each(data, function(key, value) {
                        prodiSelect.append('<option value="' + value.id + '">' + value.nama_prodi + '</option>');
                    });
                },
                error: function() {
                    prodiSelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            prodiSelect.html('<option value="">Pilih Program Studi</option>');
        }
    });
});
</script>
@endpush
