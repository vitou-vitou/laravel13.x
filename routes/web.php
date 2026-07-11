<?php

use App\Http\Controllers\Auth\OidcCallbackController;
use App\Http\Controllers\Auth\StartController;
use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Auth\UserinfoController;
use App\Http\Controllers\Auth\WidgetCallbackController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\LoginController;
use App\Http\Controllers\Dashboard\OnboardingController;
use App\Http\Controllers\Dashboard\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('dashboard.register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('dashboard.login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->prefix('dashboard')->group(function (): void {
    Route::get('/', HomeController::class)->name('dashboard.home');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('dashboard.logout');
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('dashboard.onboarding');
    Route::post('/onboarding/application', [OnboardingController::class, 'storeApplication'])->name('dashboard.onboarding.application');
    Route::post('/onboarding/bot', [OnboardingController::class, 'storeBot'])->name('dashboard.onboarding.bot');
});

Route::middleware('throttle:telegram-auth-start')->group(function (): void {
    Route::get('/auth/start', StartController::class)->name('auth.start');
});

Route::middleware('throttle:telegram-auth-callback')->group(function (): void {
    Route::match(['get', 'post'], '/auth/callback/widget', WidgetCallbackController::class)->name('auth.callback.widget');
    Route::get('/auth/callback/oidc', OidcCallbackController::class)->name('auth.callback.oidc');
});

Route::middleware('throttle:telegram-token')->group(function (): void {
    Route::post('/oauth/token', TokenController::class)->name('oauth.token');
    Route::get('/oauth/userinfo', UserinfoController::class)->name('oauth.userinfo');
});

Route::get('/.well-known/jwks.json', function () {
    return response()->json(['keys' => []]);
})->name('jwks');
