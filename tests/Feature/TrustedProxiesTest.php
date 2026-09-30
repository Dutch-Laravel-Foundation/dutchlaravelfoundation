<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

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

    public function test_haproxy_on_the_same_host_provides_scheme_and_client_ip(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-For' => '203.0.113.7',
            ])
            ->get('http://localhost/_proxy-probe')
            ->assertExactJson([
                'secure' => true,
                'ip' => '203.0.113.7',
                'url' => 'https://localhost/x',
            ]);
    }

    public function test_client_supplied_port_and_prefix_are_ignored(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Port' => '8081',
                'X-Forwarded-Prefix' => '/evil',
            ])
            ->get('http://localhost/_proxy-probe')
            ->assertJsonPath('url', 'https://localhost/x');
    }

    public function test_forwarded_headers_from_other_addresses_are_ignored(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.9'])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-For' => '203.0.113.7',
            ])
            ->get('http://localhost/_proxy-probe')
            ->assertExactJson([
                'secure' => false,
                'ip' => '198.51.100.9',
                'url' => 'http://localhost/x',
            ]);
    }
}
