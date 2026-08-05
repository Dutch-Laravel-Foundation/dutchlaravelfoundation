<?php

declare(strict_types=1);

use Tests\TestCase;

it('static pages use an isolated cache store', function () {
    expect(config('cache.stores.static_cache.driver'))->toBe('file');
    expect(config('cache.stores.static_cache.path'))->toBe(base_path('cache/static'));
    expect(config('statamic.static_caching.strategies.full.driver'))->toBe('file');
    expect(config('statamic.static_caching.strategies.full.path'))->toBe(public_path('static'));
});
it('deployment warms before activation and checks health before cleanup', function () {
    $deployment = file_get_contents(base_path('Envoy.blade.php'));

    $this->assertNotFalse($deployment);
    $this->assertStringContainsString('php please static:clear', $deployment);
    $this->assertStringContainsString('php please static:warm', $deployment);

    $staticWarm = strpos($deployment, 'php please static:warm');
    $activation = strpos($deployment, 'activate_release "$RELEASE_PATH"');
    $opcacheReset = strpos($deployment, "\n    reset_opcache\n");
    $healthCheck = strpos($deployment, "\n    check_health\n");
    $cleanup = strpos($deployment, '    if ! cleanup_releases');

    $this->assertNotFalse($staticWarm);
    $this->assertNotFalse($activation);
    $this->assertNotFalse($opcacheReset);
    $this->assertNotFalse($healthCheck);
    $this->assertNotFalse($cleanup);
    expect($staticWarm)->toBeLessThan($activation);
    expect($activation)->toBeLessThan($opcacheReset);
    expect($opcacheReset)->toBeLessThan($healthCheck);
    expect($healthCheck)->toBeLessThan($cleanup);
});
it('shared layout keeps non critical third parties off the critical path', function () {
    $layout = file_get_contents(resource_path('views/layouts/layout.antlers.html'));

    $this->assertNotFalse($layout);
    $this->assertStringNotContainsString('use.typekit.net', $layout);
    $this->assertStringNotContainsString('fonts.googleapis.com', $layout);
    $this->assertStringNotContainsString('fonts.gstatic.com', $layout);
    $this->assertStringNotContainsString('unpkg.com/aos', $layout);
    $this->assertStringNotContainsString('googletagmanager.com/gtm.js', $layout);
    $this->assertStringNotContainsString('cdn.leadinfo.net/ping.js', $layout);
    $this->assertStringNotContainsString('snap.licdn.com/li.lms-analytics', $layout);
    $this->assertStringNotContainsString('{{ captcha:head }}', $layout);
    $this->assertStringContainsString('data-environment="{{ environment }}"', $layout);
});
it('main entrypoint loads optional enhancements conditionally', function () {
    $entrypoint = file_get_contents(resource_path('js/site.js'));

    $this->assertNotFalse($entrypoint);

    foreach (['highlight.js', 'swiper', 'aos', 'gsap'] as $package) {
        $this->assertDoesNotMatchRegularExpression(
            '/^import .*'.preg_quote($package, '/').'.*;$/m',
            $entrypoint,
            $package,
        );
    }

    $this->assertStringContainsString('import("./components/syntax-highlighting")', $entrypoint);
    $this->assertStringContainsString('import("./components/swiper")', $entrypoint);
    $this->assertStringContainsString('import("./components/scroll-animations")', $entrypoint);
    $this->assertStringContainsString('import("./components/floor-animations")', $entrypoint);
    $this->assertStringContainsString('import("./components/deferred-third-parties")', $entrypoint);
    $this->assertStringContainsString('import("./components/vragen-ai-search")', $entrypoint);
    $this->assertStringContainsString('import("./components/turnstile")', $entrypoint);
});
it('homepage serves a responsive modern hero image', function () {
    $xpath = performancePageXPath($this, '/');
    $heroImage = $xpath->query('//figure[contains(@class, "dlf-home-hero__photo")]//img');
    $webpSource = $xpath->query('//figure[contains(@class, "dlf-home-hero__photo")]//source[@type="image/webp"]');

    expect($heroImage)->toBeInstanceOf(DOMNodeList::class);
    expect($webpSource)->toBeInstanceOf(DOMNodeList::class);
    expect($heroImage)->toHaveCount(1);
    expect($webpSource)->toHaveCount(1);

    $image = $heroImage->item(0);
    $source = $webpSource->item(0);

    expect($image)->toBeInstanceOf(DOMElement::class);
    expect($source)->toBeInstanceOf(DOMElement::class);
    expect($image->getAttribute('loading'))->toBe('eager');
    expect($image->getAttribute('fetchpriority'))->toBe('high');
    expect($image->getAttribute('decoding'))->toBe('async');
    $this->assertStringContainsString('640w', $source->getAttribute('srcset'));
    $this->assertStringContainsString('1280w', $source->getAttribute('srcset'));
    $this->assertStringContainsString('1920w', $source->getAttribute('srcset'));
    expect($source->getAttribute('sizes'))->toBe('(min-width: 1024px) 50vw, 100vw');
});
it('shared footer uses sized lazy loaded badge images', function () {
    $xpath = performancePageXPath($this, '/');
    $badges = $xpath->query('//footer//*[contains(concat(" ", normalize-space(@class), " "), " dlf-footer-badges ")]//img');

    expect($badges)->toBeInstanceOf(DOMNodeList::class);
    expect($badges)->toHaveCount(3);

    foreach ($badges as $badge) {
        expect($badge)->toBeInstanceOf(DOMElement::class);
        expect($badge->getAttribute('loading'))->toBe('lazy');
        expect($badge->getAttribute('decoding'))->toBe('async');
        expect($badge->getAttribute('width'))->toMatch('/^[1-9][0-9]*$/');
        expect($badge->getAttribute('height'))->toMatch('/^[1-9][0-9]*$/');
    }
});
it('homepage defers below the fold partner and client logos', function () {
    $xpath = performancePageXPath($this, '/');
    $logos = $xpath->query('//section[contains(@class, "dlf-home-partners") or contains(@class, "dlf-home-clients")]//img');

    expect($logos)->toBeInstanceOf(DOMNodeList::class);
    expect($logos->length)->toBeGreaterThan(20);

    foreach ($logos as $logo) {
        expect($logo)->toBeInstanceOf(DOMElement::class);
        expect($logo->getAttribute('loading'))->toBe('lazy');
        expect($logo->getAttribute('decoding'))->toBe('async');
    }
});
it('public page families serve glide photography as webp', function () {
    $uris = [
        '/',
        '/aanbestedingen',
        '/agenda',
        '/cases',
        '/cases/diabetes-nl-helpt-je-verder-weten-delen-doen',
        '/events/cxo-diner-2026',
        '/kennis',
        '/kennis/graphql-met-laravel-en-lighthouse',
        '/nieuws',
        '/nieuws/dlf-meetup-bij-dij',
        '/over-ons',
    ];

    foreach ($uris as $uri) {
        $xpath = performancePageXPath($this, $uri);
        $images = $xpath->query('//img[@data-progressive-media and starts-with(@src, "/img/")]');

        expect($images)->toBeInstanceOf(DOMNodeList::class);
        expect($images->length)->toBeGreaterThan(0, $uri);

        foreach ($images as $image) {
            expect($image)->toBeInstanceOf(DOMElement::class);
            $this->assertStringContainsString('fm=webp', $image->getAttribute('src'), $uri);
        }
    }
});
function performancePageXPath(TestCase $testCase, string $uri): DOMXPath
{
    $response = $testCase->get($uri);
    $response->assertOk();

    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return new DOMXPath($document);
}
