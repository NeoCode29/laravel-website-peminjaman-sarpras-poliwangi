<?php

/**
 * Setup System Settings Command
 * 
 * This command will:
 * 1. Run the system_settings migration
 * 2. Seed the default system settings
 * 3. Clear cache
 */

// Run migration
echo "Running system_settings migration...\n";
$migrationResult = shell_exec('php artisan migrate --path=database/migrations/2025_01_28_000001_create_system_settings_table.php');
echo $migrationResult . "\n";

// Run seeder
echo "Seeding system settings...\n";
$seederResult = shell_exec('php artisan db:seed --class=SystemSettingSeeder');
echo $seederResult . "\n";

// Clear cache
echo "Clearing cache...\n";
$cacheResult = shell_exec('php artisan cache:clear');
echo $cacheResult . "\n";

echo "System settings setup completed!\n";



