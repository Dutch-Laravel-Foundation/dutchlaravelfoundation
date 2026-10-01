<?php

declare(strict_types=1);
test('every public page family is served by its inertia component', function () {
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
        $response = $this->withHeaders(inertiaHeaders())->get($uri);

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', $component);
        $response->assertJsonStructure(['props' => ['page', 'site']]);
    }
});
/** @return array<string, string> */
