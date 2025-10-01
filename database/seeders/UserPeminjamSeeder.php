<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserPeminjamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get user_peminjam role
        $peminjamRole = \Spatie\Permission\Models\Role::where('name', 'user_peminjam')->first();
        
        if (!$peminjamRole) {
            $this->command->error('User Peminjam role not found. Please run CreateUserPeminjamRole first.');
            return;
        }

        // Create user peminjam 1 (Mahasiswa)
        $peminjam1 = User::create([
            'name' => 'Ahmad Rizki',
            'username' => 'ahmad.rizki',
            'email' => 'ahmad.rizki@student.poliwangi.ac.id',
            'password' => Hash::make('peminjam123'),
            'phone' => '081234567891',
            'user_type' => 'mahasiswa',
            'status' => 'active',
            'role_id' => $peminjamRole->id,
            'profile_completed' => false,
            'profile_completed_at' => null,
        ]);

        // Assign user_peminjam role
        $peminjam1->assignRole($peminjamRole);

        // Create user peminjam 2 (Staff)
        $peminjam2 = User::create([
            'name' => 'Siti Nurhaliza',
            'username' => 'siti.nurhaliza',
            'email' => 'siti.nurhaliza@poliwangi.ac.id',
            'password' => Hash::make('peminjam123'),
            'phone' => '081234567892',
            'user_type' => 'staff',
            'status' => 'active',
            'role_id' => $peminjamRole->id,
            'profile_completed' => false,
            'profile_completed_at' => null,
        ]);

        // Assign user_peminjam role
        $peminjam2->assignRole($peminjamRole);

        // Create user peminjam 3 (Mahasiswa)
        $peminjam3 = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email' => 'budi.santoso@student.poliwangi.ac.id',
            'password' => Hash::make('peminjam123'),
            'phone' => '081234567893',
            'user_type' => 'mahasiswa',
            'status' => 'active',
            'role_id' => $peminjamRole->id,
            'profile_completed' => false,
            'profile_completed_at' => null,
        ]);

        // Assign user_peminjam role
        $peminjam3->assignRole($peminjamRole);

        $this->command->info('User Peminjam created successfully!');
        $this->command->info('Username: ahmad.rizki, Password: peminjam123');
        $this->command->info('Username: siti.nurhaliza, Password: peminjam123');
        $this->command->info('Username: budi.santoso, Password: peminjam123');
        $this->command->info('All users have status: active and profile_completed: false');
    }
}

