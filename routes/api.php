<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', static fn () => response()->json(['status' => 'ok']));
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('library.auth')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::get('/admin-only', static fn () => response()->json(['status' => 'ok']))
            ->middleware('library.role:admin');
    });
});
