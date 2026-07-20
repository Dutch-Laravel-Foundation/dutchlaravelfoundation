<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class AanbestedingenDownloadTest extends TestCase
{
    public function testPageOffersTheLaravelTenderPackageAsADownload(): void
    {
        $response = $this->get('/aanbestedingen');

        $response->assertOk();
        $response->assertSee('Download het Laravel Aanbestedingspakket');
        $response->assertSee('Download PDF');
        $response->assertSee(
            'href="/assets/uploads/assets/laravel-aanbestedingspakket.pdf" download',
            false,
        );
        $this->assertFileExists(public_path('assets/uploads/assets/laravel-aanbestedingspakket.pdf'));
    }
}
