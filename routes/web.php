<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\FotosController; // ✅ IMPORTANTE

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/** HOME FC */
Route::get('/fccogotesalado', [AvisoController::class, 'home'])
    ->name('fccs.home'); // ✅ ESTE NOMBRE FALTABA

/** GALERÍA */
Route::get('/fccogotesalado/fotos', [FotosController::class, 'index'])
    ->name('fccs.fotos');

require __DIR__.'/auth.php';
