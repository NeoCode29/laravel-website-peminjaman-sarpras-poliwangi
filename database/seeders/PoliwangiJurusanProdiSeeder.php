<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurusan;
use App\Models\Prodi;

class PoliwangiJurusanProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed data khusus jurusan dan prodi Poliwangi
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🎓 Seeding Jurusan dan Prodi Poliwangi...');
        
        // Clear existing data first
        $this->clearExistingData();
        
        // Seed Jurusan
        $this->seedJurusan();
        
        // Seed Prodi
        $this->seedProdi();
        
        // Display summary
        $this->displaySummary();
        
        $this->command->info('✅ Data Jurusan dan Prodi Poliwangi berhasil di-seed!');
    }

    /**
     * Clear existing data
     */
    private function clearExistingData()
    {
        $this->command->info('🗑️  Clearing existing jurusan dan prodi data...');
        
        // Disable foreign key checks temporarily
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear in reverse order due to foreign key constraints
        Prodi::truncate();
        Jurusan::truncate();
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
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
            // TEKNIK SIPIL (ID: 1) - 4 prodi
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
            
            // TEKNIK MESIN (ID: 2) - 3 prodi
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
            
            // BISNIS & INFORMATIKA (ID: 3) - 3 prodi
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
            
            // PARIWISATA (ID: 4) - 3 prodi
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
            
            // PERTANIAN (ID: 5) - 6 prodi
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
     * Display summary of seeded data
     */
    private function displaySummary()
    {
        $this->command->info('');
        $this->command->info('📊 SUMMARY DATA POLIWANGI:');
        $this->command->info('========================');
        
        // Count jurusan
        $jurusanCount = Jurusan::count();
        $this->command->info("📚 Total Jurusan: {$jurusanCount}");
        
        // Count prodi
        $prodiCount = Prodi::count();
        $this->command->info("🎓 Total Prodi: {$prodiCount}");
        
        // Count by jenjang
        $d3Count = Prodi::where('jenjang', 'D3')->count();
        $d4Count = Prodi::where('jenjang', 'D4')->count();
        $this->command->info("📈 Distribusi Jenjang: D3 ({$d3Count}), D4 ({$d4Count})");
        
        // Count by jurusan
        $this->command->info('');
        $this->command->info('📋 Detail per Jurusan:');
        $jurusanList = Jurusan::withCount('prodi')->get();
        foreach ($jurusanList as $jurusan) {
            $this->command->info("   • {$jurusan->nama_jurusan}: {$jurusan->prodi_count} prodi");
        }
        
        $this->command->info('');
        $this->command->info('✅ Data siap digunakan untuk setup profil mahasiswa!');
    }
}
