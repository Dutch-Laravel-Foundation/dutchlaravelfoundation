<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class WhatIsLaravelPageTest extends TestCase
{
    public function test_intro_links_to_the_laravel_website_in_a_new_tab(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ])->get('/wat-is-laravel');

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', 'PublicPages/WhatIsLaravel');
        $response->assertJsonPath('props.page.slug', 'wat-is-laravel');

        $component = file_get_contents(
            resource_path('js/pages/PublicPages/WhatIsLaravel.tsx'),
        );

        $this->assertNotFalse($component);
        $this->assertStringContainsString('<SmartLink', $component);
        $this->assertStringContainsString('href="https://laravel.com"', $component);
        $this->assertStringContainsString('target="_blank"', $component);
        $this->assertStringContainsString('rel="noopener noreferrer"', $component);
        $this->assertStringContainsString('open source PHP framework', $component);
        $this->assertStringContainsString('voor het bouwen van maatwerk webapplicaties.', $component);
        $this->assertStringContainsString('miljoenen gebruikers.', $component);
    }
}
