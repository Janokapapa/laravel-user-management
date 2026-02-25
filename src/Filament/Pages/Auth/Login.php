<?php

namespace JanDev\UserManagement\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        // Register render hook for social login buttons
        if ($this->hasSocialProviders()) {
            \Filament\Support\Facades\FilamentView::registerRenderHook(
                \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn () => view('user-management::social-login', [
                    'providers' => $this->getSocialProviders(),
                ]),
                scopes: static::class,
            );
        }
    }

    protected function hasSocialProviders(): bool
    {
        return config('user-management.social_login.enabled', false)
            && !empty(config('user-management.social_login.providers', []));
    }

    public function getSocialProviders(): array
    {
        return config('user-management.social_login.providers', []);
    }
}
