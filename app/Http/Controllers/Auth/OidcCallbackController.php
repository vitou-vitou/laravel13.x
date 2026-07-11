<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthSession;
use App\Services\Auth\AuthEngine;
use App\Services\Telegram\OidcClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OidcCallbackController extends Controller
{
    public function __invoke(Request $request, AuthEngine $authEngine, OidcClient $oidcClient): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $session = AuthSession::query()
            ->where('state', $validated['state'])
            ->where('flow', 'oidc')
            ->with(['application.telegramBot'])
            ->firstOrFail();

        $application = $session->application;
        $bot = $application->telegramBot;

        if (! $bot) {
            abort(400, 'Telegram bot not configured');
        }

        $codeVerifier = session()->pull('oidc_code_verifier_'.$session->state, '');

        $tokenResponse = $oidcClient->exchangeCode(
            $validated['code'],
            $bot->bot_username,
            $bot->bot_token,
            route('auth.callback.oidc'),
            $codeVerifier
        );

        if (! $tokenResponse) {
            $authEngine->audit($application->id, 'oidc', false, 'token_exchange_failed', $request);

            abort(403, 'OIDC token exchange failed');
        }

        $idToken = $tokenResponse['id_token'] ?? null;

        if (! is_string($idToken)) {
            $authEngine->audit($application->id, 'oidc', false, 'missing_id_token', $request);

            abort(403, 'Missing id_token');
        }

        $parts = explode('.', $idToken);
        $claims = json_decode(base64_decode(strtr($parts[1] ?? '', '-_', '+/')), true);

        if (! is_array($claims) || ! isset($claims['id'])) {
            $authEngine->audit($application->id, 'oidc', false, 'invalid_id_token', $request);

            abort(403, 'Invalid id_token');
        }

        $profile = [
            'telegram_id' => (int) $claims['id'],
            'username' => $claims['preferred_username'] ?? null,
            'first_name' => $claims['given_name'] ?? $claims['name'] ?? null,
            'last_name' => $claims['family_name'] ?? null,
            'photo_url' => $claims['picture'] ?? null,
            'phone' => $claims['phone_number'] ?? null,
        ];

        $redirectUrl = $authEngine->completeOidcLogin($session, $profile, $request);

        if (! $redirectUrl) {
            abort(403, 'Authentication failed');
        }

        return redirect()->away($redirectUrl);
    }
}
