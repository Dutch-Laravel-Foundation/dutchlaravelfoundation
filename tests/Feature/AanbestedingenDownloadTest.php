<?php

declare(strict_types=1);

test('page offers the laravel tender package as a download', function () {
    $response = $this->withHeaders([
        'Accept' => 'application/json',
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ])->get('/aanbestedingen');

    $response->assertOk();
    $response->assertHeader('X-Inertia', 'true');
    $response->assertJsonPath('component', 'PublicPages/TenderLanding');
    $response->assertJsonPath(
        'props.page.callToAction.title',
        'Download het Laravel Aanbestedingspakket',
    );
    $response->assertJsonPath('props.page.callToAction.buttonText', 'Download PDF');
    $response->assertJsonPath(
        'props.page.callToAction.link.url',
        '/assets/uploads/assets/laravel-aanbestedingspakket.pdf',
    );
    expect(public_path('assets/uploads/assets/laravel-aanbestedingspakket.pdf'))->toBeFile();
});
