<?php

declare(strict_types=1);
use App\Content\Graphql\GraphqlClient;
use App\Content\Repositories\StatamicPageRepository;

it('fetches a page by uri through graphql', function () {
    $entry = [
        '__typename' => 'Entry_Pages_Pages',
        'id' => 'home',
        'title' => 'Home',
        'slug' => 'home',
        'uri' => '/',
        'template' => 'home/index',
    ];

    $client = $this->createMock(GraphqlClient::class);
    $client->expects($this->once())
        ->method('query')
        ->with(
            $this->callback(static fn (string $document): bool => str_contains(
                $document,
                '... on Entry_Pages_Pages',
            )),
            ['uri' => '/', 'site' => 'default'],
        )
        ->willReturn(['entry' => $entry]);

    $repository = new StatamicPageRepository($client);

    expect($repository->findByUri('/'))->toBe($entry);
});
it('returns null when graphql cannot find the uri', function () {
    $client = $this->createStub(GraphqlClient::class);
    $client->method('query')->willReturn(['entry' => null]);

    $repository = new StatamicPageRepository($client);

    expect($repository->findByUri('/missing'))->toBeNull();
});
