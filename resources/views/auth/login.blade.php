<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in with Telegram — {{ $application->name }}</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e293b; padding: 2rem; border-radius: 12px; text-align: center; max-width: 420px; width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,.3); }
        h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
        p { color: #94a3b8; margin: 0 0 1.5rem; font-size: .95rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $application->name }}</h1>
        <p>Sign in securely with your Telegram account</p>
        <script async src="https://telegram.org/js/telegram-widget.js?22"
            data-telegram-login="{{ $botUsername }}"
            data-size="large"
            data-auth-url="{{ route('auth.callback.widget', ['state' => $session->state]) }}"
            data-request-access="write">
        </script>
    </div>
</body>
</html>
