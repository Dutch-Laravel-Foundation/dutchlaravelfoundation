<?php

declare(strict_types=1);

namespace Tests\Unit\Content\Editorial;

use App\Content\Editorial\StatamicEditorialRepository;
use App\Content\Graphql\GraphqlClient;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatamicEditorialRepositoryTest extends TestCase
{
    #[Test]
    public function it_fetches_a_filtered_page_of_insights(): void
    {
        $response = ['entries' => ['data' => [], 'total' => 0]];
        $client = $this->expectQuery(
            ['site' => 'default', 'page' => 2, 'filter' => ['category' => ['is' => 'Leden']]],
            static fn (string $document): bool => str_contains($document, 'collection: ["insights"]')
                && str_contains($document, 'sort: ["date desc"]')
                && str_contains($document, 'fragment ArticleCardFields'),
            $response,
        );

        $this->assertSame($response, (new StatamicEditorialRepository($client))->paginateInsights(2, 'Leden'));
    }

    #[Test]
    public function it_fetches_a_page_of_knowledge_without_a_category_filter(): void
    {
        $response = ['entries' => ['data' => [], 'total' => 0]];
        $client = $this->expectQuery(
            ['site' => 'default', 'page' => 1, 'filter' => []],
            static fn (string $document): bool => str_contains($document, 'collection: ["knowledge"]')
                && str_contains($document, '... on Entry_Knowledge_Knowledge'),
            $response,
        );

        $this->assertSame($response, (new StatamicEditorialRepository($client))->paginateKnowledge(1));
    }

    #[Test]
    public function it_fetches_podcasts_by_publication_time(): void
    {
        $response = ['entries' => ['data' => [], 'total' => 0]];
        $client = $this->expectQuery(
            ['site' => 'default', 'page' => 3],
            static fn (string $document): bool => str_contains($document, 'collection: ["podcasts"]')
                && str_contains($document, 'sort: ["published_at desc"]')
                && str_contains($document, 'thumbnail_url'),
            $response,
        );

        $this->assertSame($response, (new StatamicEditorialRepository($client))->paginatePodcasts(3));
    }

    #[Test]
    public function it_fetches_all_upcoming_events_and_a_page_of_past_events_in_their_display_order(): void
    {
        $response = [
            'upcoming' => ['data' => []],
            'past' => ['data' => [], 'current_page' => 2],
        ];
        $client = $this->expectQuery(
            [
                'site' => 'default',
                'page' => 2,
                'upcomingFilter' => ['date_start' => ['is_after' => 'today']],
                'pastFilter' => ['date_start' => ['is_before' => 'today']],
            ],
            static fn (string $document): bool => str_contains($document, 'upcoming: entries')
                && str_contains($document, 'limit: 500')
                && str_contains($document, 'sort: ["date_start asc"]')
                && str_contains($document, 'past: entries')
                && str_contains($document, 'page: $page, limit: 10')
                && str_contains($document, 'sort: ["date_start desc"]'),
            $response,
        );

        $this->assertSame($response, (new StatamicEditorialRepository($client))->paginateEvents(2));
    }

    #[Test]
    public function it_fetches_each_detail_family_by_uri_with_complete_fragments(): void
    {
        $families = [
            ['findInsightByUri', '/nieuws/example', 'Entry_Insights_Insights', 'author_name', true],
            ['findKnowledgeByUri', '/kennis/example', 'Entry_Knowledge_Knowledge', 'authors', true],
            ['findPodcastByUri', '/podcast/example', 'Entry_Podcasts_Podcasts', 'transcript', true],
            ['findEventByUri', '/events/example', 'Entry_Events_Events', 'signup_link', false],
        ];

        foreach ($families as [$method, $uri, $type, $field, $hasCallToAction]) {
            $entry = ['id' => 'entry-id', 'uri' => $uri];
            $client = $this->expectQuery(
                ['site' => 'default', 'uri' => $uri],
                static fn (string $document): bool => str_contains($document, "... on {$type}")
                    && str_contains($document, $field)
                    && str_contains($document, 'fragment SeoFields')
                    && (str_contains($document, 'fragment CallToActionFields') === $hasCallToAction),
                ['entry' => $entry],
            );

            $this->assertSame($entry, (new StatamicEditorialRepository($client))->{$method}($uri));
        }
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  callable(string): bool  $documentMatches
     * @param  array<string, mixed>  $response
     */
    private function expectQuery(array $variables, callable $documentMatches, array $response): GraphqlClient
    {
        $client = $this->createMock(GraphqlClient::class);
        $client->expects($this->once())
            ->method('query')
            ->with($this->callback($documentMatches), $variables)
            ->willReturn($response);

        return $client;
    }
}
