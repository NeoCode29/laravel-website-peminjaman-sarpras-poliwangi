<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get admin role
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('Admin role not found. Please run RolePermissionSeeder first.');
            return;
        }

        // Create admin user - admin juga harus setup profile
        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@poliwangi.ac.id',
            'password' => Hash::make('admin123'),
            'phone' => '081234567890',
            'user_type' => 'staff',
            'status' => 'active',
            'role_id' => $adminRole->id,
            'profile_completed' => false,        // Admin juga harus setup profile
            'profile_completed_at' => null,      // Belum setup profile
        ]);

        // Assign admin role using Spatie Permission
        $admin->assignRole($adminRole);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Username: admin');
        $this->command->info('Password: admin123');
        $this->command->info('Email: admin@poliwangi.ac.id');
    }
}
