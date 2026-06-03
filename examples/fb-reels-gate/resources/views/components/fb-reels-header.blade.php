<header class="flex h-[56px] items-center justify-between border-b border-fb-border bg-white px-4 md:px-6">
    <a href="/" class="text-[28px] font-bold tracking-tight text-fb-blue" aria-label="Facebook">facebook</a>

    <form class="hidden items-center gap-2 sm:flex" action="#" method="post" aria-label="Log in to Facebook (header)">
        <label class="sr-only" for="header-email">Email or phone</label>
        <input
            id="header-email"
            type="text"
            name="email"
            placeholder="Email or phone"
            class="h-9 w-36 rounded-md border border-fb-border px-2 text-sm md:w-40"
            autocomplete="username"
        />
        <label class="sr-only" for="header-password">Password</label>
        <input
            id="header-password"
            type="password"
            name="password"
            placeholder="Password"
            class="h-9 w-36 rounded-md border border-fb-border px-2 text-sm md:w-40"
            autocomplete="current-password"
        />
        <button type="submit" class="h-9 rounded-md bg-fb-blue px-4 text-sm font-semibold text-white">
            Log In
        </button>
        <a href="#" class="text-xs text-fb-blue hover:underline">Forgot Account?</a>
    </form>
</header>
