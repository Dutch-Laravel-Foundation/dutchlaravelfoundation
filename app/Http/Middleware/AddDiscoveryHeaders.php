<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddDiscoveryHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (! str_starts_with($contentType, 'text/html')) {
            return $response;
        }

        $base = rtrim(config('app.url'), '/');

        $response->headers->set('Link', implode(', ', [
            '<' . $base . '/llms.txt>; rel="llms-txt"',
            '<' . $base . '/sitemap.xml>; rel="sitemap"',
            '<' . $base . '/.well-known/mcp.json>; rel="mcp"',
        ]));

        return $response;
    }
}
