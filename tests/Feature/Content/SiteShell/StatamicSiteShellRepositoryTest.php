<?php

declare(strict_types=1);

namespace Tests\Feature\Content\SiteShell;

use App\Content\Graphql\GraphqlClient;
use App\Content\SiteShell\StatamicSiteShellRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatamicSiteShellRepositoryTest extends TestCase
{
    #[Test]
    public function its_query_matches_the_live_statamic_graphql_schema(): void
    {
        $repository = new StatamicSiteShellRepository(
            $this->app->make(GraphqlClient::class),
        );

        $response = $repository->fetch();

        $this->assertSame('Gegevens Dutch Laravel Foundation', $response['organization']['title']);
        $this->assertSame('Dutch Laravel Foundation', $response['seo']['meta_title']);
        $this->assertSame('main', $response['mainNavigation']['handle']);
        $this->assertSame('legal', $response['legalNavigation']['handle']);
        $this->assertNotEmpty($response['members']['data']);
        $this->assertNotEmpty($response['socials']['data']);
        $this->assertSame(
            'ee5d33de-9a24-4860-92dd-3503740b62af',
            $response['defaultCta']['id'],
        );
        $this->assertSame('newsletter', $response['newsletter']['handle']);
        $this->assertNotEmpty($response['newsletter']['fields']);
    }
}
