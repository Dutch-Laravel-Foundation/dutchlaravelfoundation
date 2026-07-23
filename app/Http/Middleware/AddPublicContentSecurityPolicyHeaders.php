<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Csp\AddCspHeaders;
use Symfony\Component\HttpFoundation\Response;

final class AddPublicContentSecurityPolicyHeaders extends AddCspHeaders
{
    public function handle(Request $request, Closure $next, ?string $customPreset = null): Response
    {
        $response = $next($request);

        if ($this->isControlPanelRequest($request) || ! $this->isHtmlResponse($response)) {
            return $response;
        }

        return parent::handle(
            $request,
            static fn (): Response => $response,
            $customPreset,
        );
    }

    private function isControlPanelRequest(Request $request): bool
    {
        $controlPanelRoute = trim((string) config('statamic.cp.route', 'cp'), '/');

        return $request->is($controlPanelRoute, "{$controlPanelRoute}/*");
    }

    private function isHtmlResponse(Response $response): bool
    {
        return str_starts_with(
            (string) $response->headers->get('Content-Type'),
            'text/html',
        );
    }
}
