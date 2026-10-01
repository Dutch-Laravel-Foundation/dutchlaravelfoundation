<?php

declare(strict_types=1);
use App\Content\Graphql\GraphqlClient;
use App\Content\PublicPages\PublicPageDataMapper;
use App\Content\PublicPages\StatamicPublicPageRepository;
use App\Data\PublicPages\PublicPageData;

it('s query executes against the real schema and preserves bard html', function () {
    $page = find('/wat-is-laravel');

    expect($page)->toBeInstanceOf(PublicPageData::class);
    expect($page->template)->toBe('templates/what-is-laravel/index');
    expect($page->content)->not->toBeEmpty();
    $this->assertStringContainsString('Laravel', $page->content[0]->headingHtml ?? '');
    expect($page->support->memberCount)->toBeGreaterThan(0);
});
it('fetches support collections and page specific content', function () {
    $page = find('/over-ons');

    expect($page)->toBeInstanceOf(PublicPageData::class);
    expect($page->support->board)->not->toBeEmpty();
    expect($page->support->foundingPartners)->not->toBeEmpty();
    expect($page->support->generalLandingCases)->not->toBeEmpty();
    expect($page->support->frameworkLandingCases)->not->toBeEmpty();
});
function find(string $uri): ?PublicPageData
{
    $repository = new StatamicPublicPageRepository(app()->make(GraphqlClient::class));

    return (new PublicPageDataMapper)->map($repository->findByUri($uri));
}
