# 07 — Security Playbook

> One leak kills your reputation. One breach kills your business. Security is not optional. It's baseline.

---

## The Senior's Security Mantra

> Trust nothing. Validate everything. Assume breach. Patch fast.

---

## OWASP Top 10 — Laravel Defense Map

| OWASP Risk | Laravel Defense |
|------------|-----------------|
| A01 Broken Access Control | Policies + `authorize()` + Gates |
| A02 Cryptographic Failures | Use `Hash::make`, `Crypt::`, never MD5/SHA1 |
| A03 Injection | Eloquent + Query Builder bindings; never `DB::raw($input)` |
| A04 Insecure Design | Threat-model new features; document trust boundaries |
| A05 Security Misconfig | `APP_DEBUG=false` in prod; no Telescope in prod |
| A06 Vulnerable Components | `composer audit` weekly; Dependabot enabled |
| A07 Auth Failures | Built-in auth + rate limiting + MFA |
| A08 Software Integrity | Sign your deploys; verify webhook signatures |
| A09 Logging Failures | Sentry + structured logs; alert on auth failures |
| A10 SSRF | Validate URLs, restrict outbound network |

---

## Authentication: The Senior Setup

### Use the Framework

```bash
composer require laravel/breeze --dev
php artisan breeze:install
```

Don't roll your own auth. Ever. Breeze/Jetstream are battle-tested.

### Password Policy

`app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Validation\Rules\Password;

public function boot(): void
{
    Password::defaults(function () {
        return Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();  // Checks haveibeenpwned
    });
}
```

12 chars minimum. Mixed. Symbols. Checked against breach databases.

### Rate Limiting Auth

`app/Providers/RouteServiceProvider.php`:
```php
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by(
        $request->input('email').'|'.$request->ip()
    );
});

RateLimiter::for('register', function (Request $request) {
    return Limit::perMinute(3)->by($request->ip());
});
```

`routes/web.php`:
```php
Route::post('/login', ...)->middleware('throttle:login');
```

### Two-Factor Authentication

Use Jetstream or `laragear/two-factor`:

```bash
composer require laragear/two-factor
php artisan vendor:publish --provider=Laragear\TwoFactor\TwoFactorServiceProvider
```

Force 2FA for admin roles:
```php
Route::middleware(['auth', '2fa.required'])->group(function () {
    Route::resource('users', AdminUserController::class);
});
```

### Session Security

`config/session.php`:
```php
'lifetime' => 120,
'expire_on_close' => false,
'encrypt' => true,
'secure' => env('SESSION_SECURE_COOKIE', true),  // HTTPS only
'http_only' => true,
'same_site' => 'lax',
```

In `.env` production:
```env
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.yourdomain.com
```

### Logout Discipline

After password change, force logout on other devices:
```php
Auth::logoutOtherDevices($request->newPassword);
```

After role change, regenerate session:
```php
$request->session()->regenerate();
```

---

## Authorization: Policy-First

### Every Resource Has a Policy

```bash
php artisan make:policy PostPolicy --model=Post
```

```php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id
            || $user->hasRole('admin');
    }
}
```

### Use in Controllers

```php
public function update(UpdatePostRequest $request, Post $post)
{
    $this->authorize('update', $post);
    // ...
}
```

Or in `FormRequest::authorize()`:
```php
public function authorize(): bool
{
    return $this->user()->can('update', $this->route('post'));
}
```

### Default Deny

In `AuthServiceProvider`:
```php
Gate::before(function (User $user, string $ability) {
    return $user->hasRole('super-admin') ? true : null;
});
```

Super-admin bypasses everything. Everyone else must have explicit permission.

---

## SQL Injection

### Safe (Always)

```php
User::where('email', $request->email)->first();        // Bound
DB::select('SELECT * FROM users WHERE id = ?', [$id]); // Bound
DB::table('users')->whereRaw('email = ?', [$email]);   // Bound
```

### Dangerous (Refuse to Write)

```php
DB::select("SELECT * FROM users WHERE id = $id");          // INJECTION
User::whereRaw("name = '$name'");                          // INJECTION
DB::table('users')->whereRaw("name = '" . $name . "'");    // INJECTION
```

If you see this in a PR: block it. Always.

---

## XSS Prevention

### Blade Escapes By Default

```blade
{{ $user->name }}        {{-- safe, escaped --}}
{!! $user->name !!}      {{-- UNSAFE, raw --}}
```

Never use `{!! !!}` with user input. EVER.

### When You MUST Render HTML

Use HTML Purifier:
```bash
composer require mews/purifier
```

```php
echo Purifier::clean($userInput);
```

### Vue/Inertia

