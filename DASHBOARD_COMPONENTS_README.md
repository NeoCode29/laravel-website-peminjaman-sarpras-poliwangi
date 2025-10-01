# Komponen Dashboard - Sistem Peminjaman Sarpras Poliwangi

## Overview
Dokumen ini menjelaskan komponen-komponen dashboard yang telah dibuat untuk sistem peminjaman sarana dan prasarana Poliwangi. Semua komponen menggunakan vanilla JavaScript, CSS, dan HTML tanpa framework frontend tambahan.

## Struktur File

### Layout Files
- `resources/views/layouts/dashboard.blade.php` - Layout dashboard lengkap (monolitik)
- `resources/views/layouts/dashboard-modular.blade.php` - Layout dashboard modular
- `resources/views/dashboard/index.blade.php` - Halaman dashboard (monolitik)
- `resources/views/dashboard/index-modular.blade.php` - Halaman dashboard modular

### Component Files
- `resources/views/components/header.blade.php` - Komponen header
- `resources/views/components/sidebar.blade.php` - Komponen sidebar
- `resources/views/components/footer.blade.php` - Komponen footer

### Asset Files
- `public/css/dashboard.css` - CSS utama untuk dashboard
- `public/js/dashboard.js` - JavaScript utama untuk dashboard

## Fitur Utama

### 1. Header
- **Logo**: Logo sistem dengan ikon universitas
- **Sidebar Toggle**: Tombol untuk menampilkan/menyembunyikan sidebar
- **Notifications**: Badge notifikasi dengan counter
- **User Menu**: Dropdown menu user dengan profil dan logout

### 2. Sidebar
- **Menu Dinamis**: Menu berdasarkan permission user menggunakan Laravel's `@can` directive
- **Active State**: Menu aktif berdasarkan route saat ini
- **Responsive**: Sidebar dapat disembunyikan di mobile
- **Smooth Animation**: Transisi smooth saat toggle

### 3. Main Content
- **Breadcrumb**: Navigasi breadcrumb opsional
- **Content Header**: Judul dan subtitle halaman
- **Flash Messages**: Alert untuk success, error, warning, dan info
- **Page Content**: Area konten utama halaman

### 4. Footer
- **Copyright**: Informasi copyright sistem
- **Responsive**: Footer menyesuaikan dengan sidebar

## Permission System

Dashboard menggunakan sistem permission Laravel dengan Spatie Laravel Permission:

### Menu Permissions
- `peminjaman.view` - Akses menu peminjaman
- `sarpras.view` - Akses menu sarana & prasarana
- `user.view` - Akses manajemen user
- `role.view` - Akses role & permission
- `report.view` - Akses laporan
- `system.settings` - Akses pengaturan

### Dashboard Features
- `peminjaman.create` - Tombol ajukan peminjaman
- `peminjaman.approve` - Section pending approvals
- `peminjaman.view` - Section recent peminjaman

## CSS Architecture

### CSS Variables
```css
:root {
    --primary-color: #4e73df;
    --primary-dark: #224abe;
    --success-color: #1cc88a;
    --info-color: #36b9cc;
    --warning-color: #f6c23e;
    --danger-color: #e74a3b;
    --light-color: #f8f9fc;
    --gray-800: #3a3b45;
    /* ... dan lainnya */
}
```

### Component Classes
- `.header` - Header utama
- `.sidebar` - Sidebar navigasi
- `.main-content` - Area konten utama
- `.footer` - Footer
- `.card` - Card component
- `.alert` - Alert component
- `.btn` - Button component

### Responsive Design
- Mobile-first approach
- Breakpoint: 768px untuk mobile
- Sidebar collapse di mobile
- Button group stack di mobile

## JavaScript Architecture

### DashboardManager Class
```javascript
class DashboardManager {
    constructor() {
        this.initializeElements();
        this.bindEvents();
        this.loadDashboardData();
        this.setupAutoRefresh();
    }
    
    // Methods
    toggleSidebar()
    toggleUserDropdown()
    loadDashboardData()
    showAlert(message, type)
    // ... dan lainnya
}
```

