<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Shop — TelegramAuth</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; background: #fafafa; color: #111; }
        header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        main { max-width: 720px; margin: 3rem auto; padding: 0 1.5rem; text-align: center; }
        .hero { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 2.5rem 2rem; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
        h1 { margin: 0 0 .5rem; font-size: 1.75rem; }
        .subtitle { color: #6b7280; margin-bottom: 2rem; }
        .btn { display: inline-block; padding: .875rem 1.5rem; border-radius: 10px; font-weight: 600; text-decoration: none; border: 0; cursor: pointer; font-size: 1rem; }
        .btn-telegram { background: #229ED9; color: #fff; }
        .btn-telegram:hover { background: #1a8bc4; }
        .btn-logout { background: #f3f4f6; color: #374151; margin-top: 1rem; }
        .logged-in { background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; text-align: left; }
        .logged-in h2 { margin: 0 0 .75rem; font-size: 1.1rem; color: #065f46; }
        .logged-in dl { margin: 0; display: grid; grid-template-columns: auto 1fr; gap: .25rem 1rem; font-size: .95rem; }
        .logged-in dt { color: #6b7280; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .notice { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .9rem; }
        .hint { color: #9ca3af; font-size: .85rem; margin-top: 2rem; }
    </style>
</head>
<body>
    <header>
        <strong>🛍️ Demo Shop</strong>
        <span style="color:#6b7280;font-size:.875rem">Powered by TelegramAuth</span>
    </header>

    <main>
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <div class="hero">
            @if (session('demo_logged_in_user'))
                @php $user = session('demo_logged_in_user'); @endphp
                <h1>Welcome back!</h1>
                <p class="subtitle">You are logged in as a customer. This is what your users see after Telegram login.</p>

                <div class="logged-in">
                    <h2>Your account</h2>
                    <dl>
                        <dt>Name</dt><dd>{{ trim(($user['first_name'] ?? '').' '.($user['last_name'] ?? '')) ?: '—' }}</dd>
                        <dt>Username</dt><dd>{{ $user['username'] ? '@'.$user['username'] : '—' }}</dd>
                        <dt>Telegram ID</dt><dd>{{ $user['telegram_id'] }}</dd>
                        <dt>Phone verified</dt><dd>{{ ($user['phone_verified'] ?? false) ? 'Yes' : 'No' }}</dd>
                    </dl>
                </div>

                <form method="POST" action="{{ route('demo-shop.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-logout">Log out</button>
                </form>
            @else
                <h1>{{ $application?->name ?? 'Demo Shop' }}</h1>
                <p class="subtitle">Browse our store — log in with Telegram to checkout.</p>

                @if ($ready)
                    <a href="{{ route('demo-shop.login') }}" class="btn btn-telegram" data-testid="demo-shop-login">
                        Log in with Telegram
                    </a>
                    <p class="hint">One click — just like a real customer. No URLs or codes to copy.</p>
                @else
                    <div class="notice">
                        @if ($application?->telegramBot)
                            <strong>One more step:</strong> Add this redirect URI in
                            <a href="{{ route('dashboard.onboarding') }}">onboarding</a>:<br>
                            <code style="word-break:break-all">{{ $demoCallbackUri }}</code>
                        @else
                            <strong>Setup needed:</strong> Register a company, connect a Telegram bot in
                            <a href="{{ route('dashboard.onboarding') }}">onboarding</a>, then return here.
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <p class="hint">
            This page simulates any company's website. Customers only see the blue button above.
        </p>
    </main>
</body>
</html>
