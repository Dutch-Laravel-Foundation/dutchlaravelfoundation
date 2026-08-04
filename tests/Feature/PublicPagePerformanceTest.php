<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagePerformanceTest extends TestCase
{
    public function test_static_pages_use_an_isolated_cache_store(): void
    {
        $this->assertSame('file', config('cache.stores.static_cache.driver'));
        $this->assertSame(base_path('cache/static'), config('cache.stores.static_cache.path'));
        $this->assertSame('file', config('statamic.static_caching.strategies.full.driver'));
        $this->assertSame(public_path('static'), config('statamic.static_caching.strategies.full.path'));
    }

    public function test_deployment_warms_before_activation_and_checks_health_before_cleanup(): void
    {
        $deployment = file_get_contents(base_path('Envoy.blade.php'));

        $this->assertNotFalse($deployment);
        $this->assertStringContainsString('bun install --frozen-lockfile', $deployment);
        $this->assertStringContainsString('bun run build', $deployment);
        $this->assertStringNotContainsString('npm ci', $deployment);
        $this->assertStringNotContainsString('npm run build', $deployment);
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
        $this->assertLessThan($activation, $staticWarm);
        $this->assertLessThan($opcacheReset, $activation);
        $this->assertLessThan($healthCheck, $opcacheReset);
        $this->assertLessThan($cleanup, $healthCheck);
    }

    public function test_shared_layout_keeps_non_critical_third_parties_off_the_critical_path(): void
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

    public function test_main_entrypoint_loads_optional_enhancements_conditionally(): void
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

    public function test_homepage_serves_a_responsive_modern_hero_image(): void
    {
        $hero = file_get_contents(resource_path('js/components/home/HomeHero.tsx'));

        $this->assertNotFalse($hero);
        $this->assertStringContainsString('type="image/webp"', $hero);
        $this->assertStringContainsString('640.webp 640w', $hero);
        $this->assertStringContainsString('1280.webp 1280w', $hero);
        $this->assertStringContainsString('1920.webp 1920w', $hero);
        $this->assertStringContainsString('sizes="(min-width: 1024px) 50vw, 100vw"', $hero);
        $this->assertStringContainsString('loading="eager"', $hero);
        $this->assertStringContainsString('fetchPriority="high"', $hero);
        $this->assertStringContainsString('decoding="async"', $hero);
    }

    public function test_shared_footer_uses_sized_lazy_loaded_badge_images(): void
    {
        $footer = file_get_contents(resource_path('js/components/site/Footer.tsx'));

        $this->assertNotFalse($footer);
        $this->assertStringContainsString('leadinfo-240.webp', $footer);
        $this->assertStringContainsString('larabelles-badge-320.webp', $footer);
        $this->assertStringContainsString('shockmedia-320.webp', $footer);
        $this->assertGreaterThanOrEqual(3, substr_count($footer, 'loading="lazy"'));
        $this->assertGreaterThanOrEqual(3, substr_count($footer, 'decoding="async"'));
    }

    public function test_homepage_defers_below_the_fold_partner_and_client_logos(): void
    {
        $partners = file_get_contents(resource_path('js/components/home/PartnerMarquee.tsx'));
        $clients = file_get_contents(resource_path('js/components/home/ClientLogoWall.tsx'));

        foreach ([$partners, $clients] as $component) {
            $this->assertNotFalse($component);
            $this->assertStringContainsString('loading="lazy"', $component);
            $this->assertStringContainsString('decoding="async"', $component);
        }
    }

    public function test_react_page_families_keep_modern_image_source_contracts(): void
    {
        $components = [
            resource_path('js/components/home/HomeHero.tsx'),
            resource_path('js/components/editorial-react/Media.tsx'),
            resource_path('js/components/public-pages-react/ContentBlocks.tsx'),
            resource_path('js/components/public-pages-react/LandingParts.tsx'),
        ];

        foreach ($components as $path) {
            $component = file_get_contents($path);

            $this->assertNotFalse($component);
            $this->assertStringContainsString('ProgressiveImage', $component, $path);
            $this->assertStringContainsString('width=', $component, $path);
            $this->assertStringContainsString('height=', $component, $path);
            $this->assertStringContainsString('decoding="async"', $component, $path);
        }
    }
}
