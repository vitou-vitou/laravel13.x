<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login/github', function () {
    return Socialite::driver('github')->redirect();
})->name('login.github');

Route::get('/login/github/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    $user = User::firstOrCreate(
        ['email' => $githubUser->getEmail()],
        ['name' => $githubUser->getName() ?? $githubUser->getNickname(), 'password' => Str::random(40)]
    );

    Auth::login($user);

    return redirect('/');
})->name('login.github.callback');
