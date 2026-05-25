<?php

use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpaceController::class, 'index'])->name('spaces.index');
