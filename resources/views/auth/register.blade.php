@extends('auth.layout')

@section('title', 'Daftar')
@section('subtitle', 'Buat akun baru')

@section('content')
<form method="POST" action="{{ route('register') }}" class="auth-form">
    @csrf
    
    <div class="form-group">
        <label for="name" class="form-label">Nama Lengkap</label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            class="form-control @error('name') error @enderror" 
            value="{{ old('name') }}" 
            required 
            autofocus
            minlength="2"
            maxlength="255"
            placeholder="Masukkan nama lengkap"
        >
        @error('name')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <input 
            type="text" 
            id="username" 
            name="username" 
            class="form-control @error('username') error @enderror" 
            value="{{ old('username') }}" 
            required
            minlength="3"
            maxlength="255"
            pattern="[a-zA-Z0-9_]+"
            placeholder="Masukkan username (huruf, angka, underscore)"
        >
        <small class="form-text text-muted">Username hanya boleh berisi huruf, angka, dan underscore</small>
        @error('username')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control @error('email') error @enderror" 
            value="{{ old('email') }}" 
            required
            placeholder="Masukkan email"
        >
        @error('email')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="phone" class="form-label">Nomor Handphone</label>
        <input 
            type="tel" 
            id="phone" 
            name="phone" 
            class="form-control @error('phone') error @enderror" 
            value="{{ old('phone') }}" 
            required
            minlength="10"
            maxlength="15"
            pattern="[0-9+\-\s()]+"
            placeholder="Masukkan nomor handphone"
        >
        <small class="form-text text-muted">Contoh: 081234567890 atau +6281234567890</small>
        @error('phone')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="user_type" class="form-label">Tipe User</label>
        <select 
            id="user_type" 
            name="user_type" 
            class="form-control @error('user_type') error @enderror" 
            required
        >
            <option value="">Pilih tipe user</option>
            <option value="mahasiswa" {{ old('user_type') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
            <option value="staff" {{ old('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
        </select>
        @error('user_type')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <div class="password-input-group">
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control @error('password') error @enderror" 
                required
                minlength="8"
                placeholder="Masukkan password (minimal 8 karakter)"
            >
            <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <small class="form-text text-muted">Password harus mengandung huruf besar, huruf kecil, dan angka</small>
        @error('password')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <div class="password-input-group">
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                class="form-control @error('password_confirmation') error @enderror" 
                required
                minlength="8"
                placeholder="Konfirmasi password"
            >
            <button type="button" class="password-toggle-btn" onclick="togglePassword('password_confirmation')">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('password_confirmation')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <button type="submit" class="btn btn-primary">
        Daftar
    </button>
</form>
@endsection

@section('footer')
<p class="text-muted">
    Sudah punya akun? 
    <a href="{{ route('login') }}">Masuk di sini</a>
</p>
@endsection

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = checkPasswordStrength(password);
    
    // Update UI based on strength
    updatePasswordStrengthIndicator(strength);
});

function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

function updatePasswordStrengthIndicator(strength) {
    // You can add visual feedback here
    console.log('Password strength:', strength);
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    
    form.addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Konfirmasi password tidak cocok!');
            return false;
        }
        
        if (password.value.length < 8) {
            e.preventDefault();
            alert('Password minimal 8 karakter!');
            return false;
        }
    });
});
</script>
@endpush
