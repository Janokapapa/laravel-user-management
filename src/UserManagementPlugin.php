<?php

namespace JanDev\UserManagement;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JanDev\UserManagement\Filament\Resources\UserResource;
use JanDev\UserManagement\Filament\Resources\RoleResource;
use JanDev\UserManagement\Filament\Resources\PermissionResource;

class UserManagementPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'user-management';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            UserResource::class,
            RoleResource::class,
            PermissionResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
