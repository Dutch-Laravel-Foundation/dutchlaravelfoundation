<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Statamic\Facades\Entry;
use Tests\TestCase;

class KnowledgeToolsTest extends TestCase
{
    public function testListKnowledgeReturnsJsonArray(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_knowledge',
                'arguments' => ['limit' => 5],
            ],
        ]);

        $response->assertOk();
        $items = json_decode($response->json('result.content.0.text'), true);

        $this->assertIsArray($items);
        if ($items !== []) {
            $this->assertSame('knowledge', $items[0]['collection']);
        }
    }

    public function testGetKnowledgeArticleReturnsMarkdown(): void
    {
        $entry = Entry::query()
            ->where('collection', 'knowledge')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No knowledge entries');
        }

        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_knowledge_article',
                'arguments' => ['slug' => $entry->slug()],
            ],
        ]);

        $response->assertOk();
        $text = $response->json('result.content.0.text');
        $this->assertStringContainsString('# ' . $entry->get('title'), $text);
    }
}
