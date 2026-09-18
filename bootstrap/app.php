<?php

use App\Http\Middleware\NormaliserMontants;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Derrière un proxy HTTPS (ngrok, etc.) : Laravel lit X-Forwarded-Proto
        // pour générer des URLs https (sinon CSS/JS bloqués en « mixed content »).
        $middleware->trustProxies(at: '*');

        // Champs monétaires saisis avec espaces ("3 200 000") -> "3200000".
        $middleware->web(append: [NormaliserMontants::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
