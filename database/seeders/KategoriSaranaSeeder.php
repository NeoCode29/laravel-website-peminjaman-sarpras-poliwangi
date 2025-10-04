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
                'icon' => 'fas fa-tv',
            ],
            [
                'name' => 'Laptop',
                'description' => 'Komputer portabel untuk presentasi dan kerja',
                'icon' => 'fas fa-laptop',
            ],
            [
                'name' => 'Sound System',
                'description' => 'Sistem audio untuk acara dan presentasi',
                'icon' => 'fas fa-microphone',
            ],
            [
                'name' => 'Kursi',
                'description' => 'Tempat duduk untuk acara dan kegiatan',
                'icon' => 'fas fa-chair',
            ],
            [
                'name' => 'Meja',
                'description' => 'Meja untuk acara dan kegiatan',
                'icon' => 'fas fa-table',
            ],
            [
                'name' => 'Tenda',
                'description' => 'Tenda untuk acara outdoor',
                'icon' => 'fas fa-home',
            ],
            [
                'name' => 'Generator',
                'description' => 'Pembangkit listrik untuk acara outdoor',
                'icon' => 'fas fa-bolt',
            ],
            [
                'name' => 'Kamera',
                'description' => 'Alat dokumentasi untuk acara',
                'icon' => 'fas fa-camera',
            ],
        ];

        foreach ($kategori as $kat) {
            \App\Models\KategoriSarana::create($kat);
        }
    }
}
