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
        $this->assertStringContainsString('## Programmatic access', $body);
    }

    public function testLlmsTxtAdvertisesMcpAndMarkdownNegotiation(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringContainsString('/mcp', $body);
        $this->assertStringContainsString('/.well-known/mcp.json', $body);
        $this->assertStringContainsString('Accept: text/markdown', $body);
    }
}
