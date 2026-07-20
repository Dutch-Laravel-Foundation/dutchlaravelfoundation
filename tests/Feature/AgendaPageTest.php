<?php

declare(strict_types=1);

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class AgendaPageTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function testAgendaSeparatesUpcomingAndPastEventsInChronologicalOrder(): void
    {
        Carbon::setTestNow('2026-07-20 12:00:00');

        $response = $this->get('/agenda');

        $response->assertOk();

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);

        $this->assertSame(
            ['Laravel Hackathon 2026', 'CxO diner 2026'],
            $this->eventTitles($xpath, '//section[@aria-label="Aankomende evenementen"]'),
        );

        $pastEventTitles = $this->eventTitles($xpath, '//section[@aria-labelledby="past-events-heading"]');

        $this->assertSame(
            ['LaraFest & LarAwards 2026', 'Dutch Laravel Foundation Meetup 2026 @ DIJ!', "CxO Diner '25"],
            array_slice($pastEventTitles, 0, 3),
        );
        $this->assertSame('Laravel Hackathon', $pastEventTitles[array_key_last($pastEventTitles)]);
    }

    /** @return array<int, string> */
    private function eventTitles(DOMXPath $xpath, string $sectionQuery): array
    {
        $nodes = $xpath->query("{$sectionQuery}//h2[contains(concat(' ', normalize-space(@class), ' '), ' editorial-entry__title ')]/a");
        $titles = [];

        foreach ($nodes as $node) {
            $this->assertInstanceOf(DOMElement::class, $node);
            $titles[] = trim($node->textContent);
        }

        return $titles;
    }
}
