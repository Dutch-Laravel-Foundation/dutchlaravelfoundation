<?php

declare(strict_types=1);

it('news pagination uses compact three column navigation', function () {
    $response = $this->get('/nieuws?page=2');

    $response->assertOk();

    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($document);
    $navigation = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination ")]');
    $newer = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination ")]/a[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination__link--newer ")]');
    $status = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination ")]/span[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination__status ")]');
    $older = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination ")]/a[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination__link--older ")]');

    expect($navigation)->toBeInstanceOf(DOMNodeList::class);
    expect($newer)->toBeInstanceOf(DOMNodeList::class);
    expect($status)->toBeInstanceOf(DOMNodeList::class);
    expect($older)->toBeInstanceOf(DOMNodeList::class);
    expect($navigation)->toHaveCount(1);
    expect($newer)->toHaveCount(1);
    expect($status)->toHaveCount(1);
    expect($older)->toHaveCount(1);

    $newerLink = $newer->item(0);
    $pageStatus = $status->item(0);
    $olderLink = $older->item(0);

    expect($newerLink)->toBeInstanceOf(DOMElement::class);
    expect($pageStatus)->toBeInstanceOf(DOMElement::class);
    expect($olderLink)->toBeInstanceOf(DOMElement::class);
    expect(trim($newerLink->textContent))->toBe('← Nieuwer');
    expect(trim($pageStatus->textContent))->toBe('2 / 8');
    expect(trim($olderLink->textContent))->toBe('Ouder →');
    expect($newerLink->getAttribute('href'))->toEndWith('/nieuws?page=1');
    expect($olderLink->getAttribute('href'))->toEndWith('/nieuws?page=3');
});
