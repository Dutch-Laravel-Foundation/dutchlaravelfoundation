<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Vite;
use Tests\TestCase;

final class ContentSecurityPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['csp.enabled_while_hot_reloading' => true]);
    }

    public function test_public_html_responses_use_an_enforced_content_security_policy(): void
    {
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
    }

    public function test_public_html_renders_the_site_background_before_javascript_runs(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee(
            '<body class="dlf-site font-sans leading-normal bg-white text-primary-text">',
            escape: false,
        );

        $this->assertMatchesRegularExpression(
            '/<link rel="stylesheet" href="[^"]*tailwind[^"]*\.css"/',
            (string) $response->getContent(),
        );
    }

    public function test_public_policy_allows_only_the_verified_third_party_integrations(): void
    {
        $policy = (string) $this->get('/')->headers->get('Content-Security-Policy');

        foreach ([
            'https://www.googletagmanager.com',
            'https://www.google-analytics.com',
            'https://cdn.leadinfo.net',
            'https://api.leadinfo.com',
            'https://collector.leadinfo.net',
            'https://snap.licdn.com',
            'https://px.ads.linkedin.com',
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
    }

    public function test_inline_scripts_and_styles_use_the_response_nonce(): void
    {
        $response = $this->get('/');
        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression("/script-src[^;]*'nonce-([^']+)'/", $policy);
        preg_match("/script-src[^;]*'nonce-([^']+)'/", $policy, $matches);

        $nonce = $matches[1];
        $content = (string) $response->getContent();

        $this->assertStringContainsString(
            "<script type=\"application/ld+json\" nonce=\"{$nonce}\"",
            $content,
        );
        $this->assertMatchesRegularExpression(
            '/<script(?=[^>]*nonce="'.preg_quote($nonce, '/').'"|[^>]*\bsrc=)[^>]*>/',
            $content,
        );

        preg_match_all(
            '/<style\b[^>]*>|<script\b(?![^>]*\bsrc=)[^>]*>/',
            $content,
            $tags,
        );

        foreach ($tags[0] as $tag) {
            $this->assertStringContainsString("nonce=\"{$nonce}\"", $tag);
        }
    }

    public function test_hot_reloaded_fonts_use_the_stylesheet_pipeline_and_allowed_origin(): void
    {
        $vite = $this->app->make(Vite::class);
        $originalHotFile = $vite->hotFile();
        $temporaryHotFile = tempnam(sys_get_temp_dir(), 'dlf-vite-hot-');

        $this->assertNotFalse($temporaryHotFile);
        file_put_contents($temporaryHotFile, 'https://vite.example.test:5174');
        $vite->useHotFile($temporaryHotFile);

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
    }

    public function test_control_panel_responses_are_not_modified_by_the_public_policy(): void
    {
        $this->get('/cp')
            ->assertHeaderMissing('Content-Security-Policy')
            ->assertHeaderMissing('Content-Security-Policy-Report-Only');
    }
}
