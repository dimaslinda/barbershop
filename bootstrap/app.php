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
        // Ini adalah bagian yang perlu diubah:
        $middleware->redirectGuestsTo(fn(string $guard) => route('filament.admin.auth.login'));
        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
        // Pastikan 'admin' adalah ID panel Filament Anda.
        // Jika ID panel Anda berbeda, ubah 'filament.admin.auth.login'
        // menjadi 'filament.ID_PANEL_ANDA.auth.login'.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
