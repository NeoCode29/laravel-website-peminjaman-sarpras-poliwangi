<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Unit;
use App\Models\Position;
use App\Models\Student;
use App\Models\StaffEmployee;

class PoliwangiSafeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder aman yang menangani foreign key constraints dengan baik
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🌱 Seeding data Poliwangi (Safe Mode)...');
        
        // Clear existing data safely
        $this->clearExistingDataSafely();
        
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
     * Clear existing data safely without foreign key issues
     */
    private function clearExistingDataSafely()
    {
        $this->command->info('🗑️  Clearing existing data safely...');
        
        try {
            // First, clear dependent tables
            $this->command->info('   Clearing dependent tables...');
            
            // Clear students table first (has foreign key to prodi)
            if (class_exists('App\Models\Student')) {
                Student::query()->delete();
                $this->command->info('   ✓ Students table cleared');
            }
            
            // Clear staff employees table (has foreign key to units/positions)
            if (class_exists('App\Models\StaffEmployee')) {
                StaffEmployee::query()->delete();
                $this->command->info('   ✓ Staff employees table cleared');
            }
            
            // Now clear main tables
            $this->command->info('   Clearing main tables...');
            
            Prodi::query()->delete();
            $this->command->info('   ✓ Prodi table cleared');
            
            Jurusan::query()->delete();
            $this->command->info('   ✓ Jurusan table cleared');
            
            Position::query()->delete();
            $this->command->info('   ✓ Position table cleared');
            
            Unit::query()->delete();
            $this->command->info('   ✓ Unit table cleared');
            
        } catch (\Exception $e) {
            $this->command->warn('   ⚠️  Some tables might not exist or have different structure');
            $this->command->warn('   Error: ' . $e->getMessage());
            
            // Try alternative approach with truncate and foreign key disable
            $this->command->info('   Trying alternative approach...');
            
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            Prodi::truncate();
            Jurusan::truncate();
            Position::truncate();
            Unit::truncate();
            
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->info('   ✓ Alternative approach successful');
        }
    }

    /**
     * Seed Jurusan Poliwangi
     */
    private function seedJurusan()
    {
        $this->command->info('📚 Seeding 5 jurusan...');
        
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
            Jurusan::create($data);
            $this->command->info("   ✓ {$data['nama_jurusan']}");
        }
    }

    /**
     * Seed Prodi Poliwangi
     */
    private function seedProdi()
    {
        $this->command->info('🎓 Seeding 18 program studi...');
        
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
            Prodi::create($data);
            $this->command->info("   ✓ {$data['nama_prodi']} ({$data['jenjang']})");
        }
    }

    /**
     * Seed Units Poliwangi
     */
    private function seedUnits()
    {
        $this->command->info('🏢 Seeding units...');
        
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
            Unit::create($data);
            $this->command->info("   ✓ {$data['nama']}");
        }
    }

    /**
     * Seed Positions Poliwangi
     */
    private function seedPositions()
    {
        $this->command->info('👥 Seeding positions...');
        
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
            Position::create($data);
            $this->command->info("   ✓ {$data['nama']}");
        }
    }
}
