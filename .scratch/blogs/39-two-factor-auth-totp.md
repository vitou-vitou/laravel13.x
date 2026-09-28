# Add Two-Factor Authentication to Laravel Without Third-Party Vendor Lock-in

Your product team needs to add Two-Factor Authentication (2FA) for compliance with enterprise security requirements. Someone suggests outsourcing the feature to a commercial SaaS identity provider that charges $0.05 per active user per month. Within a few months, your application is tightly coupled to external login redirects, monthly bills increase, and authentication breaks completely whenever the identity provider experiences a DNS outage.

Two-factor authentication using Time-Based One-Time Passwords (TOTP) is an open, standardized algorithm (RFC 6238) that requires zero external third-party services. Standard authenticator apps like Google Authenticator, 1Password, and Apple Passwords generate six-digit codes locally on user devices without making network requests. If you implement TOTP directly in Laravel using lightweight open-source cryptography packages, you provide enterprise-grade 2FA with zero vendor lock-in and zero recurring per-user fees.

## How TOTP Authentication Actually Works

TOTP authentication relies on a shared cryptographic secret key between your server and the user's authenticator app:

1. **Setup:** The server generates a random 32-character secret key and encodes it into a `data:image/svg+xml` QR code.
2. **Scan:** The user scans the QR code with their authenticator app (1Password, Google Authenticator, Authy).
3. **Verification:** Every 30 seconds, both the server and the authenticator app compute a hash of the current Unix timestamp combined with the shared secret, resulting in an identical six-digit numerical code.

Because the math runs locally on both sides, authentication operates cleanly even if the user's phone is in airplane mode.

## Step 1: Store Encrypted 2FA Secrets

Never store 2FA secret keys in plaintext in your database. If a database backup is compromised, attackers could generate valid 2FA tokens for all users.

Create a migration adding encrypted columns and recovery codes:

database/migrations/2024_06_01_000001_add_two_factor_columns_to_users_table.php:
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });
    }
};
```

Update your `User` model to automatically encrypt the secret key at rest:

app/Models/User.php:
```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $casts = [
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }
}
```

Using Laravel's native `'encrypted'` cast ensures that the secret is encrypted via AES-256 before being written to MySQL or PostgreSQL.

## Step 2: Generate the Secret and QR Code

Using the established `bacon/bacon-qr-code` and `pragmarx/google2fa` libraries (the exact dependencies used by Laravel Fortify), generate the setup payload:

app/Services/TwoFactorAuthenticationService.php:
```php
namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationService
{
    public function __construct(protected Google2FA $engine) {}

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function getQrCodeSvg(User $user, string $secret): string
    {
        $companyName = config('app.name');
        $qrCodeUrl = $this->engine->getQRCodeUrl($companyName, $user->email, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($qrCodeUrl);
    }

    public function verifyKey(string $secret, string $code): bool
    {
        // Window 1 checks +/- 30 seconds to allow for slight clock drift between server and phone
        return (bool) $this->engine->verifyKey($secret, $code, 1);
    }
}
```

Notice the window parameter: `verifyKey($secret, $code, 1)`. This accommodates minor clock drift of up to 30 seconds between the user's mobile device and your server.

## Step 3: Enforce Confirmation Before Enabling

A common security vulnerability is marking 2FA as active before verifying that the user successfully configured their authenticator app. If the user closes their browser tab before scanning the QR code, they are permanently locked out of their account.

Always require a valid code check before saving the confirmation timestamp:

app/Http/Controllers/TwoFactorController.php:
```php
namespace App\Http\Controllers;

use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function confirm(Request $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);
        $user = $request->user();

        if (! $service->verifyKey(decrypt($user->two_factor_secret), $request->code)) {
            return back()->withErrors(['code' => 'The provided authentication code was invalid.']);
        }

        // Generate emergency recovery codes
        $recoveryCodes = collect(range(1, 8))->map(fn () => bin2hex(random_bytes(5)))->toArray();

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        return redirect()->route('profile.security')
            ->with('recovery_codes', $recoveryCodes)
            ->with('status', 'Two-factor authentication successfully enabled.');
    }
}
```

The user is only considered enrolled once they prove they can generate a matching six-digit code.

## What Can Go Wrong

The most frequent issue with TOTP verification is **server clock drift**.

Because the TOTP algorithm hashes the current Unix timestamp in 30-second windows, if your production server's clock drifts by more than 60 seconds from real UTC, valid codes generated by users' phones will be rejected as invalid.

Always ensure Network Time Protocol (NTP) synchronization is enabled and active on your production server instances:

```bash
# Verify system clock synchronization on Linux
timedatectl status
```

Ensure `System clock synchronized: yes` and `NTP service: active`.

## Summary

You do not need to pay monthly per-user fees to commercial identity providers to secure your Laravel application with two-factor authentication.

Implement standard RFC 6238 TOTP using open-source packages. Encrypt secret keys at rest with Laravel's `'encrypted'` model cast, provide backup recovery codes, enforce confirmation before activation, and keep your server clock synchronized via NTP.

Your users enjoy standard multi-factor authentication with their favorite password manager or authenticator app, and your business retains complete ownership over its user data.

## Further Reading

- [RFC 6238: TOTP Time-Based One-Time Password Algorithm](https://datatracker.ietf.org/doc/html/rfc6238)
- [Laravel Fortify Two-Factor Authentication Reference](https://laravel.com/docs/fortify#two-factor-authentication)
- [PragmaRX Google2FA Package Documentation](https://github.com/antonioribeiro/google2fa)
- [NIST Special Publication 800-63B: Digital Identity Guidelines](https://pages.nist.gov/800-63-3/sp800-63b.html)

How do you handle emergency account recovery when users lose access to their 2FA devices? Join the discussion in the comments below.
