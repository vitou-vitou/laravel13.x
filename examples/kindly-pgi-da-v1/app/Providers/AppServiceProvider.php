<?php

namespace App\Providers;

use App\Contracts\CreatesStripeCheckoutSession;
use App\Contracts\CreatesStripeConnectTransfer;
use App\Contracts\CreatesStripeRefund;
use App\Services\CartService;
use App\Services\Stripe\FakeStripeCheckoutService;
use App\Services\Stripe\FakeStripeConnectService;
use App\Services\Stripe\FakeStripeRefundService;
use App\Services\Stripe\LocalDevStripeCheckoutService;
use App\Services\Stripe\StripeCheckoutService;
use App\Services\Stripe\StripeConnectService;
use App\Services\Stripe\StripeRefundService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
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

        $this->app->bind(CreatesStripeRefund::class, function () {
            if ($this->app->environment('testing')) {
                return $this->app->make(FakeStripeRefundService::class);
            }

            $secret = config('stripe.secret');

            if (! is_string($secret) || $secret === '') {
                return $this->app->make(FakeStripeRefundService::class);
            }

            return $this->app->make(StripeRefundService::class);
        });

        $this->app->bind(CreatesStripeConnectTransfer::class, function () {
            if ($this->app->environment('testing')) {
                return $this->app->make(FakeStripeConnectService::class);
            }

            $secret = config('stripe.secret');

            if (! is_string($secret) || $secret === '') {
                return $this->app->make(FakeStripeConnectService::class);
            }

            return $this->app->make(StripeConnectService::class);
        });
    }

    public function boot(): void
    {
        Event::listen(Login::class, function (): void {
            app(CartService::class)->mergeGuestCartOnLogin();
        });

        View::composer('layouts.app', function ($view): void {
            $cart = app(CartService::class);

            $view->with([
                'stickyCartCount' => $cart->itemCount(),
                'stickyCartTotalCents' => $cart->totalCents(),
            ]);
        });
    }
}
