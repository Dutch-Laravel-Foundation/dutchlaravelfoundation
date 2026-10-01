<?php

declare(strict_types=1);
use App\Content\Editorial\StatamicEditorialRepository;
use App\Content\Graphql\GraphqlClient;

it('fetches a filtered page of insights', function () {
    $response = ['entries' => ['data' => [], 'total' => 0]];
    $client = expectQuery(['site' => 'default', 'page' => 2, 'filter' => ['category' => ['is' => 'Leden']]], static fn (string $document): bool => str_contains($document, 'collection: ["insights"]')
        && str_contains($document, 'sort: ["date desc"]')
        && str_contains($document, 'fragment ArticleCardFields'), $response);

    expect((new StatamicEditorialRepository($client))->paginateInsights(2, 'Leden'))->toBe($response);
});
it('fetches a page of knowledge without a category filter', function () {
    $response = ['entries' => ['data' => [], 'total' => 0]];
    $client = expectQuery(['site' => 'default', 'page' => 1, 'filter' => []], static fn (string $document): bool => str_contains($document, 'collection: ["knowledge"]')
        && str_contains($document, '... on Entry_Knowledge_Knowledge'), $response);

    expect((new StatamicEditorialRepository($client))->paginateKnowledge(1))->toBe($response);
});
it('fetches podcasts by publication time', function () {
    $response = ['entries' => ['data' => [], 'total' => 0]];
    $client = expectQuery(['site' => 'default', 'page' => 3], static fn (string $document): bool => str_contains($document, 'collection: ["podcasts"]')
        && str_contains($document, 'sort: ["published_at desc"]')
        && str_contains($document, 'thumbnail_url'), $response);

    expect((new StatamicEditorialRepository($client))->paginatePodcasts(3))->toBe($response);
});
it('fetches all upcoming events and a page of past events in their display order', function () {
    $response = [
        'upcoming' => ['data' => []],
        'past' => ['data' => [], 'current_page' => 2],
    ];
    $client = expectQuery([
        'site' => 'default',
        'page' => 2,
        'upcomingFilter' => ['date_start' => ['is_after' => 'today']],
        'pastFilter' => ['date_start' => ['is_before' => 'today']],
    ], static fn (string $document): bool => str_contains($document, 'upcoming: entries')
        && str_contains($document, 'limit: 500')
        && str_contains($document, 'sort: ["date_start asc"]')
        && str_contains($document, 'past: entries')
        && str_contains($document, 'page: $page, limit: 10')
        && str_contains($document, 'sort: ["date_start desc"]'), $response);

    expect((new StatamicEditorialRepository($client))->paginateEvents(2))->toBe($response);
});
it('fetches each detail family by uri with complete fragments', function () {
    $families = [
        ['findInsightByUri', '/nieuws/example', 'Entry_Insights_Insights', 'author_name', true],
        ['findKnowledgeByUri', '/kennis/example', 'Entry_Knowledge_Knowledge', 'authors', true],
        ['findPodcastByUri', '/podcast/example', 'Entry_Podcasts_Podcasts', 'transcript', true],
        ['findEventByUri', '/events/example', 'Entry_Events_Events', 'signup_link', false],
    ];

    foreach ($families as [$method, $uri, $type, $field, $hasCallToAction]) {
        $entry = ['id' => 'entry-id', 'uri' => $uri];
        $client = expectQuery(['site' => 'default', 'uri' => $uri], static fn (string $document): bool => str_contains($document, "... on {$type}")
            && str_contains($document, $field)
            && str_contains($document, 'fragment SeoFields')
            && (str_contains($document, 'fragment CallToActionFields') === $hasCallToAction), ['entry' => $entry]);

        expect((new StatamicEditorialRepository($client))->{$method}($uri))->toBe($entry);
    }
});
/**
 * @param  array<string, mixed>  $variables
 * @param  callable(string): bool  $documentMatches
 * @param  array<string, mixed>  $response
 */
function expectQuery(array $variables, callable $documentMatches, array $response): GraphqlClient
{
    $client = test()->createMock(GraphqlClient::class);
    $client->expects(test()->once())
        ->method('query')
        ->with(test()->callback($documentMatches), $variables)
        ->willReturn($response);

    return $client;
}
