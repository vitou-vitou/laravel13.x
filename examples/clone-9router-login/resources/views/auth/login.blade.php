<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>9Router — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .grid-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 relative overflow-hidden grid-bg">
    <div class="w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-emerald-400 mb-2">9Router</h1>
            <p class="text-sm text-slate-400">Enter your password to access the dashboard</p>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-xl p-6 shadow-2xl backdrop-blur">
            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-950/60 border border-red-800/80 text-xs text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('mustChangePassword'))
                <div class="mb-4 p-3 rounded-lg bg-amber-950/60 border border-amber-800/80 text-xs text-amber-300">
                    Default password must be changed before remote access.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autofocus
                        placeholder="Enter password"
                        class="w-full px-3 py-2 text-sm rounded-lg bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                    >
                </div>

                @if (session('retryAfter') && session('retryAfter') > 0)
                    <div id="countdown-banner" class="text-xs text-amber-400">
                        Locked. Retry in <span id="countdown-timer" class="font-mono font-bold">{{ session('retryAfter') }}</span>s.
                    </div>
                @endif

                @if (session('resetHint'))
                    <p class="text-xs text-slate-400">
                        {{ session('resetHint') }}
                    </p>
                @endif

                <button
                    type="submit"
                    id="submit-btn"
                    class="w-full py-2.5 px-4 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-sm transition shadow-lg shadow-emerald-950/40 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Login
                </button>

                <p class="text-xs text-center text-slate-500 mt-2">
                    Default password is <code class="bg-slate-800 text-slate-300 px-1.5 py-0.5 rounded font-mono">123456</code>
                </p>
            </form>
        </div>
    </div>

    @if (session('retryAfter') && session('retryAfter') > 0)
    <script>
        let timeLeft = {{ (int) session('retryAfter') }};
        const timerEl = document.getElementById('countdown-timer');
        const submitBtn = document.getElementById('submit-btn');

        if (timeLeft > 0 && timerEl && submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = `Wait ${timeLeft}s`;

            const interval = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    timerEl.parentElement.remove();
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Login';
                } else {
                    timerEl.textContent = timeLeft;
                    submitBtn.textContent = `Wait ${timeLeft}s`;
                }
            }, 1000);
        }
    </script>
    @endif
</body>
</html>
