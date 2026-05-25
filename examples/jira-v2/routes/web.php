<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dev-only quick login
Route::get('/dev-login', function () {
    $user = \App\Models\User::where('email', 'admin@jira.com')->first();
    auth()->login($user);
    return redirect('/admin');
});
