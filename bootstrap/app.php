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
