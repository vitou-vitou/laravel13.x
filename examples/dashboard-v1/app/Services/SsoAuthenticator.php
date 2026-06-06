<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Spatie\Permission\Models\Role;

class SsoAuthenticator
{
    /** @var list<string> */
    private const PROVIDERS = ['microsoft', 'google'];

    /** @return list<string> */
    public function enabledProviders(): array
    {
        return array_values(array_filter(
            self::PROVIDERS,
            fn (string $provider): bool => $this->isEnabled($provider),
        ));
    }

    public function isEnabled(string $provider): bool
    {
        if (! in_array($provider, self::PROVIDERS, true)) {
            return false;
        }

        return filled(config("services.{$provider}.client_id"))
            && filled(config("services.{$provider}.client_secret"));
    }

    public function authenticate(string $provider, SocialiteUser $socialUser): User
    {
        $ssoId = (string) $socialUser->getId();
        $email = $socialUser->getEmail();

        if ($email === null || $email === '') {
            abort(422, 'The identity provider did not return an email address.');
        }

        $bySso = User::query()
            ->where('sso_provider', $provider)
            ->where('sso_id', $ssoId)
            ->first();

        if ($bySso !== null) {
            return $bySso;
        }

        $byEmail = User::query()->where('email', $email)->first();

        if ($byEmail !== null) {
            $byEmail->forceFill([
                'sso_provider' => $provider,
                'sso_id' => $ssoId,
                'email_verified_at' => $byEmail->email_verified_at ?? now(),
            ])->save();

            return $byEmail->fresh();
        }

        $user = User::query()->create([
            'name' => $socialUser->getName() ?? Str::before($email, '@'),
            'email' => $email,
            'password' => Str::password(32),
            'sso_provider' => $provider,
            'sso_id' => $ssoId,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        Role::findOrCreate('customer');
        $user->assignRole('customer');

        return $user;
    }
}
