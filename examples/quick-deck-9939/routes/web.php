<?php

use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BoardController::class, 'index'])->name('boards.index');
Route::get('/boards/{board}', [BoardController::class, 'show'])->name('boards.show');
