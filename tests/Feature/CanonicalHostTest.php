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
