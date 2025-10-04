{{-- Sidebar (PRD-driven, Style Guide compliant) --}}
@auth
<aside class="sidebar" role="navigation" aria-label="Sidebar">
    <div class="sidebar-menu">
        <nav class="nav">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <!-- Peminjaman -->
            @can('peminjaman.view')
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('peminjaman.*') ? 'true' : 'false' }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Peminjaman</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('peminjaman.*') ? 'show' : '' }}" role="group">
                    <a href="{{ route('peminjaman.index') }}" class="nav-link sublink {{ request()->routeIs('peminjaman.index') || request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Daftar Peminjaman</span>
                    </a>
                    @can('peminjaman.approve')
                    @if(Route::has('peminjaman.approval'))
                    <a href="{{ route('peminjaman.approval') }}" class="nav-link sublink {{ request()->routeIs('peminjaman.approval') ? 'active' : '' }}">
                        <i class="fas fa-user-check"></i>
                        <span>Approval</span>
                    </a>
                    @endif
                    @endcan
                    @if(Route::has('peminjaman.marking'))
                    <a href="{{ route('peminjaman.marking') }}" class="nav-link sublink {{ request()->routeIs('peminjaman.marking') ? 'active' : '' }}">
                        <i class="fas fa-flag"></i>
                        <span>Marking</span>
                    </a>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Manajemen Sarana -->
            @can('sarpras.view')
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('sarana.*') || request()->routeIs('kategori-sarana.*') ? 'true' : 'false' }}">
                    <i class="fas fa-boxes"></i>
                    <span>Manajemen Sarana</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('sarana.*') || request()->routeIs('kategori-sarana.*') ? 'show' : '' }}" role="group">
                    <a href="{{ route('sarana.index') }}" class="nav-link sublink {{ request()->routeIs('sarana.*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Daftar Sarana</span>
                    </a>
                    
                    @if(Route::has('kategori-sarana.index'))
                    <a href="{{ route('kategori-sarana.index') }}" class="nav-link sublink {{ request()->routeIs('kategori-sarana.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i>
                        <span>Kategori Sarana</span>
                    </a>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Master Data -->
            @can('sarpras.view')
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('jurusan.*') || request()->routeIs('prodi.*') || request()->routeIs('units.*') || request()->routeIs('positions.*') || request()->routeIs('ukm.*') ? 'true' : 'false' }}">
                    <i class="fas fa-database"></i>
                    <span>Master Data</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('jurusan.*') || request()->routeIs('prodi.*') || request()->routeIs('units.*') || request()->routeIs('positions.*') || request()->routeIs('ukm.*') ? 'show' : '' }}" role="group">
                    @if(Route::has('jurusan.index'))
                    <a href="{{ route('jurusan.index') }}" class="nav-link sublink {{ request()->routeIs('jurusan.*') ? 'active' : '' }}">
                        <i class="fas fa-school"></i>
                        <span>Jurusan</span>
                    </a>
                    @endif
                    @if(Route::has('prodi.index'))
                    <a href="{{ route('prodi.index') }}" class="nav-link sublink {{ request()->routeIs('prodi.*') ? 'active' : '' }}">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Program Studi</span>
                    </a>
                    @endif
                    @if(Route::has('units.index'))
                    <a href="{{ route('units.index') }}" class="nav-link sublink {{ request()->routeIs('units.*') ? 'active' : '' }}">
                        <i class="fas fa-sitemap"></i>
                        <span>Unit</span>
                    </a>
                    @endif
                    @if(Route::has('positions.index'))
                    <a href="{{ route('positions.index') }}" class="nav-link sublink {{ request()->routeIs('positions.*') ? 'active' : '' }}">
                        <i class="fas fa-briefcase"></i>
                        <span>Posisi</span>
                    </a>
                    @endif
                    @if(Route::has('ukm.index'))
                    <a href="{{ route('ukm.index') }}" class="nav-link sublink {{ request()->routeIs('ukm.*') ? 'active' : '' }}">
                        <i class="fas fa-people-group"></i>
                        <span>UKM</span>
                    </a>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Manajemen User -->
            @can('user.view')
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('user-management.*') || request()->routeIs('role-management.*') || request()->routeIs('permission-management.*') || request()->routeIs('role-permission-matrix.*') ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i>
                    <span>Manajemen User</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('user-management.*') || request()->routeIs('role-management.*') || request()->routeIs('permission-management.*') || request()->routeIs('role-permission-matrix.*') ? 'show' : '' }}" role="group">
                    <a href="{{ route('user-management.index') }}" class="nav-link sublink {{ request()->routeIs('user-management.*') ? 'active' : '' }}">
                        <i class="fas fa-user"></i>
                        <span>Daftar User</span>
                    </a>
                    <a href="{{ route('role-management.index') }}" class="nav-link sublink {{ request()->routeIs('role-management.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tag"></i>
                        <span>Daftar Role</span>
                    </a>
                    <a href="{{ route('permission-management.index') }}" class="nav-link sublink {{ request()->routeIs('permission-management.*') ? 'active' : '' }}">
                        <i class="fas fa-key"></i>
                        <span>Daftar Permission</span>
                    </a>
                    @if(Route::has('role-permission-matrix.index'))
                    <a href="{{ route('role-permission-matrix.index') }}" class="nav-link sublink {{ request()->routeIs('role-permission-matrix.*') ? 'active' : '' }}">
                        <i class="fas fa-table"></i>
                        <span>Permission Matrix</span>
                    </a>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Laporan -->
            @can('report.view')
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('reports.*') || request()->routeIs('audit-logs.*') ? 'true' : 'false' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('reports.*') || request()->routeIs('audit-logs.*') ? 'show' : '' }}" role="group">
                    <a href="{{ route('reports.index') }}" class="nav-link sublink {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                        <i class="fas fa-file-lines"></i>
                        <span>Peminjaman</span>
                    </a>
                    @if(Route::has('reports.sarpras'))
                    <a href="{{ route('reports.sarpras') }}" class="nav-link sublink {{ request()->routeIs('reports.sarpras') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Sarana Prasarana</span>
                    </a>
                    @endif
                    @can('log.view')
                    <a href="{{ route('audit-logs.index') }}" class="nav-link sublink {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Audit Log</span>
                    </a>
                    @endcan
                </div>
            </div>
            @endcan

            <!-- Setting -->
            @can('system.settings')
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('system.*') || request()->routeIs('notifications.*') ? 'true' : 'false' }}">
                    <i class="fas fa-cog"></i>
                    <span>Setting</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('system.*') || request()->routeIs('notifications.*') ? 'show' : '' }}" role="group">
                    <a href="{{ route('system.settings') }}" class="nav-link sublink {{ request()->routeIs('system.settings') ? 'active' : '' }}">
                        <i class="fas fa-sliders"></i>
                        <span>Sistem</span>
                    </a>
                    @if(Route::has('notifications.index'))
                    <a href="{{ route('notifications.index') }}" class="nav-link sublink {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <i class="fas fa-bell"></i>
                        <span>Notifikasi</span>
                    </a>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Profil & Logout -->
            <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Profil</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-100">
                @csrf
                <button type="submit" class="nav-link logout-btn">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </nav>
    </div>
</aside>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('[data-group]').forEach(function(group) {
    const toggle = group.querySelector('.menu-toggle');
    const sub = group.querySelector('.nav-sublist');
    if (!toggle || !sub) return;
    toggle.addEventListener('click', function() {
      const isOpen = sub.classList.toggle('show');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      group.classList.toggle('open', isOpen);
    });
  });
});
</script>
@endpush
@endauth

{{-- Styles in public/css/components/sidebar.css --}}

