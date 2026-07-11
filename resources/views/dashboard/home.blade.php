<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — TelegramAuth</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f8fafc; margin: 0; color: #0f172a; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; background: #fff; border-bottom: 1px solid #e2e8f0; }
        main { max-width: 960px; margin: 2rem auto; padding: 0 1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        th, td { text-align: left; padding: .5rem; border-bottom: 1px solid #e2e8f0; }
        .badge-ok { color: #059669; } .badge-fail { color: #dc2626; }
    </style>
</head>
<body>
    <header>
        <strong>{{ $tenant->name }}</strong>
        <form method="POST" action="{{ route('dashboard.logout') }}">@csrf<button type="submit">Logout</button></form>
    </header>
    <main>
        <h1>Dashboard</h1>
        <div class="grid">
            @foreach ($tenant->applications as $app)
                <div class="card">
                    <h3>{{ $app->name }}</h3>
                    <p>Client ID: <code>{{ $app->client_id }}</code></p>
                    <p>Bot: {{ $app->telegramBot?->bot_username ?? 'Not connected' }}</p>
                    <p>Status: {{ $app->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
            @endforeach
        </div>

        <section class="card" style="margin-top: 1.5rem;">
            <h2>Recent auth events</h2>
            <table>
                <thead><tr><th>Time</th><th>Flow</th><th>Result</th><th>Reason</th></tr></thead>
                <tbody>
                    @forelse ($tenant->applications->flatMap->auditLogs->sortByDesc('created_at')->take(20) as $log)
                        <tr>
                            <td>{{ $log->created_at->diffForHumans() }}</td>
                            <td>{{ $log->flow }}</td>
                            <td class="{{ $log->success ? 'badge-ok' : 'badge-fail' }}">{{ $log->success ? 'OK' : 'Failed' }}</td>
                            <td>{{ $log->failure_reason ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No auth events yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
