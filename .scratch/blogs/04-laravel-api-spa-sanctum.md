# Laravel API and Single Page Apps: Let Sanctum Handle Cookies, Stop Inventing Auth

You are building a standalone Single Page Application in React or Vue against a Laravel backend, and the team starts discussing custom JWT generators, refresh token rotations, and storing auth tokens in `localStorage`. Within a month, you are fighting token expiration race conditions, subtle CORS bugs, and Cross-Site Scripting vulnerabilities.

Laravel Sanctum solves browser authentication out of the box using first-party, cookie-based sessions. You do not need to invent an OAuth server or hand-roll JWT lifecycles for your own frontend. If your SPA and API share a root domain, Sanctum gives you secure, HttpOnly session authentication with CSRF protection in less than ten lines of configuration.

## How Sanctum SPA Authentication Actually Works

Sanctum uses Laravel's built-in session cookie authentication for SPAs, disguised as an API. Instead of sending an `Authorization: Bearer <token>` header with every fetch, the browser sends an encrypted session cookie that JavaScript cannot read.

The authentication dance takes three straightforward steps:

1. The SPA makes a GET request to `/sanctum/csrf-cookie` to initialize CSRF protection.
2. The SPA sends user credentials via POST to your standard `/login` route.
3. Laravel sets an `HttpOnly`, secure session cookie in the browser, authenticating all subsequent API requests.

Here is the essential configuration in your Laravel environment:

.env:
```ini
APP_URL=https://api.example.com
FRONTEND_URL=https://app.example.com
SANCTUM_STATEFUL_DOMAINS=app.example.com
SESSION_DOMAIN=.example.com
```

The leading dot in `SESSION_DOMAIN=.example.com` is critical. It allows the session cookie set by `api.example.com` to be sent by the browser when making requests from `app.example.com`.

## Configure Axios for Stateful Cookies

On the frontend, configure your HTTP client to include credentials with every request. Modern libraries like Axios handle Laravel's CSRF token cookie automatically if you set one property:

src/api/client.ts:
```ts
import axios from 'axios';

const api = axios.create({
    baseURL: 'https://api.example.com',
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export default api;
```

With `withCredentials: true`, the browser automatically transmits the session cookie. It also reads the unencrypted `XSRF-TOKEN` cookie that Laravel sets and sends it back in the `X-XSRF-TOKEN` header on POST, PUT, and DELETE requests to prevent CSRF attacks.

Here is the login sequence in your SPA:

src/services/auth.ts:
```ts
import api from '../api/client';

export async function login(credentials: { email: string; password: string }) {
    // 1. Initialize CSRF protection
    await api.get('/sanctum/csrf-cookie');

    // 2. Authenticate
    const response = await api.post('/login', credentials);
    return response.data;
}

export async function getCurrentUser() {
    const response = await api.get('/api/user');
    return response.data;
}
```

Notice that you do not store any token in `localStorage` or `sessionStorage`. If a malicious script runs on your page through an untrusted npm package, it cannot steal an `HttpOnly` session cookie.

## Shape Responses with Eloquent API Resources

Do not return raw Eloquent models from your API controllers. Raw models expose internal table structures, leak database column names, and risk serializing hidden or sensitive attributes during refactors.

Always transform models using Eloquent API Resources:

app/Http/Resources/UserResource.php:
```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
```

Use the resource in your API controller:

app/Http/Controllers/Api/UserController.php:
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
```

This resource forms an explicit, documented contract with your frontend. You can safely rename database columns or restructure internal relationships without breaking the client application.

## When Are Bearer Tokens Actually Needed?

Bearer tokens (generated via Sanctum's `$user->createToken('mobile-app')->plainTextToken`) belong in mobile apps (iOS, Android) and third-party API integrations where browsers and cookies do not exist.

Do not use Bearer tokens for your first-party web application. Managing refresh tokens, token expiration timers, and secure storage in browser environments adds immense operational overhead for worse security than standard cookies.

## What Can Go Wrong

The most frequent issue teams encounter is a CORS failure or 419 Page Expired status code during login. This almost always comes down to mismatched domains or missing configuration in `config/cors.php`.

Ensure your CORS configuration explicitly enables credentials and lists the frontend origin:

config/cors.php:
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

If `supports_credentials` is `false`, the browser will strip the session cookie from cross-origin requests, leaving your user unauthenticated.

## Summary

Stop rolling custom token management for browser applications. Use Laravel Sanctum's stateful cookie authentication for your SPAs. Configure your shared root session domain, initialize CSRF protection with `/sanctum/csrf-cookie`, and use Eloquent API Resources to establish clean contracts.

Your frontend code becomes simpler, your authentication becomes more secure, and you spend your engineering time shipping product features instead of debugging token lifecycles.

## Further Reading

- [Laravel Sanctum SPA Authentication](https://laravel.com/docs/sanctum#spa-authentication)
- [Laravel Eloquent API Resources](https://laravel.com/docs/eloquent-resources)
- [OWASP Cross-Site Request Forgery (CSRF) Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
- [Laravel CORS Configuration](https://laravel.com/docs/routing#cors)

Have you migrated an SPA from localStorage JWTs to Sanctum cookies? Share your transition experience in the comments below.
