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
        // No tools registered yet — tighter assertion added in Task 3.9.
    }
}
