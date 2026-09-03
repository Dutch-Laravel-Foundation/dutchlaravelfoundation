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
        $normalizedHost = rtrim($requestHost, '.');

        if (! in_array($normalizedHost, [$canonicalHost, "www.{$canonicalHost}"], true)) {
            return $next($request);
        }

        $requestUri = $request->getRequestUri();
        $requestPath = parse_url($requestUri, PHP_URL_PATH);
        $hasTrailingSlash = is_string($requestPath)
            && $requestPath !== '/'
            && str_ends_with($requestPath, '/');
        $hasFirstPageParameter = in_array($request->getMethod(), ['GET', 'HEAD'], true)
            && $request->query('page') === '1';

        if ($requestHost === $canonicalHost && ! $hasTrailingSlash && ! $hasFirstPageParameter) {
            return $next($request);
        }

        if ($hasTrailingSlash || $hasFirstPageParameter) {
            $requestUri = is_string($requestPath) ? $requestPath : $request->getPathInfo();

            if ($hasTrailingSlash) {
                $requestUri = rtrim($requestUri, '/');
            }

            if ($hasFirstPageParameter) {
                $query = $request->query();
                unset($query['page']);
                $queryString = http_build_query($query);
            } else {
                $queryString = $request->getQueryString();
            }

            if ($queryString !== null && $queryString !== '') {
                $requestUri .= "?{$queryString}";
            }
        }

        $scheme = $canonicalUrl['scheme'] ?? $request->getScheme();
        $port = isset($canonicalUrl['port']) ? ":{$canonicalUrl['port']}" : '';

        return redirect()->away(
            "{$scheme}://{$canonicalHost}{$port}{$requestUri}",
            Response::HTTP_PERMANENTLY_REDIRECT,
        );
    }
}
