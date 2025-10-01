<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create basic roles and permissions
        $this->call(RolePermissionSeeder::class);
        
        // Create user_peminjam role
        $this->call(CreateUserPeminjamRole::class);
        
        // Create Poliwangi master data (jurusan, prodi, units, positions)
        // Using updateOrCreate to avoid foreign key constraint issues
        $this->call(PoliwangiUpdateSeeder::class);
        
        // Create admin user
        $this->call(AdminUserSeeder::class);
        
        // Create user peminjam
        $this->call(UserPeminjamSeeder::class);
        
        // Create dashboard data
        $this->call(DashboardSeeder::class);
    }
}