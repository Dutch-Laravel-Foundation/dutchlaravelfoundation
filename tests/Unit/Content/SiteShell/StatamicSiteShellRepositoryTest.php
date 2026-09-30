<?php

declare(strict_types=1);

namespace Tests\Unit\Content\SiteShell;

use App\Content\Graphql\GraphqlClient;
use App\Content\SiteShell\StatamicSiteShellRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatamicSiteShellRepositoryTest extends TestCase
{
    #[Test]
    public function it_fetches_every_shared_shell_resource_in_one_graphql_query(): void
    {
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

        $this->assertSame($response, (new StatamicSiteShellRepository($client))->fetch());
    }
}
