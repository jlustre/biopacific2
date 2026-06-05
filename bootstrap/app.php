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
    ->withMiddleware(function (Middleware $middleware) {
        // Authenticated users hitting /login or /register go to the member dashboard,
        // not route('home') which redirects to the public corporate marketing site.
        $middleware->redirectUsersTo(fn () => route('dashboard.index'));
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'resolve.tenant' => \App\Http\Middleware\ResolveTenant::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        // Register CheckFacilityShutdown as global middleware
        $middleware->append(\App\Http\Middleware\CheckFacilityShutdown::class);

        // Note: We're not adding ResolveTenant to the web group globally
        // Instead, we'll apply it selectively in routes/web.php
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
