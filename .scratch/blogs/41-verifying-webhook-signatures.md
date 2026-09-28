# Accept Inbound Webhooks Safely: Verify HMAC Signatures Before Processing

Your application receives inbound payment webhooks from Stripe, Shopify, or GitHub at `/api/webhooks/payments`. A malicious actor discovers the public URL and sends a forged HTTP POST request containing `{"event": "payment.succeeded", "order_id": 42}`. Your controller deserializes the JSON payload without verifying authenticity, marks the order as paid, and ships thousands of dollars worth of physical goods for a transaction that never occurred.

Public webhook endpoints are completely open to the internet. Anyone who knows or guesses the URL can craft arbitrary payloads. Legitimate payment and platform providers solve this by signing every outbound payload using a Hash-based Message Authentication Code (HMAC) secret. If you verify HMAC signatures before touching your database, reject stale timestamps to defeat replay attacks, and perform constant-time string comparisons, you guarantee that incoming webhooks are authentic and untampered.

## How HMAC Webhook Signatures Work

When a provider like Stripe or GitHub sends a webhook, it calculates a cryptographic hash of the exact raw request payload combined with a shared secret:

1. **Hash Calculation:** The provider computes `hash_hmac('sha256', $rawBody, $secret)`.
2. **Signature Header:** The resulting hash is transmitted in an HTTP header (e.g. `Stripe-Signature` or `X-Hub-Signature-256`).
3. **Verification:** Your Laravel application recalculates the hash using the identical shared secret and raw request body.
4. **Validation:** If your calculated hash matches the header, the payload is authentic. If they differ by even a single byte, the payload was forged or altered in transit.

## Step 1: Capture the Raw, Untouched Payload

The most frequent bug when implementing HMAC verification is verifying against parsed JSON instead of the raw HTTP request body:

```php
// Fatal error: json_encode() changes whitespace, breaking the cryptographic hash!
$computed = hash_hmac('sha256', json_encode($request->all()), $secret);
```

JSON formatting, key ordering, and character escaping will differ from the original raw string sent across the wire.

Always verify using Laravel's raw body buffer:

```php
$rawPayload = $request->getContent();
```

## Step 2: Build a Dedicated Webhook Middleware

Encapsulate signature verification inside a reusable Laravel HTTP Middleware:

app/Http/Middleware/VerifyWebhookSignature.php:
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signatureHeader = $request->header('X-Webhook-Signature');
        $secret = config('services.webhooks.secret');

        if (empty($signatureHeader) || empty($secret)) {
            return response()->json(['error' => 'Missing signature or webhook secret.'], 401);
        }

        // 1. Read raw body payload directly
        $rawPayload = $request->getContent();

        // 2. Compute expected HMAC SHA-256 signature
        $expectedSignature = hash_hmac('sha256', $rawPayload, $secret);

        // 3. Constant-time comparison to prevent timing attacks
        if (! hash_equals($expectedSignature, $signatureHeader)) {
            return response()->json(['error' => 'Invalid webhook signature.'], 403);
        }

        return $next($request);
    }
}
```

Notice the function used: `hash_equals($expectedSignature, $signatureHeader)`. Never use standard equality (`===`) when comparing cryptographic hashes. Standard string equality checks abort on the first non-matching byte, creating subtle microsecond timing differences that allow attackers to deduce valid signatures through statistical timing attacks.

## Step 3: Defeat Replay Attacks with Timestamps

A sophisticated attacker who intercepts a valid webhook payload and signature could replay that identical request hours later to trigger duplicate actions.

Providers like Stripe mitigate this by including a Unix timestamp in the signature header:

```
Stripe-Signature: t=1727440000,v1=5257a869e7ecebeda32affa62cdca3fa51cad7e77a0e56ff536d0ce8e108d8bd
```

Incorporate timestamp validation into your verification pipeline:

app/Services/WebhookSignatureValidator.php:
```php
namespace App\Services;

class WebhookSignatureValidator
{
    public static function isValid(string $rawPayload, string $header, string $secret, int $tolerance = 300): bool
    {
        // Parse "t=timestamp,v1=signature" format
        parse_str(str_replace(',', '&', $header), $parsed);

        if (! isset($parsed['t']) || ! isset($parsed['v1'])) {
            return false;
        }

        $timestamp = (int) $parsed['t'];
        $signature = $parsed['v1'];

        // Reject webhooks older than 5 minutes to prevent replay attacks
        if (abs(time() - $timestamp) > $tolerance) {
            return false;
        }

        // Hash the timestamp concatenated with the raw payload
        $expectedSignature = hash_hmac('sha256', "{$timestamp}.{$rawPayload}", $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
```

If an attacker captures the payload, they cannot replay it after five minutes have elapsed.

## What Can Go Wrong

A frequent trap in Laravel is CSRF token verification blocking inbound webhooks.

By default, Laravel's `web` middleware group applies `VerifyCsrfToken` to all POST requests. External providers cannot provide a Laravel CSRF token.

Ensure your webhook routes live in `routes/api.php` (which skips CSRF verification), or explicitly exclude your webhook endpoint from CSRF checks in `bootstrap/app.php`:

bootstrap/app.php:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'api/webhooks/*',
    ]);
})
```

Verify that signature validation middleware is applied directly to those routes so they remain strictly protected by HMAC verification.

## Summary

Never trust inbound webhook payloads based solely on their contents or origin IP address.

Verify the payload authenticity using HMAC SHA-256 signatures before touching your database or dispatching background jobs. Always hash against the raw request content (`$request->getContent()`), perform constant-time comparisons with `hash_equals()`, and validate timestamps to stop replay attacks.

With cryptographic signature verification in place, your webhook endpoints accept only authentic, verified events from your real upstream providers.

## Further Reading

- [Laravel Documentation: Routing and Middleware](https://laravel.com/docs/routing#middleware)
- [Stripe Documentation: Check Webhook Signatures](https://stripe.com/docs/webhooks/signatures)
- [GitHub Webhooks: Securing Your Webhooks](https://docs.github.com/en/webhooks/using-webhooks/validating-webhook-deliveries)
- [CWE-208: Observable Timing Discrepancy](https://cwe.mitre.org/data/definitions/208.html)

How does your team test inbound webhook verification in your local development environment? Share your workflow in the comments below.
