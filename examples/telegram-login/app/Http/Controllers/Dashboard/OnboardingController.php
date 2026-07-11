<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\TelegramBot;
use App\Services\Telegram\OidcClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View
    {
        $tenant = auth()->user()->tenant()->with('applications.telegramBot')->first();

        return view('dashboard.onboarding', [
            'tenant' => $tenant,
            'application' => $tenant?->applications->first(),
        ]);
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'redirect_uri' => ['required', 'url'],
        ]);

        $tenant = auth()->user()->tenant;
        $plainSecret = \Illuminate\Support\Str::random(64);

        $application = Application::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'client_secret' => $plainSecret,
            'redirect_uris' => [$validated['redirect_uri']],
        ]);

        return redirect()->route('dashboard.onboarding')
            ->with('status', 'Application created.')
            ->with('client_secret', $plainSecret)
            ->with('client_id', $application->client_id);
    }

    public function storeBot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'bot_username' => ['required', 'string', 'max:255'],
            'bot_token' => ['required', 'string'],
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $application = Application::query()
            ->where('id', $validated['application_id'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        TelegramBot::query()->updateOrCreate(
            ['application_id' => $application->id],
            [
                'bot_username' => ltrim($validated['bot_username'], '@'),
                'bot_token' => $validated['bot_token'],
                'domains' => [$this->normalizeDomain($validated['domain'])],
            ]
        );

        return redirect()->route('dashboard.onboarding')->with('status', 'Telegram bot connected.');
    }

    public function generateTestLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
        ]);

        $application = Application::query()
            ->where('id', $validated['application_id'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with('telegramBot')
            ->firstOrFail();

        if (! $application->telegramBot) {
            return back()->withErrors(['bot' => 'Connect a Telegram bot before generating a test link.']);
        }

        $codeVerifier = OidcClient::generateCodeVerifier();
        $codeChallenge = OidcClient::generateCodeChallenge($codeVerifier);
        $state = Str::random(32);
        $redirectUri = $application->redirect_uris[0];

        $loginUrl = url('/auth/start').'?'.http_build_query([
            'client_id' => $application->client_id,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'flow' => 'widget',
        ]);

        return redirect()->route('dashboard.onboarding')->with([
            'status' => 'Test link generated. Open it in a new tab (not an iframe).',
            'test_login_url' => $loginUrl,
            'test_code_verifier' => $codeVerifier,
            'test_state' => $state,
            'test_redirect_uri' => $redirectUri,
            'test_client_id' => $application->client_id,
        ]);
    }

    public function enableDemoShop(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
        ]);

        $application = Application::query()
            ->where('id', $validated['application_id'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $demoCallback = route('demo-shop.callback');
        $uris = $application->redirect_uris ?? [];

        if (! in_array($demoCallback, $uris, true)) {
            $uris[] = $demoCallback;
            $application->update(['redirect_uris' => $uris]);
        }

        return redirect()->route('dashboard.onboarding')
            ->with('status', 'Demo shop enabled. Try it at '.route('demo-shop.index'));
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = trim($domain);
        $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;

        return rtrim($domain, '/');
    }
}
