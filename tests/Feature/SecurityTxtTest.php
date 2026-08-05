<?php

declare(strict_types=1);
use Carbon\CarbonImmutable;

const SECURITY_TXT_CANONICAL = 'https://dutchlaravelfoundation.nl/.well-known/security.txt';

it('security txt contains the required published fields', function () {
    $response = $this->get('/.well-known/security.txt');

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertHeaderMissing('Content-Security-Policy');

    $content = (string) $response->getContent();

    $this->assertStringContainsString('Contact: mailto:info@dutchlaravelfoundation.nl', $content);
    $this->assertStringContainsString('Canonical: '.SECURITY_TXT_CANONICAL, $content);
    $this->assertStringContainsString('Preferred-Languages: nl, en', $content);
    expect($content)->toMatch('/^Expires: .+$/m');
});
it('security txt expiration always remains in the future', function () {
    CarbonImmutable::setTestNow('2026-07-20 12:00:00 Europe/Amsterdam');

    $content = (string) $this->get('/.well-known/security.txt')->getContent();
    expect($content)->toMatch('/^Expires: (.+)$/m');
    preg_match('/^Expires: (.+)$/m', $content, $matches);
    $expires = CarbonImmutable::parse($matches[1]);

    expect($expires->isAfter(CarbonImmutable::now()))->toBeTrue();
    expect($expires->lessThanOrEqualTo(CarbonImmutable::now()->addYear()))->toBeTrue();
});
