<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class KategoriSaranaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kategori = [
            [
                'name' => 'Proyektor',
                'description' => 'Alat proyeksi untuk presentasi dan pembelajaran',
            ],
            [
                'name' => 'Laptop',
                'description' => 'Komputer portabel untuk presentasi dan kerja',
            ],
            [
                'name' => 'Sound System',
                'description' => 'Sistem audio untuk acara dan presentasi',
            ],
            [
                'name' => 'Kursi',
                'description' => 'Tempat duduk untuk acara dan kegiatan',
            ],
            [
                'name' => 'Meja',
                'description' => 'Meja untuk acara dan kegiatan',
            ],
            [
                'name' => 'Tenda',
                'description' => 'Tenda untuk acara outdoor',
            ],
            [
                'name' => 'Generator',
                'description' => 'Pembangkit listrik untuk acara outdoor',
            ],
            [
                'name' => 'Kamera',
                'description' => 'Alat dokumentasi untuk acara',
            ],
        ];

        foreach ($kategori as $kat) {
            \App\Models\KategoriSarana::create($kat);
        }
    }
}
