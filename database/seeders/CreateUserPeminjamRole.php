<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CreateUserPeminjamRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create user_peminjam role
        \Spatie\Permission\Models\Role::create([
            'name' => 'user_peminjam',
            'display_name' => 'User Peminjam',
            'description' => 'Role untuk user yang dapat meminjam sarana prasarana',
            'guard_name' => 'web',
            'is_active' => true,
        ]);
        
        $this->command->info('User Peminjam role created successfully!');
    }
}
