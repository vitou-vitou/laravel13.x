# Stop Credential Stuffing: Layered Rate Limiting and Honeypots in Laravel

A botnet targets your public login endpoint with 500,000 compromised username and password combinations harvested from an external data breach. Your application runs standard password hashing via Bcrypt or Argon2id. Because password hashing is deliberately designed to be computationally expensive, the sudden spike of 200 login attempts per second pushes your web server CPU to 100%, causing a total denial-of-service outage for your legitimate customers.

Defending against credential stuffing requires stopping automated abuse before expensive cryptographic hashing algorithms ever execute. Standard IP-based rate limiting alone is insufficient because botnets distribute requests across thousands of rotating residential proxy IP addresses. If you implement layered rate limiting combining IP throttles, username-based throttles, and invisible form honeypots in Laravel, you can neutralize brute force attacks without degrading server capacity.

## The Cost of Unprotected Password Hashing

Modern password hashing algorithms like Bcrypt and Argon2id are intentionally slow to prevent offline dictionary attacks. A single `Hash::check()` call typically takes between 80 and 150 milliseconds of dedicated CPU time.

Consider what happens under attack:
- 100 concurrent brute-force login attempts hit `/login`.
- 100 PHP-FPM workers invoke `password_verify()`.
- Your 8-core application server runs at 100% CPU utilization.
- Regular users trying to browse your product catalog experience 504 Gateway Timeouts.

Attackers do not need to successfully guess a password to take down your application; the computational cost of verifying their bad guesses is enough to exhaust your servers.

## Layer 1: Multi-Key Rate Limiting

Standard rate limiters that throttle only by client IP address fail against distributed proxy botnets where each request originates from a different IP.

Instead, build a layered rate limiter in `RateLimiter::for()` that throttles by **both** IP address and username:

app/Providers/AppServiceProvider.php:
```php
namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            $ip = $request->ip();

            return [
                // 1. IP-level limit: maximum 10 attempts per minute from a single IP
                Limit::perMinute(10)->by($ip),

                // 2. Account-level limit: maximum 5 failed attempts per minute for a specific email
                Limit::perMinute(5)->by(Str::lower($email) . '|' . $ip)->response(function () {
                    return response()->json([
                        'error' => 'Too many login attempts. Please try again in 60 seconds.',
                    ], 429);
                }),
            ];
        });
    }
}
```

Applying both limits guarantees that:
1. A single IP address cannot cycle through thousands of user accounts.
2. A distributed botnet using thousands of different IPs cannot hammer a single executive's email address with guesses.

Apply the limiter to your login route:

routes/web.php:
```php
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:login');
```

## Layer 2: Invisible Honeypots to Trap Automated Bots

Most automated credential-stuffing scripts do not render HTML using a full headless browser; they parse the DOM and fill every input tag found inside the `<form>`.

A honeypot introduces a hidden form field that legitimate human users cannot see or interact with, but which automated bots blindly populate:

resources/views/auth/login.blade.php:
```blade
<form action="{{ route('login') }}" method="POST">
    @csrf

    <!-- Standard Login Fields -->
    <div>
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" required autofocus>
    </div>

    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>

    <!-- Honeypot: Hidden from real humans via CSS -->
    <div style="opacity: 0; position: absolute; top: 0; left: -5000px; height: 0; width: 0; z-index: -1;">
        <label for="preferred_contact_method">Do not fill this field</label>
        <input type="text" name="preferred_contact_method" id="preferred_contact_method" tabindex="-1" autocomplete="off">
    </div>

    <button type="submit">Sign In</button>
</form>
```

In your login controller or Form Request, check the honeypot field **before** looking up the user in the database or running password verification:

app/Http/Controllers/Auth/LoginController.php:
```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        // Check honeypot field FIRST: stops bots before hitting the database or hashing passwords!
        if (! empty($request->input('preferred_contact_method'))) {
            // Fake a realistic delay to waste the bot's time, then return a fake success or generic error
            usleep(random_int(100000, 300000));
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
}
```

When a bot fills `preferred_contact_method`, the controller immediately halts execution. Zero database lookups occur, zero Bcrypt calculations are triggered, and your CPU remains completely calm.

## What Can Go Wrong

A frequent pitfall when building honeypots is using common CSS class names like `.hidden` or `display: none`.

Sophisticated bot scripts are programmed to look for `display: none` or `visibility: hidden` inline styles and skip those inputs.

Using absolute positioning to place the field far off-screen (`left: -5000px; opacity: 0;`) makes the field appear visible to basic DOM parsers while remaining completely invisible to human users using mice, touchscreens, and screen readers (via `tabindex="-1"` and `aria-hidden="true"`).

## Summary

Protecting your login endpoints against credential stuffing requires defense in depth.

Do not rely solely on single-IP rate limits. Combine multi-key throttles (IP address + targeted email), place hidden honeypot fields in your authentication forms, and evaluate honeypot flags before invoking computationally heavy password hashing functions.

You neutralize automated brute-force attacks at the front door, preserve your database and server CPU, and keep authentication fast and responsive for real users.

## Further Reading

- [Laravel Documentation: Rate Limiting](https://laravel.com/docs/routing#rate-limiting)
- [OWASP Credential Stuffing Prevention](https://owasp.org/www-community/attacks/Credential_stuffing)
- [Bcrypt and Argon2id Cryptographic Work Factors](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [Spatie Laravel Honeypot Package](https://github.com/spatie/laravel-honeypot)

How do you protect your public registration and login forms from automated bot traffic? Share your security rules in the comments below.
