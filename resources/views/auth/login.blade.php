@extends('auth.layout')

@section('title', 'Login')
@section('subtitle', 'Masuk ke akun Anda')

@section('content')
<form method="POST" action="{{ route('login.perform') }}" class="auth-form">
    @csrf
    
    <!-- Username/Email Field -->
    <div class="form-group">
        <label for="username" class="form-label">Username atau Email</label>
        <input 
            type="text" 
            id="username" 
            name="username" 
            class="form-control @error('username') error @enderror" 
            value="{{ old('username') }}" 
            required 
            autofocus
            maxlength="255"
            placeholder="Masukkan username atau email"
            autocomplete="username"
        >
        @error('username')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <!-- Password Field with Toggle -->
    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <div class="password-input-group">
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control @error('password') error @enderror" 
                required
                minlength="1"
                placeholder="Masukkan password"
                autocomplete="current-password"
            >
            <button type="button" class="password-toggle-btn" onclick="togglePassword()" id="togglePasswordBtn" aria-label="Toggle password visibility">
                <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
        </div>
        @error('password')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>
    
    <!-- Remember Me Checkbox -->
    <div class="form-check">
        <input type="checkbox" id="remember" name="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
        <label for="remember" class="form-check-label">Ingat saya</label>
    </div>
    
    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-sign-in-alt"></i>
        Masuk
    </button>
</form>

<!-- SSO Login Button (Conditional) -->
@if (config('services.oauth_server.sso_enable'))
<div class="sso-section">
    <div class="divider">
        <span>atau</span>
    </div>
    
    <a href="{{ route('auth.oauth.login') }}" class="btn btn-sso">
        <i class="fas fa-university"></i>
        Masuk dengan SSO Poliwangi
    </a>
    
</div>
@endif

@endsection

@section('footer')
<p class="text-muted">
    Belum punya akun? 
    <a href="{{ route('register') }}">Daftar di sini</a>
</p>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
        toggleBtn.setAttribute('aria-label', 'Hide password');
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
        toggleBtn.setAttribute('aria-label', 'Show password');
    }
}

// Add focus styles for better accessibility
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});
</script>
@endpush
