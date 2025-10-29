<?php

/**
 * Complete System Settings Setup Command
 * 
 * This command will:
 * 1. Run the system_settings migration
 * 2. Seed the default system settings
 * 3. Fix permissions
 * 4. Clear cache
 */

echo "Starting complete system settings setup...\n\n";

// 1. Run migration
echo "1. Running system_settings migration...\n";
$migrationResult = shell_exec('php artisan migrate --path=database/migrations/2025_01_28_000001_create_system_settings_table.php');
echo $migrationResult . "\n";

// 2. Run seeder
echo "2. Seeding system settings...\n";
$seederResult = shell_exec('php artisan db:seed --class=SystemSettingSeeder');
echo $seederResult . "\n";

// 3. Fix permissions
echo "3. Fixing permissions...\n";
include 'fix_system_settings_permission.php';

// 4. Clear cache
echo "4. Clearing cache...\n";
$cacheResult = shell_exec('php artisan cache:clear');
echo $cacheResult . "\n";

$configResult = shell_exec('php artisan config:clear');
echo $configResult . "\n";

$viewResult = shell_exec('php artisan view:clear');
echo $viewResult . "\n";

echo "\nSystem settings setup completed successfully!\n";
echo "You can now access /system-settings as an admin user.\n";



