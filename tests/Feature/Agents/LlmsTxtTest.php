<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class LlmsTxtTest extends TestCase
{
    public function testLlmsTxtReturnsMarkdown(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    }

    public function testLlmsTxtContainsTitleAndPreamble(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringContainsString('# Dutch Laravel Foundation', $body);
        $this->assertStringContainsString(config('dlf.llms.preamble'), $body);
    }

    public function testLlmsTxtListsCoreSections(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringContainsString('## Knowledge Base', $body);
        $this->assertStringContainsString('## Insights', $body);
        $this->assertStringContainsString('## Events', $body);
        $this->assertStringContainsString('## Internships', $body);
        $this->assertStringContainsString('## Markdown access', $body);
    }

    public function testLlmsTxtAdvertisesMarkdownNegotiation(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringContainsString('Accept: text/markdown', $body);
    }

    public function testLlmsTxtLinksToExistingLaravelPageSlug(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringContainsString('/wat-is-laravel.md', $body);
        $this->assertStringNotContainsString('/what-is-laravel.md', $body);
    }
}
