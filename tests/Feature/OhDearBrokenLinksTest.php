<?php

use Illuminate\Testing\TestResponse;

test('legacy internal urls redirect to live pages', function () {
    $this->get('/about-laravel')
        ->assertStatus(301)
        ->assertRedirect('/wat-is-laravel');

    $this->get('/what-is-laravel')
        ->assertStatus(301)
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
});

test('source pages no longer render retired links', function () {
    $laracon = inertiaVisit('/nieuws/bezoek-ons-op-laracon-amsterdam-2019');
    $laracon->assertOk();
    $laraconContent = contentHtml($laracon);
    $this->assertStringContainsString(
        '/events/laravel-directors-dinner',
        $laraconContent,
    );
    $this->assertStringNotContainsString(
        '/calendar/laravel-directors-dinner',
        $laraconContent,
    );

    $ovChipkaart = inertiaVisit('/nieuws/showcase-ov-chipkaart-app');
    $ovChipkaart->assertOk();
    $this->assertStringNotContainsString(
        '/cases/mobiele-app-api-en-adminpanel-als-mvp-voor-toetsing-onder-duizenden-reizigers',
        contentHtml($ovChipkaart),
    );

    $hackathon = inertiaVisit('/events/hackathon-dutch-laravel-foundation-x-mollie');
    $hackathon->assertOk();
    $this->assertStringNotContainsString(
        '/leden/avocado-media',
        contentHtml($hackathon),
    );

    $laraward = inertiaVisit('/nieuws/september-wint-laraward-2023');
    $laraward->assertOk();
    $larawardContent = json_encode($laraward->json('props'), JSON_UNESCAPED_SLASHES);
    $this->assertStringContainsString(
        '/nieuws/larafest-2023-een-groot-feest',
        $larawardContent,
    );
    $this->assertStringNotContainsString(
        '/insights/larafest-2023-een-groot-feest',
        $larawardContent,
    );

    $framework = inertiaVisit('/kennis/laravel-meer-dan-een-framework');
    $framework->assertOk();
    $frameworkContent = json_encode($framework->json('props'), JSON_UNESCAPED_SLASHES);
    $this->assertStringContainsString('/nieuws/larafest-2024-beach', $frameworkContent);
    $this->assertStringContainsString(
        '/nieuws/web-whales-met-trippz-winnaar-laraward-2024',
        $frameworkContent,
    );
    $this->assertStringNotContainsString('/insights/larafest-2024-beach', $frameworkContent);
    $this->assertStringNotContainsString(
        '/insights/web-whales-met-trippz-winnaar-laraward-2024',
        $frameworkContent,
    );

    $meetup = inertiaVisit('/nieuws/eerste-laravel-meetup-groot-succes');
    $meetup->assertOk();
    $meetupContent = contentHtml($meetup);
    $this->assertStringNotContainsString(
        'dlf_arto_dennis_php.pdf',
        $meetupContent,
    );
    $this->assertStringNotContainsString(
        'dlf_ruud_vertalingen.pdf',
        $meetupContent,
    );
});

test('diabetes case uses valid webp image sources', function () {
    $response = inertiaVisit('/cases/diabetes-nl-helpt-je-verder-weten-delen-doen');

    $response->assertOk()
        ->assertJsonPath('component', 'Community/CasesShow');

    $content = $response->json('props.community.content');

    expect($content)->toBeArray();

    $sources = array_values(array_filter(array_map(
        static fn (mixed $block): mixed => is_array($block) ? ($block['asset']['url'] ?? null) : null,
        $content,
    )));

    expect($sources)->toBe([
        '/assets/uploads/assets/diabetes-wegwijzer_0.webp',
        '/assets/uploads/assets/diabetes.nl-architectuur-16-10.webp',
    ]);

    foreach ([
        'diabetes-wegwijzer_0.webp',
        'diabetes.nl-architectuur-16-10.webp',
    ] as $filename) {
        $image = getimagesize(public_path("assets/uploads/assets/{$filename}"));

        expect($image)->toBeArray();
        expect($image['mime'])->toBe('image/webp');
    }
});

test('member without website does not render an empty https link', function () {
    $this->get('/leden/van-der-arend-automatisering')
        ->assertOk()
        ->assertDontSee('href="https://"', false);
});

test('member form renders a valid privacy statement link', function () {
    $response = inertiaVisit('/lid-worden');

    $response->assertOk();
    $response->assertJsonPath('component', 'Forms/BecomeMember');
    $response->assertJsonPath(
        'props.acquisition.form.fields.5.display',
        'Bij het gebruiken van dit formulier ga je akkoord met de bepalingen uit ons privacy statement.',
    );
});

function contentHtml(TestResponse $response): string
{
    $blocks = $response->json('props.editorial.content');

    expect($blocks)->toBeArray();

    return implode('', array_map(
        static fn (array $block): string => (string) ($block['html'] ?? ''),
        $blocks,
    ));
}
