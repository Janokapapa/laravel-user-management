# Laravel User Management

User, Role and Permission management for Laravel with Filament 4 integration. Built on top of Spatie Laravel Permission.

## Features

- **User Management**: Create, edit, delete users with role assignment
- **Role Management**: Create roles and assign permissions
- **Permission Management**: Create and manage granular permissions
- **Filament 4 Integration**: Full admin panel integration
- **Social Login**: Google OAuth integration
- **Role-based Access Control**: Built-in canAccess, canCreate, canEdit, canDelete methods

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

### 2. Setup roles and permissions

```bash
php artisan user-management:setup
```

This creates the default roles and permissions from your config.

### 3. Update your User model

Add the `HasUserManagement` trait:

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use JanDev\UserManagement\Traits\HasUserManagement;

class User extends Authenticatable implements FilamentUser
{
    use HasUserManagement;
}
```

### 4. Register the plugin in Filament Panel

In `app/Providers/Filament/AdminPanelProvider.php`:

```php
use JanDev\UserManagement\UserManagementPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            UserManagementPlugin::make(),
        ]);
}
```

### 5. Assign super-admin role

```bash
php artisan tinker
>>> User::where('email', 'your@email.com')->first()->assignRole('super-admin');
```

## Configuration

```bash
php artisan vendor:publish --tag="user-management-config"
```

Config options in `config/user-management.php`:

```php
return [
    // User model
    'user_model' => App\Models\User::class,

    // Allow all authenticated users to access admin panel
    'allow_all_users' => true,

    // Filament navigation group
    'navigation_group' => 'User Management',
    'navigation_sort' => 100,

    // Default permissions (created by setup command)
    'default_permissions' => [
        'access admin',
        'manage users',
        'manage roles',
        'manage permissions',
        'manage content',
    ],

    // Default roles with permissions
    'default_roles' => [
        'super-admin' => ['*'],
        'admin' => ['access admin', 'manage users', 'manage roles', 'manage permissions'],
        'editor' => ['access admin', 'manage content'],
        'guest' => [], // Dashboard only
    ],

    // Social login
    'social_login' => [
        'enabled' => true,
        'providers' => ['google'],
        'auto_register' => true,
        'default_role' => 'guest',
    ],
];
```

## Social Login (Google OAuth)

### 1. Create Google OAuth credentials

[Google Cloud Console](https://console.cloud.google.com/apis/credentials) → OAuth 2.0 credentials:
- Authorized redirect URI: `https://yourdomain.com/auth/google/callback`

### 2. Add to config/services.php

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

### 3. Add to .env

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback
```

## Role-based Access Control

All resources have built-in permission checks:

- **UserResource**: Requires `super-admin` role or `manage users` permission
- **RoleResource**: Requires `super-admin` role or `manage roles` permission
- **PermissionResource**: Requires `super-admin` role or `manage permissions` permission

### Protecting your own resources

```php
use Filament\Resources\Resource;

class YourResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super-admin')
            || auth()->user()?->hasRole('editor')
            || auth()->user()?->hasPermissionTo('manage content');
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canAccess();
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canAccess();
    }
}
```

## License

MIT
