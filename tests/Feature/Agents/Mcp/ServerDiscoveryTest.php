<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class ServerDiscoveryTest extends TestCase
{
    public function testMcpEndpointReturnsToolsList(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
            'params' => [],
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('2.0', $data['jsonrpc']);
        $this->assertArrayHasKey('tools', $data['result']);
        $this->assertIsArray($data['result']['tools']);
    }

    public function testMcpEndpointListsAllExpectedTools(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list', 'params' => [],
        ]);

        $tools = collect($response->json('result.tools'))->pluck('name')->all();

        foreach ([
            'search_content',
            'list_insights', 'get_insight',
            'list_knowledge', 'get_knowledge_article',
            'list_events', 'list_internships', 'list_cases',
            'list_members', 'list_board', 'list_partners',
        ] as $expected) {
            $this->assertContains($expected, $tools, "missing tool: {$expected}");
        }
    }
}
