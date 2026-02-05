<?php

namespace JanDev\UserManagement\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    protected string $view = 'user-management::filament.pages.auth.login';

    public function getSocialProviders(): array
    {
        if (!config('user-management.social_login.enabled', false)) {
            return [];
        }

        return config('user-management.social_login.providers', []);
    }

    public function getSocialLoginUrl(string $provider): string
    {
        return route('user-management.socialite.redirect', $provider);
    }
}
