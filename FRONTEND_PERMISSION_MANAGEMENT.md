# Frontend Permission Management

Dokumentasi untuk frontend manajemen permission, role, dan role-permission matrix yang telah dibuat berdasarkan backend Laravel dan style guide yang ada.

## Struktur File

### Views (Blade Templates)
- `resources/views/permission-management/`
  - `index.blade.php` - Halaman daftar permission
  - `create.blade.php` - Form tambah permission
  - `edit.blade.php` - Form edit permission
  - `show.blade.php` - Detail permission
- `resources/views/role-management/`
  - `index.blade.php` - Halaman daftar role
  - `create.blade.php` - Form tambah role
  - `edit.blade.php` - Form edit role
  - `show.blade.php` - Detail role
- `resources/views/role-permission-matrix/`
  - `index.blade.php` - Matrix permission per role

### CSS Styles
- `public/css/components/permission-management.css` - Styles untuk permission management
- `public/css/components/role-management.css` - Styles untuk role management
- `public/css/components/role-permission-matrix.css` - Styles untuk role-permission matrix

### JavaScript
- `public/js/role-permission-matrix.js` - JavaScript untuk interaksi matrix

## Fitur yang Diimplementasikan

### 1. Permission Management
- **Daftar Permission**: Tabel dengan pagination, search, dan filter
- **Tambah Permission**: Form untuk menambah permission baru
- **Edit Permission**: Form untuk mengedit permission yang ada
- **Detail Permission**: Tampilan detail permission
- **Toggle Status**: Mengaktifkan/menonaktifkan permission
- **Hapus Permission**: Soft delete permission

### 2. Role Management
- **Daftar Role**: Tabel dengan pagination, search, dan filter
- **Tambah Role**: Form untuk menambah role baru dengan assignment permission
- **Edit Role**: Form untuk mengedit role dan permission yang ditugaskan
- **Detail Role**: Tampilan detail role beserta permission yang ditugaskan
- **Toggle Status**: Mengaktifkan/menonaktifkan role
- **Hapus Role**: Soft delete role

### 3. Role Permission Matrix
- **Matrix View**: Tampilan matrix role vs permission
- **Individual Update**: Update permission per role secara individual
- **Bulk Update**: Update multiple permission untuk role tertentu
- **Category Management**: Kelola permission berdasarkan kategori
- **Statistics**: Statistik permission per role
- **Quick Actions**: Aksi cepat untuk select all/clear all

## Komponen UI yang Digunakan

### Berdasarkan Style Guide
- `card-main` - Container utama
- `card` - Card untuk konten
- `table` - Tabel data
- `form-group` - Group form input
- `search-input` - Input pencarian
- `btn` - Button dengan berbagai variant
- `badge` - Badge untuk status
- `pagination` - Pagination
- `dialog-backdrop` - Modal backdrop
- `empty-state` - State kosong

### Custom Components
- `matrix-table` - Tabel matrix khusus
- `permission-checkbox` - Checkbox untuk permission
- `role-header` - Header untuk role
- `category-header` - Header untuk kategori
- `bulk-actions` - Aksi bulk
- `notification` - Notifikasi sistem

## Integrasi Backend

### Routes yang Digunakan
```php
// Permission Management
GET    /permission-management
GET    /permission-management/create
POST   /permission-management
GET    /permission-management/{id}
GET    /permission-management/{id}/edit
PUT    /permission-management/{id}
DELETE /permission-management/{id}
POST   /permission-management/{id}/toggle-status

// Role Management
GET    /role-management
GET    /role-management/create
POST   /role-management
GET    /role-management/{id}
GET    /role-management/{id}/edit
PUT    /role-management/{id}
DELETE /role-management/{id}
POST   /role-management/{id}/toggle-status
POST   /role-management/bulk-toggle-status

// Role Permission Matrix
GET    /role-permission-matrix
POST   /role-permission-matrix/update-role-permissions
POST   /role-permission-matrix/bulk-update-role-permissions
GET    /role-permission-matrix/get-role-permissions/{roleId}
```

### Controllers
- `PermissionManagementController` - Handle CRUD permission
- `RoleManagementController` - Handle CRUD role
- `RolePermissionMatrixController` - Handle matrix operations

## Styling

### CSS Variables
Menggunakan CSS variables berdasarkan style guide:
- `--color-primary` - Warna utama
- `--color-success` - Warna sukses
- `--color-error` - Warna error
- `--color-warning` - Warna warning
- `--color-info` - Warna info
- `--color-text-primary` - Warna teks utama
- `--color-text-secondary` - Warna teks sekunder
- `--color-background-light` - Warna background terang

### Responsive Design
- Mobile-first approach
- Breakpoint di 768px untuk mobile
- Tabel dengan horizontal scroll di mobile
- Button full width di mobile

## JavaScript Functionality

### Role Permission Matrix
- `loadRolePermissions(roleId)` - Load permissions untuk role tertentu
- `selectAllPermissions()` - Select semua permission
- `clearAllPermissions()` - Clear semua permission
- `makeRequest(url, method, data)` - Helper untuk HTTP request
- `showNotification(message, type)` - Tampilkan notifikasi
- `getNotificationIcon(type)` - Get icon untuk notifikasi

### Event Handlers
- Checkbox change untuk individual permission
- Category checkbox change untuk bulk update
- Form submission untuk bulk actions
- Search input dengan debounce
- Filter change untuk kategori

## Keamanan

### CSRF Protection
- Semua form menggunakan CSRF token
- JavaScript request menggunakan CSRF token dari meta tag

### Permission Middleware
- Semua route menggunakan `permission:user.role_edit`
- Validasi permission di backend

### Input Validation
- Client-side validation untuk form
- Server-side validation di controller
- Sanitasi input untuk mencegah XSS

## Testing

### Manual Testing
1. **Permission Management**
   - Test CRUD operations
   - Test search dan filter
   - Test pagination
   - Test toggle status
   - Test delete confirmation

2. **Role Management**
   - Test CRUD operations
   - Test permission assignment
   - Test search dan filter
   - Test pagination
   - Test toggle status
   - Test delete confirmation

3. **Role Permission Matrix**
   - Test individual permission update
   - Test bulk permission update
   - Test category management
   - Test statistics display
   - Test quick actions

### Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Deployment

### Prerequisites
- Laravel 8+ dengan Spatie Laravel Permission
- PHP 7.4+
- MySQL 5.7+ atau MariaDB 10.2+

### Setup
1. Pastikan semua route sudah terdaftar di `web.php`
2. Pastikan controller sudah dibuat
3. Pastikan model sudah dikonfigurasi
4. Pastikan CSS dan JS sudah di-compile
5. Pastikan permission sudah di-seed

### Production Considerations
- Minify CSS dan JS
- Enable caching untuk static assets
- Configure proper error handling
- Monitor performance
- Setup logging untuk audit trail

## Maintenance

### Regular Tasks
- Monitor permission usage
- Review role assignments
- Update permission list sesuai kebutuhan
- Backup database regularly
- Monitor system performance

### Troubleshooting
- Check browser console untuk JavaScript errors
- Check Laravel logs untuk server errors
- Verify CSRF token configuration
- Check permission middleware
- Verify database connections

## Future Enhancements

### Planned Features
- Export/Import permission matrix
- Permission templates
- Advanced filtering options
- Audit log viewer
- Permission usage analytics
- Role hierarchy support

### Technical Improvements
- Implement WebSocket untuk real-time updates
- Add unit tests untuk JavaScript
- Implement caching untuk permission checks
- Add keyboard shortcuts
- Improve accessibility (ARIA labels)
- Add dark mode support
