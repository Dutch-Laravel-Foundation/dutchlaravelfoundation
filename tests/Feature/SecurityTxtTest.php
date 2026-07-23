<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Tests\TestCase;

final class SecurityTxtTest extends TestCase
{
    private const CANONICAL = 'https://dutchlaravelfoundation.nl/.well-known/security.txt';

    public function testSecurityTxtContainsTheRequiredPublishedFields(): void
    {
        $response = $this->get('/.well-known/security.txt');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertHeaderMissing('Content-Security-Policy');

        $content = (string) $response->getContent();

        $this->assertStringContainsString('Contact: mailto:info@dutchlaravelfoundation.nl', $content);
        $this->assertStringContainsString('Canonical: '.self::CANONICAL, $content);
        $this->assertStringContainsString('Preferred-Languages: nl, en', $content);
        $this->assertMatchesRegularExpression('/^Expires: .+$/m', $content);
    }

    public function testSecurityTxtExpirationAlwaysRemainsInTheFuture(): void
    {
        CarbonImmutable::setTestNow('2026-07-20 12:00:00 Europe/Amsterdam');

        $content = (string) $this->get('/.well-known/security.txt')->getContent();
        $this->assertMatchesRegularExpression('/^Expires: (.+)$/m', $content);
        preg_match('/^Expires: (.+)$/m', $content, $matches);
        $expires = CarbonImmutable::parse($matches[1]);

        $this->assertTrue($expires->isAfter(CarbonImmutable::now()));
        $this->assertTrue($expires->lessThanOrEqualTo(CarbonImmutable::now()->addYear()));
    }
}
