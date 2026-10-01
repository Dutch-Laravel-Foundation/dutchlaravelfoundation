<?php

declare(strict_types=1);
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();

});
test('agenda separates upcoming and past events in chronological order', function () {
    Carbon::setTestNow('2026-07-20 12:00:00');

    $response = $this->withHeaders(inertiaHeaders())->get('/agenda');

    $response->assertOk();
    $response->assertHeader('X-Inertia', 'true');
    $response->assertJsonPath('component', 'Editorial/EventsIndex');

    $upcomingEventTitles = array_column($response->json('props.editorial.upcoming'), 'title');
    $pastEventTitles = array_column($response->json('props.editorial.past'), 'title');

    expect($upcomingEventTitles)->toBe(['Laravel Hackathon 2026', 'CxO diner 2026']);
    expect(array_slice($pastEventTitles, 0, 3))->toBe(['LaraFest & LarAwards 2026', 'Dutch Laravel Foundation Meetup 2026 @ DIJ!', "CxO Diner '25"]);
    expect($pastEventTitles)->toHaveCount(10);
    $response->assertJsonPath('props.editorial.pagination.currentPage', 1);
    $response->assertJsonPath('props.editorial.pagination.hasMorePages', true);
});
/** @return array<string, string> */
