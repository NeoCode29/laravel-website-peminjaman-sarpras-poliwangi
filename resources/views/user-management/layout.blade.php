<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Aplikasi Peminjaman Sarpras</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Style Guide CSS -->
    <link href="{{ asset('css/style-guide.css') }}" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="text-center mb-4">
                <h5 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: var(--spacing-sm);">
                    ⚙️ Admin Panel
                </h5>
            </div>
            
            <nav class="nav">
                <a class="nav-link {{ request()->is('user-management*') ? 'active' : '' }}" 
                   href="{{ route('user-management.index') }}">
                    👥 User Management
                </a>
                <a class="nav-link {{ request()->is('role-management*') ? 'active' : '' }}" 
                   href="{{ route('role-management.index') }}">
                    🏷️ Role Management
                </a>
                <a class="nav-link {{ request()->is('permission-management*') ? 'active' : '' }}" 
                   href="{{ route('permission-management.index') }}">
                    🔑 Permission Management
                </a>
                <a class="nav-link {{ request()->is('role-permission-matrix*') ? 'active' : '' }}" 
                   href="{{ route('role-permission-matrix.index') }}">
                    📊 Permission Matrix
                </a>
                <hr style="border: none; border-top: 1px solid var(--border-color); margin: var(--spacing-md) 0;">
                <a class="nav-link {{ request()->is('sarana*') ? 'active' : '' }}" 
                   href="{{ route('sarana.index') }}">
                    📦 Manajemen Sarana
                </a>
                <a class="nav-link {{ request()->is('kategori-sarana*') ? 'active' : '' }}" 
                   href="{{ route('kategori-sarana.index') }}">
                    🏷️ Kategori Sarana
                </a>
                <hr style="border: none; border-top: 1px solid var(--border-color); margin: var(--spacing-md) 0;">
                <a class="nav-link" href="/">
                    🏠 Dashboard
                </a>
                <a class="nav-link {{ request()->is('profile*') ? 'active' : '' }}" 
                   href="{{ route('profile.show') }}">
                    👤 Profil Saya
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
                            👤 Admin User
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm" type="button" onclick="toggleDropdown()">
                                ⚙️
                            </button>
                            <div class="dropdown-content" id="dropdownMenu">
                                <a href="{{ route('profile.show') }}">
                                    👤 Profil Saya
                                </a>
                                <a href="{{ route('user-management.index') }}">
                                    👥 User Management
                                </a>
                                <a href="{{ route('role-management.index') }}">
                                    🏷️ Role Management
                                </a>
                                <a href="{{ route('permission-management.index') }}">
                                    🔑 Permission Management
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
                    <div class="alert alert-success" id="alert-success">
                        ✅ {{ session('success') }}
                        <button type="button" onclick="closeAlert('alert-success')" style="background: none; border: none; float: right; font-size: 18px; cursor: pointer;">&times;</button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger" id="alert-error">
                        ❌ {{ session('error') }}
                        <button type="button" onclick="closeAlert('alert-error')" style="background: none; border: none; float: right; font-size: 18px; cursor: pointer;">&times;</button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning" id="alert-warning">
                        ⚠️ {{ session('warning') }}
                        <button type="button" onclick="closeAlert('alert-warning')" style="background: none; border: none; float: right; font-size: 18px; cursor: pointer;">&times;</button>
                    </div>
                @endif
                
                @if(session('info'))
                    <div class="alert alert-info" id="alert-info">
                        ℹ️ {{ session('info') }}
                        <button type="button" onclick="closeAlert('alert-info')" style="background: none; border: none; float: right; font-size: 18px; cursor: pointer;">&times;</button>
                    </div>
                @endif
                
                <!-- Page Content -->
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Logout Form -->
    <form id="logout-form" action="#" method="POST" style="display: none;">
        @csrf
    </form>
    
    <!-- Custom Scripts -->
    <!-- Style Guide JavaScript -->
    <script src="{{ asset('js/style-guide.js') }}"></script>
    
    @stack('scripts')
</body>
</html>