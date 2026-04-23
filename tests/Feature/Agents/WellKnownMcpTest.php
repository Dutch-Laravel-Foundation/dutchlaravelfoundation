<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class WellKnownMcpTest extends TestCase
{
    public function testWellKnownMcpReturnsValidCard(): void
    {
        $response = $this->get('/.well-known/mcp.json');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');

        $data = $response->json();
        $this->assertSame('Dutch Laravel Foundation', $data['name']);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('1.0.0', $data['version']);
        $this->assertSame('http', $data['transport']['type']);
        $this->assertStringEndsWith('/mcp', $data['transport']['url']);
        $this->assertSame('none', $data['authentication']['type']);
        $this->assertArrayHasKey('contact', $data);
    }
}
