<?php

namespace JanDev\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions from config
        $permissions = config('user-management.default_permissions', []);
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles from config
        $roles = config('user-management.default_roles', []);
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // Assign permissions (skip '*' which means all)
            if ($rolePermissions !== ['*'] && !empty($rolePermissions)) {
                $role->syncPermissions($rolePermissions);
            }
        }
    }
}
