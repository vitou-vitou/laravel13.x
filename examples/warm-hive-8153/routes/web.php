<?php
use App\Http\Controllers\ActionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ActionController::class, 'index'])->name('actions.index');
