<?php

declare(strict_types=1);
it('member with internships renders its detail page', function () {
    $this->get('/leden/besite')
        ->assertOk()
        ->assertSee('Beschikbare stages bij Besite', false)
        ->assertSee('logo-besite.svg', false);
});
it('member detail marks member navigation item active', function () {
    $response = $this->get('/leden/pionect');

    $response->assertOk();

    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($document);
    $desktopLink = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " dlf-desktop-navigation ")]//a[@href="/leden" and contains(concat(" ", normalize-space(@class), " "), " dlf-nav-link--active ")]');
    $mobileLink = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " dlf-mobile-navigation ")]//a[@href="/leden" and contains(concat(" ", normalize-space(@class), " "), " dlf-mobile-nav-link--active ")]');

    expect($desktopLink)->toBeInstanceOf(DOMNodeList::class);
    expect($mobileLink)->toBeInstanceOf(DOMNodeList::class);
    expect($desktopLink)->toHaveCount(1);
    expect($mobileLink)->toHaveCount(1);
});
