<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Unit;
use App\Models\Position;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Jurusan Poliwangi
        $jurusanData = [
            ['nama_jurusan' => 'TEKNIK SIPIL', 'deskripsi' => 'Jurusan yang mempelajari teknik sipil dan konstruksi'],
            ['nama_jurusan' => 'TEKNIK MESIN', 'deskripsi' => 'Jurusan yang mempelajari teknik mesin dan manufaktur'],
            ['nama_jurusan' => 'BISNIS & INFORMATIKA', 'deskripsi' => 'Jurusan yang mempelajari bisnis digital dan teknologi informasi'],
            ['nama_jurusan' => 'PARIWISATA', 'deskripsi' => 'Jurusan yang mempelajari pariwisata dan perhotelan'],
            ['nama_jurusan' => 'PERTANIAN', 'deskripsi' => 'Jurusan yang mempelajari pertanian dan agribisnis'],
        ];

        foreach ($jurusanData as $data) {
            \App\Models\Jurusan::create($data);
        }

        // Create Prodi Poliwangi
        $prodiData = [
            // TEKNIK SIPIL (ID: 1)
            ['nama_prodi' => 'D3 Teknik Sipil', 'jurusan_id' => 1, 'jenjang' => 'D3', 'deskripsi' => 'Program studi D3 Teknik Sipil'],
            ['nama_prodi' => 'Manajemen Konstruksi', 'jurusan_id' => 1, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Manajemen Konstruksi'],
            ['nama_prodi' => 'Teknologi Rekayasa Konstruksi Jalan & Jembatan', 'jurusan_id' => 1, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Rekayasa Konstruksi Jalan & Jembatan'],
            ['nama_prodi' => 'Teknologi Rekayasa Konstruksi Bangunan Gedung', 'jurusan_id' => 1, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Rekayasa Konstruksi Bangunan Gedung'],
            
            // TEKNIK MESIN (ID: 2)
            ['nama_prodi' => 'Teknik Manufaktur Kapal', 'jurusan_id' => 2, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknik Manufaktur Kapal'],
            ['nama_prodi' => 'Teknologi Rekayasa Otomotif', 'jurusan_id' => 2, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Rekayasa Otomotif'],
            ['nama_prodi' => 'Teknologi Rekayasa Manufaktur', 'jurusan_id' => 2, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Rekayasa Manufaktur'],
            
            // BISNIS & INFORMATIKA (ID: 3)
            ['nama_prodi' => 'Bisnis Digital', 'jurusan_id' => 3, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Bisnis Digital'],
            ['nama_prodi' => 'Teknologi Rekayasa Komputer', 'jurusan_id' => 3, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Rekayasa Komputer'],
            ['nama_prodi' => 'Teknologi Rekayasa Perangkat Lunak', 'jurusan_id' => 3, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Rekayasa Perangkat Lunak'],
            
            // PARIWISATA (ID: 4)
            ['nama_prodi' => 'Destinasi Pariwisata', 'jurusan_id' => 4, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Destinasi Pariwisata'],
            ['nama_prodi' => 'Pengelolaan Perhotelan', 'jurusan_id' => 4, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Pengelolaan Perhotelan'],
            ['nama_prodi' => 'Manajemen Bisnis Pariwisata', 'jurusan_id' => 4, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Manajemen Bisnis Pariwisata'],
            
            // PERTANIAN (ID: 5)
            ['nama_prodi' => 'Agribisnis', 'jurusan_id' => 5, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Agribisnis'],
            ['nama_prodi' => 'Teknologi Produksi Ternak', 'jurusan_id' => 5, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Produksi Ternak'],
            ['nama_prodi' => 'Teknologi Pengolahan Hasil Ternak', 'jurusan_id' => 5, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Pengolahan Hasil Ternak'],
            ['nama_prodi' => 'Teknologi Produksi Tanaman Pangan', 'jurusan_id' => 5, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Produksi Tanaman Pangan'],
            ['nama_prodi' => 'Pengembangan Produk Agroindustry', 'jurusan_id' => 5, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Pengembangan Produk Agroindustry'],
            ['nama_prodi' => 'Teknologi Budi Daya Perikanan / Teknologi Akuakultur', 'jurusan_id' => 5, 'jenjang' => 'D4', 'deskripsi' => 'Program studi Teknologi Budi Daya Perikanan / Teknologi Akuakultur'],
        ];

        foreach ($prodiData as $data) {
            \App\Models\Prodi::create($data);
        }

        // Create Units
        $unitData = [
            ['nama' => 'Fakultas Teknik'],
            ['nama' => 'Fakultas Ekonomi'],
            ['nama' => 'Fakultas Ilmu Kesehatan'],
            ['nama' => 'Bagian Umum dan Keuangan'],
            ['nama' => 'Bagian Akademik'],
            ['nama' => 'Bagian Kemahasiswaan'],
            ['nama' => 'Laboratorium Teknik Informatika'],
            ['nama' => 'Laboratorium Teknik Elektro'],
            ['nama' => 'Laboratorium Teknik Mesin'],
            ['nama' => 'Laboratorium Teknik Sipil'],
            ['nama' => 'Perpustakaan'],
            ['nama' => 'Bagian IT'],
        ];

        foreach ($unitData as $data) {
            \App\Models\Unit::create($data);
        }

        // Create Positions
        $positionData = [
            ['nama' => 'Dosen'],
            ['nama' => 'Dosen Tetap'],
            ['nama' => 'Dosen Tidak Tetap'],
            ['nama' => 'Kepala Bagian'],
            ['nama' => 'Sekretaris Bagian'],
            ['nama' => 'Administrasi'],
            ['nama' => 'Staff Administrasi'],
            ['nama' => 'Teknisi'],
            ['nama' => 'Teknisi Lab'],
            ['nama' => 'Pustakawan'],
            ['nama' => 'Security'],
            ['nama' => 'Cleaning Service'],
            ['nama' => 'Driver'],
            ['nama' => 'Gardener'],
        ];

        foreach ($positionData as $data) {
            \App\Models\Position::create($data);
        }
    }
}
