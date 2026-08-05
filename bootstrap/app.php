<?php

use App\Http\Middleware\AddDiscoveryHeaders;
use App\Http\Middleware\AddPublicContentSecurityPolicyHeaders;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectToCanonicalHost;
use App\Http\Middleware\ServeMarkdown;
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
        $middleware->trustProxies(at: '*');
        $middleware->prepend(RedirectToCanonicalHost::class);
        $middleware->append(AddPublicContentSecurityPolicyHeaders::class);
        $middleware->appendToGroup('web', [
            AddDiscoveryHeaders::class,
            ServeMarkdown::class,
        ]);
        $middleware->alias([
            'inertia' => HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
