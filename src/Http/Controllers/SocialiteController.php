<?php

namespace JanDev\UserManagement\Http\Controllers;

use Illuminate\Auth\Events\Registered;
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
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            $redirectTo = config('user-management.social_login.redirect_to');

            return redirect($redirectTo ?? filament()->getPanel('admin')->getUrl())
                ->with('error', 'Authentication failed. Please try again.');
        }

        $userModel = config('user-management.user_model');
        $isNew = false;
        $user = $userModel::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            if (!config('user-management.social_login.auto_register', true)) {
                $redirectTo = config('user-management.social_login.redirect_to');

                return redirect($redirectTo ?? filament()->getPanel('admin')->getUrl())
                    ->with('error', 'No account found with this email.');
            }

            $user = $userModel::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(24)),
                'email_verified_at' => now(),
            ]);

            $isNew = true;

            // Assign default role if configured
            $defaultRole = config('user-management.social_login.default_role');
            if ($defaultRole && method_exists($user, 'assignRole')) {
                $user->assignRole($defaultRole);
            }

            event(new Registered($user));
        }

        // Save social provider ID on user if the column exists
        $providerIdColumn = $provider . '_id';
        if (\Schema::hasColumn($user->getTable(), $providerIdColumn)) {
            $user->update([$providerIdColumn => $socialUser->getId()]);
        }

        Auth::login($user, remember: true);

        // Execute after_login callback if configured
        $callbackClass = config('user-management.social_login.after_login');
        if ($callbackClass && class_exists($callbackClass)) {
            app($callbackClass)->handle($user, $provider, $socialUser, $isNew);
        }

        $redirectTo = config('user-management.social_login.redirect_to');

        return redirect()->intended(
            $redirectTo ?? filament()->getPanel('admin')->getUrl()
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
