<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lengkapi Profil - Aplikasi Peminjaman Sarpras</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Profile Setup CSS -->
    <link href="{{ asset('css/profile-setup.css') }}" rel="stylesheet">
</head>
<body>
    <div class="profile-setup-container">
        <div class="profile-setup-card">
            <div class="profile-setup-header">
                <h1>
                    <i class="fas fa-user-plus"></i>
                    Lengkapi Profil Anda
                </h1>
                <p>Silakan lengkapi profil Anda untuk dapat menggunakan sistem peminjaman sarana dan prasarana</p>
            </div>
            
            <div class="profile-setup-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.complete-setup') }}" method="POST">
                    @csrf
                    <div class="form-grid">
                        <div class="form-section">
                            <h5>Informasi Dasar</h5>
                                <div class="form-group">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" 
                                           id="name" value="{{ $user->name }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" 
                                           id="email" value="{{ $user->email }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Nomor Handphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                                           placeholder="08xxxxxxxxxx" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tipe User</label>
                                    <input type="text" class="form-control" value="{{ $user->user_type_display }}" readonly>
                                </div>
                        </div>

                        <div class="form-section">
                            <h5>Informasi {{ $user->user_type === 'mahasiswa' ? 'Akademik' : 'Kepegawaian' }}</h5>
                                
                                    @if($user->user_type === 'mahasiswa')
                                        <div class="form-group">
                                            <label for="nim" class="form-label">NIM</label>
                                            <input type="text" class="form-control" value="{{ $user->username }}" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="angkatan" class="form-label">Angkatan</label>
                                            <input type="text" class="form-control" 
                                                   value="{{ strlen($user->username) >= 4 ? '20' . substr($user->username, 2, 2) : '-' }}" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="jurusan_id" class="form-label">Jurusan <span class="text-danger">*</span></label>
                                            <select class="form-control @error('jurusan_id') is-invalid @enderror" 
                                                    id="jurusan_id" name="jurusan_id" required>
                                                <option value="">Pilih Jurusan</option>
                                                @foreach($jurusans as $jurusan)
                                                    <option value="{{ $jurusan->id }}" {{ old('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                                        {{ $jurusan->nama_jurusan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('jurusan_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="prodi_id" class="form-label">Program Studi <span class="text-danger">*</span></label>
                                            <select class="form-control @error('prodi_id') is-invalid @enderror" 
                                                    id="prodi_id" name="prodi_id" required>
                                                <option value="">Pilih Program Studi</option>
                                            </select>
                                            @error('prodi_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @else
                                        <div class="form-group">
                                            <label for="nip" class="form-label">NIP</label>
                                            <input type="text" class="form-control @error('nip') is-invalid @enderror" 
                                                   id="nip" name="nip" value="{{ old('nip') }}" 
                                                   placeholder="Opsional">
                                            @error('nip')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="unit_id" class="form-label">Unit <span class="text-danger">*</span></label>
                                            <select class="form-control @error('unit_id') is-invalid @enderror" 
                                                    id="unit_id" name="unit_id" required>
                                                <option value="">Pilih Unit</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('unit_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="position_id" class="form-label">Posisi <span class="text-danger">*</span></label>
                                            <select class="form-control @error('position_id') is-invalid @enderror" 
                                                    id="position_id" name="position_id" required>
                                                <option value="">Pilih Posisi</option>
                                                @foreach($positions as $position)
                                                    <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
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

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle icon"></i>
                        <div>
                            <strong>Perhatian:</strong> Pastikan data yang Anda masukkan sudah benar. 
                            Setelah profil diselesaikan, beberapa data tidak dapat diubah.
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check icon"></i>
                            Selesaikan Profil
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

        // Form validation
        $('form').on('submit', function(e) {
            var isValid = true;
            var requiredFields = $(this).find('input[required], select[required]');
            
            requiredFields.each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
            }
        });

        // Real-time validation
        $('input[required], select[required]').on('blur', function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
    });
    </script>
</body>
</html>
