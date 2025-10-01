# 🌱 Poliwangi Data Seeder

Dokumentasi untuk seeder data master Poliwangi pada aplikasi peminjaman sarana dan prasarana.

## 📋 Overview

Seeder ini berisi data master yang sesuai dengan struktur akademik Poliwangi:
- **5 Jurusan** dengan 18 Program Studi
- **Units** (Fakultas, Jurusan, Bagian, Laboratorium)
- **Positions** (Dosen, Staff, Teknisi, dll)

## 🗂️ File Seeder

### 1. `PoliwangiDataSeeder.php`
Seeder lengkap untuk semua data master Poliwangi:
- Jurusan (5)
- Prodi (18)
- Units (24)
- Positions (20)

### 2. `PoliwangiJurusanProdiSeeder.php`
Seeder khusus untuk jurusan dan prodi saja:
- Jurusan (5)
- Prodi (18)

### 3. `MasterDataSeeder.php` (Updated)
Seeder master data yang telah diupdate dengan data Poliwangi.

## 🚀 Cara Menjalankan

### Opsi 1: Menggunakan Artisan Command
```bash
# Seeder lengkap
php artisan db:seed --class=PoliwangiDataSeeder

# Hanya jurusan dan prodi
php artisan db:seed --class=PoliwangiJurusanProdiSeeder

# Semua seeder (termasuk roles, admin, dll)
php artisan db:seed
```

### Opsi 2: Menggunakan Script PHP
```bash
php seed_poliwangi.php
```

### Opsi 3: Fresh Migration + Seed
```bash
php artisan migrate:fresh --seed
```

## 📊 Data yang Di-seed

### Jurusan (5)
1. **TEKNIK SIPIL** - 4 prodi
2. **TEKNIK MESIN** - 3 prodi
3. **BISNIS & INFORMATIKA** - 3 prodi
4. **PARIWISATA** - 3 prodi
5. **PERTANIAN** - 6 prodi

### Program Studi (18)
- **D3**: 1 prodi (D3 Teknik Sipil)
- **D4**: 17 prodi (semua prodi lainnya)

### Units (24)
- Fakultas (4)
- Jurusan (5)
- Bagian Administrasi (4)
- Laboratorium (5)
- Unit Pendukung (6)

### Positions (20)
- Dosen (5)
- Kepala (4)
- Administrasi (4)
- Teknis (4)
- Pendukung (3)

## 🔧 Konfigurasi

### Database Seeder Order
```php
// DatabaseSeeder.php
public function run()
{
    $this->call(RolePermissionSeeder::class);      // 1. Roles & Permissions
    $this->call(PoliwangiDataSeeder::class);       // 2. Master Data
    $this->call(AdminUserSeeder::class);           // 3. Admin User
}
```

### ID Assignment
- **Jurusan ID**: 1-5 (hardcoded untuk konsistensi)
- **Prodi ID**: 1-19 (hardcoded untuk konsistensi)
- **Unit ID**: Auto-increment
- **Position ID**: Auto-increment

## ⚠️ Important Notes

### Data Clearing
Seeder akan **menghapus semua data existing** sebelum menambahkan data baru:
```php
Prodi::truncate();
Jurusan::truncate();
Unit::truncate();
Position::truncate();
```

### Foreign Key Constraints
Pastikan tidak ada data yang bergantung pada tabel ini sebelum menjalankan seeder.

### Production Deployment
Untuk production, gunakan migration dengan data seed:
```bash
php artisan migrate --seed
```

## 🧪 Testing

### Verifikasi Data
Setelah seeder dijalankan, verifikasi data:
```sql
-- Cek jumlah jurusan
SELECT COUNT(*) as total_jurusan FROM jurusan;

-- Cek jumlah prodi per jurusan
SELECT j.nama_jurusan, COUNT(p.id) as jumlah_prodi
FROM jurusan j
LEFT JOIN prodi p ON j.id = p.jurusan_id
GROUP BY j.id, j.nama_jurusan;

-- Cek distribusi jenjang
SELECT jenjang, COUNT(*) as jumlah
FROM prodi
GROUP BY jenjang;
```

### Expected Results
- **Total Jurusan**: 5
- **Total Prodi**: 18
- **D3 Prodi**: 1
- **D4 Prodi**: 17

## 🔄 Maintenance

### Update Data
Untuk mengupdate data, edit file seeder yang sesuai dan jalankan ulang.

### Backup Data
Sebelum menjalankan seeder, backup data existing:
```bash
mysqldump -u username -p database_name > backup_before_seed.sql
```

### Rollback
Jika perlu rollback, restore dari backup atau jalankan:
```sql
DELETE FROM prodi;
DELETE FROM jurusan;
DELETE FROM units;
DELETE FROM positions;
```

## 📝 Log Output

Seeder akan menampilkan log progress:
```
🌱 Seeding data Poliwangi...
🗑️  Clearing existing data...
📚 Seeding 5 jurusan...
   ✓ TEKNIK SIPIL
   ✓ TEKNIK MESIN
   ✓ BISNIS & INFORMATIKA
   ✓ PARIWISATA
   ✓ PERTANIAN
🎓 Seeding 18 program studi...
   ✓ D3 Teknik Sipil (D3)
   ✓ Manajemen Konstruksi (D4)
   ...
✅ Data Poliwangi berhasil di-seed!
```

## 🎯 Usage dalam Aplikasi

Data ini digunakan untuk:
- **Setup Profil Mahasiswa**: Dropdown jurusan dan prodi
- **Validasi Cross-Reference**: Prodi harus sesuai dengan jurusan
- **Laporan dan Analytics**: Grouping berdasarkan jurusan/prodi
- **Master Data Management**: CRUD operations oleh admin

## 🆘 Troubleshooting

### Error: Foreign Key Constraint
Pastikan tidak ada data yang bergantung pada tabel jurusan/prodi.

### Error: Duplicate Entry
Data sudah ada, gunakan `truncate()` atau `delete()` terlebih dahulu.

### Error: Model Not Found
Pastikan model `Jurusan` dan `Prodi` sudah dibuat dan terdaftar.

## 📞 Support

Jika ada masalah dengan seeder, periksa:
1. Database connection
2. Model existence
3. Migration status
4. Foreign key constraints

---

**Created**: 2024-12-19  
**Version**: 1.0  
**Status**: Ready for Production
