<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="0;url={{ $url }}">
    <title>{{ $title ?? 'Redirecting…' }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f8fafc; color: #0f172a; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem; max-width: 420px; text-align: center; box-shadow: 0 8px 24px rgba(15, 23, 42, .08); }
        h1 { font-size: 1.25rem; margin: 0 0 .75rem; }
        p { color: #64748b; margin: 0 0 1.25rem; line-height: 1.5; }
        a { color: #2563eb; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card" data-testid="external-redirect">
        <h1>{{ $title ?? 'Redirecting…' }}</h1>
        <p>{{ $message ?? 'Taking you to Telegram to approve login.' }}</p>
        <p><a href="{{ $url }}" data-testid="external-redirect-link">Continue to Telegram</a></p>
    </div>
</body>
</html>
