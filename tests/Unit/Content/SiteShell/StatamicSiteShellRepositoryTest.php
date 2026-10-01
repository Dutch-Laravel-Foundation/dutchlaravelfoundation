<?php

declare(strict_types=1);
use App\Content\Graphql\GraphqlClient;
use App\Content\SiteShell\StatamicSiteShellRepository;

it('fetches every shared shell resource in one graphql query', function () {
    $response = ['organization' => ['title' => 'Dutch Laravel Foundation']];

    $client = $this->createMock(GraphqlClient::class);
    $client->expects($this->once())
        ->method('query')
        ->with(
            $this->callback(static function (string $document): bool {
                $requiredSelections = [
                    '... on GlobalSet_Dlf',
                    '... on GlobalSet_Seo',
                    '... on GlobalSet_Opengraph',
                    '... on NavPage_Legal',
                    '... on Entry_Members_Members',
                    '... on Entry_Socials_Socials',
                    '... on Entry_Cta_Cta',
                    'form(handle: "newsletter")',
                    'fields {',
                    'config',
                ];

                foreach ($requiredSelections as $selection) {
                    if (! str_contains($document, $selection)) {
                        return false;
                    }
                }

                return true;
            }),
            [
                'site' => 'default',
                'defaultCtaId' => 'ee5d33de-9a24-4860-92dd-3503740b62af',
            ],
        )
        ->willReturn($response);

    expect((new StatamicSiteShellRepository($client))->fetch())->toBe($response);
});
