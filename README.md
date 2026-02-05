# Laravel User Management

User, Role and Permission management for Laravel with Filament 4 integration. Built on top of Spatie Laravel Permission.

## Installation

```bash
composer require jandev/laravel-user-management
```

## Setup

### 1. Publish and run migrations

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 2. Update your User model

Add the `HasUserManagement` trait to your User model:

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use JanDev\UserManagement\Traits\HasUserManagement;

class User extends Authenticatable implements FilamentUser
{
    use HasUserManagement;

    // ... rest of your model
}
```

### 3. Register the plugin in your Filament Panel

In `app/Providers/Filament/AdminPanelProvider.php`:

```php
use JanDev\UserManagement\UserManagementPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other config
        ->plugins([
            UserManagementPlugin::make(),
        ]);
}
```

### 4. Seed default permissions and roles

```bash
php artisan db:seed --class="JanDev\UserManagement\Database\Seeders\PermissionSeeder"
```

### 5. Assign super-admin role to your user

```bash
php artisan tinker
>>> $user = User::first();
>>> $user->assignRole('super-admin');
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="user-management-config"
```

Available options in `config/user-management.php`:

```php
return [
    // User model class
    'user_model' => App\Models\User::class,

    // Navigation group in Filament
    'navigation_group' => 'User Management',

    // Navigation sort order
    'navigation_sort' => 100,

    // Default permissions created by seeder
    'default_permissions' => [
        'access admin',
        'manage users',
        'manage roles',
        'manage permissions',
    ],

    // Default roles with their permissions
    'default_roles' => [
        'super-admin' => ['*'], // All permissions
        'admin' => ['access admin', 'manage users'],
    ],
];
```

## Features

- **User Management**: Create, edit, delete users with role assignment
- **Role Management**: Create roles and assign permissions
- **Permission Management**: Create and manage granular permissions
- **Filament Integration**: Full Filament 4 admin panel integration
- **Spatie Permission**: Built on the battle-tested Spatie Laravel Permission package

## License

MIT
