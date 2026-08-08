<?php

use App\Http\Middleware\AddSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->prepend(AddSecurityHeaders::class);
       
        // Avoids false CSRF failures for token clients sharing a stateful domain (e.g. localhost).
        $middleware->validateCsrfTokens(except: [
            'api/v1/auth/tokens',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
