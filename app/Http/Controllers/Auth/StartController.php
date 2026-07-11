<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\Auth\AuthEngine;
use App\Services\Telegram\OidcClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StartController extends Controller
{
    public function __invoke(Request $request, AuthEngine $authEngine): RedirectResponse|View
    {
        $validated = $request->validate([
            'client_id' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
            'state' => ['required', 'string', 'max:128'],
            'code_challenge' => ['required', 'string', 'max:128'],
            'flow' => ['nullable', 'in:widget,oidc'],
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

        $session = $authEngine->createSession(
            $application,
            $validated['redirect_uri'],
            $validated['code_challenge'],
            $flow
        );

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

            return redirect()->away($oidcUrl);
        }

        return view('auth.login', [
            'application' => $application,
            'session' => $session,
            'botUsername' => $application->telegramBot->bot_username,
        ]);
    }
}
