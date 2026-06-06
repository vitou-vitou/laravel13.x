<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SsoAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SsoController extends Controller
{
    public function __construct(private SsoAuthenticator $sso) {}

    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureProviderEnabled($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->ensureProviderEnabled($provider);

        $socialUser = Socialite::driver($provider)->user();
        $user = $this->sso->authenticate($provider, $socialUser);

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function ensureProviderEnabled(string $provider): void
    {
        if (! $this->sso->isEnabled($provider)) {
            throw new NotFoundHttpException;
        }
    }
}
