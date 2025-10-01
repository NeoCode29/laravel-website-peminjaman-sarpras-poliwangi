<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed kategori prasarana
        $kategoriPrasarana = [
            ['name' => 'Aula', 'description' => 'Ruang aula untuk acara besar', 'icon' => 'fas fa-building'],
            ['name' => 'Ruang Meeting', 'description' => 'Ruang rapat dan meeting', 'icon' => 'fas fa-users'],
            ['name' => 'Laboratorium', 'description' => 'Ruang laboratorium', 'icon' => 'fas fa-flask'],
            ['name' => 'Ruang Kelas', 'description' => 'Ruang kelas untuk pembelajaran', 'icon' => 'fas fa-chalkboard-teacher'],
        ];

        foreach ($kategoriPrasarana as $kategori) {
            DB::table('kategori_prasarana')->updateOrInsert(
                ['name' => $kategori['name']],
                array_merge($kategori, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Seed prasarana
        $prasarana = [
            [
                'name' => 'Aula Utama',
                'kategori_id' => 1,
                'description' => 'Aula utama dengan kapasitas 200 orang',
                'status' => 'tersedia',
                'kapasitas' => 200,
                'lokasi' => 'Gedung A Lantai 2',
                'created_by' => 1,
            ],
            [
                'name' => 'Ruang Meeting 1',
                'kategori_id' => 2,
                'description' => 'Ruang meeting dengan kapasitas 20 orang',
                'status' => 'tersedia',
                'kapasitas' => 20,
                'lokasi' => 'Gedung B Lantai 1',
                'created_by' => 1,
            ],
            [
                'name' => 'Laboratorium Komputer',
                'kategori_id' => 3,
                'description' => 'Laboratorium komputer dengan 30 unit PC',
                'status' => 'tersedia',
                'kapasitas' => 30,
                'lokasi' => 'Gedung C Lantai 2',
                'created_by' => 1,
            ],
        ];

        foreach ($prasarana as $item) {
            DB::table('prasarana')->updateOrInsert(
                ['name' => $item['name']],
                array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Seed sample peminjaman
        $peminjaman = [
            [
                'user_id' => 1,
                'prasarana_id' => 1,
                'event_name' => 'Seminar Teknologi',
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'prasarana_id' => 2,
                'event_name' => 'Rapat Koordinasi',
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'status' => 'approved',
                'approved_by' => 1,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($peminjaman as $item) {
            DB::table('peminjaman')->updateOrInsert(
                [
                    'user_id' => $item['user_id'],
                    'prasarana_id' => $item['prasarana_id'],
                    'start_date' => $item['start_date'],
                ],
                $item
            );
        }

        // Seed sample notifications
        $notifications = [
            [
                'user_id' => 1,
                'title' => 'Peminjaman Disetujui',
                'message' => 'Pengajuan Anda untuk Aula Utama telah disetujui',
                'type' => 'peminjaman_approved',
                'action_url' => '/peminjaman/1',
                'is_clickable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'title' => 'Peminjaman Menunggu Persetujuan',
                'message' => 'Pengajuan Anda untuk Ruang Meeting 1 sedang menunggu persetujuan',
                'type' => 'peminjaman_pending',
                'action_url' => '/peminjaman/2',
                'is_clickable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($notifications as $item) {
            DB::table('notifications')->updateOrInsert(
                [
                    'user_id' => $item['user_id'],
                    'title' => $item['title'],
                    'type' => $item['type'],
                ],
                $item
            );
        }

        // Seed sample audit logs
        $auditLogs = [
            [
                'user_id' => 1,
                'action' => 'create',
                'model_type' => 'Peminjaman',
                'model_id' => 1,
                'description' => 'Peminjaman baru dibuat',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now(),
            ],
            [
                'user_id' => 1,
                'action' => 'approve',
                'model_type' => 'Peminjaman',
                'model_id' => 2,
                'description' => 'Peminjaman disetujui oleh admin',
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now(),
            ],
        ];

        foreach ($auditLogs as $item) {
            DB::table('audit_logs')->insert($item);
        }

        // Seed user quotas
        $userQuotas = [
            [
                'user_id' => 1,
                'max_active_borrowings' => 10,
                'current_borrowings' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($userQuotas as $item) {
            DB::table('user_quotas')->updateOrInsert(
                ['user_id' => $item['user_id']],
                $item
            );
        }
    }
}
