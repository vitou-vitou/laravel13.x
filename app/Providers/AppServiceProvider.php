<?php

namespace App\Providers;

use App\Support\Tenancy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Tenancy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configurePassport();
    }

    /**
     * Define API token scopes for policyholder vs adjuster access (task 1.5, design D2/D3).
     */
    protected function configurePassport(): void
    {
        Passport::tokensCan([
            'claims:read' => 'Read own claims and status',
            'claims:write' => 'File and update own claims',
            'adjuster' => 'Review and decide claims (insurer staff)',
            'finance' => 'Manage payouts (insurer staff)',
        ]);

        Passport::setDefaultScope(['claims:read']);
    }

    /**
     * Configure rate limiting.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('API rate limit exceeded.', 429, $headers);
                });
        });

        RateLimiter::for('api-writes', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('API write rate limit exceeded.', 429, $headers);
                });
        });
    }
}
