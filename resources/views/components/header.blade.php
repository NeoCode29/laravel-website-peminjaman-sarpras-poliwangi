{{-- Header Component --}}
<header class="header">
    <div class="header-left">
        <button class="mobile-menu-toggle d-md-none" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="logo">
            <span>Sarpras Poliwangi</span>
        </a>
    </div>
    
    <div class="header-right">
        @auth
            <!-- Notifications -->
            <div class="notification-btn" id="notificationBtn">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge">0</span>
            </div>
            
            <!-- User Menu -->
            <div class="user-menu">
                <button class="user-btn" id="userBtn">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span>{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <a href="{{ route('profile.show') }}" class="user-dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="user-dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                    <div class="user-dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="user-dropdown-item" style="padding: 0;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: inherit; width: 100%; text-align: left; display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem;">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
        @endauth
    </div>
</header>
