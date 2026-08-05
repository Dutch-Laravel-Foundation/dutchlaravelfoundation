<?php

declare(strict_types=1);

namespace Tests\Feature\Content;

use App\Content\Graphql\GraphqlClient;
use App\Content\Graphql\StatamicGraphqlClient;
use App\Content\Repositories\PageRepository;
use App\Content\Repositories\StatamicPageRepository;
use App\Content\SiteShell\StatamicSiteShellRepository;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\GraphQL;
use Rebing\GraphQL\Support\Facades\GraphQL as GraphQLFacade;
use Tests\TestCase;

final class GraphqlContentAvailabilityTest extends TestCase
{
    #[Test]
    public function the_pages_collection_is_available_to_in_process_graphql_queries(): void
    {
        $client = $this->app->make(GraphqlClient::class);

        $this->assertInstanceOf(StatamicGraphqlClient::class, $client);

        $data = $client->query(<<<'GRAPHQL'
            query ContentCollections {
                collections {
                    handle
                    title
                }
            }
            GRAPHQL);

        $this->assertContains(
            ['handle' => 'pages', 'title' => "Pagina's"],
            $data['collections'],
        );
    }

    #[Test]
    public function page_supporting_filters_are_available_to_in_process_queries(): void
    {
        $client = $this->app->make(GraphqlClient::class);

        $data = $client->query(<<<'GRAPHQL'
            query HighlightedInsight($filter: JsonArgument!) {
                entries(
                    collection: ["insights"]
                    limit: 1
                    filter: $filter
                ) {
                    total
                }
            }
            GRAPHQL, [
            'filter' => [
                'highlight' => ['equals' => true],
            ],
        ]);

        $this->assertIsInt($data['entries']['total']);
    }

    #[Test]
    public function public_form_metadata_is_available_to_in_process_queries(): void
    {
        $client = $this->app->make(GraphqlClient::class);

        $data = $client->query(<<<'GRAPHQL'
            query NewsletterForm {
                form(handle: "newsletter") {
                    handle
                    title
                }
            }
            GRAPHQL);

        $this->assertSame('newsletter', $data['form']['handle']);
    }

    #[Test]
    public function statamic_types_are_registered_again_when_the_graphql_registry_is_rebuilt(): void
    {
        $this->app->make(StatamicSiteShellRepository::class)->fetch();

        $this->app->forgetInstance(GraphQL::class);
        GraphQLFacade::clearResolvedInstance(GraphQL::class);

        $siteShell = $this->app->make(StatamicSiteShellRepository::class)->fetch();

        $this->assertSame('legal', $siteShell['legalNavigation']['handle']);
        $this->assertNotEmpty($siteShell['newsletter']['fields']);
    }

    #[Test]
    public function the_page_repository_resolves_the_home_entry_through_graphql(): void
    {
        $repository = $this->app->make(PageRepository::class);

        $this->assertInstanceOf(StatamicPageRepository::class, $repository);

        $page = $repository->findByUri('/');

        $this->assertSame('home', $page['id']);
        $this->assertSame('Entry_Pages_Pages', $page['__typename']);
    }
}
