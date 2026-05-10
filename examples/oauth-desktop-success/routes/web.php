<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/oauth/desktop/success');

Route::view('/oauth/desktop/success', 'oauth.desktop-success')->name('oauth.desktop.success');