```vue
<div>{{ user.name }}</div>     <!-- safe -->
<div v-html="user.bio"></div>  <!-- UNSAFE -->
```

Same rule: never `v-html` user content without sanitization.

### Content Security Policy

```php
// app/Http/Middleware/ContentSecurityPolicy.php
public function handle(Request $request, Closure $next)
{
    $response = $next($request);

    $response->headers->set('Content-Security-Policy',
        "default-src 'self'; " .
        "script-src 'self' 'nonce-{$nonce}' https://js.stripe.com; " .
        "style-src 'self' 'unsafe-inline'; " .
        "img-src 'self' data: https:; " .
        "connect-src 'self' https://api.stripe.com;"
    );

    return $response;
}
```

CSP blocks injected scripts even if XSS gets through.

---

## CSRF Protection

Built in. Just include the token:

Blade:
```blade
<form method="POST">
    @csrf
    ...
</form>
```

Vue/Inertia: handled automatically by Inertia's adapter.

### API Routes

`routes/api.php` uses Sanctum, not CSRF tokens. Use Bearer tokens or `sanctum/csrf-cookie` for SPA.

### Webhooks (Exempt CSRF)

```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'stripe/webhook',
    'github/webhook',
];
```

But **verify the signature** of every webhook (Stripe, GitHub send a signed header).

---

## File Upload Security

### Validate Type AND Content

```php
$request->validate([
    'avatar' => ['required', 'image', 'mimes:jpg,png,webp', 'max:2048'],
]);
```

`mimes:jpg,png,webp` checks actual content, not extension.

### Store Outside Public

```php
$path = $request->file('avatar')->storeAs(
    "avatars/{$user->id}",
    Str::random(40).'.'.$file->extension(),
    's3'
);
```

Use S3. Never public storage with predictable paths.

### Image Resize Defense (Pixel Bomb)

```php
$image = Image::make($file)->resize(800, 800, function ($c) {
    $c->aspectRatio();
    $c->upsize();
});

if ($image->width() * $image->height() > 25_000_000) {
    throw new \Exception('Image too large');
}
```

---

## Secrets Management

### Never in Repo

`.env` NEVER goes to git. Verify `.gitignore`:
```
.env
.env.backup
.env.production
```

### Render Sets Vars

In Render dashboard → Environment → Add Environment Variable.

For app key, let Render generate:
```yaml
# render.yaml
envVars:
  - key: APP_KEY
    generateValue: true
```

### Rotate Secrets

| Secret | Rotate Every |
|--------|-------------|
| DB password | 6 months |
| API tokens (Stripe, etc) | When compromised or yearly |
| APP_KEY | NEVER (decrypts existing data) |
| OAuth secrets | Yearly |
| Webhook secrets | Yearly |

### Use Laravel Encrypter for At-Rest

```php
$encrypted = Crypt::encryptString($apiKey);
$user->update(['api_key_encrypted' => $encrypted]);

// Later
$apiKey = Crypt::decryptString($user->api_key_encrypted);
```

Cast it:
```php
protected $casts = [
    'api_key' => 'encrypted',
    'sensitive_data' => 'encrypted:json',
];
```

---

## Mass Assignment Prevention

```php
// Don't trust this in production
protected $guarded = [];

// Use FormRequests EVERY time
public function update(UpdateUserRequest $request, User $user)
{
    $user->update($request->validated());  // ONLY validated fields
}
```

### Prevent Privilege Escalation

```php
class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'email' => 'email|max:255',
            // NEVER allow user to update is_admin or role
        ];
    }
}
```

Test this:
```php
it('does not allow privilege escalation', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->put("/users/{$user->id}", [
        'name' => 'Hacker',
        'is_admin' => true,  // attempting escalation
    ]);

    expect($user->fresh()->is_admin)->toBeFalse();
});
```

---

## API Security

### Sanctum Setup

```bash
php artisan install:api
```

Tokens with abilities:
```php
$token = $user->createToken('mobile', ['posts:read', 'posts:create']);
```

Use:
```php
Route::middleware(['auth:sanctum', 'ability:posts:create'])
    ->post('/posts', PostController::class);
```

### Rate Limiting APIs

```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(60)->by($request->user()->id)
        : Limit::perMinute(20)->by($request->ip());
});
```

### Token Expiry

`config/sanctum.php`:
```php
'expiration' => 60 * 24 * 7,  // 1 week
```

Force re-auth weekly. Logged-out tokens can't be reused.

### CORS

```php
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => [env('FRONTEND_URL')],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

NEVER `'allowed_origins' => ['*']` with credentials. Disaster.

---

## Dependency Security

### Weekly Audit

```bash
composer audit
npm audit
```

CI step:
```yaml
- run: composer audit --no-dev --format=json
```

Fail build on high severity.

### Dependabot (Free)

`.github/dependabot.yml`:
```yaml
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "weekly"
```

Dependabot opens PRs. Review weekly. Merge fast.

---

## Logging & Alerting

### Log Security Events

```php
// On failed login
Log::warning('Failed login', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);

