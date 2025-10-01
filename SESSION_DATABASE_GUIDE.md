# Session Database Management Guide

## Overview
Aplikasi telah dikonfigurasi untuk menggunakan database sebagai penyimpanan session, menggantikan file-based session storage.

## Konfigurasi

### 1. File Konfigurasi
- **File**: `config/session.php`
- **Driver**: `database` (baris 21)
- **Table**: `sessions` (baris 88)
- **Lifetime**: 240 menit (4 jam) - dikonfigurasi di `.env`

### 2. Environment Variables (.env)
```env
SESSION_DRIVER=database
SESSION_LIFETIME=240
```

### 3. Database Table
Tabel `sessions` sudah dibuat dengan struktur:
- `id` (string, primary key) - Session ID
- `user_id` (foreign key, nullable) - ID user yang login
- `ip_address` (string, 45 chars) - IP address user
- `user_agent` (text) - User agent browser
- `payload` (longText) - Data session yang dienkripsi
- `last_activity` (integer) - Timestamp aktivitas terakhir

## Keuntungan Session Database

1. **Scalability**: Dapat menangani multiple server instances
2. **Persistence**: Session data tersimpan di database, tidak hilang saat server restart
3. **Security**: Data session dienkripsi sebelum disimpan
4. **Monitoring**: Dapat memantau session aktif melalui database
5. **Cleanup**: Session expired dapat dibersihkan otomatis

## Verifikasi

Session database telah ditest dan berfungsi dengan baik:
- ✅ Database connection berhasil
- ✅ Session data tersimpan di tabel `sessions`
- ✅ Data session terenkripsi dengan aman
- ✅ Session dapat diambil dan digunakan normal

## Maintenance

### Membersihkan Session Expired
```bash
php artisan session:gc
```

### Melihat Session Aktif
```sql
SELECT id, user_id, ip_address, last_activity 
FROM sessions 
WHERE last_activity > UNIX_TIMESTAMP() - 14400;
```

## Catatan Penting

1. Pastikan database connection berfungsi dengan baik
2. Session lifetime dikonfigurasi 4 jam (240 menit)
3. Data session dienkripsi untuk keamanan
4. Session akan otomatis dibersihkan berdasarkan `last_activity`
