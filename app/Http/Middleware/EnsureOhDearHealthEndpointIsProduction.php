<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureOhDearHealthEndpointIsProduction
{
    public function handle(Request $request, Closure $next): Response
    {
        $isProductionHost = $request->getHost() === config('health.oh_dear_endpoint.production_host');

        abort_unless(
            app()->environment('production') && $isProductionHost,
            Response::HTTP_NOT_FOUND,
        );

        return $next($request);
    }
}
