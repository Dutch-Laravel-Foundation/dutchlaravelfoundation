<?php

declare(strict_types=1);

namespace Tests\Unit\Content;

use App\Content\Graphql\GraphqlClient;
use App\Content\Repositories\StatamicPageRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatamicPageRepositoryTest extends TestCase
{
    #[Test]
    public function it_fetches_a_page_by_uri_through_graphql(): void
    {
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

        $this->assertSame($entry, $repository->findByUri('/'));
    }

    #[Test]
    public function it_returns_null_when_graphql_cannot_find_the_uri(): void
    {
        $client = $this->createStub(GraphqlClient::class);
        $client->method('query')->willReturn(['entry' => null]);

        $repository = new StatamicPageRepository($client);

        $this->assertNull($repository->findByUri('/missing'));
    }
}
