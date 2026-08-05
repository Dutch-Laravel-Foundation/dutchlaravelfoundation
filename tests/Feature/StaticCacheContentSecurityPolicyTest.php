<?php

declare(strict_types=1);
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->staticCachePath = storage_path('framework/testing/static-csp');

    File::deleteDirectory($this->staticCachePath);

    config([
        'cache.stores.static_cache' => ['driver' => 'array'],
        'statamic.static_caching.strategy' => 'full',
        'statamic.static_caching.strategies.full.path' => $this->staticCachePath,
    ]);
});
afterEach(function () {
    File::deleteDirectory($this->staticCachePath);

});
it('full measure cache misses and hits use the response csp nonce', function () {
    $cacheMiss = $this->get('/stagebank');

    $cacheMiss->assertOk();
    assertInlineElementsUseResponseNonce($cacheMiss);
    expect("{$this->staticCachePath}/stagebank_.html")->toBeFile();
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

    expect($cachedNonces[2])->not->toBeEmpty();

    foreach ($cachedNonces[2] as $nonce) {
        expect($nonce)->toBe('STATAMIC_CSP_NONCE');
    }

    $cacheHit = $this->get('/stagebank');

    $cacheHit->assertOk();
    assertInlineElementsUseResponseNonce($cacheHit);
});
function assertInlineElementsUseResponseNonce(TestResponse $response): void
{
    $response->assertHeader('Content-Security-Policy');

    $policy = (string) $response->headers->get('Content-Security-Policy');

    expect($policy)->toMatch("/script-src[^;]*'nonce-([^']+)'/");
    preg_match("/script-src[^;]*'nonce-([^']+)'/", $policy, $matches);

    $nonce = $matches[1];
    $content = (string) $response->getContent();

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
        expect($tag)->toContain("nonce=\"{$nonce}\"");
    }
}
