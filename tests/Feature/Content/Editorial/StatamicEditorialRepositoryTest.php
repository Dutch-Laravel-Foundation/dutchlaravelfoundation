<?php

declare(strict_types=1);

namespace Tests\Feature\Content\Editorial;

use App\Content\Editorial\EditorialDataMapper;
use App\Content\Editorial\StatamicEditorialRepository;
use App\Content\Graphql\GraphqlClient;
use App\Data\Editorial\EventData;
use App\Data\Editorial\InsightData;
use App\Data\Editorial\KnowledgeData;
use App\Data\Editorial\PodcastData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatamicEditorialRepositoryTest extends TestCase
{
    #[Test]
    public function editorial_index_queries_execute_against_the_real_schema(): void
    {
        $repository = $this->repository();
        $mapper = new EditorialDataMapper;

        $insights = $mapper->mapArticleIndex($repository->paginateInsights(1, 'Netwerk'));
        $knowledge = $mapper->mapArticleIndex($repository->paginateKnowledge());
        $podcasts = $mapper->mapPodcastIndex($repository->paginatePodcasts());
        $events = $mapper->mapEventIndex($repository->paginateEvents());

        $this->assertNotEmpty($insights->items);
        $this->assertSame('Netwerk', $insights->items[0]->category);
        $this->assertNotEmpty($knowledge->items);
        $this->assertNotEmpty($podcasts->items);
        $this->assertNotEmpty($events->upcoming);
        $this->assertNotEmpty($events->past);
        $this->assertSame(10, $events->pagination->perPage);
    }

    #[Test]
    public function editorial_detail_queries_preserve_family_specific_content(): void
    {
        $repository = $this->repository();
        $mapper = new EditorialDataMapper;

        $insight = $mapper->mapInsight($repository->findInsightByUri('/nieuws/winstgevers-eerlijke-marketing-slimme-techniek-en-0-bullshit'));
        $knowledge = $mapper->mapKnowledge($repository->findKnowledgeByUri('/kennis/common-ground-en-wat-dit-betekent-voor-laravel-developers'));
        $podcast = $mapper->mapPodcast($repository->findPodcastByUri('/podcast/ana-lisboa-from-first-laravel-project-to-larabelles-board-member'));
        $event = $mapper->mapEvent($repository->findEventByUri('/events/online-meet-up-mohamed-said-over-laravel-queues-in-action'));

        $this->assertInstanceOf(InsightData::class, $insight);
        $this->assertNotEmpty($insight->content);
        $this->assertInstanceOf(KnowledgeData::class, $knowledge);
        $this->assertNotNull($knowledge->contentHtml);
        $this->assertInstanceOf(PodcastData::class, $podcast);
        $this->assertNotNull($podcast->transcriptHtml);
        $this->assertInstanceOf(EventData::class, $event);
        $this->assertSame('19:00', $event->timeStart);
    }

    private function repository(): StatamicEditorialRepository
    {
        return new StatamicEditorialRepository($this->app->make(GraphqlClient::class));
    }
}
