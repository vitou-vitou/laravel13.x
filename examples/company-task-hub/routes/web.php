<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/signing', function () {
//    signing
    return response()->json([
        'url' => URL::signedRoute('email.action', ['user' => 123])
    ]);
});

Route::get('/action', function (Request $request) {
    if (!$request->hasValidSignature()) {
        abort(403);
    }

    // safe to proceed
    return response()->json([
        'message' => 'Signed route found'
    ]);

})->name('email.action');

