<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GitHubAuthenticator
{
    public function isEnabled(): bool
    {
        return filled(config('services.github.client_id'))
            && filled(config('services.github.client_secret'));
    }

    public function authenticate(SocialiteUser $githubUser): User
    {
        $githubId = (string) $githubUser->getId();
        $email = $githubUser->getEmail();

        if ($email === null || $email === '') {
            abort(422, 'GitHub did not return an email address.');
        }

        $byGitHub = User::query()->where('github_id', $githubId)->first();

        if ($byGitHub !== null) {
            $this->syncProfile($byGitHub, $githubUser);
            $byGitHub->save();

            return $byGitHub->fresh();
        }

        $byEmail = User::query()->where('email', $email)->first();

        if ($byEmail !== null) {
            $byEmail->forceFill([
                'github_id' => $githubId,
                'email_verified_at' => $byEmail->email_verified_at ?? now(),
            ]);
            $this->syncProfile($byEmail, $githubUser);
            $byEmail->save();

            return $byEmail->fresh();
        }

        $user = User::query()->create([
            'name' => $githubUser->getName() ?? Str::before($email, '@'),
            'email' => $email,
            'password' => Str::password(32),
            'github_id' => $githubId,
        ]);
        $this->syncProfile($user, $githubUser);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->fresh();
    }

    private function syncProfile(User $user, SocialiteUser $githubUser): void
    {
        $avatar = $githubUser->getAvatar();

        if ($avatar !== null && $avatar !== '') {
            $user->avatar = $avatar;
        }

        $name = $githubUser->getName();

        if ($name !== null && $name !== '') {
            $user->name = $name;
        }
    }
}
