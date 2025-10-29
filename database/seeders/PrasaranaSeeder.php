<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prasarana;
use App\Models\KategoriPrasarana;
use App\Models\User;

class PrasaranaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for created_by
        $admin = User::where('email', 'admin@poliwangi.ac.id')->first();
        if (!$admin) {
            $admin = User::first();
        }

        // Get or create kategori prasarana
        $kategoriAula = KategoriPrasarana::firstOrCreate(
            ['name' => 'Aula'],
            [
                'description' => 'Ruang pertemuan besar untuk acara formal',
                'is_active' => true
            ]
        );

        $kategoriRuangMeeting = KategoriPrasarana::firstOrCreate(
            ['name' => 'Ruang Meeting'],
            [
                'description' => 'Ruang pertemuan kecil untuk rapat',
                'is_active' => true
            ]
        );

        $kategoriLaboratorium = KategoriPrasarana::firstOrCreate(
            ['name' => 'Laboratorium'],
            [
                'description' => 'Ruang laboratorium untuk praktikum',
                'is_active' => true
            ]
        );

        $kategoriWorkshop = KategoriPrasarana::firstOrCreate(
            ['name' => 'Workshop'],
            [
                'description' => 'Ruang workshop untuk kegiatan praktik',
                'is_active' => true
            ]
        );

        // Sample prasarana data
        $prasaranaData = [
            [
                'name' => 'Aula Utama Poliwangi',
                'kategori_id' => $kategoriAula->id,
                'description' => 'Aula utama kampus dengan kapasitas besar untuk acara formal dan seminar',
                'lokasi' => 'Gedung Utama Lantai 2',
                'kapasitas' => 500,
                'status' => 'tersedia',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Aula Kecil',
                'kategori_id' => $kategoriAula->id,
                'description' => 'Aula kecil untuk acara internal dan presentasi',
                'lokasi' => 'Gedung Utama Lantai 1',
                'kapasitas' => 100,
                'status' => 'tersedia',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Ruang Meeting Rektorat',
                'kategori_id' => $kategoriRuangMeeting->id,
                'description' => 'Ruang meeting khusus untuk rapat pimpinan',
                'lokasi' => 'Gedung Rektorat Lantai 3',
                'kapasitas' => 20,
                'status' => 'tersedia',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Ruang Meeting Fakultas Teknik',
                'kategori_id' => $kategoriRuangMeeting->id,
                'description' => 'Ruang meeting untuk rapat fakultas teknik',
                'lokasi' => 'Gedung Fakultas Teknik Lantai 2',
                'kapasitas' => 30,
                'status' => 'tersedia',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Lab Komputer 1',
                'kategori_id' => $kategoriLaboratorium->id,
                'description' => 'Laboratorium komputer untuk praktikum mahasiswa',
                'lokasi' => 'Gedung Fakultas Teknik Lantai 1',
                'kapasitas' => 40,
                'status' => 'tersedia',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Lab Komputer 2',
                'kategori_id' => $kategoriLaboratorium->id,
                'description' => 'Laboratorium komputer untuk praktikum mahasiswa',
                'lokasi' => 'Gedung Fakultas Teknik Lantai 1',
                'kapasitas' => 40,
                'status' => 'maintenance',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Lab Mesin',
                'kategori_id' => $kategoriLaboratorium->id,
                'description' => 'Laboratorium mesin untuk praktikum teknik mesin',
                'lokasi' => 'Gedung Fakultas Teknik Lantai 1',
                'kapasitas' => 25,
                'status' => 'tersedia',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Workshop Otomotif',
                'kategori_id' => $kategoriWorkshop->id,
                'description' => 'Workshop untuk praktikum otomotif',
                'lokasi' => 'Gedung Fakultas Teknik Lantai 1',
                'kapasitas' => 20,
                'status' => 'tersedia',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Workshop Kayu',
                'kategori_id' => $kategoriWorkshop->id,
                'description' => 'Workshop untuk praktikum kayu dan furniture',
                'lokasi' => 'Gedung Fakultas Teknik Lantai 1',
                'kapasitas' => 15,
                'status' => 'rusak',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Ruang Seminar',
                'kategori_id' => $kategoriAula->id,
                'description' => 'Ruang seminar dengan fasilitas lengkap',
                'lokasi' => 'Gedung Perpustakaan Lantai 2',
                'kapasitas' => 80,
                'status' => 'tersedia',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($prasaranaData as $data) {
            Prasarana::create($data);
        }
    }
}
