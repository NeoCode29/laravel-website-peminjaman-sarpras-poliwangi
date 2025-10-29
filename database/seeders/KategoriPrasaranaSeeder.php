<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class KategoriPrasaranaSeeder extends Seeder
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
                'name' => 'Aula',
                'description' => 'Ruang besar untuk acara dan pertemuan',
            ],
            [
                'name' => 'Ruang Meeting',
                'description' => 'Ruang untuk rapat dan diskusi',
            ],
            [
                'name' => 'Laboratorium',
                'description' => 'Ruang untuk praktikum dan penelitian',
            ],
            [
                'name' => 'Ruang Kelas',
                'description' => 'Ruang untuk kegiatan pembelajaran',
            ],
            [
                'name' => 'Auditorium',
                'description' => 'Ruang besar dengan panggung untuk presentasi',
            ],
            [
                'name' => 'Lapangan',
                'description' => 'Area outdoor untuk kegiatan olahraga dan acara',
            ],
            [
                'name' => 'Perpustakaan',
                'description' => 'Ruang untuk membaca dan belajar',
            ],
            [
                'name' => 'Kantin',
                'description' => 'Ruang untuk makan dan istirahat',
            ],
        ];

        foreach ($kategori as $kat) {
            \App\Models\KategoriPrasarana::firstOrCreate(
                ['name' => $kat['name']],
                $kat
            );
        }
    }
}
