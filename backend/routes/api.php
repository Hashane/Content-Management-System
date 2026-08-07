<?php

use App\Http\Controllers\Api\V1\Admin\MenuController;
use App\Http\Controllers\Api\V1\Admin\PageController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/auth/tokens', [TokenController::class, 'store'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
        Route::get('/me', MeController::class);
        Route::delete('/auth/tokens/current', [TokenController::class, 'destroy']);

        Route::prefix('admin')->group(function (): void {
            Route::get('/pages/trash', [PageController::class, 'trash']);
            Route::post('/pages/{page}/restore', [PageController::class, 'restore'])->withTrashed();
            Route::apiResource('pages', PageController::class);

            Route::post('/menus/{menu}/restore', [MenuController::class, 'restore'])->withTrashed();
            Route::apiResource('menus', MenuController::class);
        });
    });
});
