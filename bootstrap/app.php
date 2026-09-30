<?php

use App\Http\Controllers\ErrorPageController;
use App\Http\Middleware\AddDiscoveryHeaders;
use App\Http\Middleware\AddPublicContentSecurityPolicyHeaders;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectToCanonicalHost;
use App\Http\Middleware\ServeMarkdown;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Production runs behind HAProxy in transparent mode: PHP already sees the visitor's
        // IP as REMOTE_ADDR, so the proxy cannot be matched by address. HAProxy deletes any
        // client X-Forwarded-Proto and sets it itself, so trust that header only. Client
        // X-Forwarded-Port/Prefix pass through HAProxy and must not be trusted.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_PROTO,
        );
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
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if (app()->environment('local', 'testing')) {
                return $response;
            }

            $status = $response->getStatusCode();
            $route = $request->route();

            if (
                ! in_array($status, [403, 404, 500, 503], true)
                || ! $route instanceof Route
                || ! in_array('inertia', $route->gatherMiddleware(), true)
                || $request->is('cp', 'cp/*')
                || ($request->expectsJson() && ! $request->header('X-Inertia'))
            ) {
                return $response;
            }

            return resolve(ErrorPageController::class)->render($request, $status);
        });
    })->create();
