<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalUrl = parse_url((string) config('app.url'));
        $canonicalHost = is_array($canonicalUrl) ? ($canonicalUrl['host'] ?? null) : null;

        if ($canonicalHost === null) {
            return $next($request);
        }

        $canonicalHost = strtolower($canonicalHost);
        $requestHost = strtolower($request->getHost());

        if ($requestHost === $canonicalHost) {
            return $next($request);
        }

        $normalizedHost = rtrim($requestHost, '.');

        if (! in_array($normalizedHost, [$canonicalHost, "www.{$canonicalHost}"], true)) {
            return $next($request);
        }

        $scheme = $canonicalUrl['scheme'] ?? $request->getScheme();
        $port = isset($canonicalUrl['port']) ? ":{$canonicalUrl['port']}" : '';

        return redirect()->away(
            "{$scheme}://{$canonicalHost}{$port}{$request->getRequestUri()}",
            Response::HTTP_PERMANENTLY_REDIRECT,
        );
    }
}
