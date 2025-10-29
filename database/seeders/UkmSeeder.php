<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UkmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ukm = [
            [
                'nama' => 'MPM',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'BEM',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'HMJ SIPIL',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'HMJ MESIN',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'HMJ TI',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'HMJ TANI',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'HMJ PARIWISATA',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'FORBIM',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'GENIWANGI',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PERS',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'KWU',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'KSR',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'OLAHRAGA',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'MAPALA',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'RPB',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'RACANA',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'MENWA',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'IMAM',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'MEDSEN',
                'deskripsi' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ukm')->insert($ukm);
    }
}
