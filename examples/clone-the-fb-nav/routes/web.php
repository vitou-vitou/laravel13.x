<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/watch', 'pages.watch')->name('watch');
Route::view('/marketplace', 'pages.marketplace')->name('marketplace');
Route::view('/groups', 'pages.groups')->name('groups');
Route::view('/gaming', 'pages.gaming')->name('gaming');
