<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
        Route::get('/me', MeController::class);
       
    });
});
