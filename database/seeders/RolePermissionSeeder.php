<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions sesuai PRD
        $permissions = [
            // User Management
            ['name' => 'user.view', 'display_name' => 'Melihat daftar user', 'description' => 'Dapat melihat daftar semua user', 'category' => 'user'],
            ['name' => 'user.create', 'display_name' => 'Menambah user baru', 'description' => 'Dapat menambah user baru ke sistem', 'category' => 'user'],
            ['name' => 'user.edit', 'display_name' => 'Mengedit profil user', 'description' => 'Dapat mengedit profil user lain', 'category' => 'user'],
            ['name' => 'user.delete', 'display_name' => 'Menghapus user', 'description' => 'Dapat menghapus user dari sistem', 'category' => 'user'],
            ['name' => 'user.block', 'display_name' => 'Memblokir user', 'description' => 'Dapat memblokir user dengan durasi tertentu', 'category' => 'user'],
            ['name' => 'user.unblock', 'display_name' => 'Membuka blokir user', 'description' => 'Dapat membuka blokir user', 'category' => 'user'],
            ['name' => 'user.role_edit', 'display_name' => 'Mengubah role user', 'description' => 'Dapat mengubah role user', 'category' => 'user'],

            // Sarana/Prasarana Management
            ['name' => 'sarpras.view', 'display_name' => 'Melihat data sarpras', 'description' => 'Dapat melihat data sarana dan prasarana', 'category' => 'sarpras'],
            ['name' => 'sarpras.create', 'display_name' => 'Menambah sarpras baru', 'description' => 'Dapat menambah sarana dan prasarana baru', 'category' => 'sarpras'],
            ['name' => 'sarpras.edit', 'display_name' => 'Mengedit data sarpras', 'description' => 'Dapat mengedit data sarana dan prasarana', 'category' => 'sarpras'],
            ['name' => 'sarpras.delete', 'display_name' => 'Menghapus sarpras', 'description' => 'Dapat menghapus sarana dan prasarana', 'category' => 'sarpras'],
            ['name' => 'sarpras.status_update', 'display_name' => 'Mengubah status sarpras', 'description' => 'Dapat mengubah status ketersediaan sarpras', 'category' => 'sarpras'],
            ['name' => 'sarpras.unit_manage', 'display_name' => 'Mengelola unit sarpras', 'description' => 'Dapat mengelola unit sarana', 'category' => 'sarpras'],
            ['name' => 'sarpras.approval_assign', 'display_name' => 'Menetapkan approver sarpras', 'description' => 'Dapat menetapkan approver untuk sarpras spesifik', 'category' => 'sarpras'],
            ['name' => 'sarpras.approval_global', 'display_name' => 'Approval global sarpras', 'description' => 'Dapat melakukan approval global untuk semua sarpras', 'category' => 'sarpras'],

            // Peminjaman Management
            ['name' => 'peminjaman.view', 'display_name' => 'Melihat data peminjaman', 'description' => 'Dapat melihat data peminjaman', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.create', 'display_name' => 'Membuat pengajuan peminjaman', 'description' => 'Dapat membuat pengajuan peminjaman', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.approve', 'display_name' => 'Approve pengajuan', 'description' => 'Dapat approve pengajuan peminjaman', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.approve_specific', 'display_name' => 'Approve sarpras spesifik', 'description' => 'Dapat approve sarpras yang ditetapkan', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.approve_workflow', 'display_name' => 'Mengelola workflow approval', 'description' => 'Dapat mengelola workflow approval', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.reject', 'display_name' => 'Reject pengajuan', 'description' => 'Dapat reject pengajuan peminjaman', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.reject_specific', 'display_name' => 'Reject sarpras spesifik', 'description' => 'Dapat reject sarpras yang ditetapkan', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.validate_pickup', 'display_name' => 'Validasi pengambilan', 'description' => 'Dapat validasi pengambilan sarpras', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.validate_return', 'display_name' => 'Validasi pengembalian', 'description' => 'Dapat validasi pengembalian sarpras', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.marking_override', 'display_name' => 'Override marking', 'description' => 'Dapat override marking user lain', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.adjust_sarpras', 'display_name' => 'Mengatur unit sarpras', 'description' => 'Dapat menentukan unit sarana setelah approval', 'category' => 'peminjaman'],

            // Report & Analytics
            ['name' => 'report.view', 'display_name' => 'Melihat laporan', 'description' => 'Dapat melihat laporan sistem', 'category' => 'report'],
            ['name' => 'report.export', 'display_name' => 'Export laporan', 'description' => 'Dapat export laporan ke PDF/Excel', 'category' => 'report'],
            ['name' => 'log.view', 'display_name' => 'Melihat log aktivitas', 'description' => 'Dapat melihat log aktivitas sistem', 'category' => 'log'],
            ['name' => 'analytics.view', 'display_name' => 'Melihat analytics', 'description' => 'Dapat melihat analytics dan statistik', 'category' => 'analytics'],

            // System Management
            ['name' => 'system.settings', 'display_name' => 'Mengatur setting sistem', 'description' => 'Dapat mengatur setting sistem', 'category' => 'system'],
            ['name' => 'system.backup', 'display_name' => 'Backup sistem', 'description' => 'Dapat melakukan backup sistem', 'category' => 'system'],
            ['name' => 'system.monitoring', 'display_name' => 'Monitoring sistem', 'description' => 'Dapat melakukan monitoring sistem', 'category' => 'system'],

            // Notification Management
            ['name' => 'notification.view', 'display_name' => 'Melihat notifikasi', 'description' => 'Dapat melihat notifikasi personal', 'category' => 'notification'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles sesuai PRD
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin (Petugas Sarpras)',
            'description' => 'Administrator sistem dengan akses penuh',
            'is_active' => true
        ]);

        $peminjamRole = Role::create([
            'name' => 'peminjam',
            'display_name' => 'Peminjam (Mahasiswa/Staff)',
            'description' => 'User yang dapat mengajukan peminjaman',
            'is_active' => true
        ]);

        $approverRole = Role::create([
            'name' => 'approver',
            'display_name' => 'Approver',
            'description' => 'User yang dapat melakukan approval peminjaman',
            'is_active' => true
        ]);

        $globalApproverRole = Role::create([
            'name' => 'global_approver',
            'display_name' => 'Global Approver',
            'description' => 'User yang dapat melakukan approval global',
            'is_active' => true
        ]);

        $specificApproverRole = Role::create([
            'name' => 'specific_approver',
            'display_name' => 'Specific Approver',
            'description' => 'User yang dapat melakukan approval sarpras spesifik',
            'is_active' => true
        ]);

        // Assign permissions to admin (semua permission)
        $adminRole->givePermissionTo(Permission::all());

        // Assign permissions to peminjam (permission terbatas)
        $peminjamRole->givePermissionTo([
            'sarpras.view',
            'peminjaman.create',
            'peminjaman.view',
            'notification.view'
        ]);

        // Assign permissions to approver
        $approverRole->givePermissionTo([
            'sarpras.view',
            'peminjaman.view',
            'peminjaman.approve',
            'peminjaman.approve_specific',
            'peminjaman.approve_workflow',
            'peminjaman.reject',
            'peminjaman.reject_specific',
            'notification.view'
        ]);

        // Assign permissions to global approver
        $globalApproverRole->givePermissionTo([
            'sarpras.view',
            'peminjaman.view',
            'peminjaman.approve',
            'peminjaman.approve_workflow',
            'peminjaman.reject_specific',
            'notification.view'
        ]);

        // Assign permissions to specific approver
        $specificApproverRole->givePermissionTo([
            'sarpras.view',
            'peminjaman.view',
            'peminjaman.approve_specific',
            'peminjaman.approve_workflow',
            'peminjaman.reject_specific',
            'notification.view'
        ]);
    }
}
