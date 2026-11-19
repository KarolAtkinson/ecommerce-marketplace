<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureAdministratorMfa;
use App\Http\Middleware\EnsureApprovedRetailer;
use App\Http\Middleware\RetailerMaintenance;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->authenticateSessions();
        $middleware->web(append: [SetLocale::class]);
        $middleware->alias([
            'account.active' => EnsureAccountIsActive::class,
            'admin.mfa' => EnsureAdministratorMfa::class,
            'retailer.approved' => EnsureApprovedRetailer::class,
            'retailer.maintenance' => RetailerMaintenance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
