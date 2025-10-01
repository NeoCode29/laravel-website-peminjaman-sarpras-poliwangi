# 📊 Dashboard Sistem Peminjaman Sarpras

## 🎯 **Overview**

Dashboard sistem peminjaman sarana dan prasarana Poliwangi yang dirancang khusus untuk memberikan pengalaman pengguna yang optimal berdasarkan role pengguna.

## 🏗️ **Struktur Dashboard**

### **1. Dashboard Peminjam** (`/dashboard` - Role: `peminjam`)

#### **Fitur Utama:**
- **Statistik Personal**: Total peminjaman, peminjaman aktif, menunggu persetujuan, kuota tersisa
- **Peminjaman Aktif**: Daftar peminjaman yang sedang berjalan
- **Sarana Tersedia**: Quick access ke sarana yang bisa dipinjam
- **Notifikasi**: Notifikasi personal user
- **Acara Mendatang**: Jadwal peminjaman yang akan datang
- **Aksi Cepat**: Tombol untuk fitur-fitur utama

#### **Komponen:**
```php
// Controller: DashboardController@index()
// View: resources/views/dashboard/peminjam.blade.php
// Route: /dashboard (middleware: auth, user.not.blocked, profile.completed)
```

### **2. Dashboard Admin** (`/dashboard` - Role: `admin`)

#### **Fitur Utama:**
- **Statistik Sistem**: Total user, sarana, prasarana, peminjaman, dll
- **System Alerts**: Peringatan sistem (user diblokir, pending approvals)
- **Menunggu Persetujuan**: Daftar pengajuan yang perlu disetujui
- **Aktivitas Terbaru**: Log aktivitas sistem
- **Acara Mendatang**: Jadwal peminjaman sistem
- **Status Sistem**: Health check database, cache, SSO
- **Aksi Cepat**: Tombol untuk fitur admin

#### **Komponen:**
```php
// Controller: DashboardController@index()
// View: resources/views/dashboard/admin.blade.php
// Route: /dashboard (middleware: auth, user.not.blocked, profile.completed)
```

## 🔧 **Implementasi Teknis**

### **Controller Structure:**
```php
DashboardController
├── index()                    // Main dashboard route
├── adminDashboard()          // Admin dashboard logic
├── peminjamDashboard()       // Peminjam dashboard logic
├── getAdminStats()           // Admin statistics
├── getPeminjamStats()        // Peminjam statistics
├── getRecentActivities()     // System activities
├── getUserRecentActivities() // User activities
├── getPendingApprovals()     // Pending approvals
├── getActiveBorrowings()     // User active borrowings
├── getSystemAlerts()         // System alerts
├── getUserNotifications()    // User notifications
├── getAvailableSarpras()     // Available sarpras
├── getUpcomingEvents()       // Upcoming events
└── getSystemHealth()         // System health check
```

### **Route Structure:**
```php
// Main dashboard route
Route::get('/dashboard', 'DashboardController@index')
    ->middleware(['auth', 'user.not.blocked', 'profile.completed'])
    ->name('dashboard');

// API endpoints
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
    Route::get('/activities', [DashboardController::class, 'getActivities'])->name('activities');
    Route::get('/notifications', [DashboardController::class, 'getNotifications'])->name('notifications');
});
```

### **View Structure:**
```
resources/views/
├── layouts/
│   └── app.blade.php          // Main layout with sidebar
├── dashboard/
│   ├── peminjam.blade.php     // Peminjam dashboard
│   └── admin.blade.php        // Admin dashboard
└── [other views...]
```

## 🎨 **Design Features**

### **Responsive Design:**
- **Mobile First**: Optimized for mobile devices
- **Bootstrap 5**: Modern CSS framework
- **Font Awesome**: Icon library
- **Custom CSS**: Poliwangi branding

### **Color Scheme:**
- **Primary**: #4e73df (Blue)
- **Success**: #1cc88a (Green)
- **Warning**: #f6c23e (Yellow)
- **Danger**: #e74a3b (Red)
- **Info**: #36b9cc (Cyan)

### **Layout Components:**
- **Sidebar Navigation**: Role-based menu
- **Statistics Cards**: Color-coded metrics
- **Data Tables**: Responsive tables
- **Timeline**: Activity feed
- **Alerts**: System notifications
- **Quick Actions**: Fast access buttons

## 🔐 **Security Features**

### **Middleware Protection:**
- `auth`: User must be authenticated
- `user.not.blocked`: User must not be blocked
- `profile.completed`: User profile must be complete

