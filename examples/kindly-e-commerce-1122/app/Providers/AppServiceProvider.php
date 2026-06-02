<?php

namespace App\Providers;

use App\Contracts\CreatesStripeCheckoutSession;
use App\Services\Stripe\FakeStripeCheckoutService;
use App\Services\Stripe\LocalDevStripeCheckoutService;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CreatesStripeCheckoutSession::class, function () {
            if ($this->app->environment('testing')) {
                return $this->app->make(FakeStripeCheckoutService::class);
            }

            $secret = config('stripe.secret');

            if (! is_string($secret) || $secret === '') {
                return $this->app->make(LocalDevStripeCheckoutService::class);
            }

            return $this->app->make(StripeCheckoutService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
