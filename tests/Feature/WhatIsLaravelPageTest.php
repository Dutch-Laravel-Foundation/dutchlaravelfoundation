<?php

declare(strict_types=1);

test('intro links to the laravel website in a new tab', function () {
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
});
