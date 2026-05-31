<?php

namespace App\Providers;

use App\Services\Telegram\TelegramClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramClient::class, function (): TelegramClient {
            return new TelegramClient(config('telegram.bot_token'));
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
