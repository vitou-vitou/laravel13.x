<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\Auth\AuthEngine;
use App\Services\Telegram\OidcClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DemoShopController extends Controller
{
    public function index(): View
    {
        $application = $this->resolveDemoApplication();

        return view('demo-shop.index', [
            'application' => $application,
            'ready' => $application?->telegramBot !== null
                && $application->allowsRedirectUri(route('demo-shop.callback')),
            'demoCallbackUri' => route('demo-shop.callback'),
        ]);
    }

    public function login(Request $request): RedirectResponse|View
    {
        $application = $this->resolveDemoApplication();

        if (! $application?->telegramBot) {
            return redirect()->route('demo-shop.index')
                ->with('error', 'Demo shop is not configured yet. A tenant must connect a Telegram bot first.');
        }

        $codeVerifier = OidcClient::generateCodeVerifier();
        $codeChallenge = OidcClient::generateCodeChallenge($codeVerifier);
        $state = Str::random(32);
        $redirectUri = route('demo-shop.callback');

        if (! $application->allowsRedirectUri($redirectUri)) {
            return redirect()->route('demo-shop.index')
                ->with('error', 'Add this redirect URI to your app in onboarding, then try again: '.$redirectUri);
        }

        session([
            'demo_code_verifier' => $codeVerifier,
            'demo_state' => $state,
            'demo_redirect_uri' => $redirectUri,
            'demo_application_id' => $application->id,
        ]);

        $startUrl = route('auth.start').'?'.http_build_query([
            'client_id' => $application->client_id,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'flow' => 'widget',
        ]);

        return redirect()->to($startUrl);
    }

    public function callback(Request $request, AuthEngine $authEngine): View|RedirectResponse
    {
        if ($request->query('state') !== session('demo_state')) {
            return redirect()->route('demo-shop.index')
                ->with('error', 'Login failed: invalid state (possible CSRF).');
        }

        $code = $request->query('code');

        if (! is_string($code) || $code === '') {
            return redirect()->route('demo-shop.index')
                ->with('error', 'Login failed: no authorization code received.');
        }

        $application = Application::query()
            ->with('telegramBot')
            ->find(session('demo_application_id'));

        if (! $application) {
            return redirect()->route('demo-shop.index')
                ->with('error', 'Demo session expired. Please try again.');
        }

        $tokens = $authEngine->exchangeAuthCode(
            $application,
            $code,
            session('demo_code_verifier', ''),
            session('demo_redirect_uri', route('demo-shop.callback'))
        );

        session()->forget(['demo_code_verifier', 'demo_state', 'demo_redirect_uri', 'demo_application_id']);

        if (! $tokens) {
            return redirect()->route('demo-shop.index')
                ->with('error', 'Login failed: could not exchange authorization code.');
        }

        session(['demo_logged_in_user' => $tokens['user']]);

        return redirect()->route('demo-shop.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('demo_logged_in_user');

        return redirect()->route('demo-shop.index');
    }

    private function resolveDemoApplication(): ?Application
    {
        $configuredId = config('telegramauth.demo.application_id');

        if ($configuredId) {
            return Application::query()
                ->where('is_active', true)
                ->with('telegramBot')
                ->find($configuredId);
        }

        return Application::query()
            ->where('is_active', true)
            ->whereHas('telegramBot')
            ->with('telegramBot')
            ->latest()
            ->first();
    }
}
