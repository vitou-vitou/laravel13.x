<?php
use App\Http\Controllers\SheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SheetController::class, 'index'])->name('sheets.index');
