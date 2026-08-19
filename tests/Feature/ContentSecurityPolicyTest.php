<?php

declare(strict_types=1);
use Illuminate\Foundation\Vite;

it('public html responses use an enforced content security policy', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertHeader('Content-Security-Policy');
    $response->assertHeaderMissing('Content-Security-Policy-Report-Only');

    $policy = (string) $response->headers->get('Content-Security-Policy');

    foreach ([
        "base-uri 'self'",
        "connect-src 'self'",
        "default-src 'self'",
        "font-src 'self' data:",
        "form-action 'self'",
        'frame-src https://challenges.cloudflare.com',
        "frame-ancestors 'self'",
        "img-src 'self' data: blob:",
        "object-src 'none'",
        "script-src 'self'",
        "style-src 'self'",
        "style-src 'self' https://dlf.vragen.ai",
        "style-src-attr 'unsafe-inline'",
        'upgrade-insecure-requests',
    ] as $directive) {
        $this->assertStringContainsString($directive, $policy);
    }

    $this->assertStringNotContainsString("'unsafe-eval'", $policy);
    $this->assertStringNotContainsString(' *', $policy);
});
it('public policy allows only the verified third party integrations', function () {
    $policy = (string) $this->get('/')->headers->get('Content-Security-Policy');

    foreach ([
        'https://www.googletagmanager.com',
        'https://www.google-analytics.com',
        'https://cdn.leadinfo.net',
        'https://cdn.ldnfrpl.com',
        'https://api.leadinfo.com',
        'https://collector.leadinfo.net',
        'https://snap.licdn.com',
        'https://*.ads.linkedin.com',
        'https://challenges.cloudflare.com',
        'https://app.vragen.ai',
        'https://dlf.vragen.ai',
        'https://www.youtube.com',
        'https://i.ytimg.com',
        'https://player.vimeo.com',
        'https://open.spotify.com',
    ] as $origin) {
        $this->assertStringContainsString($origin, $policy);
    }
});
it('inline scripts and styles use the response nonce', function () {
    $response = $this->get('/');
    $policy = (string) $response->headers->get('Content-Security-Policy');

    expect($policy)->toMatch("/script-src[^;]*'nonce-([^']+)'/");
    preg_match("/script-src[^;]*'nonce-([^']+)'/", $policy, $matches);

    $nonce = $matches[1];
    $content = (string) $response->getContent();

    $this->assertStringContainsString("<style nonce=\"{$nonce}\">", $content);
    expect($content)->toMatch('/<script(?=[^>]*nonce="'.preg_quote($nonce, '/').'"|[^>]*\bsrc=)[^>]*>/');

    preg_match_all(
        '/<style\b[^>]*>|<script\b(?![^>]*\bsrc=)[^>]*>/',
        $content,
        $tags,
    );

    foreach ($tags[0] as $tag) {
        $this->assertStringContainsString("nonce=\"{$nonce}\"", $tag);
    }
});
it('hot reloaded fonts use the stylesheet pipeline and allowed origin', function () {
    $vite = $this->app->make(Vite::class);
    $originalHotFile = $vite->hotFile();
    $temporaryHotFile = tempnam(sys_get_temp_dir(), 'dlf-vite-hot-');

    $this->assertNotFalse($temporaryHotFile);
    file_put_contents($temporaryHotFile, 'https://vite.example.test:5174');
    $vite->useHotFile($temporaryHotFile);
    config()->set('csp.enabled_while_hot_reloading', true);

    try {
        $policy = (string) $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString(
            "font-src 'self' data: https://vite.example.test:5174",
            $policy,
        );
        $this->assertStringContainsString(
            '@import "./fonts.css";',
            (string) file_get_contents(resource_path('css/tailwind.css')),
        );
        $this->assertStringNotContainsString(
            'import "../css/fonts.css";',
            (string) file_get_contents(resource_path('js/site.js')),
        );
    } finally {
        $vite->useHotFile($originalHotFile);
        unlink($temporaryHotFile);
    }
});
it('control panel responses are not modified by the public policy', function () {
    $this->get('/cp')
        ->assertHeaderMissing('Content-Security-Policy')
        ->assertHeaderMissing('Content-Security-Policy-Report-Only');
});