### Features
- **Sidebar Toggle**: Toggle sidebar dengan state persistence
- **User Dropdown**: Dropdown menu user dengan click outside
- **Auto-refresh**: Refresh data setiap 30 detik
- **Alert System**: Sistem notifikasi dengan auto-hide
- **Responsive Handling**: Handle resize events
- **API Integration**: Methods untuk fetch data dari API

## Usage

### 1. Layout Monolitik
```php
@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('content')
    <!-- Konten halaman -->
@endsection
```

### 2. Layout Modular
```php
@extends('layouts.dashboard-modular')

@section('title', 'Dashboard')
@section('content')
    <!-- Konten halaman -->
@endsection
```

### 3. Menggunakan Komponen
```php
@include('components.header')
@include('components.sidebar')
@include('components.footer')
```

## Customization

### 1. Menambah Menu Sidebar
Edit `resources/views/components/sidebar.blade.php`:
```php
@can('permission.name')
<div class="menu-item">
    <a href="{{ route('route.name') }}" class="menu-link {{ request()->routeIs('route.*') ? 'active' : '' }}">
        <i class="fas fa-icon"></i>
        <span>Menu Name</span>
    </a>
</div>
@endcan
```

### 2. Menambah Dashboard Widget
Edit halaman dashboard dan tambahkan card:
```html
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Widget Title</h5>
    </div>
    <div class="card-body">
        <!-- Widget content -->
    </div>
</div>
```

### 3. Custom CSS
Tambahkan CSS custom di `@push('styles')`:
```php
@push('styles')
<style>
    .custom-class {
        /* Custom styles */
    }
</style>
@endpush
```

### 4. Custom JavaScript
Tambahkan JavaScript custom di `@push('scripts')`:
```php
@push('scripts')
<script>
    // Custom JavaScript
</script>
@endpush
```

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

### Optimizations
- CSS variables untuk konsistensi
- Vanilla JavaScript (no framework overhead)
- Efficient DOM manipulation
- Lazy loading untuk data dashboard
- Auto-refresh dengan interval yang optimal

### Best Practices
- Minimal DOM queries
- Event delegation
- CSS transitions untuk smooth animations
- Responsive images
- Accessible markup

## Accessibility

### Features
- Keyboard navigation support
- Focus indicators
- ARIA labels
- Screen reader friendly
- High contrast support
- Touch target size (min 40px)

### Implementation
```html
<button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
    <i class="fas fa-bars" aria-hidden="true"></i>
</button>
```

## Troubleshooting

### Common Issues

1. **Sidebar tidak toggle**
   - Pastikan JavaScript dimuat dengan benar
   - Check console untuk error
   - Pastikan element ID benar

2. **Menu tidak muncul**
   - Check permission user
   - Pastikan route sudah didefinisikan
   - Check Laravel's `@can` directive

3. **CSS tidak ter-load**
   - Pastikan file CSS ada di `public/css/`
   - Check path asset di layout
   - Clear browser cache

4. **JavaScript error**
   - Check console untuk error
   - Pastikan semua dependencies loaded
   - Check CSRF token

### Debug Mode
Aktifkan debug mode di JavaScript:
```javascript
window.DashboardManager.debug = true;
```

## Future Enhancements

### Planned Features
- Real-time notifications dengan WebSocket
- Dark mode toggle
- Advanced calendar widget
- Drag & drop untuk dashboard widgets
- Export dashboard data
- Advanced filtering dan search

### Technical Improvements
- Service Worker untuk offline support
- Progressive Web App features
- Advanced caching strategies
- Performance monitoring
- Error tracking

## Support

Untuk pertanyaan atau masalah terkait komponen dashboard, silakan:
1. Check dokumentasi ini
2. Check console browser untuk error
3. Check Laravel logs
4. Contact development team

---

**Dibuat oleh**: Development Team  
**Tanggal**: Desember 2024  
**Versi**: 1.0.0  
**Status**: Production Ready
