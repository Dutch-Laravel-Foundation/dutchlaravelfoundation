<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Statamic\Facades\Entry;
use Tests\TestCase;

class MarkdownNegotiationTest extends TestCase
{
    private function firstInsightSlug(): ?string
    {
        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->first();

        return $entry?->slug();
    }

    public function testMdSuffixReturnsMarkdown(): void
    {
        $slug = $this->firstInsightSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published insights');
        }

        $response = $this->get('/nieuws/' . $slug . '.md');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $response->assertHeader('Vary', 'Accept');
        $this->assertStringStartsWith('# ', $response->getContent());
    }

    public function testAcceptHeaderReturnsMarkdown(): void
    {
        $slug = $this->firstInsightSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published insights');
        }

        $response = $this->get('/nieuws/' . $slug, ['Accept' => 'text/markdown']);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $response->assertHeader('Vary', 'Accept');
    }

    public function testHtmlStillServedWhenAcceptIsDefault(): void
    {
        $slug = $this->firstInsightSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published insights');
        }

        $response = $this->get('/nieuws/' . $slug);

        // The middleware must NOT return text/markdown — it should pass through to Statamic.
        // In the test environment Statamic may throw a 500 (missing Vite build), so we only
        // assert the Content-Type is not text/markdown, not that the status is 200.
        $this->assertStringNotContainsString(
            'text/markdown',
            (string) $response->headers->get('Content-Type', '')
        );
    }

    public function testNonWhitelistedPathIgnoresMarkdownNegotiation(): void
    {
        // Homepage is not in the whitelist — Accept header should be ignored.
        $response = $this->get('/', ['Accept' => 'text/markdown']);

        $this->assertStringContainsString('text/html', (string) $response->headers->get('Content-Type'));
    }
}