### **Role-Based Access:**
- **Admin**: Full system access
- **Peminjam**: Limited to borrowing features
- **Permission-based**: Granular permission control

### **Data Validation:**
- **Server-side validation**: All data validated on server
- **CSRF protection**: All forms protected
- **XSS prevention**: Output escaped

## 📊 **Statistics & Metrics**

### **Admin Statistics:**
- Total Users (Active)
- Total Sarana
- Total Prasarana
- Pending Borrowings
- Active Borrowings
- Borrowings Today
- Blocked Users
- System Health (%)

### **Peminjam Statistics:**
- Total Borrowings
- Active Borrowings
- Completed Borrowings
- Pending Borrowings
- Rejected Borrowings
- Quota Used/Limit

## 🚀 **Performance Features**

### **Database Optimization:**
- **Efficient Queries**: Optimized database queries
- **Index Usage**: Proper database indexing
- **Query Caching**: Cached frequently accessed data

### **Frontend Optimization:**
- **Lazy Loading**: Images and components
- **Minified Assets**: Compressed CSS/JS
- **CDN Usage**: External libraries from CDN

## 🔄 **Real-time Features**

### **Auto-refresh:**
- **Statistics**: Auto-update every 30 seconds
- **Notifications**: Real-time notification updates
- **Activities**: Live activity feed

### **Interactive Elements:**
- **AJAX Loading**: Smooth data loading
- **Modal Dialogs**: Interactive popups
- **Toast Notifications**: User feedback

## 📱 **Mobile Responsiveness**

### **Breakpoints:**
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### **Mobile Features:**
- **Collapsible Sidebar**: Mobile-friendly navigation
- **Touch-friendly**: Large touch targets
- **Swipe Gestures**: Mobile interactions

## 🛠️ **Development Notes**

### **File Structure:**
```
project_baru/
├── app/Http/Controllers/
│   └── DashboardController.php
├── resources/views/
│   ├── layouts/app.blade.php
│   └── dashboard/
│       ├── peminjam.blade.php
│       └── admin.blade.php
├── routes/
│   ├── web.php
│   ├── dashboard.php
│   └── placeholder.php
└── DASHBOARD_README.md
```

### **Dependencies:**
- **Laravel 8+**: PHP framework
- **Bootstrap 5**: CSS framework
- **Font Awesome 6**: Icon library
- **Spatie Permission**: Role management

### **Browser Support:**
- **Chrome**: 90+
- **Firefox**: 88+
- **Safari**: 14+
- **Edge**: 90+

## 🎯 **Future Enhancements**

### **Planned Features:**
- **Real-time Notifications**: WebSocket integration
- **Advanced Analytics**: Charts and graphs
- **Export Functionality**: PDF/Excel export
- **Dark Mode**: Theme switching
- **Multi-language**: Internationalization

### **Performance Improvements:**
- **Redis Caching**: Advanced caching
- **Database Optimization**: Query optimization
- **CDN Integration**: Asset delivery
- **Progressive Web App**: PWA features

## 📝 **Usage Examples**

### **Accessing Dashboard:**
```php
// Redirect to dashboard after login
return redirect()->route('dashboard');

// Check if user can access dashboard
if (auth()->user()->can('dashboard.view')) {
    return view('dashboard');
}
```

### **Getting Statistics:**
```php
// Get admin statistics
$stats = $this->getAdminStats();

// Get user statistics
$stats = $this->getPeminjamStats($userId);
```

### **Checking Permissions:**
```php
// Check if user is admin
if (auth()->user()->hasRole('admin')) {
    return $this->adminDashboard();
}

// Check specific permission
if (auth()->user()->can('peminjaman.view')) {
    // Show peminjaman features
}
```

## 🐛 **Troubleshooting**

### **Common Issues:**
1. **Dashboard not loading**: Check middleware and authentication
2. **Statistics not showing**: Verify database connections
3. **Permissions denied**: Check role assignments
4. **Mobile layout issues**: Verify responsive CSS

### **Debug Mode:**
```php
// Enable debug mode in .env
APP_DEBUG=true
LOG_LEVEL=debug
```

## 📞 **Support**

Untuk pertanyaan atau masalah terkait dashboard, silakan hubungi:
- **Email**: admin@poliwangi.ac.id
- **Documentation**: [Link ke dokumentasi lengkap]
- **Issue Tracker**: [Link ke issue tracker]

---

**Dibuat dengan ❤️ untuk Poliwangi**
