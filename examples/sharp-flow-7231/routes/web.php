<?php
use App\Http\Controllers\FolderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FolderController::class, 'index'])->name('folders.index');
