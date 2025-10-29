<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $this->command->info('Seeding system settings...');
            
            // Initialize default settings using model method
            SystemSetting::initializeDefaults();
            
            $this->command->info('System settings seeded successfully!');
            
            // Display seeded settings
            $settings = SystemSetting::all();
            $this->command->table(
                ['Key', 'Value', 'Description'],
                $settings->map(function ($setting) {
                    return [
                        $setting->key,
                        $setting->value,
                        $setting->description
                    ];
                })->toArray()
            );

        } catch (\Exception $e) {
            Log::error('Error seeding system settings', [
                'error' => $e->getMessage()
            ]);
            
            $this->command->error('Failed to seed system settings: ' . $e->getMessage());
        }
    }
}



