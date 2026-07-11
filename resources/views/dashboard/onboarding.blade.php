<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboarding — TelegramAuth</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f8fafc; margin: 0; color: #0f172a; }
        main { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        section { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; margin-bottom: 1rem; }
        label { display: block; margin-top: .75rem; font-weight: 600; font-size: .875rem; }
        input, textarea { width: 100%; padding: .5rem; margin-top: .25rem; border: 1px solid #cbd5e1; border-radius: 6px; }
        button { margin-top: 1rem; padding: .625rem 1rem; background: #2563eb; color: #fff; border: 0; border-radius: 6px; cursor: pointer; }
        pre { background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 8px; overflow-x: auto; font-size: .8rem; }
        .status { color: #059669; margin-bottom: 1rem; }
        ol { line-height: 1.7; }
    </style>
</head>
<body>
    <main>
        <h1>Onboarding — {{ $tenant->name }}</h1>
        @if (session('status'))<p class="status">{{ session('status') }}</p>@endif
        @if (session('client_secret'))
            <section style="background:#fef3c7;border-color:#fcd34d;">
                <h2>Save your client secret</h2>
                <p>This is shown once. Store it securely.</p>
                <p><strong>Client ID:</strong> <code>{{ session('client_id') }}</code></p>
                <p><strong>Client Secret:</strong> <code>{{ session('client_secret') }}</code></p>
            </section>
        @endif

        @if (! $application)
            <section>
                <h2>Step 1 — Create application</h2>
                <form method="POST" action="{{ route('dashboard.onboarding.application') }}">
                    @csrf
                    <label>App name<input type="text" name="name" required placeholder="My Store"></label>
                    <label>Redirect URI<input type="url" name="redirect_uri" required placeholder="https://example.com/auth/callback"></label>
                    <button type="submit">Create application</button>
                </form>
            </section>
        @else
            <section>
                <h2>Application credentials</h2>
                <p><strong>Name:</strong> {{ $application->name }}</p>
                <p><strong>Client ID:</strong> <code>{{ $application->client_id }}</code></p>
                <p><strong>Redirect URIs:</strong> {{ implode(', ', $application->redirect_uris) }}</p>
            </section>

            <section>
                <h2>Step 2 — Connect Telegram bot</h2>
                <ol>
                    <li>Create a bot via <a href="https://t.me/BotFather" target="_blank">@BotFather</a></li>
                    <li>Run <code>/setdomain</code> and register your platform domain</li>
                    <li>Paste bot username and token below</li>
                </ol>
                <form method="POST" action="{{ route('dashboard.onboarding.bot') }}">
                    @csrf
                    <input type="hidden" name="application_id" value="{{ $application->id }}">
                    <label>Bot username<input type="text" name="bot_username" required placeholder="my_bot"></label>
                    <label>Bot token<input type="text" name="bot_token" required></label>
                    <label>Domain (for BotFather)<input type="text" name="domain" required placeholder="{{ parse_url(config('app.url'), PHP_URL_HOST) }}"></label>
                    <button type="submit">Save bot</button>
                </form>
            </section>

            @if ($application->telegramBot)
                <section>
                    <h2>Step 3 — Integration snippet</h2>
                    <p>Use this link on your site (generate PKCE <code>code_verifier</code> and <code>code_challenge</code> on your backend):</p>
                    <pre>&lt;a href="{{ url('/auth/start') }}?client_id={{ $application->client_id }}&amp;redirect_uri={{ urlencode($application->redirect_uris[0]) }}&amp;state=RANDOM_STATE&amp;code_challenge=YOUR_CODE_CHALLENGE"&gt;
  Log in with Telegram
&lt;/a&gt;</pre>
                    <p>Exchange the returned <code>code</code> at <code>POST /oauth/token</code> with <code>code_verifier</code>, <code>client_id</code>, and <code>client_secret</code>.</p>
                    <p><a href="{{ route('dashboard.home') }}">Go to dashboard →</a></p>
                </section>
            @endif
        @endif
    </main>
</body>
</html>
