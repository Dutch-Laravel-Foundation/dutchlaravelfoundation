<?php

declare(strict_types=1);

namespace Tests\Feature\Content\PublicPages;

use App\Content\Graphql\GraphqlClient;
use App\Content\PublicPages\PublicPageDataMapper;
use App\Content\PublicPages\StatamicPublicPageRepository;
use App\Data\PublicPages\PublicPageData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatamicPublicPageRepositoryTest extends TestCase
{
    #[Test]
    public function its_query_executes_against_the_real_schema_and_preserves_bard_html(): void
    {
        $page = $this->find('/wat-is-laravel');

        $this->assertInstanceOf(PublicPageData::class, $page);
        $this->assertSame('templates/what-is-laravel/index', $page->template);
        $this->assertNotEmpty($page->content);
        $this->assertStringContainsString('Laravel', $page->content[0]->headingHtml ?? '');
        $this->assertGreaterThan(0, $page->support->memberCount);
    }

    #[Test]
    public function it_fetches_support_collections_and_page_specific_content(): void
    {
        $page = $this->find('/over-ons');

        $this->assertInstanceOf(PublicPageData::class, $page);
        $this->assertNotEmpty($page->support->board);
        $this->assertNotEmpty($page->support->foundingPartners);
        $this->assertNotEmpty($page->support->generalLandingCases);
        $this->assertNotEmpty($page->support->frameworkLandingCases);
    }

    private function find(string $uri): ?PublicPageData
    {
        $repository = new StatamicPublicPageRepository($this->app->make(GraphqlClient::class));

        return (new PublicPageDataMapper)->map($repository->findByUri($uri));
    }
}
