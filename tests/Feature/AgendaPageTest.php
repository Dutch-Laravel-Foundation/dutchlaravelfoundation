<?php

declare(strict_types=1);
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();

});
it('agenda separates upcoming and past events in chronological order', function () {
    Carbon::setTestNow('2026-07-20 12:00:00');

    $response = $this->get('/agenda');

    $response->assertOk();

    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($document);

    expect(eventTitles($xpath, '//section[@aria-label="Aankomende evenementen"]'))->toBe(['Laravel Hackathon 2026', 'CxO diner 2026']);

    $pastEventTitles = eventTitles($xpath, '//section[@aria-labelledby="past-events-heading"]');

    expect(array_slice($pastEventTitles, 0, 3))->toBe(['LaraFest & LarAwards 2026', 'Dutch Laravel Foundation Meetup 2026 @ DIJ!', "CxO Diner '25"]);
    expect($pastEventTitles[array_key_last($pastEventTitles)])->toBe('Laravel Hackathon');
});
/** @return array<int, string> */
function eventTitles(DOMXPath $xpath, string $sectionQuery): array
{
    $nodes = $xpath->query("{$sectionQuery}//h2[contains(concat(' ', normalize-space(@class), ' '), ' editorial-entry__title ')]/a");
    $titles = [];

    foreach ($nodes as $node) {
        expect($node)->toBeInstanceOf(DOMElement::class);
        $titles[] = trim($node->textContent);
    }

    return $titles;
}
