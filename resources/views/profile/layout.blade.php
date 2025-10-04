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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Style Guide CSS -->
    <link href="{{ asset('css/style-guide.css') }}" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="text-center mb-4">
                <h5 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: var(--spacing-sm);">
                    👤 Profil Saya
                </h5>
            </div>
            
            <nav class="nav">
                <a class="nav-link {{ request()->is('profile') && !request()->is('profile/*') ? 'active' : '' }}" 
                   href="{{ route('profile.show') }}">
                    👁️ Lihat Profil
                </a>
                <a class="nav-link {{ request()->is('profile/edit') ? 'active' : '' }}" 
                   href="{{ route('profile.edit') }}">
                    ✏️ Edit Profil
                </a>
                <hr style="border: none; border-top: 1px solid var(--border-color); margin: var(--spacing-md) 0;">
                <a class="nav-link" href="/">
                    🏠 Dashboard
                </a>
                <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    🚪 Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 style="font-size: 20px; font-weight: 500; margin: 0;">@yield('title')</h4>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3">
                            👤 {{ Auth::user()->name }}
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm" type="button" onclick="toggleDropdown()">
                                ⚙️
                            </button>
                            <div class="dropdown-content" id="dropdownMenu">
                                <a href="{{ route('profile.show') }}">
                                    👁️ Lihat Profil
                                </a>
                                <a href="{{ route('profile.edit') }}">
                                    ✏️ Edit Profil
                                </a>
                                <a href="/">
                                    🏠 Dashboard
                                </a>
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    🚪 Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdownMenu');
            const button = event.target.closest('button');
            
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleDropdown') === -1) {
                dropdown.style.display = 'none';
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
