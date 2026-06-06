<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GitHubAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GitHubAuthController extends Controller
{
    public function __construct(private GitHubAuthenticator $github) {}

    public function redirect(): RedirectResponse
    {
        $this->ensureEnabled();

        return Socialite::driver('github')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $this->ensureEnabled();

        $githubUser = Socialite::driver('github')->user();
        $user = $this->github->authenticate($githubUser);

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function ensureEnabled(): void
    {
        if (! $this->github->isEnabled()) {
            throw new NotFoundHttpException;
        }
    }
}
