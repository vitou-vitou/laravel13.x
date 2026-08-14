<?php

use App\Http\Controllers\ShipNoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShipNoteController::class, 'index'])->name('ship.index');
Route::get('/notes/create', [ShipNoteController::class, 'create'])->name('ship.create');
Route::post('/notes', [ShipNoteController::class, 'store'])->name('ship.store');
Route::get('/notes/{ship}/edit', [ShipNoteController::class, 'edit'])->name('ship.edit');
Route::put('/notes/{ship}', [ShipNoteController::class, 'update'])->name('ship.update');
Route::delete('/notes/{ship}', [ShipNoteController::class, 'destroy'])->name('ship.destroy');
