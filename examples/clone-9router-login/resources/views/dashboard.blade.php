<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>9Router — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex flex-col">
    <header class="border-b border-slate-800 bg-slate-900/60 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-xl font-bold text-emerald-400">9Router</span>
            <span class="text-xs px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 border border-emerald-800 font-mono">Dashboard</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs text-slate-400">Logged in as <strong class="text-slate-200">Password user</strong></span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <main class="flex-1 p-6 max-w-5xl mx-auto w-full">
        <div class="p-6 rounded-xl bg-slate-900 border border-slate-800">
            <h2 class="text-lg font-semibold text-slate-100 mb-2">Welcome to 9Router Clone</h2>
            <p class="text-sm text-slate-400">
                You are authenticated via 9Router single-master password session.
            </p>
        </div>
    </main>
</body>
</html>
