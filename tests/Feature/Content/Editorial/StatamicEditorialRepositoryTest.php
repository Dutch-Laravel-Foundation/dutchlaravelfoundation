<?php

declare(strict_types=1);
use App\Content\Editorial\EditorialDataMapper;
use App\Content\Editorial\StatamicEditorialRepository;
use App\Content\Graphql\GraphqlClient;
use App\Data\Editorial\EventData;
use App\Data\Editorial\InsightData;
use App\Data\Editorial\KnowledgeData;
use App\Data\Editorial\PodcastData;

test('editorial index queries execute against the real schema', function () {
    $repository = repository();
    $mapper = new EditorialDataMapper;

    $insights = $mapper->mapArticleIndex($repository->paginateInsights(1, 'Netwerk'));
    $knowledge = $mapper->mapArticleIndex($repository->paginateKnowledge());
    $podcasts = $mapper->mapPodcastIndex($repository->paginatePodcasts());
    $events = $mapper->mapEventIndex($repository->paginateEvents());

    expect($insights->items)->not->toBeEmpty();
    expect($insights->items[0]->category)->toBe('Netwerk');
    expect($knowledge->items)->not->toBeEmpty();
    expect($podcasts->items)->not->toBeEmpty();
    expect($events->upcoming)->not->toBeEmpty();
    expect($events->past)->not->toBeEmpty();
    expect($events->pagination->perPage)->toBe(10);
});
test('editorial detail queries preserve family specific content', function () {
    $repository = repository();
    $mapper = new EditorialDataMapper;

    $insight = $mapper->mapInsight($repository->findInsightByUri('/nieuws/winstgevers-eerlijke-marketing-slimme-techniek-en-0-bullshit'));
    $knowledge = $mapper->mapKnowledge($repository->findKnowledgeByUri('/kennis/common-ground-en-wat-dit-betekent-voor-laravel-developers'));
    $podcast = $mapper->mapPodcast($repository->findPodcastByUri('/podcast/ana-lisboa-from-first-laravel-project-to-larabelles-board-member'));
    $event = $mapper->mapEvent($repository->findEventByUri('/events/online-meet-up-mohamed-said-over-laravel-queues-in-action'));

    expect($insight)->toBeInstanceOf(InsightData::class);
    expect($insight->content)->not->toBeEmpty();
    expect($knowledge)->toBeInstanceOf(KnowledgeData::class);
    expect($knowledge->contentHtml)->not->toBeNull();
    expect($podcast)->toBeInstanceOf(PodcastData::class);
    expect($podcast->transcriptHtml)->not->toBeNull();
    expect($event)->toBeInstanceOf(EventData::class);
    expect($event->timeStart)->toBe('19:00');
});
function repository(): StatamicEditorialRepository
{
    return new StatamicEditorialRepository(app()->make(GraphqlClient::class));
}
