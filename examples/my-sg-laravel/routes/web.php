<?php

use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::view('/services/laravel', 'services.laravel')->name('services.laravel');

Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/locale/{locale}', [LocaleController::class, 'update'])
    ->name('locale.switch');

Route::post('/cookie-consent', [CookieConsentController::class, 'store'])
    ->name('cookie.consent');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
