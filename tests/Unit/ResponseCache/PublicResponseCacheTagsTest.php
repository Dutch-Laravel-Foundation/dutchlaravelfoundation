<?php

declare(strict_types=1);

use App\Http\Middleware\CachePublicResponse;
use App\ResponseCache\PublicResponseCacheTags;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Spatie\ResponseCache\ResponseCache;
use Symfony\Component\HttpFoundation\Response;

it('tags an entry detail independently from sibling entries', function (): void {
    $tags = resolve(PublicResponseCacheTags::class);

    expect($tags->forRequest(responseCacheRequest('app.insights.show', '/nieuws/eerste-artikel')))
        ->toBe([
            PublicResponseCacheTags::SITE_SHELL,
            'entry:/nieuws/eerste-artikel',
        ])
        ->and($tags->forRequest(responseCacheRequest('app.insights.show', '/nieuws/tweede-artikel')))
        ->toBe([
            PublicResponseCacheTags::SITE_SHELL,
            'entry:/nieuws/tweede-artikel',
        ]);
});

it('tags overview and aggregate responses with their content dependencies', function (): void {
    $tags = resolve(PublicResponseCacheTags::class);

    expect($tags->forRequest(responseCacheRequest('app.insights.index', '/nieuws')))
        ->toBe([
            PublicResponseCacheTags::SITE_SHELL,
            'overview:insights',
        ])
        ->and($tags->forRequest(responseCacheRequest('app.home', '/')))
        ->toBe([
            PublicResponseCacheTags::SITE_SHELL,
            'entry:/',
            'overview:insights',
            'overview:knowledge',
            'overview:partners',
            'overview:clients',
        ])
        ->and($tags->forRequest(responseCacheRequest('app.knowledge.show', '/kennis/een-artikel')))
        ->toBe([
            PublicResponseCacheTags::SITE_SHELL,
            'entry:/kennis/een-artikel',
            'overview:authors',
        ])
        ->and($tags->forRequest(responseCacheRequest('app.members.show', '/leden/swis')))
        ->toBe([
            PublicResponseCacheTags::SITE_SHELL,
            'entry:/leden/swis',
            'overview:internships',
            'overview:cases',
        ]);
});

it('passes the route dependency tags to Spatie response cache', function (): void {
    config(['responsecache.debug.enabled' => false]);

    $request = responseCacheRequest('app.insights.show', '/nieuws/eerste-artikel');
    $expectedTags = [
        PublicResponseCacheTags::SITE_SHELL,
        'entry:/nieuws/eerste-artikel',
    ];
    $responseCache = $this->createMock(ResponseCache::class);
    $responseCache->method('enabled')->willReturn(true);
    $responseCache->method('shouldBypass')->willReturn(false);
    $responseCache->expects($this->once())
        ->method('getCachedResponseFor')
        ->with($this->identicalTo($request), $this->identicalTo($expectedTags))
        ->willReturn(null);
    $responseCache->method('shouldCache')->willReturn(false);

    $middleware = new CachePublicResponse(
        $responseCache,
        new PublicResponseCacheTags,
    );

    $response = $middleware->handle(
        $request,
        static fn (): Response => new Response('fresh'),
    );

    expect($response->getContent())->toBe('fresh');
});

function responseCacheRequest(string $routeName, string $uri): Request
{
    $request = Request::create($uri, 'GET');
    $route = app('router')->getRoutes()->getByName($routeName);

    expect($route)->toBeInstanceOf(Route::class);

    $route->bind($request);
    $request->setRouteResolver(static fn (): Route => $route);

    return $request;
}
