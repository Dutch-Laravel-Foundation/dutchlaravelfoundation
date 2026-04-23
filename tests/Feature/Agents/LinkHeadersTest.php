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

    public function testHomepageAdvertisesMcpDiscovery(): void
    {
        $link = $this->get('/')->headers->get('Link');

        $this->assertStringContainsString('rel="mcp"', (string) $link);
        $this->assertStringContainsString('/.well-known/mcp.json', (string) $link);
    }

    public function testLinkHeadersOnlyAppearOnHtmlResponses(): void
    {
        $link = $this->get('/robots.txt')->headers->get('Link');

        $this->assertNull($link);
    }
}
