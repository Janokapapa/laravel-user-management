<?php

namespace JanDev\UserManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Authentication failed. Please try again.');
        }

        $userModel = config('user-management.user_model');
        $user = $userModel::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            if (!config('user-management.social_login.auto_register', true)) {
                return redirect()->route('filament.admin.auth.login')
                    ->with('error', 'No account found with this email.');
            }

            $user = $userModel::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(24)),
                'email_verified_at' => now(),
            ]);

            // Assign default role if configured
            $defaultRole = config('user-management.social_login.default_role');
            if ($defaultRole && method_exists($user, 'assignRole')) {
                $user->assignRole($defaultRole);
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended(
            filament()->getPanel('admin')->getUrl()
        );
    }

    protected function validateProvider(string $provider): void
    {
        $providers = config('user-management.social_login.providers', []);

        if (!in_array($provider, $providers)) {
            abort(404, 'Provider not supported.');
        }
    }
}
