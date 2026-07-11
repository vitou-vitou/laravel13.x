<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TelegramAuth — Log in with Telegram for any company</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; background: #fff; border-bottom: 1px solid #e2e8f0; }
        .hero { max-width: 960px; margin: 4rem auto; padding: 0 1.5rem; text-align: center; }
        h1 { font-size: 2.5rem; margin-bottom: 1rem; }
        .lead { font-size: 1.125rem; color: #475569; max-width: 640px; margin: 0 auto 2rem; }
        .actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        a.btn { padding: .75rem 1.25rem; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .primary { background: #2563eb; color: #fff; }
        .secondary { background: #fff; color: #2563eb; border: 1px solid #cbd5e1; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; max-width: 960px; margin: 3rem auto; padding: 0 1.5rem; }
        .feature { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; }
    </style>
</head>
<body>
    <header>
        <strong>TelegramAuth</strong>
        <nav>
            <a href="{{ route('dashboard.login') }}">Sign in</a>
            &nbsp;·&nbsp;
            <a href="{{ route('dashboard.register') }}">Register</a>
        </nav>
    </header>
    <section class="hero">
        <h1>Log in with Telegram for any customer</h1>
        <p class="lead">Multi-tenant B2B platform that lets any company add secure Telegram authentication in minutes — widget, OIDC, and token exchange included.</p>
        <div class="actions">
            <a class="btn primary" href="{{ route('dashboard.register') }}">Get started free</a>
            <a class="btn secondary" href="{{ route('demo-shop.index') }}" data-testid="demo-customer-link">Try demo shop (customer view)</a>
            <a class="btn secondary" href="{{ route('dashboard.login') }}">Tenant dashboard</a>
        </div>
    </section>
    <section class="features">
        <div class="feature"><strong>5-minute setup</strong><p>Connect your bot, add redirect URI, copy integration snippet.</p></div>
        <div class="feature"><strong>Secure by default</strong><p>HMAC verification, PKCE, auth_date checks, audit logs.</p></div>
        <div class="feature"><strong>Multi-tenant</strong><p>Each company manages apps, bots, and end-user identities.</p></div>
    </section>
</body>
</html>
