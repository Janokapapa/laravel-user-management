<?php

namespace JanDev\UserManagement;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JanDev\UserManagement\Filament\Pages\Auth\Login;
use JanDev\UserManagement\Filament\Resources\UserResource;
use JanDev\UserManagement\Filament\Resources\RoleResource;
use JanDev\UserManagement\Filament\Resources\PermissionResource;

class UserManagementPlugin implements Plugin
{
    protected bool $socialLogin = true;

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

    public function socialLogin(bool $enabled = true): static
    {
        $this->socialLogin = $enabled;

        return $this;
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            UserResource::class,
            RoleResource::class,
            PermissionResource::class,
        ]);

        // Override login page if social login is enabled
        if ($this->socialLogin && config('user-management.social_login.enabled', false)) {
            $panel->login(Login::class);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
