<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.reel-gate')->name('home');

Route::view('/reel/{reelId}', 'pages.reel-gate')->name('reel.gate');
