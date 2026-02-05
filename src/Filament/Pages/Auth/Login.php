<?php

namespace JanDev\UserManagement\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),
                $this->getFormContentComponent(),
                $this->getMultiFactorChallengeFormContentComponent(),
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
                $this->getSocialLoginComponent(),
            ]);
    }

    protected function getSocialLoginComponent(): Component
    {
        $providers = $this->getSocialProviders();

        if (empty($providers)) {
            return \Filament\Schemas\Components\Placeholder::make('no-social')
                ->hiddenLabel()
                ->content('');
        }

        $html = $this->renderSocialButtons($providers);

        return \Filament\Schemas\Components\Placeholder::make('social-login')
            ->hiddenLabel()
            ->content(new HtmlString($html));
    }

    protected function renderSocialButtons(array $providers): string
    {
        $buttons = '';

        foreach ($providers as $provider) {
            $url = $this->getSocialLoginUrl($provider);

            if ($provider === 'google') {
                $buttons .= <<<HTML
                <a href="{$url}" class="flex items-center justify-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span>Sign in with Google</span>
                </a>
                HTML;
            } else {
                $providerName = ucfirst($provider);
                $buttons .= <<<HTML
                <a href="{$url}" class="flex items-center justify-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                    <span>Sign in with {$providerName}</span>
                </a>
                HTML;
            }
        }

        return <<<HTML
        <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-gray-900 text-gray-500">or continue with</span>
            </div>
        </div>
        <div class="flex flex-col gap-3">
            {$buttons}
        </div>
        HTML;
    }

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
