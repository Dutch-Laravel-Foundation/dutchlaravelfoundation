<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Statamic\Facades\Entry;
use Tests\TestCase;

class GetInsightTest extends TestCase
{
    public function testReturnsFullMarkdownForKnownSlug(): void
    {
        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No insights');
        }

        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_insight',
                'arguments' => ['slug' => $entry->slug()],
            ],
        ]);

        $response->assertOk();
        $text = $response->json('result.content.0.text');

        $this->assertStringContainsString('# ' . $entry->get('title'), $text);
        $this->assertStringContainsString('**URL:**', $text);
    }

    public function testUnknownSlugReturnsError(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_insight',
                'arguments' => ['slug' => 'definitely-does-not-exist-12345'],
            ],
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('result.isError') ?? false);
    }
}
