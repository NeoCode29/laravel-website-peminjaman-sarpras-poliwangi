# 🔧 Foreign Key Constraint Troubleshooting

Dokumentasi untuk mengatasi masalah foreign key constraint pada seeder Poliwangi.

## ❌ Error yang Terjadi

```
SQLSTATE[42000]: Syntax error or access violation: 1701 
Cannot truncate a table referenced in a foreign key constraint 
(`core`.`students`, CONSTRAINT `students_prodi_id_foreign`) 
(SQL: truncate table `prodi`)
```

## 🔍 Penyebab Masalah

1. **Foreign Key Constraints**: Tabel `prodi` memiliki foreign key constraint dari tabel `students`
2. **Truncate Operation**: `TRUNCATE` tidak bisa dijalankan pada tabel yang memiliki foreign key references
3. **Dependency Chain**: Ada data yang bergantung pada tabel yang akan di-truncate

## ✅ Solusi yang Tersedia

### 1. **PoliwangiUpdateSeeder** (Recommended)
```bash
php artisan db:seed --class=PoliwangiUpdateSeeder
```

**Keunggulan:**
- ✅ Menggunakan `updateOrCreate()` - tidak ada foreign key issues
- ✅ Aman untuk production
- ✅ Tidak menghapus data existing
- ✅ Update data jika sudah ada, create jika belum ada

### 2. **PoliwangiSafeSeeder** (Alternative)
```bash
php artisan db:seed --class=PoliwangiSafeSeeder
```

**Keunggulan:**
- ✅ Menangani foreign key constraints dengan aman
- ✅ Fallback mechanism jika ada error
- ✅ Clear dependent tables terlebih dahulu

### 3. **Manual Database Approach**
```sql
-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS=0;

-- Clear tables
TRUNCATE TABLE prodi;
TRUNCATE TABLE jurusan;
TRUNCATE TABLE units;
TRUNCATE TABLE positions;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;
```

## 🚀 Cara Menjalankan Seeder yang Benar

### Opsi 1: Menggunakan Update Seeder (Paling Aman)
```bash
# Hanya jurusan dan prodi
php artisan db:seed --class=PoliwangiUpdateSeeder

# Atau melalui DatabaseSeeder
php artisan db:seed
```

### Opsi 2: Fresh Migration (Jika Database Kosong)
```bash
php artisan migrate:fresh --seed
```

### Opsi 3: Manual Clear + Seed
```bash
# Clear data manual terlebih dahulu
php artisan tinker
>>> App\Models\Student::query()->delete();
>>> App\Models\StaffEmployee::query()->delete();
>>> App\Models\Prodi::query()->delete();
>>> App\Models\Jurusan::query()->delete();
>>> exit

# Kemudian jalankan seeder
php artisan db:seed --class=PoliwangiDataSeeder
```

## 🔧 Perbaikan yang Telah Dilakukan

### 1. **PoliwangiDataSeeder.php**
- ✅ Menambahkan `SET FOREIGN_KEY_CHECKS=0;` sebelum truncate
- ✅ Menambahkan `SET FOREIGN_KEY_CHECKS=1;` setelah truncate

### 2. **PoliwangiJurusanProdiSeeder.php**
- ✅ Menambahkan foreign key checks disable/enable

### 3. **PoliwangiSafeSeeder.php** (New)
- ✅ Menggunakan `delete()` instead of `truncate()`
- ✅ Clear dependent tables terlebih dahulu
- ✅ Fallback mechanism dengan foreign key disable

### 4. **PoliwangiUpdateSeeder.php** (New)
- ✅ Menggunakan `updateOrCreate()` - tidak ada foreign key issues
- ✅ Paling aman untuk production

## 📋 Checklist Troubleshooting

### Sebelum Menjalankan Seeder:
- [ ] Pastikan database connection berfungsi
- [ ] Pastikan tabel `jurusan`, `prodi`, `units`, `positions` ada
- [ ] Pastikan tidak ada data penting yang akan terhapus
- [ ] Backup database jika diperlukan

### Jika Error Masih Terjadi:
- [ ] Cek apakah ada tabel lain yang bergantung pada `prodi` atau `jurusan`
- [ ] Gunakan `PoliwangiUpdateSeeder` sebagai alternatif
- [ ] Cek foreign key constraints di database
- [ ] Pastikan model `Jurusan` dan `Prodi` sudah dibuat

### Setelah Seeder Berhasil:
- [ ] Verifikasi data dengan query SQL
- [ ] Test dropdown jurusan/prodi di aplikasi
- [ ] Pastikan tidak ada error di log

## 🧪 Testing Seeder

### Test Query untuk Verifikasi:
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

### Expected Results:
- **Total Jurusan**: 5
- **Total Prodi**: 18
- **D3 Prodi**: 1
- **D4 Prodi**: 17

## 🆘 Emergency Recovery

### Jika Seeder Gagal:
```bash
# Rollback migration
php artisan migrate:rollback

# Atau reset database
php artisan migrate:fresh

# Kemudian jalankan seeder yang aman
php artisan db:seed --class=PoliwangiUpdateSeeder
```

### Jika Data Corrupt:
```sql
-- Manual cleanup
DELETE FROM students WHERE prodi_id IS NOT NULL;
DELETE FROM prodi;
DELETE FROM jurusan;
```

## 📞 Support

Jika masalah masih terjadi:
1. Cek log Laravel di `storage/logs/laravel.log`
2. Cek database error log
3. Pastikan semua model dan migration sudah benar
4. Gunakan `PoliwangiUpdateSeeder` sebagai solusi terakhir

---

**Status**: ✅ Fixed  
**Last Updated**: 2024-12-19  
**Tested**: ✅ Working
