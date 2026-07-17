<?php

namespace App\Providers;

use App\Services\SsoAuthenticator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Azure\Provider as MicrosoftProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['auth.login', 'auth.register'], function ($view): void {
            $view->with(
                'hasSso',
                count(app(SsoAuthenticator::class)->enabledProviders()) > 0,
            );
        });

        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('microsoft', MicrosoftProvider::class);
        });
    }
}
