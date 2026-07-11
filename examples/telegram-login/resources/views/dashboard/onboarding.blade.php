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
        button.secondary { background: #fff; color: #2563eb; border: 1px solid #cbd5e1; }
        .btn-row { display: flex; gap: .75rem; flex-wrap: wrap; align-items: center; margin-top: 1rem; }
        .test-box { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 1rem; margin-top: 1rem; }
        .test-box code { word-break: break-all; }
        .hint { color: #64748b; font-size: .875rem; margin-top: .5rem; }
        .error { color: #dc2626; font-size: .875rem; margin-top: .5rem; }
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
                @php($platformHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'laravel13.x.test')
                <ol>
                    <li>Create a bot via <a href="https://t.me/BotFather" target="_blank">@BotFather</a></li>
                    <li>In BotFather, send <code>/setdomain</code> → pick your bot → enter exactly: <strong><code>{{ $platformHost }}</code></strong> (hostname only, no <code>https://</code>)</li>
                    <li>Paste bot username and token below</li>
                </ol>
                <p class="hint">Telegram OAuth uses <code>{{ rtrim(config('app.url'), '/') }}</code> as origin. If you see “Bot domain invalid”, BotFather domain and <code>APP_URL</code> must match this host.</p>
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
                    <h2>Step 3 — Try demo shop (customer view)</h2>
                    <p class="hint">See exactly what your customers experience — one button, no technical setup.</p>
                    <form method="POST" action="{{ route('dashboard.onboarding.enable-demo') }}" class="btn-row">
                        @csrf
                        <input type="hidden" name="application_id" value="{{ $application->id }}">
                        <button type="submit" class="secondary">Enable demo shop</button>
                        <a href="{{ route('demo-shop.index') }}" target="_blank" rel="noopener">Open demo shop →</a>
                    </form>
                </section>

                <section>
                    <h2>Step 4 — Test login (developer)</h2>
                    <p class="hint">Generates a ready-to-use URL with valid PKCE <code>code_challenge</code> and <code>state</code>.</p>
                    @error('bot')<p class="error">{{ $message }}</p>@enderror
                    <form method="POST" action="{{ route('dashboard.onboarding.test-link') }}">
                        @csrf
                        <input type="hidden" name="application_id" value="{{ $application->id }}">
                        <button type="submit">Generate test login link</button>
                    </form>

                    @if (session('test_login_url'))
                        <div class="test-box">
                            <p><strong>1.</strong> Open this link in a <strong>new browser tab</strong> (not W3Schools / iframe):</p>
                            <p><a href="{{ session('test_login_url') }}" target="_blank" rel="noopener">{{ session('test_login_url') }}</a></p>

                            <p><strong>2.</strong> After login, exchange the <code>code</code> at <code>POST /oauth/token</code> using:</p>
                            <pre>code_verifier: {{ session('test_code_verifier') }}
state:         {{ session('test_state') }}
redirect_uri:  {{ session('test_redirect_uri') }}
client_id:     {{ session('test_client_id') }}</pre>
                            <p class="hint">Save <code>code_verifier</code> now — it is shown once and cannot be recovered.</p>
                        </div>
                    @endif
                </section>

                <section>
                    <h2>Step 5 — Integration snippet</h2>
                    <p>On your production site, generate PKCE on your backend per login attempt:</p>
                    <pre>&lt;a href="{{ url('/auth/start') }}?client_id={{ $application->client_id }}&amp;redirect_uri={{ urlencode($application->redirect_uris[0]) }}&amp;state=RANDOM_STATE&amp;code_challenge=YOUR_CODE_CHALLENGE&amp;flow=widget"&gt;
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
