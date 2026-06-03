<div
    class="fixed inset-0 z-20 flex items-start justify-center bg-black/40 pt-16 sm:items-center sm:pt-0"
    data-reels-gate="true"
>
    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="reels-gate-title"
        class="relative mx-4 w-full max-w-[396px] rounded-lg bg-white p-4 shadow-xl sm:p-6"
    >
        <button
            type="button"
            class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-fb-page text-fb-muted hover:bg-fb-border"
            aria-label="Close login dialog"
        >
            <span aria-hidden="true" class="text-xl leading-none">&times;</span>
        </button>

        <h2 id="reels-gate-title" class="pr-10 text-center text-xl font-bold text-fb-text">
            See more on Facebook
        </h2>

        <form class="mt-4 space-y-3" action="#" method="post">
            <div>
                <label for="reels-gate-email" class="mb-1 block text-sm text-fb-muted">Email or phone number</label>
                <input
                    id="reels-gate-email"
                    type="text"
                    name="email"
                    class="w-full rounded-md border border-fb-border px-3 py-2.5 text-base focus:border-fb-blue focus:outline-none focus:ring-1 focus:ring-fb-blue"
                    autocomplete="username"
                />
            </div>
            <div>
                <label for="reels-gate-password" class="mb-1 block text-sm text-fb-muted">Password</label>
                <input
                    id="reels-gate-password"
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="w-full rounded-md border border-fb-border px-3 py-2.5 text-base focus:border-fb-blue focus:outline-none focus:ring-1 focus:ring-fb-blue"
                    autocomplete="current-password"
                />
            </div>
            <button type="submit" class="w-full rounded-md bg-fb-blue py-2.5 text-lg font-semibold text-white">
                Log in to Facebook
            </button>
        </form>

        <p class="mt-3 text-center">
            <a href="#" class="text-sm text-fb-blue hover:underline">Forgot password?</a>
        </p>

        <div class="my-4 flex items-center gap-3">
            <span class="h-px flex-1 bg-fb-border"></span>
            <span class="text-sm text-fb-muted">or</span>
            <span class="h-px flex-1 bg-fb-border"></span>
        </div>

        <button type="button" class="w-full rounded-md bg-fb-green py-2.5 text-lg font-semibold text-white">
            Create new account
        </button>
    </div>
</div>
