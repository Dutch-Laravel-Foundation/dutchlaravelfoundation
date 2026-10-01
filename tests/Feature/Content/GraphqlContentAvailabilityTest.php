<?php

declare(strict_types=1);
use App\Content\Graphql\GraphqlClient;
use App\Content\Graphql\StatamicGraphqlClient;
use App\Content\Repositories\PageRepository;
use App\Content\Repositories\StatamicPageRepository;
use App\Content\SiteShell\StatamicSiteShellRepository;
use Rebing\GraphQL\GraphQL;
use Rebing\GraphQL\Support\Facades\GraphQL as GraphQLFacade;

test('the pages collection is available to in process graphql queries', function () {
    $client = $this->app->make(GraphqlClient::class);

    expect($client)->toBeInstanceOf(StatamicGraphqlClient::class);

    $data = $client->query(<<<'GRAPHQL'
            query ContentCollections {
                collections {
                    handle
                    title
                }
            }
            GRAPHQL);

    expect($data['collections'])->toContain(['handle' => 'pages', 'title' => "Pagina's"]);
});
test('page supporting filters are available to in process queries', function () {
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

    expect($data['entries']['total'])->toBeInt();
});
test('public form metadata is available to in process queries', function () {
    $client = $this->app->make(GraphqlClient::class);

    $data = $client->query(<<<'GRAPHQL'
            query NewsletterForm {
                form(handle: "newsletter") {
                    handle
                    title
                }
            }
            GRAPHQL);

    expect($data['form']['handle'])->toBe('newsletter');
});
test('statamic types are registered again when the graphql registry is rebuilt', function () {
    $this->app->make(StatamicSiteShellRepository::class)->fetch();

    $this->app->forgetInstance(GraphQL::class);
    GraphQLFacade::clearResolvedInstance(GraphQL::class);

    $siteShell = $this->app->make(StatamicSiteShellRepository::class)->fetch();

    expect($siteShell['legalNavigation']['handle'])->toBe('legal');
    expect($siteShell['newsletter']['fields'])->not->toBeEmpty();
});
test('the page repository resolves the home entry through graphql', function () {
    $repository = $this->app->make(PageRepository::class);

    expect($repository)->toBeInstanceOf(StatamicPageRepository::class);

    $page = $repository->findByUri('/');

    expect($page['id'])->toBe('home');
    expect($page['__typename'])->toBe('Entry_Pages_Pages');
});
