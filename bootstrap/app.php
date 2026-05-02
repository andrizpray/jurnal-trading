<?php

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
        $middleware->validateCsrfTokens([
            'login',
            'register',
            'logout',
            'api/*',
        ]);
        
        // Add web middleware to API routes for session-based auth
        $middleware->prependToGroup('api', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);

        // Redirect unauthenticated users to login (for web), return 401 for API
        $middleware->redirectGuestsTo(fn ($request) => 
            $request->expectsJson() ? null : route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
