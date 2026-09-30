<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\ResponseCache\PublicResponseCacheTags;
use Closure;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Configuration\CacheConfiguration;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\ResponseCache;
use Symfony\Component\HttpFoundation\Response;

final class CachePublicResponse extends CacheResponse
{
    public function __construct(
        ResponseCache $responseCache,
        private readonly PublicResponseCacheTags $tags,
    ) {
        parent::__construct($responseCache);
    }

    public function handle(Request $request, Closure $next, ...$args): Response
    {
        $configuration = new CacheConfiguration(
            tags: $this->tags->forRequest($request),
        );

        return parent::handle(
            $request,
            $next,
            base64_encode(serialize($configuration)),
        );
    }
}
