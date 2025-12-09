<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware &$middleware): void {
        $middleware = (new Middleware)->redirectGuestsTo(function () {
            if (auth()->guard() instanceof Illuminate\Auth\SessionGuard && auth()->guard()->name === 'admin') {
                return route('admin.login');
            }

            return route(tenant() ? 'tenant.login' : 'central.login');
        });
        
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TenantCouldNotBeIdentifiedException $e) {
            return redirect(config('app.url'));
        });
        //
    })->create();
