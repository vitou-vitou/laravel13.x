# Harden Laravel Session Cookies: Prevent Session Fixation and Cross-Site Hijacking

A user logs in on an unencrypted public Wi-Fi network at a coffee shop or clicks a phishing link that passes a pre-generated session identifier. Because your session cookie settings were left at framework defaults, the attacker intercepts or fixes the session ID, attaches it to their own browser, and bypasses your login form entirely—even though the legitimate user had two-factor authentication enabled.

Authentication systems are only as secure as the session token that represents them. If a session cookie can be read by JavaScript, transmitted over insecure HTTP connections, or shared across third-party contexts, your authentication checks are trivial to bypass. If you configure `HttpOnly`, `SameSite=Lax`, and `Secure` attributes, regenerate session IDs upon privilege elevation, and store sessions in Redis or encrypted databases, you close the primary vectors for session hijacking.

## The Three Essential Cookie Security Flags

Every session cookie sent by Laravel to a client browser must carry three critical security attributes:

1. **`HttpOnly`:** Prohibits client-side JavaScript (`document.cookie`) from reading the cookie. Even if your application suffers an XSS vulnerability, a malicious script cannot extract the session identifier.
2. **`Secure`:** Enforces that the browser only transmits the cookie over encrypted HTTPS connections, preventing man-in-the-middle packet sniffing on public networks.
3. **`SameSite=Lax` (or `Strict`):** Prevents the browser from sending the cookie during cross-site requests (such as an external malicious website triggering a hidden POST request), serving as a crucial defense against Cross-Site Request Forgery (CSRF).

Audit your `config/session.php` configuration to ensure these settings are locked down:

config/session.php:
```php
return [
    'driver' => env('SESSION_DRIVER', 'redis'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => env('SESSION_ENCRYPT', true),

    // Essential Security Flags
    'secure' => env('SESSION_SECURE_COOKIE', true),
    'http_only' => true,
    'same_site' => 'lax',
    'domain' => env('SESSION_DOMAIN', null),
];
```

Ensure your `.env` sets `SESSION_SECURE_COOKIE=true` and `SESSION_ENCRYPT=true` in staging and production environments.

## Defeat Session Fixation with Session Regeneration

Session fixation occurs when an attacker forces a victim to use a known session ID (e.g., via a crafted link) before the victim authenticates. When the victim logs in, the attacker uses that same session ID to inherit their authenticated privileges.

To defeat session fixation, Laravel must discard the old session identifier and generate a fresh cryptographic token the moment authentication succeeds.

Always invoke `$request->session()->regenerate()` during login:

app/Http/Controllers/Auth/LoginController.php:
```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        // Critical: Invalidate previous session ID and generate a new one
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate the session and regenerate the CSRF token upon logout
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

Calling `$request->session()->regenerate()` swaps the session ID in the browser cookie while retaining user session data, rendering any pre-existing attacker token useless.

## Store Sessions in Redis or Database, Never Files

By default, Laravel stores session files in `storage/framework/sessions/` on the local disk.

In a load-balanced multi-server production environment, file-based sessions create major operational failures:
- If a user's next request routes to Server B, their session on Server A is missing, forcing them to log in repeatedly.
- Session files can be read by any local system process or compromised script on the host.

Store sessions in an encrypted database table or dedicated Redis cluster:

.env:
```ini
SESSION_DRIVER=redis
SESSION_CONNECTION=session
```

Redis stores sessions in fast in-memory key-value structures, automatically expires stale sessions via TTLs, and shares session state across all web application servers.

## Invalidate Sessions on Password Changes

When a user resets their password or suspects an account compromise, all other active sessions across mobile devices and browsers must be terminated immediately.

Laravel provides a built-in `AuthenticateSession` middleware that verifies the user's password hash against the session:

bootstrap/app.php:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Illuminate\Session\Middleware\AuthenticateSession::class,
    ]);
})
```

When a user changes their password, Laravel's authentication system updates the password hash in the database. The `AuthenticateSession` middleware detects the discrepancy on all other active devices and logs them out automatically.

## What Can Go Wrong

A frequent misconfiguration occurs when developers set `SESSION_DOMAIN=.example.com` to share cookies across subdomains without verifying HTTPS certificates.

If an unencrypted staging subdomain exists (`http://staging.example.com`), transmitting cookies scoped to `.example.com` will send your production session cookies in plaintext over HTTP, allowing network eavesdroppers to capture them.

Only use wildcard cookie domains if every subdomain strictly enforces HTTPS and HSTS (HTTP Strict Transport Security).

## Summary

Never treat session cookies as passive framework boilerplate.

Enforce `HttpOnly`, `Secure`, and `SameSite=Lax` flags in `config/session.php`. Regenerate session IDs on every login using `$request->session()->regenerate()`, invalidate sessions on logout, and store session data in centralized Redis instances rather than local disks.

Your authentication state remains secure, protected from XSS theft, network interception, and cross-site fixation attacks.

## Further Reading

- [Laravel Documentation: Session Configuration](https://laravel.com/docs/session#configuration)
- [OWASP Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- [MDN Web Docs: Using HTTP Cookies Securely](https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies#security)
- [RFC 6265: HTTP State Management Mechanism](https://datatracker.ietf.org/doc/html/rfc6265)

How do you manage cross-device session termination and active device listings in your user settings? Share your approach in the comments below.
