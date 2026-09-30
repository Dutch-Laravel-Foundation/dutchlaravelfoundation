<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Production: HAProxy terminates TLS in transparent mode, so REMOTE_ADDR is the visitor's IP,
 * and HAProxy replaces X-Forwarded-Proto. Client X-Forwarded-Port/Prefix pass through.
 */
final class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_proxy-probe', fn (Request $request): array => [
            'secure' => $request->isSecure(),
            'ip' => $request->ip(),
            'url' => url('/x'),
        ]);
    }

    public function test_haproxy_scheme_is_trusted_for_a_visitor_address(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('http://localhost/_proxy-probe')
            ->assertExactJson([
                'secure' => true,
                'ip' => '203.0.113.7',
                'url' => 'https://localhost/x',
            ]);
    }

    public function test_client_supplied_port_prefix_host_and_ip_are_ignored(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Port' => '8081',
                'X-Forwarded-Prefix' => '/evil',
                'X-Forwarded-Host' => 'evil.example',
                'X-Forwarded-For' => '198.51.100.9',
            ])
            ->get('http://localhost/_proxy-probe')
            ->assertExactJson([
                'secure' => true,
                'ip' => '203.0.113.7',
                'url' => 'https://localhost/x',
            ]);
    }
}
