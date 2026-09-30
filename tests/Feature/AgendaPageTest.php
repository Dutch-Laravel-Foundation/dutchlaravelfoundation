<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;

final class AgendaPageTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_agenda_separates_upcoming_and_past_events_in_chronological_order(): void
    {
        Carbon::setTestNow('2026-07-20 12:00:00');

        $response = $this->withHeaders($this->inertiaHeaders())->get('/agenda');

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', 'Editorial/EventsIndex');

        $upcomingEventTitles = array_column($response->json('props.editorial.upcoming'), 'title');
        $pastEventTitles = array_column($response->json('props.editorial.past'), 'title');

        $this->assertSame(['Laravel Hackathon 2026', 'CxO diner 2026'], $upcomingEventTitles);
        $this->assertSame(
            ['LaraFest & LarAwards 2026', 'Dutch Laravel Foundation Meetup 2026 @ DIJ!', "CxO Diner '25"],
            array_slice($pastEventTitles, 0, 3),
        );
        $this->assertCount(10, $pastEventTitles);
        $response->assertJsonPath('props.editorial.pagination.currentPage', 1);
        $response->assertJsonPath('props.editorial.pagination.hasMorePages', true);
    }

    /** @return array<string, string> */
    private function inertiaHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ];
    }
}
