<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class LlmsFullTxtTest extends TestCase
{
    public function testLlmsFullTxtReturnsMarkdown(): void
    {
        $response = $this->get('/llms-full.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    }

    public function testLlmsFullTxtIncludesInlinedEntries(): void
    {
        $body = $this->get('/llms-full.txt')->getContent();

        $this->assertStringContainsString('# Dutch Laravel Foundation', $body);
        // Inlined entries are separated by --- blocks
        $this->assertGreaterThanOrEqual(2, substr_count($body, "\n---\n"));
    }
}
