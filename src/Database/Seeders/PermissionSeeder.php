<?php

namespace JanDev\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = config('user-management.default_permissions', [
            'access admin',
            'manage users',
            'manage roles',
            'manage permissions',
        ]);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with permissions
        $roles = config('user-management.default_roles', [
            'super-admin' => ['*'],
            'admin' => ['access admin', 'manage users'],
        ]);

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if (in_array('*', $rolePermissions)) {
                // Super admin gets all permissions
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($rolePermissions);
            }
        }
    }
}
