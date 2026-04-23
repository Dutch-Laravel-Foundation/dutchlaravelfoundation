<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class SearchContentTest extends TestCase
{
    public function testSearchReturnsJsonArray(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'search_content',
                'arguments' => ['query' => 'laravel', 'limit' => 5],
            ],
        ]);

        $response->assertOk();
        $items = json_decode((string) $response->json('result.content.0.text'), true);
        $this->assertIsArray($items);

        if ($items !== []) {
            $this->assertArrayHasKey('title', $items[0]);
            $this->assertArrayHasKey('url', $items[0]);
            $this->assertArrayHasKey('collection', $items[0]);
        }
    }

    public function testSearchRequiresQuery(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'search_content',
                'arguments' => [],
            ],
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('result.isError') ?? false);
    }
}
