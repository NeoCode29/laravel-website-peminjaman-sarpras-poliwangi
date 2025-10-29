<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ApprovalPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create approval permissions
        $approvalPermissions = [
            ['name' => 'sarpras.approval_assign', 'display_name' => 'Menetapkan approver sarpras', 'description' => 'Dapat menetapkan approver untuk sarpras spesifik', 'category' => 'sarpras'],
            ['name' => 'sarpras.approval_global', 'display_name' => 'Approval global sarpras', 'description' => 'Dapat melakukan approval global untuk semua sarpras', 'category' => 'sarpras'],
            ['name' => 'peminjaman.approve_specific', 'display_name' => 'Approve sarpras spesifik', 'description' => 'Dapat approve sarpras yang ditetapkan', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.reject_specific', 'display_name' => 'Reject sarpras spesifik', 'description' => 'Dapat reject sarpras yang ditetapkan', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.approve_workflow', 'display_name' => 'Mengelola workflow approval', 'description' => 'Dapat mengelola workflow approval', 'category' => 'peminjaman'],
            ['name' => 'peminjaman.adjust_sarpras', 'display_name' => 'Mengatur unit sarpras', 'description' => 'Dapat menentukan unit sarana setelah approval', 'category' => 'peminjaman'],
        ];

        foreach ($approvalPermissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => $permissionData['display_name'],
                    'description' => $permissionData['description'],
                    'category' => $permissionData['category'],
                    'guard_name' => 'web'
                ]
            );
        }

        // Create approval roles
        $approverRole = Role::firstOrCreate(
            ['name' => 'approver'],
            [
                'display_name' => 'Approver',
                'description' => 'User yang dapat melakukan approval peminjaman',
                'is_active' => true
            ]
        );

        $globalApproverRole = Role::firstOrCreate(
            ['name' => 'global_approver'],
            [
                'display_name' => 'Global Approver',
                'description' => 'User yang dapat melakukan approval global',
                'is_active' => true
            ]
        );

        $specificApproverRole = Role::firstOrCreate(
            ['name' => 'specific_approver'],
            [
                'display_name' => 'Specific Approver',
                'description' => 'User yang dapat melakukan approval spesifik',
                'is_active' => true
            ]
        );

        // Assign permissions to roles
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

        $globalApproverRole->givePermissionTo([
            'sarpras.view',
            'peminjaman.view',
            'peminjaman.approve',
            'sarpras.approval_global',
            'peminjaman.approve_workflow',
            'peminjaman.reject',
            'peminjaman.reject_specific',
            'notification.view'
        ]);

        $specificApproverRole->givePermissionTo([
            'sarpras.view',
            'peminjaman.view',
            'peminjaman.approve_specific',
            'peminjaman.approve_workflow',
            'peminjaman.reject',
            'peminjaman.reject_specific',
            'notification.view'
        ]);

        // Add approval permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'sarpras.approval_assign',
                'sarpras.approval_global',
                'peminjaman.approve_specific',
                'peminjaman.approve_workflow',
                'peminjaman.reject_specific',
                'peminjaman.adjust_sarpras'
            ]);
        }

        $this->command->info('Approval permissions and roles created successfully!');
    }
}











