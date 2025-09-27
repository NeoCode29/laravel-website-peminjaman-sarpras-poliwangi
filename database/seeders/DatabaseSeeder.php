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
        
        // Create admin user
        $this->call(AdminUserSeeder::class);
    }
}