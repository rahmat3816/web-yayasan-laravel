<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureSetoranListAccess;
use App\Http\Middleware\EnsureGuruPengampu;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Pastikan namespace Spatie Permission benar (tanpa 's' di Middleware)
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'unit.access' => \App\Http\Middleware\CheckUnitAccess::class,
            'ensure.setoran.list.access' => EnsureSetoranListAccess::class,
            'ensure.guru.pengampu' => EnsureGuruPengampu::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handler (optional)
    })
    ->create();
