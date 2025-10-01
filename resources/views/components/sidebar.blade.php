{{-- Sidebar Component --}}
@auth
<nav class="sidebar" id="sidebar">
    <div class="sidebar-menu">
        <div class="menu-item">
            <a href="{{ route('dashboard') }}" class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        @can('peminjaman.view')
        <div class="menu-item">
            <a href="{{ route('peminjaman.index') }}" class="menu-link {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Peminjaman</span>
            </a>
        </div>
        @endcan
        
        @can('sarpras.view')
        <div class="menu-item">
            <a href="{{ route('sarpras.index') }}" class="menu-link {{ request()->routeIs('sarpras.*') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i>
                <span>Sarana & Prasarana</span>
            </a>
        </div>
        @endcan
        
        @can('user.view')
        <div class="menu-item">
            <a href="{{ route('users.index') }}" class="menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Manajemen User</span>
            </a>
        </div>
        @endcan
        
        @can('role.view')
        <div class="menu-item">
            <a href="{{ route('roles.index') }}" class="menu-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Role & Permission</span>
            </a>
        </div>
        @endcan
        
        @can('report.view')
        <div class="menu-item">
            <a href="{{ route('reports.index') }}" class="menu-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Laporan</span>
            </a>
        </div>
        @endcan
        
        @can('system.settings')
        <div class="menu-item">
            <a href="{{ route('system.settings') }}" class="menu-link {{ request()->routeIs('system.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </div>
        @endcan
    </div>
</nav>
@endauth
