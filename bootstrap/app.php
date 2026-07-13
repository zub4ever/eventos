<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Modules\Tenancy\Http\Middleware\EnsureAuthenticatedUserBelongsToTenant;
use App\Modules\Tenancy\Http\Middleware\ResolveTenantFromSubdomain;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ResolveTenantFromSubdomain::class,
            EnsureAuthenticatedUserBelongsToTenant::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->api(append: [
            ResolveTenantFromSubdomain::class,
            EnsureAuthenticatedUserBelongsToTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
