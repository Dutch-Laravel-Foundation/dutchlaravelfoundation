<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class LinkHeadersTest extends TestCase
{
    public function testHomepageAdvertisesLlmsTxt(): void
    {
        $response = $this->get('/');
        $link = $response->headers->get('Link');

        $this->assertNotNull($link);
        $this->assertStringContainsString('rel="llms-txt"', $link);
        $this->assertStringContainsString('/llms.txt', $link);
    }

    public function testHomepageAdvertisesSitemap(): void
    {
        $link = $this->get('/')->headers->get('Link');

        $this->assertStringContainsString('rel="sitemap"', (string) $link);
        $this->assertStringContainsString('/sitemap.xml', (string) $link);
    }

    public function testResponsesDeclareContentSignals(): void
    {
        $this->get('/')->assertHeader(
            'Content-Signal',
            'search=yes, ai-train=no, ai-input=yes',
        );
    }

    public function testLinkHeadersOnlyAppearOnHtmlResponses(): void
    {
        $link = $this->get('/robots.txt')->headers->get('Link');

        $this->assertNull($link);
    }
}
