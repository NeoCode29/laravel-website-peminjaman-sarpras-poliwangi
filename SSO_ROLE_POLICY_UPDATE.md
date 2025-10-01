# 🔄 SSO ROLE ASSIGNMENT POLICY UPDATE

## 📋 **Perubahan Kebijakan Role Assignment untuk SSO Login**

### 🎯 **Kebijakan Baru:**
**Semua user yang login via SSO akan selalu mendapat role `peminjam` (ID: 2) tanpa mempertimbangkan data SSO.**

### ❌ **Kebijakan Lama (Dihapus):**
```php
// Berdasarkan staff code dari SSO data
switch ($ssoData['staff']) {
    case 0: return 'admin';        // Admin
    case 3: return 'staff';        // PLP  
    case 4: return 'staff';        // Dosen
    case 999: return 'peminjam';   // Mahasiswa
    default: return 'peminjam';
}
```

### ✅ **Kebijakan Baru:**
```php
// Semua user SSO selalu mendapat role peminjam
return 'peminjam';
```

## 🔧 **File yang Diubah:**

### 1. **OAuthService.php**
- **Method**: `getRoleNameFromSSO()`
- **Perubahan**: Menghapus logika berdasarkan `staff` code
- **Hasil**: Selalu return `'peminjam'`

### 2. **test_sso_role_assignment.php**
- **Perubahan**: Update test cases untuk expect role `peminjam` untuk semua
- **Test Cases**: Admin, Dosen, PLP, Mahasiswa → semua expect `peminjam`

### 3. **fix_sso_role_id.php**
- **Perubahan**: Update script perbaikan untuk assign role `peminjam` ke semua user SSO
- **Logic**: Tidak lagi mempertimbangkan `staff` code

## 📊 **Mapping Role Baru:**

| **User Type** | **SSO Staff Code** | **Role Name** | **Role ID** | **Keterangan** |
|---------------|-------------------|---------------|-------------|----------------|
| Admin | 0 | `peminjam` | **2** | ✅ Berubah dari admin ke peminjam |
| PLP | 3 | `peminjam` | **2** | ✅ Berubah dari staff ke peminjam |
| Dosen | 4 | `peminjam` | **2** | ✅ Berubah dari staff ke peminjam |
| Mahasiswa | 999 | `peminjam` | **2** | ✅ Tetap peminjam |
| Default | - | `peminjam` | **2** | ✅ Tetap peminjam |

## 🎯 **Dampak Perubahan:**

### ✅ **Keuntungan:**
1. **Konsistensi**: Semua user SSO mendapat role yang sama
2. **Kesederhanaan**: Tidak perlu logika kompleks berdasarkan SSO data
3. **Keamanan**: User baru tidak langsung mendapat akses admin
4. **Fleksibilitas**: Admin dapat mengubah role manual jika diperlukan

### ⚠️ **Yang Perlu Diperhatikan:**
1. **User SSO yang sudah ada** perlu di-update role-nya
2. **Admin yang login via SSO** akan jadi peminjam (perlu diubah manual)
3. **Permission management** perlu dilakukan manual untuk user khusus

## 🛠️ **Cara Implementasi:**

### 1. **Jalankan Script Perbaikan:**
```bash
# Fix existing SSO users
php fix_sso_role_id.php

# Test role assignment
php test_sso_role_assignment.php
```

### 2. **Verifikasi Database:**
```sql
-- Cek semua user SSO dengan role peminjam
SELECT 
    id, 
    username, 
    name, 
    role_id,
    JSON_EXTRACT(sso_data, '$.staff') as staff_code
FROM users 
WHERE sso_id IS NOT NULL 
AND role_id = 2;

-- Cek user SSO yang masih belum punya role_id
SELECT 
    id, 
    username, 
    name, 
    role_id
FROM users 
WHERE sso_id IS NOT NULL 
AND role_id IS NULL;
```

### 3. **Manual Role Assignment untuk Admin:**
```php
// Untuk user yang perlu role admin
$user = User::find($userId);
$adminRole = Role::where('name', 'admin')->first();
$user->assignRole($adminRole);
$user->role_id = $adminRole->id;
$user->save();
```

## 🧪 **Testing:**

### Test Case 1: Admin SSO Login
```json
{
    "staff": 0,
    "username": "admin001",
    "name": "Admin Test"
}
```
**Expected**: Role = `peminjam` (ID: 2)

### Test Case 2: Dosen SSO Login
```json
{
    "staff": 4,
    "username": "dosen001", 
    "name": "Dosen Test"
}
```
**Expected**: Role = `peminjam` (ID: 2)

### Test Case 3: Mahasiswa SSO Login
```json
{
    "staff": 999,
    "username": "202310001",
    "name": "Mahasiswa Test"
}
```
**Expected**: Role = `peminjam` (ID: 2)

## 📝 **Log Monitoring:**

Script akan menampilkan log:
```
Role assigned to SSO user: user_id=123, role_id=2, role_name=peminjam
```

## 🔄 **Rollback (Jika Diperlukan):**

Jika perlu kembali ke kebijakan lama:

```php
protected function getRoleNameFromSSO($ssoData)
{
    if (isset($ssoData['staff'])) {
        switch ($ssoData['staff']) {
            case 0: return 'admin';
            case 3: 
            case 4: return 'staff';
            case 999: 
            default: return 'peminjam';
        }
    }
    return 'peminjam';
}
```

## ✅ **Status Implementasi:**

- [x] Update OAuthService.php
- [x] Update test script
- [x] Update fix script
- [x] Update dokumentasi
- [ ] Test dengan data real
- [ ] Deploy ke production

---

**Kebijakan baru: Semua user SSO = Role Peminjam (ID: 2)** 🎯
