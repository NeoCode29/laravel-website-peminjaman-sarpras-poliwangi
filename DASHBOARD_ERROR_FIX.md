# 🔧 Dashboard Error Fix Guide

## ❌ **Error yang Terjadi:**

```
Illuminate\Database\QueryException
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'core.peminjaman' doesn't exist
```

## 🎯 **Penyebab Error:**

Error ini terjadi karena tabel-tabel yang diperlukan untuk dashboard belum ada di database. Dashboard memerlukan tabel-tabel berikut:

- `peminjaman` - Tabel peminjaman
- `prasarana` - Tabel prasarana
- `kategori_prasarana` - Tabel kategori prasarana
- `notifications` - Tabel notifikasi
- `audit_logs` - Tabel log aktivitas
- `user_quotas` - Tabel kuota user

## 🚀 **Solusi:**

### **1. Jalankan Migration dan Seeder:**

```bash
# Dari direktori project_baru
php artisan migrate --force
php artisan db:seed --class=DashboardSeeder --force
```

### **2. Atau Gunakan Script Otomatis:**

```bash
# Jalankan script fix
php fix_dashboard_error.php
```

### **3. Atau Jalankan Manual:**

```bash
# Step 1: Run migrations
php artisan migrate --force

# Step 2: Run dashboard seeder
php artisan db:seed --class=DashboardSeeder --force

# Step 3: Check tables
php artisan tinker --execute="echo 'Tables: ' . count(DB::select('SHOW TABLES'));"
```

## 📊 **Tabel yang Akan Dibuat:**

### **1. Tabel Peminjaman:**
```sql
CREATE TABLE peminjaman (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    prasarana_id BIGINT NULL,
    event_name VARCHAR(255) NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    status ENUM('pending', 'approved', 'rejected', 'picked_up', 'returned', 'cancelled') DEFAULT 'pending',
    surat_path VARCHAR(255) NULL,
    rejection_reason TEXT NULL,
    approved_by BIGINT NULL,
    approved_at TIMESTAMP NULL,
    pickup_validated_by BIGINT NULL,
    pickup_validated_at TIMESTAMP NULL,
    return_validated_by BIGINT NULL,
    return_validated_at TIMESTAMP NULL,
    cancelled_by BIGINT NULL,
    cancelled_reason TEXT NULL,
    cancelled_at TIMESTAMP NULL,
    foto_pickup_path VARCHAR(255) NULL,
    foto_return_path VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prasarana_id) REFERENCES prasarana(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (pickup_validated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (return_validated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL
);
```

### **2. Tabel Prasarana:**
```sql
CREATE TABLE prasarana (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    kategori_id BIGINT NOT NULL,
    description TEXT NULL,
    status ENUM('tersedia', 'rusak', 'maintenance') DEFAULT 'tersedia',
    kapasitas INT NULL,
    lokasi VARCHAR(255) NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (kategori_id) REFERENCES kategori_prasarana(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### **3. Tabel Kategori Prasarana:**
```sql
CREATE TABLE kategori_prasarana (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    icon VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### **4. Tabel Notifications:**
```sql
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    action_url VARCHAR(500) NULL,
    is_clickable BOOLEAN DEFAULT TRUE,
    read_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### **5. Tabel Audit Logs:**
```sql
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NULL,
    action VARCHAR(100) NOT NULL,
    model_type VARCHAR(100) NOT NULL,
    model_id BIGINT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### **6. Tabel User Quotas:**
```sql
CREATE TABLE user_quotas (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    max_active_borrowings INT DEFAULT 3,
    current_borrowings INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🌱 **Data Sample yang Akan Diisi:**

### **Kategori Prasarana:**
- Aula
- Ruang Meeting
- Laboratorium
- Ruang Kelas

### **Prasarana:**
- Aula Utama (kapasitas 200)
- Ruang Meeting 1 (kapasitas 20)
- Laboratorium Komputer (kapasitas 30)

### **Peminjaman Sample:**
- Seminar Teknologi (pending)
- Rapat Koordinasi (approved)

### **Notifikasi Sample:**
- Peminjaman Disetujui
- Peminjaman Menunggu Persetujuan

### **Audit Logs Sample:**
- Peminjaman baru dibuat
- Peminjaman disetujui oleh admin

### **User Quotas:**
- Admin: 10 peminjaman aktif
- Peminjam: 3 peminjaman aktif

## 🔍 **Verifikasi:**

Setelah menjalankan migration dan seeder, verifikasi dengan:

```bash
# Check tables
php artisan tinker --execute="echo 'Tables: ' . count(DB::select('SHOW TABLES'));"

# Check peminjaman table
php artisan tinker --execute="echo 'Peminjaman count: ' . DB::table('peminjaman')->count();"

# Check prasarana table
php artisan tinker --execute="echo 'Prasarana count: ' . DB::table('prasarana')->count();"
```

## 🎉 **Hasil:**

Setelah menjalankan solusi di atas, dashboard akan berfungsi dengan baik dan menampilkan:

- **Dashboard Peminjam**: Statistik personal, peminjaman aktif, sarana tersedia
- **Dashboard Admin**: Statistik sistem, pending approvals, aktivitas terbaru
- **Data Sample**: Data contoh untuk testing

## 🚨 **Troubleshooting:**

### **Jika masih error:**

1. **Check Database Connection:**
   ```bash
   php artisan tinker --execute="echo 'DB Connected: ' . (DB::connection()->getPdo() ? 'Yes' : 'No');"
   ```

2. **Check Migration Status:**
   ```bash
   php artisan migrate:status
   ```

3. **Check Seeder Status:**
   ```bash
   php artisan db:seed --class=DashboardSeeder --force
   ```

4. **Clear Cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

## 📞 **Support:**

Jika masih mengalami masalah, silakan hubungi:
- **Email**: admin@poliwangi.ac.id
- **Documentation**: DASHBOARD_README.md

---

**Dashboard Error Fix Status: Ready** ✅
