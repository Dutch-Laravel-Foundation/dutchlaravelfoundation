<?php

declare(strict_types=1);

namespace Tests\Feature\Content\Home;

use App\Content\Graphql\GraphqlClient;
use App\Content\Home\StatamicHomeRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatamicHomeRepositoryTest extends TestCase
{
    #[Test]
    public function its_query_matches_the_real_statamic_graphql_schema(): void
    {
        $repository = new StatamicHomeRepository($this->app->make(GraphqlClient::class));

        $content = $repository->get();

        $this->assertSame('Entry_Insights_Insights', $content['latestInsight']['data'][0]['__typename']);
        $this->assertSame('Entry_Knowledge_Knowledge', $content['latestKnowledge']['data'][0]['__typename']);
        $this->assertSame('Entry_Insights_Insights', $content['highlightedInsight']['data'][0]['__typename']);
        $this->assertNotEmpty($content['partners']['data']);
        $this->assertNotEmpty($content['clients']['data']);
        $this->assertArrayHasKey('featured_image', $content['latestInsight']['data'][0]);
        $this->assertArrayHasKey('logo', $content['partners']['data'][0]);
        $this->assertArrayHasKey('logo', $content['clients']['data'][0]);
    }
}
