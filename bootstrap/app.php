<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LogCsrfViolations;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            LogCsrfViolations::class,
        ]);

        // Ensure CSRF protection is enabled for web routes
        $middleware->validateCsrfTokens(except: [
            // API routes if needed
        ]);

        // Register the admin middleware alias
        $middleware->alias([
            'admin' => AdminAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
