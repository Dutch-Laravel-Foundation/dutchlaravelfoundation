<?php

declare(strict_types=1);

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Tests\TestCase;

class PublicPagePerformanceTest extends TestCase
{
    public function testStaticPagesUseAnIsolatedCacheStore(): void
    {
        $this->assertSame('file', config('cache.stores.static_cache.driver'));
        $this->assertSame(base_path('cache/static'), config('cache.stores.static_cache.path'));
        $this->assertSame('file', config('statamic.static_caching.strategies.full.driver'));
        $this->assertSame(public_path('static'), config('statamic.static_caching.strategies.full.path'));
    }

    public function testDeploymentResetsPhpBeforeWarmingStaticPages(): void
    {
        $deployment = file_get_contents(base_path('Envoy.blade.php'));

        $this->assertNotFalse($deployment);
        $this->assertStringContainsString('php please static:clear', $deployment);
        $this->assertStringContainsString('php please static:warm', $deployment);
        $this->assertLessThan(
            strpos($deployment, "    warm_static_cache\n"),
            strpos($deployment, "    reset_php_cache\n"),
        );
    }

    public function testSharedLayoutKeepsNonCriticalThirdPartiesOffTheCriticalPath(): void
    {
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
    }

    public function testMainEntrypointLoadsOptionalEnhancementsConditionally(): void
    {
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
    }

    public function testHomepageServesAResponsiveModernHeroImage(): void
    {
        $xpath = $this->pageXPath('/');
        $heroImage = $xpath->query('//figure[contains(@class, "dlf-home-hero__photo")]//img');
        $webpSource = $xpath->query('//figure[contains(@class, "dlf-home-hero__photo")]//source[@type="image/webp"]');

        $this->assertInstanceOf(DOMNodeList::class, $heroImage);
        $this->assertInstanceOf(DOMNodeList::class, $webpSource);
        $this->assertCount(1, $heroImage);
        $this->assertCount(1, $webpSource);

        $image = $heroImage->item(0);
        $source = $webpSource->item(0);

        $this->assertInstanceOf(DOMElement::class, $image);
        $this->assertInstanceOf(DOMElement::class, $source);
        $this->assertSame('eager', $image->getAttribute('loading'));
        $this->assertSame('high', $image->getAttribute('fetchpriority'));
        $this->assertSame('async', $image->getAttribute('decoding'));
        $this->assertStringContainsString('640w', $source->getAttribute('srcset'));
        $this->assertStringContainsString('1280w', $source->getAttribute('srcset'));
        $this->assertStringContainsString('1920w', $source->getAttribute('srcset'));
        $this->assertSame('(min-width: 1024px) 50vw, 100vw', $source->getAttribute('sizes'));
    }

    public function testSharedFooterUsesSizedLazyLoadedBadgeImages(): void
    {
        $xpath = $this->pageXPath('/');
        $badges = $xpath->query('//footer//*[contains(concat(" ", normalize-space(@class), " "), " dlf-footer-badges ")]//img');

        $this->assertInstanceOf(DOMNodeList::class, $badges);
        $this->assertCount(3, $badges);

        foreach ($badges as $badge) {
            $this->assertInstanceOf(DOMElement::class, $badge);
            $this->assertSame('lazy', $badge->getAttribute('loading'));
            $this->assertSame('async', $badge->getAttribute('decoding'));
            $this->assertMatchesRegularExpression('/^[1-9][0-9]*$/', $badge->getAttribute('width'));
            $this->assertMatchesRegularExpression('/^[1-9][0-9]*$/', $badge->getAttribute('height'));
        }
    }

    public function testHomepageDefersBelowTheFoldPartnerAndClientLogos(): void
    {
        $xpath = $this->pageXPath('/');
        $logos = $xpath->query('//section[contains(@class, "dlf-home-partners") or contains(@class, "dlf-home-clients")]//img');

        $this->assertInstanceOf(DOMNodeList::class, $logos);
        $this->assertGreaterThan(20, $logos->length);

        foreach ($logos as $logo) {
            $this->assertInstanceOf(DOMElement::class, $logo);
            $this->assertSame('lazy', $logo->getAttribute('loading'));
            $this->assertSame('async', $logo->getAttribute('decoding'));
        }
    }

    public function testPublicPageFamiliesServeGlidePhotographyAsWebp(): void
    {
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
            $xpath = $this->pageXPath($uri);
            $images = $xpath->query('//img[@data-progressive-media and starts-with(@src, "/img/")]');

            $this->assertInstanceOf(DOMNodeList::class, $images);
            $this->assertGreaterThan(0, $images->length, $uri);

            foreach ($images as $image) {
                $this->assertInstanceOf(DOMElement::class, $image);
                $this->assertStringContainsString('fm=webp', $image->getAttribute('src'), $uri);
            }
        }
    }

    private function pageXPath(string $uri): DOMXPath
    {
        $response = $this->get($uri);
        $response->assertOk();

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }
}
