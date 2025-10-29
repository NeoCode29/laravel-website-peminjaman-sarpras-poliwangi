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
                    @if(Route::has('marking.index'))
                    <a href="{{ route('marking.index') }}" class="nav-link sublink {{ request()->routeIs('marking.*') ? 'active' : '' }}">
                        <i class="fas fa-bookmark"></i>
                        <span>Marking</span>
                    </a>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Katalog Sarana & Prasarana (read-only) untuk user yang tidak punya permission manajemen) -->
            @can('sarpras.view')
            @php
                $canManageSarana = auth()->user()->canAny(['sarpras.create','sarpras.edit','sarpras.delete','sarpras.status_update','sarpras.unit_manage']);
                $canManagePrasarana = auth()->user()->canAny(['sarpras.create','sarpras.edit','sarpras.delete','sarpras.status_update']);
            @endphp
            @unless($canManageSarana || $canManagePrasarana)
            <a href="{{ route('sarana.index') }}" class="nav-link {{ request()->routeIs('sarana.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>Katalog Sarana</span>
            </a>
            <a href="{{ route('prasarana.index') }}" class="nav-link {{ request()->routeIs('prasarana.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Katalog Prasarana</span>
            </a>
            @endunless
            @endcan

            <!-- Manajemen Sarana -->
            @canany(['sarpras.create','sarpras.edit','sarpras.delete','sarpras.status_update','sarpras.unit_manage'])
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
            @endcanany

            <!-- Manajemen Prasarana -->
            @canany(['sarpras.create','sarpras.edit','sarpras.delete','sarpras.status_update'])
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('prasarana.*') || request()->routeIs('kategori-prasarana.*') ? 'true' : 'false' }}">
                    <i class="fas fa-building"></i>
                    <span>Manajemen Prasarana</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('prasarana.*') || request()->routeIs('kategori-prasarana.*') ? 'show' : '' }}" role="group">
                    <a href="{{ route('prasarana.index') }}" class="nav-link sublink {{ request()->routeIs('prasarana.*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>Daftar Prasarana</span>
                    </a>
                    
                    @if(Route::has('kategori-prasarana.index'))
                    <a href="{{ route('kategori-prasarana.index') }}" class="nav-link sublink {{ request()->routeIs('kategori-prasarana.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i>
                        <span>Kategori Prasarana</span>
                    </a>
                    @endif
                </div>
            </div>
            @endcanany

            <!-- Master Data (Admin saja) -->

            <!-- Manajemen User -->
            @can('user.view')
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('user-management.*') || request()->routeIs('role-management.*') || request()->routeIs('permission-management.*') ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i>
                    <span>Manajemen User</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('user-management.*') || request()->routeIs('role-management.*') || request()->routeIs('permission-management.*') ? 'show' : '' }}" role="group">
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
                </div>
            </div>
            @endcan

            <!-- Setting -->
            @can('system.settings')
            <div class="menu-group" data-group>
                <button type="button" class="nav-link menu-toggle" aria-expanded="{{ request()->routeIs('system.*') || request()->routeIs('system-settings.*') || request()->routeIs('notifications.*') ? 'true' : 'false' }}">
                    <i class="fas fa-cog"></i>
                    <span>Setting</span>
                    <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                </button>
                <div class="nav-sublist {{ request()->routeIs('system.*') || request()->routeIs('system-settings.*') || request()->routeIs('notifications.*') || request()->routeIs('approval-assignment.global.*') ? 'show' : '' }}" role="group">
                    <a href="{{ route('system-settings.index') }}" class="nav-link sublink {{ request()->routeIs('system-settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                    @if(auth()->user()->can('sarpras.approval_assign') && Route::has('approval-assignment.global.index'))
                    <a href="{{ route('approval-assignment.global.index') }}" class="nav-link sublink {{ request()->routeIs('approval-assignment.global.*') ? 'active' : '' }}">
                        <i class="fas fa-user-check"></i>
                        <span>Assign Global Approver</span>
                    </a>
                    @endif
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

