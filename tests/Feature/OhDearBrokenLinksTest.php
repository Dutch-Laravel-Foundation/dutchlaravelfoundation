<?php

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class OhDearBrokenLinksTest extends TestCase
{
    public function test_legacy_internal_urls_redirect_to_live_pages(): void
    {
        $this->get('/about-laravel')
            ->assertRedirect('/wat-is-laravel');

        $this->get('/calendar/laravel-directors-dinner')
            ->assertRedirect('/events/laravel-directors-dinner');

        $this->get('/calendar')
            ->assertRedirect('/agenda');

        $this->get('/calendar/cxo-diner-2026')
            ->assertRedirect('/events/cxo-diner-2026');

        $this->get('/cases/mobiele-app-api-en-adminpanel-als-mvp-voor-toetsing-onder-duizenden-reizigers')
            ->assertRedirect('/nieuws/showcase-ov-chipkaart-app');

        $this->get('/leden/avocado-media')
            ->assertRedirect('/leden');
    }

    public function test_source_pages_no_longer_render_retired_links(): void
    {
        $laracon = $this->inertia('/nieuws/bezoek-ons-op-laracon-amsterdam-2019');
        $laracon->assertOk();
        $laraconContent = $this->contentHtml($laracon);
        $this->assertStringContainsString(
            '/events/laravel-directors-dinner',
            $laraconContent,
        );
        $this->assertStringNotContainsString(
            '/calendar/laravel-directors-dinner',
            $laraconContent,
        );

        $ovChipkaart = $this->inertia('/nieuws/showcase-ov-chipkaart-app');
        $ovChipkaart->assertOk();
        $this->assertStringNotContainsString(
            '/cases/mobiele-app-api-en-adminpanel-als-mvp-voor-toetsing-onder-duizenden-reizigers',
            $this->contentHtml($ovChipkaart),
        );

        $hackathon = $this->inertia('/events/hackathon-dutch-laravel-foundation-x-mollie');
        $hackathon->assertOk();
        $this->assertStringNotContainsString(
            '/leden/avocado-media',
            $this->contentHtml($hackathon),
        );

        $meetup = $this->inertia('/nieuws/eerste-laravel-meetup-groot-succes');
        $meetup->assertOk();
        $meetupContent = $this->contentHtml($meetup);
        $this->assertStringNotContainsString(
            'dlf_arto_dennis_php.pdf',
            $meetupContent,
        );
        $this->assertStringNotContainsString(
            'dlf_ruud_vertalingen.pdf',
            $meetupContent,
        );
    }

    public function test_diabetes_case_uses_valid_webp_image_sources(): void
    {
        $response = $this->inertia('/cases/diabetes-nl-helpt-je-verder-weten-delen-doen');

        $response->assertOk()
            ->assertJsonPath('component', 'Community/CasesShow');

        $content = $response->json('props.community.content');

        $this->assertIsArray($content);

        $sources = array_values(array_filter(array_map(
            static fn (mixed $block): mixed => is_array($block) ? ($block['asset']['url'] ?? null) : null,
            $content,
        )));

        $this->assertSame([
            '/assets/uploads/assets/diabetes-wegwijzer_0.webp',
            '/assets/uploads/assets/diabetes.nl-architectuur-16-10.webp',
        ], $sources);

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

    public function test_member_form_renders_a_valid_privacy_statement_link(): void
    {
        $response = $this->inertia('/lid-worden');

        $response->assertOk();
        $response->assertJsonPath('component', 'Forms/BecomeMember');
        $response->assertJsonPath(
            'props.acquisition.form.fields.5.display',
            'Bij het gebruiken van dit formulier ga je akkoord met de bepalingen uit ons privacy statement.',
        );
    }

    private function inertia(string $uri): TestResponse
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ])->get($uri);
    }

    private function contentHtml(TestResponse $response): string
    {
        $blocks = $response->json('props.editorial.content');

        $this->assertIsArray($blocks);

        return implode('', array_map(
            static fn (array $block): string => (string) ($block['html'] ?? ''),
            $blocks,
        ));
    }
}
