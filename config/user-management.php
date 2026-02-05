<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The model used for users. Must implement FilamentUser interface
    | and use the HasRoles trait from Spatie Permission.
    |
    */
    'user_model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Allow All Users
    |--------------------------------------------------------------------------
    |
    | If true, all authenticated users can access the admin panel.
    | If false, users need 'access admin' permission or super-admin role.
    |
    */
    'allow_all_users' => true,

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | The navigation group for user management resources in Filament.
    |
    */
    'navigation_group' => 'User Management',

    /*
    |--------------------------------------------------------------------------
    | Navigation Sort
    |--------------------------------------------------------------------------
    |
    | The sort order for the navigation group.
    |
    */
    'navigation_sort' => 100,

    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | Permissions that will be created by the seeder.
    |
    */
    'default_permissions' => [
        'access admin',
        'manage users',
        'manage roles',
        'manage permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    |
    | Roles that will be created by the seeder with their permissions.
    |
    */
    'default_roles' => [
        'super-admin' => ['*'], // All permissions
        'admin' => [
            'access admin',
            'manage users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Login
    |--------------------------------------------------------------------------
    |
    | Enable social login providers. Set credentials in config/services.php
    |
    */
    'social_login' => [
        'enabled' => true,
        'providers' => ['google'],
        'auto_register' => true, // Auto-create user if not exists
        'default_role' => null, // Role to assign to new users (null = no role)
    ],
];
