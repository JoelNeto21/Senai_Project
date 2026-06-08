<?php

use App\Http\Middleware\EnsureEmployeeRole;
use App\Http\Middleware\EnsureFuncionarioAuthenticated;
use App\Http\Middleware\EnsureFuncionarioRole;
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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'employee.auth' => EnsureFuncionarioAuthenticated::class,
            'employee.role' => EnsureEmployeeRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
