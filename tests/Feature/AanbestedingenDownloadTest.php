<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class AanbestedingenDownloadTest extends TestCase
{
    public function test_page_offers_the_laravel_tender_package_as_a_download(): void
    {
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
        $this->assertFileExists(public_path('assets/uploads/assets/laravel-aanbestedingspakket.pdf'));
    }
}
