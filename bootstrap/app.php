<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // Alias para verificación de roles vía per_dep.id_rol
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            // Sobrescribe el alias "verified" con nuestra versión JSON (no redirige)
            'verified' => \App\Http\Middleware\EnsureEmailIsVerifiedJson::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

