<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PublicPagesTest extends TestCase
{
    #[Test]
    public function every_public_page_family_is_served_by_its_inertia_component(): void
    {
        $routes = [
            '/co-organised-meet-ups' => 'PublicPages/Default',
            '/over-ons' => 'PublicPages/About',
            '/wat-is-laravel' => 'PublicPages/WhatIsLaravel',
            '/een-eigen-systeem-laten-bouwen-is-betaalbaarder-dan-je-denkt' => 'PublicPages/GeneralLanding',
            '/laravel-het-framework-dat-jouw-systeem-op-maat-tot-een-succes-maakt' => 'PublicPages/FrameworkLanding',
            '/aanbestedingen' => 'PublicPages/TenderLanding',
            '/privacy-statement' => 'PublicPages/PrivacyStatement',
            '/newsletter' => 'PublicPages/Newsletter',
        ];

        foreach ($routes as $uri => $component) {
            $response = $this->withHeaders($this->inertiaHeaders())->get($uri);

            $response->assertOk();
            $response->assertHeader('X-Inertia', 'true');
            $response->assertJsonPath('component', $component);
            $response->assertJsonStructure(['props' => ['page', 'site']]);
        }
    }

    /** @return array<string, string> */
    private function inertiaHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ];
    }
}
