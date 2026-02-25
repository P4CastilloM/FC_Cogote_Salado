<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\LineupController;
use App\Http\Middleware\TrackPageVisit;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\FotosController;
use App\Http\Controllers\NoticiasController;
use App\Http\Controllers\DirectivaController;
use App\Http\Controllers\PlantelController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\PartidoAsistenciaController;
use App\Http\Controllers\Admin\PartidoStatsController;

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
        Route::get('/partidos-activos', [AdminDashboardController::class, 'activeMatches'])->name('partidos.activos');
        Route::delete('/partidos/{partidoId}/confirmados/{jugadorRut}', [AdminDashboardController::class, 'removeConfirmedPlayer'])->name('partidos.confirmados.destroy');
        Route::get('/partidos/{id}/estadisticas', [PartidoStatsController::class, 'index'])->name('partidos.stats');
        Route::post('/partidos/{id}/estadisticas', [PartidoStatsController::class, 'update'])->name('partidos.stats.update');
        Route::get('/partidos/{id}/estadisticas/data', [PartidoStatsController::class, 'data'])->name('partidos.stats.data');
        Route::post('/partidos/{id}/estadisticas/finalizar', [PartidoStatsController::class, 'finish'])->name('partidos.stats.finish');
        Route::post('/dashboard/convert-images-webp', [AdminDashboardController::class, 'convertImagesToWebp'])->name('dashboard.convert-images-webp');
        Route::get('/plantilla', [LineupController::class, 'index'])->name('lineup.index');
        Route::post('/visitantes/{rut}/traspasar', [AdminModuleController::class, 'transferVisitante'])->name('visitantes.transfer');

        $modules = ['plantel', 'visitantes', 'noticias', 'avisos', 'album', 'directiva', 'partidos', 'premios', 'temporadas', 'staff', 'modificaciones'];

        foreach ($modules as $module) {
            Route::get("/{$module}", fn (\Illuminate\Http\Request $request, AdminModuleController $controller) => $controller->index($request, $module))->name("{$module}.index");
            Route::get("/{$module}/create", fn (AdminModuleController $controller) => $controller->create($module))->name("{$module}.create");
            Route::post("/{$module}", fn (\Illuminate\Http\Request $request, AdminModuleController $controller) => $controller->store($request, $module))->name("{$module}.store");
            Route::get("/{$module}/{id}/edit", fn (string $id, AdminModuleController $controller) => $controller->edit($module, $id))->name("{$module}.edit");
            Route::put("/{$module}/{id}", fn (\Illuminate\Http\Request $request, string $id, AdminModuleController $controller) => $controller->update($request, $module, $id))->name("{$module}.update");
            Route::delete("/{$module}/{id}", fn (string $id, AdminModuleController $controller) => $controller->destroy($module, $id))->name("{$module}.destroy");
        }
    });

/** HOME FC */
Route::get('/fccogotesalado', [AvisoController::class, 'home'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.home');




/** PLANTEL */
Route::get('/fccogotesalado/plantel', [PlantelController::class, 'index'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.plantel');

/** CALENDARIO */
Route::get('/fccogotesalado/calendario', [CalendarioController::class, 'index'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.calendario');

/** DIRECTIVA */
Route::get('/fccogotesalado/directiva', [DirectivaController::class, 'index'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.directiva');

/** NOTICIAS */
Route::get('/fccogotesalado/noticias', [NoticiasController::class, 'index'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.noticias.index');

Route::get('/fccogotesalado/noticias/{id}', [NoticiasController::class, 'show'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.noticias.show');

/** GALERÍA */
Route::get('/fccogotesalado/fotos', [FotosController::class, 'index'])
    ->middleware(TrackPageVisit::class)
    ->name('fccs.fotos');

Route::prefix('/fccogotesalado/partidos/asistencia')->name('fccs.partidos.asistencia.')->group(function () {
    Route::get('/{token}', [PartidoAsistenciaController::class, 'show'])->name('show');
    Route::get('/{token}/buscar', [PartidoAsistenciaController::class, 'search'])->name('search');
    Route::post('/{token}', [PartidoAsistenciaController::class, 'confirm'])->name('confirm');
});

require __DIR__.'/auth.php';
