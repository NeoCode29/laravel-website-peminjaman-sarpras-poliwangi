<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'max_duration_days', 'value' => '7', 'description' => 'Durasi maksimal peminjaman (hari)', 'type' => 'integer', 'is_editable' => true],
            ['key' => 'event_gap_hours', 'value' => '0', 'description' => 'Jeda antar acara (jam)', 'type' => 'integer', 'is_editable' => true],
            ['key' => 'marking_duration_days', 'value' => '3', 'description' => 'Masa berlaku marking (hari)', 'type' => 'integer', 'is_editable' => true],
            ['key' => 'max_planned_submit_days', 'value' => '7', 'description' => 'Batas waktu submit pengajuan (hari)', 'type' => 'integer', 'is_editable' => true],
            ['key' => 'max_active_borrowings', 'value' => '3', 'description' => 'Batas kuota peminjaman aktif per user', 'type' => 'integer', 'is_editable' => true],
            ['key' => 'notifications_enabled', 'value' => 'true', 'description' => 'Aktifkan notifikasi in-web', 'type' => 'boolean', 'is_editable' => true],
        ];

        foreach ($settings as $s) {
            SystemSetting::updateOrCreate(
                ['key' => $s['key']],
                [
                    'value' => $s['value'],
                    'description' => $s['description'],
                    'type' => $s['type'],
                    'is_editable' => $s['is_editable'],
                ]
            );
        }
    }
}
