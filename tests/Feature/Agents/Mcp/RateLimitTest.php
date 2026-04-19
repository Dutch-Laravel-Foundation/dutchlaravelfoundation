<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear any cached rate limiter state from previous test runs
        RateLimiter::clear('throttle:' . request()->ip() . '|60|1');
    }

    public function testMcpEndpointEnforcesRateLimit(): void
    {
        // 60 allowed, 61st should 429
        for ($i = 0; $i < 60; $i++) {
            $r = $this->postJson('/mcp', [
                'jsonrpc' => '2.0', 'id' => $i, 'method' => 'tools/list', 'params' => [],
            ]);
            $this->assertSame(200, $r->status(), "request {$i} expected 200");
        }

        $over = $this->postJson('/mcp', [
            'jsonrpc' => '2.0', 'id' => 61, 'method' => 'tools/list', 'params' => [],
        ]);

        $this->assertSame(429, $over->status());
    }
}
