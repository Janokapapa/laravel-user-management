<?php

namespace JanDev\UserManagement;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JanDev\UserManagement\Console\Commands\SetupCommand;
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

        // Register socialite routes
        $this->registerRoutes();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupCommand::class,
            ]);
        }
    }

    protected function registerRoutes(): void
    {
        if (!config('user-management.social_login.enabled', false)) {
            return;
        }

        Route::middleware('web')
            ->group(function () {
                Route::get('auth/{provider}/redirect', [Http\Controllers\SocialiteController::class, 'redirect'])
                    ->name('user-management.socialite.redirect');
                Route::get('auth/{provider}/callback', [Http\Controllers\SocialiteController::class, 'callback'])
                    ->name('user-management.socialite.callback');
            });
    }
}
