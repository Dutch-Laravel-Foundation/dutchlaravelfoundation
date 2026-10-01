<?php

declare(strict_types=1);
use App\Content\Graphql\GraphqlClient;
use App\Content\PublicPages\PublicPageRepository;
use App\Content\PublicPages\StatamicPublicPageRepository;

it('fetches a public page and its template support data by uri', function () {
    $response = ['page' => ['id' => 'page-id', 'uri' => '/privacy-statement']];
    $client = $this->createMock(GraphqlClient::class);
    $client->expects($this->once())
        ->method('query')
        ->with(
            $this->callback(static fn (string $document): bool => str_contains($document, 'query PublicPage')
                && str_contains($document, '... on Entry_Pages_Pages')
                && str_contains($document, 'fragment PageContentFields')
                && str_contains($document, '... on Set_Content_DoubleColumn')
                && str_contains($document, '... on Set_Content_DlfHero')
                && str_contains($document, '... on Set_Content_DlfMediaText')
                && str_contains($document, '... on Set_Content_DlfFeatureGrid')
                && str_contains($document, '... on Set_Content_DlfCardGrid')
                && str_contains($document, '... on Set_Content_DlfStats')
                && str_contains($document, '... on Set_Content_DlfQuote')
                && str_contains($document, '... on Set_Content_DlfLogoCloud')
                && str_contains($document, '... on Set_Content_DlfPricing')
                && str_contains($document, '... on Set_Content_DlfCtaPanel')
                && str_contains($document, 'members: entries')
                && str_contains($document, 'board: entries')
                && str_contains($document, 'foundingPartners: entries')
                && str_contains($document, 'landingCases: entries')
                && str_contains($document, 'body(format: "html")')
                && str_contains($document, 'description(format: "html")')),
            [
                'site' => 'default',
                'uri' => '/privacy-statement',
                'foundingPartnerFilter' => ['founding_partner' => ['equals' => true]],
                'landingCaseFilter' => ['slug' => ['in' => PublicPageRepository::LANDING_CASE_SLUGS]],
            ],
        )
        ->willReturn($response);

    expect((new StatamicPublicPageRepository($client))->findByUri('/privacy-statement'))->toBe($response);
});
