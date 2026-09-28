---
title: "How to Build a Seamless Single Sign-On Flow with Laravel and Keycloak"
published: true
description: "Configure OpenID Connect Single Sign-On authentication between a Laravel backend and a central Keycloak identity provider."
tags: "laravel, keycloak, sso, oauth2, security"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/87-laravel-keycloak-sso-oidc-integration.md"
---

Managing separate user credentials across multiple internal portals—such as claims systems, quotation engines, and customer databases—creates security risks and administrative overhead. Password fatigue leads to weak credentials, while revoking access for departed staff requires updates across multiple databases.

Centralizing authentication through OpenID Connect (OIDC) Single Sign-On (SSO) with Keycloak solves these issues. A Laravel application delegates identity verification to Keycloak, which handles multi-factor challenges and returns signed identity tokens. Let's see how.

## The Architecture: Authorization Code Flow with PKCE

Laravel acts as a confidential OpenID Connect client interacting with the Keycloak identity provider:

```text
Browser ──> Laravel (/login) ──Redirect──> Keycloak Login Screen
Keycloak ──Validate Credentials──> Redirects with Auth Code ──> Laravel Callback
Laravel ──Exchange Code for JWT Tokens (Back-channel)──> Keycloak Token Endpoint
Laravel ──Establish Local Web Session Cookie──> Browser Authenticated
```

By completing the token exchange over a direct back-channel HTTP request, sensitive access tokens and refresh tokens are kept out of the browser.

## Step 1: Configure OpenID Connect Client Settings

Define your Keycloak server endpoints and client credentials in your application configuration:

`config/services.php:`
```php
<?php

return [
    'keycloak' => [
        'base_url' => env('KEYCLOAK_BASE_URL', 'https://iam.enterprise.com'),
        'realm' => env('KEYCLOAK_REALM', 'master'),
        'client_id' => env('KEYCLOAK_CLIENT_ID', 'pgi-portal-backend'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'redirect_uri' => env('KEYCLOAK_REDIRECT_URI', 'https://portal.enterprise.com/auth/callback'),
    ],
];
```

Store the client secret securely in your production environment variables (`.env`) rather than committing it to source control.

## Step 2: Implement the Authentication Controller

Build controller actions to initiate the authorization redirect and process the incoming callback:

`app/Http/Controllers/Auth/KeycloakSsoController.php:`
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KeycloakSsoController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);

        $config = config('services.keycloak');
        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid profile email roles',
            'state' => $state,
        ]);

        return redirect("{$config['base_url']}/realms/{$config['realm']}/protocol/openid-connect/auth?{$query}");
    }

    public function callback(Request $request): RedirectResponse
    {
        $savedState = $request->session()->pull('oauth_state');
        if (!$savedState || $savedState !== $request->query('state')) {
            abort(403, 'Invalid or expired authentication state parameter.');
        }

        $config = config('services.keycloak');

        // Exchange authorization code for tokens via back-channel
        $response = Http::asForm()->post("{$config['base_url']}/realms/{$config['realm']}/protocol/openid-connect/token", [
            'grant_type' => 'authorization_code',
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'code' => $request->query('code'),
        ]);

        if ($response->failed()) {
            abort(401, 'Failed to retrieve access token from Keycloak.');
        }

        $tokens = $response->json();

        // Fetch verified user profile
        $userResponse = Http::withToken($tokens['access_token'])
            ->get("{$config['base_url']}/realms/{$config['realm']}/protocol/openid-connect/userinfo");

        $profile = $userResponse->json();

        // Sync or register local user model
        $user = User::updateOrCreate(
            ['email' => $profile['email']],
            [
                'name' => $profile['name'] ?? $profile['preferred_username'],
                'keycloak_id' => $profile['sub'],
                'email_verified_at' => now(),
            ]
        );

        // Store tokens securely in the server-side session
        $request->session()->put('keycloak_token', $tokens['access_token']);
        $request->session()->put('keycloak_refresh_token', $tokens['refresh_token'] ?? null);

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
```

Verifying the `state` parameter prevents cross-site request forgery (CSRF) during the OAuth exchange.

## Step 3: Implement Centralized Single Sign-Out

When a user logs out of Laravel, invalidate their local session and notify Keycloak to terminate their centralized SSO session:

`app/Http/Controllers/Auth/KeycloakLogoutController.php:`
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class KeycloakLogoutController extends Controller
{
    public function logout(Request $request): RedirectResponse
    {
        $idToken = $request->session()->get('keycloak_token');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $config = config('services.keycloak');
        $redirectUrl = urlencode(url('/'));

        // Redirect user to Keycloak end-session endpoint
        return redirect("{$config['base_url']}/realms/{$config['realm']}/protocol/openid-connect/logout?post_logout_redirect_uri={$redirectUrl}");
    }
}
```

Logging out via the end-session endpoint ensures that user sessions across all connected applications are terminated consistently.

## What Can Go Wrong

- **Mismatched Redirect URIs**: If the `redirect_uri` sent during the authorization request does not match the URI configured in the Keycloak admin console character for character (including trailing slashes and HTTP vs. HTTPS), Keycloak returns an `Invalid parameter: redirect_uri` error.
- **Clock Drift**: Differences in system clocks between the Laravel web server and the Keycloak host can cause token validation to fail due to premature expiration. Synchronize both systems via NTP.

## Summary

Integrating Laravel with Keycloak via OpenID Connect provides robust Single Sign-On across your application ecosystem. By handling token exchanges through secure back-channels and validating cryptographic state tokens, applications maintain tight security while simplifying user access.

## Further Reading

- [OpenID Connect Core 1.0 Specification](https://openid.net/specs/openid-connect-core-1_0.html)
- [Keycloak OpenID Connect Implementation](https://www.keycloak.org/docs/latest/securing_apps/#_oidc)
- [Laravel Authentication Documentation](https://laravel.com/docs/authentication)

Configure an OIDC client in your Keycloak realm to enable secure Single Sign-On across your Laravel applications.
