<?php

use App\Http\Middleware\LibraryAuthenticate;
use App\Http\Middleware\LibraryRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(api: __DIR__.'/../routes/api.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'library.auth' => LibraryAuthenticate::class,
            'library.role' => LibraryRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception) {
            $status = $exception->status ?? 422;
            $message = $status === 401 ? 'Las credenciales no son válidas.' : 'La solicitud no es válida.';
            return response()->json([
                'code' => $status === 401 ? 'INVALID_CREDENTIALS' : 'VALIDATION_ERROR',
                'message' => $message,
                'details' => [],
            ], $status);
        });
    })->create();
