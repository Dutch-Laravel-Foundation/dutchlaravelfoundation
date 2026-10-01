<?php

declare(strict_types=1);
use App\Http\Middleware\CachePublicResponse;
use App\Http\Middleware\HandleInertiaRequests;
use App\ResponseCache\PublicResponseCacheProfile;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Vite;
use Illuminate\Testing\TestResponse;
use Inertia\Support\Header;
use Spatie\ResponseCache\Facades\ResponseCache;
use Spatie\ResponseCache\ResponseCache as ResponseCacheManager;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    config([
        'cache.stores.response_cache_testing' => ['driver' => 'array'],
        'csp.enabled_while_hot_reloading' => true,
        'inertia.ssr.enabled' => false,
        'responsecache.cache.store' => 'response_cache_testing',
        'responsecache.debug.enabled' => true,
        'responsecache.enabled' => true,
    ]);

    Cache::store('response_cache_testing')->clear();
    ResponseCache::clear();
});
test('documents and inertia visits are cached separately', function () {
    app()->instance('csp-nonce', 'document-cache-miss-nonce');
    Vite::useCspNonce('document-cache-miss-nonce');
    $documentMiss = $this->get('/stagebank');

    app()->instance('csp-nonce', 'document-cache-hit-nonce');
    Vite::useCspNonce('document-cache-hit-nonce');
    $documentHit = $this->get('/stagebank');

    $documentMiss->assertHeader('X-Cache-Status', 'MISS');
    $documentHit->assertHeader('X-Cache-Status', 'HIT');
    assertInlineElementsUseResponseNonce($documentMiss);
    assertInlineElementsUseResponseNonce($documentHit);
    expect(responseNonce($documentMiss))->toBe('document-cache-miss-nonce');
    expect(responseNonce($documentHit))->toBe('document-cache-hit-nonce');

    $inertiaMiss = $this->withHeaders(documentInertiaHeaders())->get('/stagebank');
    $cachedCsrfToken = $inertiaMiss->json('props.app.csrfToken');

    $this->app['session']->driver()->regenerateToken();
    $inertiaHit = $this->withHeaders(documentInertiaHeaders())->get('/stagebank');

    $inertiaMiss
        ->assertHeader('X-Cache-Status', 'MISS')
        ->assertHeader(Header::INERTIA, 'true')
        ->assertJsonPath('component', 'Community/InternshipsIndex');
    $inertiaHit
        ->assertHeader('X-Cache-Status', 'HIT')
        ->assertJsonPath('component', 'Community/InternshipsIndex')
        ->assertJsonPath('props.app.csrfToken', csrf_token());

    $this->assertNotSame($cachedCsrfToken, $inertiaHit->json('props.app.csrfToken'));

    $this->assertNotSame(
        $documentHit->headers->get('X-Cache-Key'),
        $inertiaHit->headers->get('X-Cache-Key'),
    );
    $this->assertStringNotContainsString(
        '<laravel-responsecache-',
        (string) $inertiaHit->getContent(),
    );
});
test('only page and category query strings are cached', function () {
    $this->get('/nieuws?page=2')->assertHeader('X-Cache-Status', 'MISS');
    $this->get('/nieuws?page=2')->assertHeader('X-Cache-Status', 'HIT');

    foreach ([
        '/nieuws?utm_source=newsletter',
        '/nieuws?gclid=abc',
        '/nieuws?unknown=1',
        '/nieuws?page=abc',
        '/nieuws?page=1001',
        '/nieuws?category='.str_repeat('a', 65),
    ] as $uri) {
        $this->get($uri);
        $repeat = $this->get($uri);

        $this->assertNotSame('HIT', $repeat->headers->get('X-Cache-Status'), $uri);
    }
});
test('cached pages expire at the next full hour', function () {
    $profile = resolve(PublicResponseCacheProfile::class);
    $request = Request::create('/agenda');

    config(['responsecache.cache.lifetime_in_seconds' => 604800]);

    $this->travelTo(now()->setTime(10, 59, 30));
    expect($profile->cacheLifetimeInSeconds($request))->toBe(30);

    $this->travelTo(now()->setTime(11, 0, 0));
    expect($profile->cacheLifetimeInSeconds($request))->toBe(3600);

    config(['responsecache.cache.lifetime_in_seconds' => 60]);
    expect($profile->cacheLifetimeInSeconds($request))->toBe(60);
});
test('tracking parameters stay on the rendered page', function () {
    $this->get('/stagebank');

    $this->withHeaders(documentInertiaHeaders())
        ->get('/stagebank?utm_source=newsletter')
        ->assertJsonPath('url', '/stagebank?utm_source=newsletter');
});
test('response cache runs before inertia and statamic page resolution', function () {
    $router = app(Router::class);
    $route = $router->getRoutes()->getByName('app.insights.index');
    $middleware = $router->gatherRouteMiddleware($route);
    $sessionPosition = array_search(StartSession::class, $middleware, true);
    $cachePosition = array_search(CachePublicResponse::class, $middleware, true);
    $inertiaPosition = array_search(HandleInertiaRequests::class, $middleware, true);

    expect($sessionPosition)->toBeInt();
    expect($cachePosition)->toBeInt();
    expect($inertiaPosition)->toBeInt();
    expect($sessionPosition)->toBeLessThan($cachePosition);
    expect($cachePosition)->toBeLessThan($inertiaPosition);
});
test('infinite scroll variants have independent cache entries', function () {
    $fullHeaders = documentInertiaHeaders();
    $appendHeaders = [
        ...$fullHeaders,
        Header::PARTIAL_COMPONENT => 'Editorial/InsightsIndex',
        Header::PARTIAL_ONLY => 'editorial',
        Header::INFINITE_SCROLL_MERGE_INTENT => 'append',
    ];
    $prependHeaders = [
        ...$appendHeaders,
        Header::INFINITE_SCROLL_MERGE_INTENT => 'prepend',
    ];

    $full = $this->withHeaders($fullHeaders)->get('/nieuws?page=2');
    $appendMiss = $this->withHeaders($appendHeaders)->get('/nieuws?page=2');
    $appendHit = $this->withHeaders($appendHeaders)->get('/nieuws?page=2');
    $prepend = $this->withHeaders($prependHeaders)->get('/nieuws?page=2');

    $full->assertHeader('X-Cache-Status', 'MISS');
    $appendMiss->assertHeader('X-Cache-Status', 'MISS');
    $appendHit->assertHeader('X-Cache-Status', 'HIT');
    $prepend->assertHeader('X-Cache-Status', 'MISS');

    expect($appendHit->json('props.editorial.items'))->toHaveCount(10);
    $this->assertNotSame(
        $full->headers->get('X-Cache-Key'),
        $appendHit->headers->get('X-Cache-Key'),
    );
    $this->assertNotSame(
        $appendHit->headers->get('X-Cache-Key'),
        $prepend->headers->get('X-Cache-Key'),
    );
});
test('entry and overview tags leave sibling detail responses cached', function () {
    $responseCache = resolve(ResponseCacheManager::class);
    $firstEntry = Request::create('/nieuws/eerste-artikel');
    $secondEntry = Request::create('/nieuws/tweede-artikel');
    $overview = Request::create('/nieuws');
    $siteShellTag = 'site-shell';

    $responseCache->cacheResponse(
        $firstEntry,
        new Response('First'),
        tags: [$siteShellTag, 'entry:/nieuws/eerste-artikel'],
    );
    $responseCache->cacheResponse(
        $secondEntry,
        new Response('Second'),
        tags: [$siteShellTag, 'entry:/nieuws/tweede-artikel'],
    );
    $responseCache->cacheResponse(
        $overview,
        new Response('Overview'),
        tags: [$siteShellTag, 'overview:insights'],
    );

    $responseCache->clear([
        'entry:/nieuws/eerste-artikel',
        'overview:insights',
    ]);

    expect($responseCache->hasBeenCached(
        $firstEntry,
        [$siteShellTag, 'entry:/nieuws/eerste-artikel'],
    ))->toBeFalse();
    expect($responseCache->hasBeenCached(
        $secondEntry,
        [$siteShellTag, 'entry:/nieuws/tweede-artikel'],
    ))->toBeTrue();
    expect($responseCache->hasBeenCached(
        $overview,
        [$siteShellTag, 'overview:insights'],
    ))->toBeFalse();
});
/** @return array<string, string> */
function documentInertiaHeaders(): array
{
    return [
        'Accept' => 'text/html, application/xhtml+xml',
        Header::INERTIA => 'true',
        Header::VERSION => hash_file('xxh128', public_path('build/manifest.json')),
        'X-Requested-With' => 'XMLHttpRequest',
    ];
}
function responseNonce(TestResponse $response): string
{
    $policy = (string) $response->headers->get('Content-Security-Policy');

    preg_match("/script-src[^;]*'nonce-([^']+)'/", $policy, $matches);

    return $matches[1] ?? '';
}
function assertInlineElementsUseResponseNonce(TestResponse $response): void
{
    $nonce = responseNonce($response);
    $content = (string) $response->getContent();

    test()->assertNotSame('', $nonce);

    preg_match_all('/\bnonce=(["\'])(.*?)\1/i', $content, $responseNonces);

    expect($responseNonces[2])->not->toBeEmpty();

    foreach ($responseNonces[2] as $responseNonce) {
        expect($responseNonce)->toBe($nonce);
    }

    preg_match_all(
        '/<style\b[^>]*>|<script\b(?![^>]*\bsrc=)[^>]*>/',
        $content,
        $tags,
    );

    expect($tags[0])->not->toBeEmpty();

    foreach ($tags[0] as $tag) {
        if (preg_match('/\btype=(["\'])application\/(?:ld\+)?json\1/i', $tag)) {
            continue;
        }

        test()->assertStringContainsString("nonce=\"{$nonce}\"", $tag);
    }
}
