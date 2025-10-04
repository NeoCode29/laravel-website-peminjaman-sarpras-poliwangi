<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Unit;
use App\Models\Position;

class PoliwangiUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder yang menggunakan updateOrCreate untuk menghindari foreign key issues
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🌱 Seeding data Poliwangi (Update Mode)...');
        
        // Seed Jurusan
        $this->seedJurusan();
        
        // Seed Prodi
        $this->seedProdi();
        
        // Seed Units
        $this->seedUnits();
        
        // Seed Positions
        $this->seedPositions();
        
        $this->command->info('✅ Data Poliwangi berhasil di-seed!');
    }

    /**
     * Seed Jurusan Poliwangi using updateOrCreate
     */
    private function seedJurusan()
    {
        $this->command->info('📚 Seeding/Updating 5 jurusan...');
        
        $jurusanData = [
            [
                'id' => 1,
                'nama_jurusan' => 'TEKNIK SIPIL',
                'deskripsi' => 'Jurusan yang mempelajari teknik sipil dan konstruksi'
            ],
            [
                'id' => 2,
                'nama_jurusan' => 'TEKNIK MESIN',
                'deskripsi' => 'Jurusan yang mempelajari teknik mesin dan manufaktur'
            ],
            [
                'id' => 3,
                'nama_jurusan' => 'BISNIS & INFORMATIKA',
                'deskripsi' => 'Jurusan yang mempelajari bisnis digital dan teknologi informasi'
            ],
            [
                'id' => 4,
                'nama_jurusan' => 'PARIWISATA',
                'deskripsi' => 'Jurusan yang mempelajari pariwisata dan perhotelan'
            ],
            [
                'id' => 5,
                'nama_jurusan' => 'PERTANIAN',
                'deskripsi' => 'Jurusan yang mempelajari pertanian dan agribisnis'
            ],
        ];

        foreach ($jurusanData as $data) {
            $jurusan = Jurusan::updateOrCreate(
                ['id' => $data['id']], // Find by ID
                $data // Update or create with this data
            );
            
            $action = $jurusan->wasRecentlyCreated ? 'Created' : 'Updated';
            $this->command->info("   ✓ {$action}: {$data['nama_jurusan']}");
        }
    }

    /**
     * Seed Prodi Poliwangi using updateOrCreate
     */
    private function seedProdi()
    {
        $this->command->info('🎓 Seeding/Updating 18 program studi...');
        
        $prodiData = [
            // TEKNIK SIPIL (ID: 1)
            [
                'id' => 1,
                'nama_prodi' => 'D3 Teknik Sipil',
                'jurusan_id' => 1,
                'jenjang' => 'D3',
                'deskripsi' => 'Program studi D3 Teknik Sipil'
            ],
            [
                'id' => 2,
                'nama_prodi' => 'Manajemen Konstruksi',
                'jurusan_id' => 1,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Manajemen Konstruksi'
            ],
            [
                'id' => 3,
                'nama_prodi' => 'Teknologi Rekayasa Konstruksi Jalan & Jembatan',
                'jurusan_id' => 1,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Rekayasa Konstruksi Jalan & Jembatan'
            ],
            [
                'id' => 4,
                'nama_prodi' => 'Teknologi Rekayasa Konstruksi Bangunan Gedung',
                'jurusan_id' => 1,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Rekayasa Konstruksi Bangunan Gedung'
            ],
            
            // TEKNIK MESIN (ID: 2)
            [
                'id' => 5,
                'nama_prodi' => 'Teknik Manufaktur Kapal',
                'jurusan_id' => 2,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknik Manufaktur Kapal'
            ],
            [
                'id' => 6,
                'nama_prodi' => 'Teknologi Rekayasa Otomotif',
                'jurusan_id' => 2,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Rekayasa Otomotif'
            ],
            [
                'id' => 7,
                'nama_prodi' => 'Teknologi Rekayasa Manufaktur',
                'jurusan_id' => 2,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Rekayasa Manufaktur'
            ],
            
            // BISNIS & INFORMATIKA (ID: 3)
            [
                'id' => 8,
                'nama_prodi' => 'Bisnis Digital',
                'jurusan_id' => 3,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Bisnis Digital'
            ],
            [
                'id' => 9,
                'nama_prodi' => 'Teknologi Rekayasa Komputer',
                'jurusan_id' => 3,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Rekayasa Komputer'
            ],
            [
                'id' => 10,
                'nama_prodi' => 'Teknologi Rekayasa Perangkat Lunak',
                'jurusan_id' => 3,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Rekayasa Perangkat Lunak'
            ],
            
            // PARIWISATA (ID: 4)
            [
                'id' => 11,
                'nama_prodi' => 'Destinasi Pariwisata',
                'jurusan_id' => 4,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Destinasi Pariwisata'
            ],
            [
                'id' => 12,
                'nama_prodi' => 'Pengelolaan Perhotelan',
                'jurusan_id' => 4,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Pengelolaan Perhotelan'
            ],
            [
                'id' => 13,
                'nama_prodi' => 'Manajemen Bisnis Pariwisata',
                'jurusan_id' => 4,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Manajemen Bisnis Pariwisata'
            ],
            
            // PERTANIAN (ID: 5)
            [
                'id' => 14,
                'nama_prodi' => 'Agribisnis',
                'jurusan_id' => 5,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Agribisnis'
            ],
            [
                'id' => 15,
                'nama_prodi' => 'Teknologi Produksi Ternak',
                'jurusan_id' => 5,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Produksi Ternak'
            ],
            [
                'id' => 16,
                'nama_prodi' => 'Teknologi Pengolahan Hasil Ternak',
                'jurusan_id' => 5,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Pengolahan Hasil Ternak'
            ],
            [
                'id' => 17,
                'nama_prodi' => 'Teknologi Produksi Tanaman Pangan',
                'jurusan_id' => 5,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Produksi Tanaman Pangan'
            ],
            [
                'id' => 18,
                'nama_prodi' => 'Pengembangan Produk Agroindustry',
                'jurusan_id' => 5,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Pengembangan Produk Agroindustry'
            ],
            [
                'id' => 19,
                'nama_prodi' => 'Teknologi Budi Daya Perikanan / Teknologi Akuakultur',
                'jurusan_id' => 5,
                'jenjang' => 'D4',
                'deskripsi' => 'Program studi Teknologi Budi Daya Perikanan / Teknologi Akuakultur'
            ],
        ];

        foreach ($prodiData as $data) {
            $prodi = Prodi::updateOrCreate(
                ['id' => $data['id']], // Find by ID
                $data // Update or create with this data
            );
            
            $action = $prodi->wasRecentlyCreated ? 'Created' : 'Updated';
            $this->command->info("   ✓ {$action}: {$data['nama_prodi']} ({$data['jenjang']})");
        }
    }

    /**
     * Seed Units Poliwangi using updateOrCreate
     */
    private function seedUnits()
    {
        $this->command->info('🏢 Seeding/Updating units...');
        
        $unitData = [
            // Fakultas
            ['nama' => 'Fakultas Teknik'],
            ['nama' => 'Fakultas Ekonomi dan Bisnis'],
            ['nama' => 'Fakultas Pariwisata'],
            ['nama' => 'Fakultas Pertanian'],
            
            // Jurusan
            ['nama' => 'Jurusan Teknik Sipil'],
            ['nama' => 'Jurusan Teknik Mesin'],
            ['nama' => 'Jurusan Bisnis & Informatika'],
            ['nama' => 'Jurusan Pariwisata'],
            ['nama' => 'Jurusan Pertanian'],
            
            // Bagian Administrasi
            ['nama' => 'Bagian Umum dan Keuangan'],
            ['nama' => 'Bagian Akademik'],
            ['nama' => 'Bagian Kemahasiswaan'],
            ['nama' => 'Bagian IT'],
            
            // Laboratorium
            ['nama' => 'Laboratorium Teknik Sipil'],
            ['nama' => 'Laboratorium Teknik Mesin'],
            ['nama' => 'Laboratorium Komputer'],
            ['nama' => 'Laboratorium Pariwisata'],
            ['nama' => 'Laboratorium Pertanian'],
            
            // Unit Pendukung
            ['nama' => 'Perpustakaan'],
            ['nama' => 'Gedung Serbaguna'],
            ['nama' => 'Aula Utama'],
            ['nama' => 'Ruang Meeting'],
        ];

        foreach ($unitData as $data) {
            $unit = Unit::updateOrCreate(
                ['nama' => $data['nama']], // Find by name
                $data // Update or create with this data
            );
            
            $action = $unit->wasRecentlyCreated ? 'Created' : 'Updated';
            $this->command->info("   ✓ {$action}: {$data['nama']}");
        }
    }

    /**
     * Seed Positions Poliwangi using updateOrCreate
     */
    private function seedPositions()
    {
        $this->command->info('👥 Seeding/Updating positions...');
        
        $positionData = [
            // Dosen
            ['nama' => 'Dosen'],
            ['nama' => 'Dosen Tetap'],
            ['nama' => 'Dosen Tidak Tetap'],
            ['nama' => 'Dosen Pengajar'],
            ['nama' => 'Dosen Pembimbing'],
            
            // Kepala
            ['nama' => 'Kepala Jurusan'],
            ['nama' => 'Kepala Bagian'],
            ['nama' => 'Kepala Laboratorium'],
            ['nama' => 'Kepala Perpustakaan'],
            
            // Administrasi
            ['nama' => 'Administrasi'],
            ['nama' => 'Staff Administrasi'],
            ['nama' => 'Sekretaris Jurusan'],
            ['nama' => 'Sekretaris Bagian'],
            
            // Teknis
            ['nama' => 'Teknisi'],
            ['nama' => 'Teknisi Lab'],
            ['nama' => 'Teknisi IT'],
            ['nama' => 'Teknisi Sarpras'],
            
            // Pendukung
            ['nama' => 'Pustakawan'],
            ['nama' => 'Security'],
            ['nama' => 'Cleaning Service'],
            ['nama' => 'Driver'],
            ['nama' => 'Gardener'],
        ];

        foreach ($positionData as $data) {
            $position = Position::updateOrCreate(
                ['nama' => $data['nama']], // Find by name
                $data // Update or create with this data
            );
            
            $action = $position->wasRecentlyCreated ? 'Created' : 'Updated';
            $this->command->info("   ✓ {$action}: {$data['nama']}");
        }
    }
}
