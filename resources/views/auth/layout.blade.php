<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Aplikasi Peminjaman Sarpras</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Style Guide CSS -->
    <link href="{{ asset('css/style-guide.css') }}" rel="stylesheet">
    
    <style>
        /* Auth Container - Full height dengan background putih */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            padding: 24px;
        }
        
        /* Auth Card - Menggunakan style guide card */
        .auth-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 32px;
            width: 100%;
            max-width: 400px;
            position: relative;
            transition: box-shadow 0.2s ease;
        }
        
        .auth-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Auth Header */
        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .auth-logo {
            width: 60px;
            height: 60px;
            background: #333333;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: 600;
        }
        
        .auth-title {
            font-size: 24px;
            font-weight: 600;
            line-height: 1.2;
            color: #333333;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .auth-subtitle {
            font-size: 14px;
            font-weight: 400;
            line-height: 1.4;
            color: #666666;
        }
        
        /* Auth Form */
        .auth-form {
            margin-bottom: 24px;
        }
        
        /* Form Groups - Menggunakan style guide form groups */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 12px;
        }
        
        .form-label {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
            color: #333333;
            margin-bottom: 8px;
        }
        
        /* Form Controls - Menggunakan style guide form inputs */
        .form-control {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 400;
            color: #333333;
            height: 40px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
        }
        
        .form-control:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }
        
        .form-control:hover {
            border-color: #cccccc;
        }
        
        .form-control.error {
            border-color: #dc3545;
            outline-color: #dc3545;
        }
        
        /* Password Input Group */
        .password-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .password-input-group .form-control {
            padding-right: 48px; /* Space for toggle button */
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: #666666;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            transition: all 0.2s ease;
            z-index: 2;
        }
        
        .password-toggle-btn:hover {
            background-color: #f5f5f5;
            color: #333333;
        }
        
        .password-toggle-btn:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        /* Buttons - Menggunakan style guide buttons */
        .btn {
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            width: 100%;
        }
        
        .btn-primary {
            background: #333333;
            color: #ffffff;
        }
        
        .btn-primary:hover {
            background: #555555;
            transform: translateY(-1px);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn.loading {
            position: relative;
            color: transparent;
        }
        
        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        .btn-link {
            background: none;
            color: #333333;
            text-decoration: none;
            padding: 8px 0;
            width: auto;
            font-size: 14px;
        }
        
        .btn-link:hover {
            text-decoration: underline;
        }
        
        /* Remember Me Checkbox */
        .remember-me,
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .remember-me input[type="checkbox"],
        .form-check-input {
            width: 16px;
            height: 16px;
            accent-color: #007bff;
        }
        
        .remember-me label,
        .form-check-label {
            font-size: 14px;
            color: #666666;
            cursor: pointer;
        }
        
        /* SSO Section */
        .sso-section {
            margin-top: 24px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            text-align: center;
            color: #666666;
            font-size: 14px;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            padding: 0 16px;
            background: #ffffff;
        }
        
        .btn-sso {
            background: #ffffff;
            color: #333333;
            border: 1px solid #e0e0e0;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .btn-sso:hover {
            background: #f5f5f5;
            border-color: #cccccc;
        }
        
        .sso-info {
            text-align: center;
        }
        
        .sso-info small {
            font-size: 12px;
            color: #666666;
        }
        
        /* Auth Footer */
        .auth-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        
        .auth-footer a {
            color: #333333;
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        /* Alerts - Menggunakan style guide alerts */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 6px;
            border: 1px solid transparent;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #eaf7ee;
            border-color: #cfead6;
            color: #2e7d32;
        }
        
        .alert-danger {
            background: #ffe9ec;
            border-color: #ffccd2;
            color: #c62828;
        }
        
        .alert-info {
            background: #e7f1ff;
            border-color: #cfe3ff;
            color: #0d6efd;
        }
        
        .alert-warning {
            background: #fff7e6;
            border-color: #ffe7b5;
            color: #b26a00;
        }
        
        /* Error Messages */
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
        }
        
        /* Utility Classes */
        .text-center {
            text-align: center;
        }
        
        .text-muted {
            color: #666666;
        }
        
        .mt-3 {
            margin-top: 16px;
        }
        
        .mb-3 {
            margin-bottom: 16px;
        }
        
        
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-container {
                padding: 16px;
            }
            
            .auth-card {
                padding: 24px;
                max-width: 100%;
            }
            
            .auth-title {
                font-size: 20px;
            }
            
            .auth-logo {
                width: 50px;
                height: 50px;
                font-size: 18px;
            }
        }
        
        @media (max-width: 480px) {
            .auth-container {
                padding: 12px;
            }
            
            .auth-card {
                padding: 20px;
            }
            
            .auth-title {
                font-size: 18px;
            }
            
            .auth-logo {
                width: 45px;
                height: 45px;
                font-size: 16px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">SP</div>
                <h1 class="auth-title">@yield('title')</h1>
                <p class="auth-subtitle">@yield('subtitle')</p>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif
            
            @if(session('status'))
                <div class="alert alert-info">
                    {{ session('status') }}
                </div>
            @endif
            
            @yield('content')
            
            <div class="auth-footer">
                @yield('footer')
            </div>
        </div>
    </div>
    
    <!-- Style Guide JavaScript -->
    <script src="{{ asset('js/style-guide.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
