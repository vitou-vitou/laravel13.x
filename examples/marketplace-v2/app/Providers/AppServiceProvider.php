<?php

namespace App\Providers;

use App\Contracts\CreatesStripeCheckoutSession;
use App\Services\CartService;
use App\Services\Stripe\FakeStripeCheckoutService;
use App\Services\Stripe\LocalDevStripeCheckoutService;
use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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

    public function boot(): void
    {
        Event::listen(Login::class, function (): void {
            app(CartService::class)->mergeGuestCartOnLogin();
        });
    }
}
