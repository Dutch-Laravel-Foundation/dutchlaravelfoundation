<?php

it('legacy internal urls redirect to live pages', function () {
    $this->get('/calendar/laravel-directors-dinner')
        ->assertRedirect('/events/laravel-directors-dinner');

    $this->get('/cases/mobiele-app-api-en-adminpanel-als-mvp-voor-toetsing-onder-duizenden-reizigers')
        ->assertRedirect('/nieuws/showcase-ov-chipkaart-app');

    $this->get('/leden/avocado-media')
        ->assertRedirect('/leden');
});

it('source pages no longer render retired links', function () {
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
});

it('diabetes case uses valid webp image sources', function () {
    $response = $this->get('/cases/diabetes-nl-helpt-je-verder-weten-delen-doen');

    $response->assertOk()
        ->assertSee('diabetes-wegwijzer_0.webp', false)
        ->assertSee('diabetes.nl-architectuur-16-10.webp', false)
        ->assertDontSee('diabetes-wegwijzer_0.png', false)
        ->assertDontSee('diabetes.nl-architectuur-16-10.png', false);

    preg_match_all('/src="([^"]+(?:diabetes-wegwijzer_0|diabetes\.nl-architectuur-16-10)\.webp[^"]*)"/', $response->getContent(), $matches);

    expect($matches[1])->toHaveCount(2);

    foreach ([
        'diabetes-wegwijzer_0.webp',
        'diabetes.nl-architectuur-16-10.webp',
    ] as $filename) {
        $image = getimagesize(public_path("assets/uploads/assets/{$filename}"));

        expect($image)->toBeArray();
        expect($image['mime'])->toBe('image/webp');
    }
});

it('member without website does not render an empty https link', function () {
    $this->get('/leden/van-der-arend-automatisering')
        ->assertOk()
        ->assertDontSee('href="https://"', false);
});

it('member form renders a valid privacy statement link', function () {
    $this->get('/lid-worden')
        ->assertOk()
        ->assertSee("href='/privacy-statement'", false)
        ->assertDontSee('href=.</span', false);
});
