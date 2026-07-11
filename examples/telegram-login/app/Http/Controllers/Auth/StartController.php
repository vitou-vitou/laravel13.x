<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuthSession;
use App\Services\Auth\AuthEngine;
use App\Services\Telegram\OidcClient;
use App\Services\Telegram\TelegramOAuthClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StartController extends Controller
{
    public function __invoke(
        Request $request,
        AuthEngine $authEngine,
        TelegramOAuthClient $telegramOAuth
    ): RedirectResponse|View {
        $validated = $request->validate([
            'client_id' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
            'state' => ['required', 'string', 'max:128'],
            'code_challenge' => ['required', 'string', 'max:128'],
            'flow' => ['nullable', 'in:widget,oidc'],
            'id' => ['nullable'],
            'hash' => ['nullable', 'string'],
            'auth_date' => ['nullable'],
        ]);

        $application = Application::query()
            ->where('client_id', $validated['client_id'])
            ->where('is_active', true)
            ->with('telegramBot')
            ->firstOrFail();

        if (! $application->allowsRedirectUri($validated['redirect_uri'])) {
            abort(400, 'Invalid redirect_uri');
        }

        if (! $application->telegramBot) {
            abort(400, 'Telegram bot not configured for this application');
        }

        $flow = $validated['flow'] ?? 'widget';

        $session = $this->resolveSession($application, $validated, $flow, $authEngine);

        if ($this->hasTelegramAuthData($request)) {
            $telegramData = $request->only([
                'id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash',
            ]);

            $redirectUrl = $authEngine->completeWidgetLogin($session, $telegramData, $request);

            if (! $redirectUrl) {
                abort(403, 'Telegram authentication failed');
            }

            return redirect()->away($redirectUrl);
        }

        if ($flow === 'oidc') {
            $platformVerifier = OidcClient::generateCodeVerifier();
            $platformChallenge = OidcClient::generateCodeChallenge($platformVerifier);
            session(['oidc_code_verifier_'.$session->state => $platformVerifier]);

            $oidcUrl = app(OidcClient::class)->buildAuthorizationUrl(
                $application->telegramBot->bot_username,
                route('auth.callback.oidc'),
                $session->state,
                $platformChallenge
            );

            return $this->externalRedirect($oidcUrl, 'Continue to Telegram', 'Approve login in Telegram to finish signing in.');
        }

        $returnTo = route('auth.start').'?'.http_build_query([
            'client_id' => $validated['client_id'],
            'redirect_uri' => $validated['redirect_uri'],
            'state' => $validated['state'],
            'code_challenge' => $validated['code_challenge'],
            'flow' => $flow,
        ]);

        $oauthUrl = $telegramOAuth->buildAuthorizationUrl(
            $application->telegramBot,
            $returnTo
        );

        return view('auth.widget-oauth-capture', [
            'oauthUrl' => $oauthUrl,
            'startUrl' => route('auth.start'),
            'startParams' => [
                'client_id' => $validated['client_id'],
                'redirect_uri' => $validated['redirect_uri'],
                'state' => $validated['state'],
                'code_challenge' => $validated['code_challenge'],
                'flow' => $flow,
            ],
        ]);
    }

    private function externalRedirect(string $url, string $title, string $message): View
    {
        return view('auth.external-redirect', [
            'url' => $url,
            'title' => $title,
            'message' => $message,
        ]);
    }

    private function resolveSession(
        Application $application,
        array $validated,
        string $flow,
        AuthEngine $authEngine
    ): AuthSession {
        $existing = AuthSession::query()
            ->where('application_id', $application->id)
            ->where('client_state', $validated['state'])
            ->where('redirect_uri', $validated['redirect_uri'])
            ->where('code_challenge', $validated['code_challenge'])
            ->where('flow', $flow)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($existing) {
            return $existing;
        }

        return $authEngine->createSession(
            $application,
            $validated['redirect_uri'],
            $validated['code_challenge'],
            $validated['state'],
            $flow
        );
    }

    private function hasTelegramAuthData(Request $request): bool
    {
        return $request->filled('hash')
            && $request->filled('auth_date')
            && $request->filled('id');
    }
}
