<?php
use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BoardController::class, 'index'])->name('boards.index');
