<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    // ... (kode lainnya)

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        dd('Caught unauthenticated exception!');
        if ($request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        // --- UBAH BARIS INI ---
        // Ganti 'filament.admin.auth.login' dengan NAMA RUTE YANG ANDA TEMUKAN
        return redirect()->guest(route('filament.admin.auth.login'));
    }
}
