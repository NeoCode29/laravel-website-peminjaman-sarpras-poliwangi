@extends('layouts.app')

@section('title', 'Keamanan Akun')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Keamanan Akun</li>
                    </ol>
                </div>
                <h4 class="page-title">Keamanan Akun</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Account Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt text-primary"></i>
                        Informasi Keamanan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Username:</label>
                                <span class="info-value">{{ $user->username }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Email:</label>
                                <span class="info-value">{{ $user->email }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Tipe User:</label>
                                <span class="info-value badge badge-info">{{ $user->getUserTypeDisplayAttribute() }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Status:</label>
                                <span class="info-value badge badge-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                    {{ $user->getStatusDisplayAttribute() }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Terakhir Login:</label>
                                <span class="info-value">
                                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Belum pernah login' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Password Diubah:</label>
                                <span class="info-value">
                                    {{ $user->password_changed_at ? $user->password_changed_at->format('d/m/Y H:i') : 'Tidak diketahui' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key text-warning"></i>
                        Ubah Password
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Fitur ubah password telah dinonaktifkan.</strong><br>
                        Untuk keamanan, silakan hubungi administrator jika Anda perlu mengubah password.
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb text-info"></i>
                        Tips Keamanan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="security-tips">
                        <div class="tip-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Gunakan password yang kuat dan unik</span>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Jangan bagikan kredensial login Anda</span>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Logout setelah selesai menggunakan sistem</span>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Gunakan browser yang terbaru dan aman</span>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Hindari login dari jaringan publik</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history text-secondary"></i>
                        Aktivitas Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <div id="recentActivity">
                        <div class="text-center">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Memuat aktivitas...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.info-item {
    margin-bottom: 1rem;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    display: block;
    margin-bottom: 0.25rem;
}

.info-value {
    color: #495057;
    font-size: 0.95rem;
}

.password-input-group {
    position: relative;
}

.password-toggle-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
}

.password-toggle-btn:hover {
    color: #495057;
}

.security-tips {
    space-y: 0.75rem;
}

.tip-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.tip-item i {
    margin-right: 0.5rem;
    font-size: 0.8rem;
}

.activity-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-time {
    font-size: 0.8rem;
    color: #6c757d;
}

.activity-description {
    font-size: 0.9rem;
    color: #495057;
}
</style>
@endpush

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

// Load recent activity
document.addEventListener('DOMContentLoaded', function() {
    loadRecentActivity();
});

function loadRecentActivity() {
    fetch('{{ route("api.login-attempts") }}')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentActivity');
            
            if (data.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Tidak ada aktivitas terbaru</p>';
                return;
            }
            
            let html = '';
            data.slice(0, 5).forEach(activity => {
                html += `
                    <div class="activity-item">
                        <div class="activity-description">${activity.description || 'Login attempt'}</div>
                        <div class="activity-time">${new Date(activity.created_at).toLocaleString('id-ID')}</div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading activity:', error);
            document.getElementById('recentActivity').innerHTML = '<p class="text-danger text-center">Gagal memuat aktivitas</p>';
        });
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = checkPasswordStrength(password);
    
    // You can add visual feedback here
    console.log('Password strength:', strength);
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
</script>
@endpush

