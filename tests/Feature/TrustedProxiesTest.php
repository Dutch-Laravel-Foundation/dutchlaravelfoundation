<?php

declare(strict_types=1);

/*
 * Production: HAProxy terminates TLS in transparent mode, so REMOTE_ADDR is the visitor's IP,
 * and HAProxy replaces X-Forwarded-Proto. Client X-Forwarded-Port/Prefix pass through.
 */
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/_proxy-probe', fn (Request $request): array => [
        'secure' => $request->isSecure(),
        'ip' => $request->ip(),
        'url' => url('/x'),
    ]);
});
test('haproxy scheme is trusted for a visitor address', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
        ->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('http://localhost/_proxy-probe')
        ->assertExactJson([
            'secure' => true,
            'ip' => '203.0.113.7',
            'url' => 'https://localhost/x',
        ]);
});
test('client supplied port prefix host and ip are ignored', function () {
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
});
