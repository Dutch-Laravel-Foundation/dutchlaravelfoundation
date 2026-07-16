<?php

namespace Tests\Feature;

use Tests\TestCase;

class OhDearBrokenLinksTest extends TestCase
{
    public function test_legacy_internal_urls_redirect_to_live_pages(): void
    {
        $this->get('/calendar/laravel-directors-dinner')
            ->assertRedirect('/events/laravel-directors-dinner');

        $this->get('/cases/mobiele-app-api-en-adminpanel-als-mvp-voor-toetsing-onder-duizenden-reizigers')
            ->assertRedirect('/nieuws/showcase-ov-chipkaart-app');

        $this->get('/leden/avocado-media')
            ->assertRedirect('/leden');
    }

    public function test_source_pages_no_longer_render_retired_links(): void
    {
        $this->get('/nieuws/bezoek-ons-op-laracon-amsterdam-2019')
            ->assertOk()
            ->assertSee('/events/laravel-directors-dinner', false)
            ->assertDontSee('/calendar/laravel-directors-dinner', false);

        $this->get('/nieuws/showcase-ov-chipkaart-app')
            ->assertOk()
            ->assertDontSee('/cases/mobiele-app-api-en-adminpanel-als-mvp-voor-toetsing-onder-duizenden-reizigers', false);

        $this->get('/events/hackathon-dutch-laravel-foundation-x-mollie')
            ->assertOk()
            ->assertDontSee('/leden/avocado-media', false);

        $this->get('/nieuws/eerste-laravel-meetup-groot-succes')
            ->assertOk()
            ->assertDontSee('dlf_arto_dennis_php.pdf', false)
            ->assertDontSee('dlf_ruud_vertalingen.pdf', false);
    }

    public function test_diabetes_case_uses_valid_webp_image_sources(): void
    {
        $response = $this->get('/cases/diabetes-nl-helpt-je-verder-weten-delen-doen');

        $response->assertOk()
            ->assertSee('diabetes-wegwijzer_0.webp', false)
            ->assertSee('diabetes.nl-architectuur-16-10.webp', false)
            ->assertDontSee('diabetes-wegwijzer_0.png', false)
            ->assertDontSee('diabetes.nl-architectuur-16-10.png', false);

        preg_match_all('/src="([^"]+(?:diabetes-wegwijzer_0|diabetes\.nl-architectuur-16-10)\.webp[^"]*)"/', $response->getContent(), $matches);

        $this->assertCount(2, $matches[1]);

        foreach ([
            'diabetes-wegwijzer_0.webp',
            'diabetes.nl-architectuur-16-10.webp',
        ] as $filename) {
            $image = getimagesize(public_path("assets/uploads/assets/{$filename}"));

            $this->assertIsArray($image);
            $this->assertSame('image/webp', $image['mime']);
        }
    }

    public function test_member_without_website_does_not_render_an_empty_https_link(): void
    {
        $this->get('/leden/van-der-arend-automatisering')
            ->assertOk()
            ->assertDontSee('href="https://"', false);
    }
}
