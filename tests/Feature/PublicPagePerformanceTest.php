<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

test('public responses default to an isolated cache store', function () {
    $process = new Process([
        PHP_BINARY,
        '-r',
        'require "vendor/autoload.php"; echo json_encode(require "config/responsecache.php", JSON_THROW_ON_ERROR);',
    ], base_path(), [
        'RESPONSE_CACHE_DRIVER' => false,
        'RESPONSE_CACHE_WARM_CONCURRENCY' => false,
    ]);
    $process->mustRun();
    $responseCache = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);

    expect($responseCache['cache']['store'])->toBe('response_cache');
    expect(config('cache.stores.response_cache.driver'))->toBe('redis');
    expect(config('cache.stores.response_cache.connection'))->toBe('response_cache');
    expect(config('database.redis.response_cache.database'))->toBe('2');
    expect($responseCache['warm']['concurrency'])->toBe(20);
    expect(config('statamic.static_caching.strategy'))->toBeNull();
});
test('deployment warms the active release after health check', function () {
    $deployment = file_get_contents(base_path('Envoy.blade.php'));

    $this->assertNotFalse($deployment);
    $this->assertStringContainsString('npm ci --no-audit --no-fund', $deployment);
    $this->assertStringContainsString('npm run build', $deployment);
    $this->assertStringNotContainsString('bun install', $deployment);
    $this->assertStringNotContainsString('bun run build', $deployment);
    $this->assertStringContainsString('php artisan responsecache:clear', $deployment);
    $this->assertStringContainsString(
        'php artisan responsecache:warm --base-url=https://dutchlaravelfoundation.nl --concurrency=4 --requests-per-second=4',
        $deployment,
    );
    $this->assertStringContainsString('php artisan inertia:check-ssr', $deployment);
    $this->assertStringNotContainsString('php please static:clear', $deployment);
    $this->assertStringNotContainsString('php please static:warm', $deployment);

    $responseCacheClear = strpos($deployment, 'php artisan responsecache:clear');
    $responseCacheWarm = strpos($deployment, 'php artisan responsecache:warm');
    $ssrHealthCheck = strpos($deployment, "\n    check_ssr\n");
    $activation = strpos($deployment, 'activate_release "$RELEASE_PATH"');
    $opcacheReset = strpos($deployment, "\n    reset_opcache\n");
    $healthCheck = strpos($deployment, "\n    check_health\n");
    $cleanup = strpos($deployment, '    if ! cleanup_releases');

    $this->assertNotFalse($responseCacheClear);
    $this->assertNotFalse($responseCacheWarm);
    $this->assertNotFalse($ssrHealthCheck);
    $this->assertNotFalse($activation);
    $this->assertNotFalse($opcacheReset);
    $this->assertNotFalse($healthCheck);
    $this->assertNotFalse($cleanup);
    expect($activation)->toBeLessThan($opcacheReset);
    expect($opcacheReset)->toBeLessThan($healthCheck);
    expect($healthCheck)->toBeLessThan($ssrHealthCheck);
    expect($ssrHealthCheck)->toBeLessThan($responseCacheClear);
    expect($responseCacheClear)->toBeLessThan($responseCacheWarm);
    expect($responseCacheWarm)->toBeLessThan($cleanup);
});
test('shared layout keeps non critical third parties off the critical path', function () {
    $layout = file_get_contents(resource_path('views/app.blade.php'));

    $this->assertNotFalse($layout);
    $this->assertStringNotContainsString('use.typekit.net', $layout);
    $this->assertStringNotContainsString('fonts.googleapis.com', $layout);
    $this->assertStringNotContainsString('fonts.gstatic.com', $layout);
    $this->assertStringNotContainsString('unpkg.com/aos', $layout);
    $this->assertStringNotContainsString('googletagmanager.com/gtm.js', $layout);
    $this->assertStringNotContainsString('cdn.leadinfo.net/ping.js', $layout);
    $this->assertStringNotContainsString('snap.licdn.com/li.lms-analytics', $layout);
    $this->assertStringNotContainsString('{{ captcha:head }}', $layout);
    $this->assertStringContainsString('<x-inertia::app />', $layout);
});
test('main entrypoint loads optional enhancements conditionally', function () {
    $syntaxHighlighting = file_get_contents(resource_path('js/hooks/useSyntaxHighlighting.ts'));
    $trackingConsent = file_get_contents(resource_path('js/hooks/useTrackingConsent.ts'));
    $vragenAi = file_get_contents(resource_path('js/hooks/useVragenAiSearch.ts'));

    $this->assertNotFalse($syntaxHighlighting);
    $this->assertNotFalse($trackingConsent);
    $this->assertNotFalse($vragenAi);

    $this->assertStringContainsString('import("@/components/syntax-highlighting")', $syntaxHighlighting);
    $this->assertStringContainsString('import("@/components/deferred-third-parties")', $trackingConsent);
    $this->assertStringContainsString('import("@/components/vragen-ai-search")', $vragenAi);
});
test('homepage serves a responsive modern hero image', function () {
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
});
test('shared footer uses sized lazy loaded badge images', function () {
    $footer = file_get_contents(resource_path('js/components/site/Footer.tsx'));

    $this->assertNotFalse($footer);
    $this->assertStringContainsString('leadinfo-240.webp', $footer);
    $this->assertStringContainsString('larabelles-badge-320.webp', $footer);
    $this->assertStringContainsString('shockmedia-320.webp', $footer);
    expect(substr_count($footer, 'loading="lazy"'))->toBeGreaterThanOrEqual(3);
    expect(substr_count($footer, 'decoding="async"'))->toBeGreaterThanOrEqual(3);
});
test('homepage defers below the fold partner and client logos', function () {
    $partners = file_get_contents(resource_path('js/components/home/PartnerMarquee.tsx'));
    $clients = file_get_contents(resource_path('js/components/home/ClientLogoWall.tsx'));

    foreach ([$partners, $clients] as $component) {
        $this->assertNotFalse($component);
        $this->assertStringContainsString('loading="lazy"', $component);
        $this->assertStringContainsString('decoding="async"', $component);
    }
});
test('react page families keep modern image source contracts', function () {
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
});
