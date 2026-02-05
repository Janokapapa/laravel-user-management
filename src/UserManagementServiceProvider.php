<?php

namespace JanDev\UserManagement;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class UserManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/user-management.php', 'user-management');

        // Register Spatie Permission
        $this->app->register(PermissionServiceProvider::class);
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/user-management.php' => config_path('user-management.php'),
        ], 'user-management-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations/' => database_path('migrations'),
        ], 'user-management-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/user-management'),
        ], 'user-management-views');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'user-management');
    }
}
