<?php

use App\Http\Controllers\Api\V1\Admin\MenuController;
use App\Http\Controllers\Api\V1\Admin\MenuItemController;
use App\Http\Controllers\Api\V1\Admin\PageController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\TokenController;
use App\Http\Controllers\Api\V1\Public\MenuController as PublicMenuController;
use App\Http\Controllers\Api\V1\Public\PageController as PublicPageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/auth/tokens', [TokenController::class, 'store'])->middleware('throttle:10,1');

    Route::prefix('public')->group(function (): void {
        Route::get('/menu', PublicMenuController::class);
        Route::get('/pages/{slug}', PublicPageController::class);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
        Route::get('/me', MeController::class);
        Route::delete('/auth/tokens/current', [TokenController::class, 'destroy']);

        Route::prefix('admin')->group(function (): void {
            Route::get('/pages/trash', [PageController::class, 'trash']);
            Route::post('/pages/{page}/restore', [PageController::class, 'restore'])->withTrashed();
            Route::apiResource('pages', PageController::class);

            Route::get('/menu/tree', [MenuController::class, 'tree']);
            Route::post('/menu/items', [MenuItemController::class, 'store']);
            Route::put('/menu/items/{item}', [MenuItemController::class, 'update']);
            Route::patch('/menu/items/{item}/move', [MenuItemController::class, 'move']);
            Route::delete('/menu/items/{item}', [MenuItemController::class, 'destroy']);

            Route::patch('/roles/{role}/privileges', [RoleController::class, 'syncPrivileges']);
        });
    });
});
