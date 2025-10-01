# 🔧 Dashboard Migration Fix Guide

## ❌ **Error yang Terjadi:**

```
SQLSTATE[HY000]: General error: 1824 Failed to open the referenced table 'prasarana'
```

## 🎯 **Penyebab Error:**

Error ini terjadi karena urutan migration yang salah. Tabel `peminjaman` mencoba membuat foreign key ke tabel `prasarana` yang belum ada.

## 🚀 **Solusi:**

### **Opsi 1: Quick Fix (Recommended)**
```bash
cd project_baru
php quick_fix.php
```

### **Opsi 2: Manual Step by Step**
```bash
cd project_baru

# 1. Rollback all migrations
php artisan migrate:reset --force

# 2. Run all migrations
php artisan migrate --force

# 3. Run dashboard seeder
php artisan db:seed --class=DashboardSeeder --force
```

### **Opsi 3: Step by Step Migration**
```bash
cd project_baru
php run_migrations_step_by_step.php
```

## 📊 **Urutan Migration yang Benar:**

1. **kategori_prasarana** - Tabel kategori prasarana
2. **prasarana** - Tabel prasarana
3. **peminjaman** - Tabel peminjaman (tanpa foreign key ke prasarana)
4. **add_prasarana_foreign_key** - Menambahkan foreign key ke prasarana
5. **notifications** - Tabel notifikasi
6. **audit_logs** - Tabel log aktivitas
7. **user_quotas** - Tabel kuota user

## 🔧 **Perbaikan yang Dilakukan:**

### **1. Migration Peminjaman:**
- Menghapus foreign key ke `prasarana` dari migration awal
- Membuat migration terpisah untuk menambahkan foreign key setelah tabel `prasarana` dibuat

### **2. Migration Urutan:**
- `2025_09_28_150200_create_kategori_prasarana_table.php`
- `2025_09_28_150100_create_prasarana_table.php`
- `2025_09_28_150000_create_peminjaman_table.php`
- `2025_09_28_150600_add_prasarana_foreign_key_to_peminjaman.php`
- `2025_09_28_150300_create_notifications_table.php`
- `2025_09_28_150400_create_audit_logs_table.php`
- `2025_09_28_150500_create_user_quotas_table.php`

## 🌱 **Data yang Akan Diisi:**

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

Setelah menjalankan solusi, verifikasi dengan:

```bash
# Check tables
php artisan tinker --execute="echo 'Tables: ' . count(DB::select('SHOW TABLES'));"

# Check peminjaman table
php artisan tinker --execute="echo 'Peminjaman count: ' . DB::table('peminjaman')->count();"

# Check prasarana table
php artisan tinker --execute="echo 'Prasarana count: ' . DB::table('prasarana')->count();"

# Check foreign keys
php artisan tinker --execute="echo 'Foreign keys: ' . count(DB::select('SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = \"peminjaman\" AND REFERENCED_TABLE_NAME IS NOT NULL'));"
```

## 🎉 **Hasil:**

Setelah menjalankan solusi di atas, dashboard akan berfungsi dengan baik dan menampilkan:

- **Dashboard Peminjam**: Statistik personal, peminjaman aktif, sarana tersedia
- **Dashboard Admin**: Statistik sistem, pending approvals, aktivitas terbaru
- **Data Sample**: Data contoh untuk testing

## 🚨 **Troubleshooting:**

### **Jika masih error:**

1. **Check Migration Status:**
   ```bash
   php artisan migrate:status
   ```

2. **Check Database Connection:**
   ```bash
   php artisan tinker --execute="echo 'DB Connected: ' . (DB::connection()->getPdo() ? 'Yes' : 'No');"
   ```

3. **Clear Cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

4. **Check Foreign Keys:**
   ```bash
   php artisan tinker --execute="echo 'Foreign keys: ' . count(DB::select('SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = \"peminjaman\" AND REFERENCED_TABLE_NAME IS NOT NULL'));"
   ```

## 📞 **Support:**

Jika masih mengalami masalah, silakan hubungi:
- **Email**: admin@poliwangi.ac.id
- **Documentation**: DASHBOARD_README.md

---

**Dashboard Migration Fix Status: Ready** ✅
