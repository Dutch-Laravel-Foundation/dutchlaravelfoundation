<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    public function testRobotsTxtIsServed(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function testRobotsTxtAllowsGeneralCrawlers(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Allow: /', $body);
    }

    public function testRobotsTxtDisallowsStatamicAdmin(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertStringContainsString('Disallow: /cp/', $body);
        $this->assertStringContainsString('Disallow: /statamic/', $body);
    }

    public function testRobotsTxtExplicitlyAllowsAiBots(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        foreach ([
            'GPTBot', 'OAI-SearchBot', 'ChatGPT-User',
            'ClaudeBot', 'Claude-User', 'Claude-SearchBot',
            'PerplexityBot', 'Perplexity-User',
            'Google-Extended', 'Applebot-Extended',
            'CCBot', 'Bytespider',
        ] as $bot) {
            $this->assertStringContainsString('User-agent: ' . $bot, $body);
        }
    }

    public function testRobotsTxtDeclaresContentSignals(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertStringContainsString('Content-Signal: search=yes, ai-train=no, ai-input=yes', $body);
    }

    public function testRobotsTxtIncludesSitemap(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertMatchesRegularExpression('#^Sitemap: https?://.+/sitemap\.xml$#m', $body);
    }
}
