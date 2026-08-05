<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\CachePublicResponse;
use App\Http\Middleware\HandleInertiaRequests;
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
use Tests\TestCase;

final class PublicResponseCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_documents_and_inertia_visits_are_cached_separately(): void
    {
        app()->instance('csp-nonce', 'document-cache-miss-nonce');
        Vite::useCspNonce('document-cache-miss-nonce');
        $documentMiss = $this->get('/stagebank');

        app()->instance('csp-nonce', 'document-cache-hit-nonce');
        Vite::useCspNonce('document-cache-hit-nonce');
        $documentHit = $this->get('/stagebank');

        $documentMiss->assertHeader('X-Cache-Status', 'MISS');
        $documentHit->assertHeader('X-Cache-Status', 'HIT');
        $this->assertInlineElementsUseResponseNonce($documentMiss);
        $this->assertInlineElementsUseResponseNonce($documentHit);
        $this->assertSame('document-cache-miss-nonce', $this->responseNonce($documentMiss));
        $this->assertSame('document-cache-hit-nonce', $this->responseNonce($documentHit));

        $inertiaMiss = $this->withHeaders($this->inertiaHeaders())->get('/stagebank');
        $cachedCsrfToken = $inertiaMiss->json('props.app.csrfToken');

        $this->app['session']->driver()->regenerateToken();
        $inertiaHit = $this->withHeaders($this->inertiaHeaders())->get('/stagebank');

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
    }

    public function test_response_cache_runs_before_inertia_and_statamic_page_resolution(): void
    {
        $router = app(Router::class);
        $route = $router->getRoutes()->getByName('app.insights.index');
        $middleware = $router->gatherRouteMiddleware($route);
        $sessionPosition = array_search(StartSession::class, $middleware, true);
        $cachePosition = array_search(CachePublicResponse::class, $middleware, true);
        $inertiaPosition = array_search(HandleInertiaRequests::class, $middleware, true);

        $this->assertIsInt($sessionPosition);
        $this->assertIsInt($cachePosition);
        $this->assertIsInt($inertiaPosition);
        $this->assertLessThan($cachePosition, $sessionPosition);
        $this->assertLessThan($inertiaPosition, $cachePosition);
    }

    public function test_infinite_scroll_variants_have_independent_cache_entries(): void
    {
        $fullHeaders = $this->inertiaHeaders();
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

        $this->assertCount(10, $appendHit->json('props.editorial.items'));
        $this->assertNotSame(
            $full->headers->get('X-Cache-Key'),
            $appendHit->headers->get('X-Cache-Key'),
        );
        $this->assertNotSame(
            $appendHit->headers->get('X-Cache-Key'),
            $prepend->headers->get('X-Cache-Key'),
        );
    }

    public function test_entry_and_overview_tags_leave_sibling_detail_responses_cached(): void
    {
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

        $this->assertFalse($responseCache->hasBeenCached(
            $firstEntry,
            [$siteShellTag, 'entry:/nieuws/eerste-artikel'],
        ));
        $this->assertTrue($responseCache->hasBeenCached(
            $secondEntry,
            [$siteShellTag, 'entry:/nieuws/tweede-artikel'],
        ));
        $this->assertFalse($responseCache->hasBeenCached(
            $overview,
            [$siteShellTag, 'overview:insights'],
        ));
    }

    /** @return array<string, string> */
    private function inertiaHeaders(): array
    {
        return [
            'Accept' => 'text/html, application/xhtml+xml',
            Header::INERTIA => 'true',
            Header::VERSION => hash_file('xxh128', public_path('build/manifest.json')),
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    private function responseNonce(TestResponse $response): string
    {
        $policy = (string) $response->headers->get('Content-Security-Policy');

        preg_match("/script-src[^;]*'nonce-([^']+)'/", $policy, $matches);

        return $matches[1] ?? '';
    }

    private function assertInlineElementsUseResponseNonce(TestResponse $response): void
    {
        $nonce = $this->responseNonce($response);
        $content = (string) $response->getContent();

        $this->assertNotSame('', $nonce);

        preg_match_all('/\bnonce=(["\'])(.*?)\1/i', $content, $responseNonces);

        $this->assertNotEmpty($responseNonces[2]);

        foreach ($responseNonces[2] as $responseNonce) {
            $this->assertSame($nonce, $responseNonce);
        }

        preg_match_all(
            '/<style\b[^>]*>|<script\b(?![^>]*\bsrc=)[^>]*>/',
            $content,
            $tags,
        );

        $this->assertNotEmpty($tags[0]);

        foreach ($tags[0] as $tag) {
            if (preg_match('/\btype=(["\'])application\/(?:ld\+)?json\1/i', $tag)) {
                continue;
            }

            $this->assertStringContainsString("nonce=\"{$nonce}\"", $tag);
        }
    }
}
