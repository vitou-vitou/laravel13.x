<?php
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index']);
Route::get('/pages/{page}', [PageController::class, 'show'])->name('pages.show');
