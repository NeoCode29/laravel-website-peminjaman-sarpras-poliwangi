@extends('layouts.app')

@section('title', 'Ubah Password')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile-change-password.css') }}?v={{ filemtime(public_path('css/profile-change-password.css')) }}">
@endpush

@section('content')
<section class="detail-page change-password-page">
    <div class="card user-detail-card">
        <div class="card-main">
            <form action="{{ route('profile.password.update') }}" method="POST" class="change-password-form">
                @csrf
                @method('PUT')

                @if(session('success'))
                    <div class="feedback-message feedback-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                        <button type="button" class="feedback-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="feedback-message feedback-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                        <button type="button" class="feedback-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-key"></i>
                        Ubah Password
                    </h3>
                    <div class="detail-block">
                        <div class="info-alert">
                            <i class="fas fa-info-circle"></i>
                            Gunakan password yang kuat dengan kombinasi huruf besar, huruf kecil, dan angka.
                        </div>
                        <div class="form-grid single-column">
                            <div class="form-group">
                                <label for="current_password" class="form-label required">Password Lama</label>
                                <div class="password-input-wrapper">
                                    <input type="password"
                                           id="current_password"
                                           name="current_password"
                                           class="form-input @error('current_password') is-invalid @enderror"
                                           placeholder="Masukkan password lama"
                                           required>
                                    <button type="button" class="password-toggle" data-target="current_password" aria-label="Tampilkan password lama">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label required">Password Baru</label>
                                <div class="password-input-wrapper">
                                    <input type="password"
                                           id="password"
                                           name="password"
                                           class="form-input @error('password') is-invalid @enderror"
                                           placeholder="Masukkan password baru"
                                           minlength="8"
                                           required>
                                    <button type="button" class="password-toggle" data-target="password" aria-label="Tampilkan password baru">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <p class="form-hint">Minimal 8 karakter dan mengandung huruf besar, huruf kecil, serta angka.</p>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="form-label required">Konfirmasi Password Baru</label>
                                <div class="password-input-wrapper">
                                    <input type="password"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           class="form-input"
                                           placeholder="Ulangi password baru"
                                           minlength="8"
                                           required>
                                    <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Tindakan</h3>
                    <div class="detail-actions">
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary btn-cancel">
                            <i class="fas fa-arrow-left"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.password-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-target');
            const field = document.getElementById(targetId);
            const icon = button.querySelector('i');

            if (!field) {
                return;
            }

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>
@endpush