// On privilege escalation attempt
Log::error('Privilege escalation attempt', [
    'user_id' => auth()->id(),
    'attempted' => $request->all(),
]);

// On admin action
activity()
    ->causedBy(auth()->user())
    ->performedOn($targetUser)
    ->log('changed-role');
```

### Sentry for Errors

```bash
composer require sentry/sentry-laravel
```

`.env`:
```env
SENTRY_LARAVEL_DSN=https://...@sentry.io/...
SENTRY_TRACES_SAMPLE_RATE=0.1
```

Sentry catches every uncaught exception. Alerts you in Slack.

### Alert on Suspicious Patterns

- 10+ failed logins from same IP in 5 min → block IP
- Multiple users from same IP in different countries → flag
- Sudden spike in 500 errors → page oncall

Use Render's log alerts or external monitoring (Better Stack, Cronitor).

---

## Production Hardening Checklist

```env
APP_ENV=production
APP_DEBUG=false              # CRITICAL
APP_URL=https://yourdomain.com

SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

TELESCOPE_ENABLED=false      # Telescope leaks data
DEBUGBAR_ENABLED=false

LOG_LEVEL=error              # Don't log debug data
LOG_CHANNEL=stack            # Stack to multiple destinations
```

### `app/Http/Middleware/TrustProxies.php`

```php
protected $proxies = '*';  // Render uses reverse proxy
protected $headers = Request::HEADER_X_FORWARDED_FOR
                  | Request::HEADER_X_FORWARDED_HOST
                  | Request::HEADER_X_FORWARDED_PORT
                  | Request::HEADER_X_FORWARDED_PROTO
                  | Request::HEADER_X_FORWARDED_AWS_ELB;
```

Without this, `$request->ip()` returns Render's proxy IP, not the real user.

### Force HTTPS

`app/Providers/AppServiceProvider.php`:
```php
public function boot(): void
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
}
```

---

## Database Security

### Connection Encryption

Render Postgres requires SSL:
```env
DB_SSLMODE=require
```

### Limit Privileges

In production, the app DB user has:
- SELECT, INSERT, UPDATE, DELETE on app tables
- NO DROP, NO CREATE on production DB

Use a separate `migration_user` for `php artisan migrate`.

### Backup Encryption

```php
// config/backup.php
'destination' => [
    'disks' => ['s3'],
    'encryption' => 'aes-256-cbc',
    'compression' => 'gzip',
],
```

Encrypted backups in S3. Recoverable. Stealable but useless without key.

---

## Incident Response

When you suspect a breach:

1. **Confirm.** Is it real? Logs, anomalies, reports?
2. **Contain.** Block the attacker IP. Force logout all sessions. Revoke API tokens.
3. **Assess.** What data was accessed? What was modified?
4. **Notify.** Affected users within 72h (GDPR). Customers within 24h (good practice).
5. **Patch.** Fix the vulnerability. Test the fix.
6. **Postmortem.** What failed? What detection gap? What's the systemic fix?

Have a runbook BEFORE you need it. Print it. Share it.

---

## Compliance Lite

| Regulation | Key Requirements |
|------------|------------------|
| GDPR | Right to delete, right to export, 72h breach notice |
| CCPA | Right to know, right to delete (California) |
| HIPAA | If health data, BAA required, encryption mandatory |
| SOC 2 | Documented controls, audit logs, access reviews |
| PCI-DSS | Don't store CVV, tokenize cards via Stripe |

Most apps need:
- Privacy policy
- Terms of service
- Cookie consent (if EU users)
- Data export endpoint
- Account deletion that actually deletes

```php
// app/Actions/User/DeleteAccount.php
public function execute(User $user): void
{
    DB::transaction(function () use ($user) {
        $user->subscriptions->each->cancel();
        $user->posts()->delete();
        $user->comments()->delete();
        $user->media()->each->delete();  // also deletes from S3
        $user->tokens()->delete();
        $user->delete();
    });

    Mail::to($user->email)->queue(new AccountDeletedMail());
}
```

---

## The Senior's Security Discipline

1. **Weekly:** `composer audit && npm audit`
2. **Monthly:** Review Render logs for anomalies
3. **Quarterly:** Penetration test (or self-test with OWASP ZAP)
4. **Yearly:** Rotate API secrets, audit user roles, review access patterns
5. **Continuously:** Update dependencies, watch Sentry, alert on auth failures

Security is not a feature. It's the floor below every feature.
