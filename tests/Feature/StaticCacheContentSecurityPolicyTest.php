<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class StaticCacheContentSecurityPolicyTest extends TestCase
{
    private string $staticCachePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staticCachePath = storage_path('framework/testing/static-csp');

        File::deleteDirectory($this->staticCachePath);

        config([
            'cache.stores.static_cache' => ['driver' => 'array'],
            'csp.enabled_while_hot_reloading' => true,
            'statamic.static_caching.strategy' => 'full',
            'statamic.static_caching.strategies.full.path' => $this->staticCachePath,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->staticCachePath);

        parent::tearDown();
    }

    public function test_full_measure_cache_misses_and_hits_use_the_response_csp_nonce(): void
    {
        $cacheMiss = $this->get('/stagebank');

        $cacheMiss->assertOk();
        $this->assertInlineElementsUseResponseNonce($cacheMiss);
        $this->assertFileExists("{$this->staticCachePath}/stagebank_.html");
        $cachedContent = File::get("{$this->staticCachePath}/stagebank_.html");

        preg_match_all(
            '/<style\b[^>]*>|<script\b(?![^>]*\bsrc=)[^>]*>/',
            $cachedContent,
            $cachedTags,
        );

        foreach ($cachedTags[0] as $tag) {
            $this->assertStringContainsString('nonce="STATAMIC_CSP_NONCE"', $tag);
        }

        preg_match_all('/\bnonce=(["\'])(.*?)\1/i', $cachedContent, $cachedNonces);

        $this->assertNotEmpty($cachedNonces[2]);

        foreach ($cachedNonces[2] as $nonce) {
            $this->assertSame('STATAMIC_CSP_NONCE', $nonce);
        }

        $cacheHit = $this->get('/stagebank');

        $cacheHit->assertOk();
        $this->assertInlineElementsUseResponseNonce($cacheHit);
    }

    private function assertInlineElementsUseResponseNonce(TestResponse $response): void
    {
        $response->assertHeader('Content-Security-Policy');

        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression("/script-src[^;]*'nonce-([^']+)'/", $policy);
        preg_match("/script-src[^;]*'nonce-([^']+)'/", $policy, $matches);

        $nonce = $matches[1];
        $content = (string) $response->getContent();

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
            $this->assertStringContainsString("nonce=\"{$nonce}\"", $tag);
        }
    }
}
