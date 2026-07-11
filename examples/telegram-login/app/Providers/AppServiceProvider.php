<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('telegram-auth-start', function (Request $request) {
            return Limit::perMinute((int) config('telegramauth.rate_limits.auth_start', 30))->by($request->ip());
        });

        RateLimiter::for('telegram-auth-callback', function (Request $request) {
            return Limit::perMinute((int) config('telegramauth.rate_limits.auth_callback', 60))->by($request->ip());
        });

        RateLimiter::for('telegram-token', function (Request $request) {
            return Limit::perMinute((int) config('telegramauth.rate_limits.token', 30))->by($request->ip());
        });
    }
}
