<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $e, $request) {
            $message = 'Los archivos superan el tamaño permitido por el servidor. Intenta con menos fotos por lote o menor tamaño por archivo.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 413);
            }

            return redirect()->back()->withErrors(['fotos' => $message])->withInput();
        });
    })->create();
