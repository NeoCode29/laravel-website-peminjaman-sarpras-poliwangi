<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - Sistem Peminjaman Sarpras Poliwangi</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    @include('components.header')

    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Breadcrumb -->
        @hasSection('breadcrumb')
        <nav class="breadcrumb">
            @yield('breadcrumb')
        </nav>
        @endif

        <!-- Content Header -->
        <div class="content-header">
            <h1 class="content-title">@yield('title', 'Dashboard')</h1>
            @hasSection('subtitle')
            <p class="content-subtitle">@yield('subtitle')</p>
            @endif
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success fade-in" role="alert">
                <i class="fas fa-check-circle alert-icon"></i>
                <div>
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger fade-in" role="alert">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <div>
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning fade-in" role="alert">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div>
                    <strong>Peringatan!</strong> {{ session('warning') }}
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info fade-in" role="alert">
                <i class="fas fa-info-circle alert-icon"></i>
                <div>
                    <strong>Info!</strong> {{ session('info') }}
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <div class="page-content">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Dashboard JavaScript -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
    
    <!-- Auto-hide alerts script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert.fade-in');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert.parentElement) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(function() {
                            if (alert.parentElement) {
                                alert.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
