<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

it('www requests redirect permanently to the canonical host', function () {
    config(['app.url' => 'https://dutchlaravelfoundation.nl']);

    $this->get('https://www.dutchlaravelfoundation.nl/lid-worden?source=test')
        ->assertStatus(Response::HTTP_PERMANENTLY_REDIRECT)
        ->assertRedirect('https://dutchlaravelfoundation.nl/lid-worden?source=test');
});

it('trailing-dot requests redirect permanently to the canonical host', function () {
    config(['app.url' => 'https://dutchlaravelfoundation.nl']);

    $this->get('https://dutchlaravelfoundation.nl./aanvraag?source=test')
        ->assertStatus(Response::HTTP_PERMANENTLY_REDIRECT)
        ->assertRedirect('https://dutchlaravelfoundation.nl/aanvraag?source=test');
});

it('trailing slash requests redirect to the canonical path', function () {
    config(['app.url' => 'https://dutchlaravelfoundation.nl']);

    $this->get('https://dutchlaravelfoundation.nl/leden/?source=test')
        ->assertStatus(Response::HTTP_PERMANENTLY_REDIRECT)
        ->assertRedirect('https://dutchlaravelfoundation.nl/leden?source=test');

    $this->get('https://dutchlaravelfoundation.nl/leden')->assertOk();
});

it('canonicalizes the host and path in one redirect', function () {
    config(['app.url' => 'https://dutchlaravelfoundation.nl']);

    $this->get('https://www.dutchlaravelfoundation.nl/leden/?source=test')
        ->assertStatus(Response::HTTP_PERMANENTLY_REDIRECT)
        ->assertRedirect('https://dutchlaravelfoundation.nl/leden?source=test');
});

it('removes the first page query parameter while preserving other filters', function () {
    config(['app.url' => 'https://dutchlaravelfoundation.nl']);

    $this->get('https://dutchlaravelfoundation.nl/kennis?page=1')
        ->assertStatus(Response::HTTP_PERMANENTLY_REDIRECT)
        ->assertRedirect('https://dutchlaravelfoundation.nl/kennis');

    $this->get('https://dutchlaravelfoundation.nl/kennis?category=Tooling&page=1')
        ->assertStatus(Response::HTTP_PERMANENTLY_REDIRECT)
        ->assertRedirect('https://dutchlaravelfoundation.nl/kennis?category=Tooling');
});
