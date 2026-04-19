<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class ListInsightsTest extends TestCase
{
    public function testListsInsightsAsJsonArray(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_insights',
                'arguments' => ['limit' => 5],
            ],
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertArrayHasKey('content', $data['result']);
        $text = $data['result']['content'][0]['text'];
        $decoded = json_decode($text, true);
        $this->assertIsArray($decoded);

        if ($decoded !== []) {
            $this->assertArrayHasKey('title', $decoded[0]);
            $this->assertArrayHasKey('url', $decoded[0]);
            $this->assertArrayHasKey('collection', $decoded[0]);
            $this->assertSame('insights', $decoded[0]['collection']);
        }

        $this->assertLessThanOrEqual(5, count($decoded));
    }
}
