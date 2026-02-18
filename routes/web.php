<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Middleware\TrackPageVisit;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\FotosController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/fccogotesalado/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/fccogotesalado/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/fccogotesalado/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/fccogotesalado/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])
    ->prefix('/fccogotesalado/admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        $modules = ['plantel', 'noticias', 'avisos', 'album', 'directiva', 'partidos', 'premios', 'temporadas', 'staff', 'modificaciones'];

        foreach ($modules as $module) {
            Route::get("/{$module}", [AdminModuleController::class, 'index'])->name("{$module}.index");
            Route::get("/{$module}/create", [AdminModuleController::class, 'create'])->name("{$module}.create");
            Route::post("/{$module}", [AdminModuleController::class, 'store'])->name("{$module}.store");
            Route::get("/{$module}/{id}/edit", [AdminModuleController::class, 'edit'])->name("{$module}.edit");
            Route::put("/{$module}/{id}", [AdminModuleController::class, 'update'])->name("{$module}.update");
            Route::delete("/{$module}/{id}", [AdminModuleController::class, 'destroy'])->name("{$module}.destroy");
        }
    });

/** HOME FC */
Route::get('/fccogotesalado', [AvisoController::class, 'home'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.home');

/** GALERÍA */
Route::get('/fccogotesalado/fotos', [FotosController::class, 'index'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.fotos');

require __DIR__.'/auth.php';
