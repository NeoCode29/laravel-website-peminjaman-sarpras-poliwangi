<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $names = [
            'Ahmad Rizki','Siti Nurhaliza','Budi Santoso','Dewi Lestari','Rizal Fadillah',
            'Nadia Putri','Rangga Pratama','Tika Susanti','Fajar Ramadhan','Ayu Wulandari',
            'Yoga Firmansyah','Intan Permata','Raka Mahendra','Mira Safitri','Dimas Saputra'
        ];

        $created = 0;

        for ($i = 0; $i < 15; $i++) {
            $name = $names[$i] ?? ('User Peminjam '.($i+1));
            $isMahasiswa = $i % 2 === 0; // selang-seling mahasiswa/staff
            $username = Str::slug($name, '.');
            // pastikan unik dengan menambah index jika perlu
            $baseUsername = $username;
            $suffix = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername.$suffix;
                $suffix++;
            }

            $emailDomain = $isMahasiswa ? 'student.poliwangi.ac.id' : 'poliwangi.ac.id';
            $email = Str::slug(str_replace('.', ' ', $username), '.').'@'.$emailDomain;
            
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => Hash::make('peminjam123'),
                    'phone' => '0812'.str_pad((string)($i+34567890), 7, '0', STR_PAD_LEFT),
                    'user_type' => $isMahasiswa ? 'mahasiswa' : 'staff',
                    'status' => 'active',
                    'role_id' => $peminjamRole->id,
                    'profile_completed' => false,
                    'profile_completed_at' => null,
                ]
            );

            // Assign role jika belum
            if (!$user->hasRole($peminjamRole)) {
                $user->assignRole($peminjamRole);
            }

            $created++;
        }

        $this->command->info("User Peminjam created/updated: {$created} accounts (password: peminjam123)");
        $this->command->info('Semua user berstatus active dan profile_completed = false.');
    }
}

