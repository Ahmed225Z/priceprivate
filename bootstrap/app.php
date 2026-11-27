<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'merchant' => \App\Http\Middleware\EnsureMerchant::class,
            'onboarded' => \App\Http\Middleware\EnsureOnboarded::class,
        ]);
        $middleware->redirectGuestsTo('/login');
        // The webhook is verified by HMAC signature instead of a CSRF token.
        $middleware->validateCsrfTokens(except: ['payment/webhook/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
