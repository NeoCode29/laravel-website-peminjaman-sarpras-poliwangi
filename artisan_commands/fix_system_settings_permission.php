<?php

/**
 * Fix System Settings Permission Command
 * 
 * This command will:
 * 1. Check if system.settings permission exists
 * 2. Check if admin role has system.settings permission
 * 3. Fix permission if needed
 */

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "Checking system.settings permission...\n";

// Check if permission exists
$permission = Permission::where('name', 'system.settings')->first();
if (!$permission) {
    echo "Creating system.settings permission...\n";
    $permission = Permission::create([
        'name' => 'system.settings',
        'display_name' => 'Mengatur setting sistem',
        'description' => 'Dapat mengatur setting sistem',
        'category' => 'system'
    ]);
    echo "Permission created successfully!\n";
} else {
    echo "Permission already exists.\n";
}

// Check if admin role exists
$adminRole = Role::where('name', 'admin')->first();
if (!$adminRole) {
    echo "Admin role not found! Please run RolePermissionSeeder first.\n";
    exit(1);
}

// Check if admin has permission
if (!$adminRole->hasPermissionTo('system.settings')) {
    echo "Adding system.settings permission to admin role...\n";
    $adminRole->givePermissionTo('system.settings');
    echo "Permission added to admin role!\n";
} else {
    echo "Admin role already has system.settings permission.\n";
}

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Permission cache cleared.\n";

echo "System settings permission setup completed!\n";

