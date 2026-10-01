<?php

declare(strict_types=1);
use App\Content\Graphql\GraphqlClient;
use App\Content\Home\HomeRepository;
use App\Content\Home\StatamicHomeRepository;

it('fetches all homepage supporting content in one graphql query', function () {
    $response = [
        'latestInsight' => ['data' => []],
        'latestKnowledge' => ['data' => []],
        'highlightedInsight' => ['data' => []],
        'partners' => ['data' => []],
        'clients' => ['data' => []],
    ];

    $client = $this->createMock(GraphqlClient::class);
    $client->expects($this->once())
        ->method('query')
        ->with(
            $this->callback(function (string $document): bool {
                $this->assertStringContainsString(
                    'latestInsight: entries(collection: ["insights"], site: $site, limit: 1, sort: ["date desc"])',
                    $document,
                );
                $this->assertStringContainsString(
                    'latestKnowledge: entries(collection: ["knowledge"], site: $site, limit: 1, sort: ["date desc"])',
                    $document,
                );
                $this->assertStringContainsString(
                    'highlightedInsight: entries(collection: ["insights"], site: $site, limit: 1, sort: ["date desc"], filter: $highlightFilter)',
                    $document,
                );
                $this->assertStringContainsString(
                    'partners: entries(collection: ["partners"], site: $site, sort: ["title asc"])',
                    $document,
                );
                $this->assertStringContainsString(
                    'clients: entries(collection: ["clients"], site: $site, filter: $clientFilter)',
                    $document,
                );
                $this->assertStringContainsString('... on Entry_Insights_Insights', $document);
                $this->assertStringContainsString('category', $document);
                $this->assertStringContainsString('... on Entry_Knowledge_Knowledge', $document);
                $this->assertStringContainsString('... on Entry_Partners_Partner', $document);
                $this->assertStringContainsString('... on Entry_Clients_Clients', $document);
                $this->assertStringContainsString('... on Asset_Insights', $document);
                $this->assertStringContainsString('... on Asset_Knowledge', $document);
                $this->assertStringContainsString('... on Asset_Assets', $document);
                $this->assertStringContainsString('... on Asset_Clients', $document);

                return true;
            }),
            [
                'site' => 'default',
                'highlightFilter' => ['highlight' => ['equals' => true]],
                'clientFilter' => ['slug' => ['in' => HomeRepository::CURATED_CLIENT_SLUGS]],
            ],
        )
        ->willReturn($response);

    $repository = new StatamicHomeRepository($client);

    expect($repository->get())->toBe($response);
});
