<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ApiUserAuthenticated;
use App\Http\Middleware\AdminAuthenticated;
use App\Http\Middleware\AdminRole;
use App\Http\Middleware\UserPhoneAuthenticated;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.user' => ApiUserAuthenticated::class,
            'admin.auth' => AdminAuthenticated::class,
            'admin.role' => AdminRole::class,
            'user.auth' => UserPhoneAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
