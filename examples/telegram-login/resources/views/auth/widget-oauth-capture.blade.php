<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completing Telegram login…</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f8fafc; color: #0f172a; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem; max-width: 420px; text-align: center; box-shadow: 0 8px 24px rgba(15, 23, 42, .08); }
        h1 { font-size: 1.25rem; margin: 0 0 .75rem; }
        p { color: #64748b; margin: 0 0 1rem; line-height: 1.5; }
        .error { color: #b91c1c; }
        a { color: #2563eb; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card" data-testid="widget-oauth-capture">
        <h1>Completing Telegram login…</h1>
        <p id="status">Please wait while we finish signing you in.</p>
        <p id="fallback" hidden>
            <a href="{{ $oauthUrl }}" data-testid="widget-oauth-retry">Continue to Telegram</a>
        </p>
    </div>
    <script>
        (function () {
            const startParams = @json($startParams);
            const oauthUrl = @json($oauthUrl);
            const startUrl = @json($startUrl);
            const attemptKey = 'telegram_oauth_attempt_' + startParams.state;

            function parseTgAuthResult(hash) {
                if (!hash || hash.indexOf('#tgAuthResult=') !== 0) {
                    return null;
                }

                const raw = hash.slice('#tgAuthResult='.length);

                try {
                    return JSON.parse(decodeURIComponent(raw));
                } catch (error) {
                    try {
                        const normalized = raw.replace(/-/g, '+').replace(/_/g, '/');
                        const padded = normalized + '='.repeat((4 - normalized.length % 4) % 4);

                        return JSON.parse(atob(padded));
                    } catch (innerError) {
                        return null;
                    }
                }
            }

            function showError(message) {
                const status = document.getElementById('status');
                const fallback = document.getElementById('fallback');

                status.textContent = message;
                status.classList.add('error');
                fallback.hidden = false;
            }

            const authResult = parseTgAuthResult(window.location.hash);

            if (authResult && authResult.hash && authResult.id && authResult.auth_date) {
                sessionStorage.removeItem(attemptKey);

                const params = new URLSearchParams();

                Object.entries(startParams).forEach(function (entry) {
                    if (entry[1] !== null && entry[1] !== undefined && entry[1] !== '') {
                        params.set(entry[0], String(entry[1]));
                    }
                });

                ['id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash'].forEach(function (key) {
                    if (authResult[key] !== null && authResult[key] !== undefined && authResult[key] !== '') {
                        params.set(key, String(authResult[key]));
                    }
                });

                window.location.replace(startUrl + '?' + params.toString());

                return;
            }

            if (sessionStorage.getItem(attemptKey)) {
                showError('Telegram did not return login data. Check BotFather /setdomain for {{ parse_url(config('app.url'), PHP_URL_HOST) }}, then try again.');

                return;
            }

            sessionStorage.setItem(attemptKey, '1');
            window.location.replace(oauthUrl);
        })();
    </script>
</body>
</html>
