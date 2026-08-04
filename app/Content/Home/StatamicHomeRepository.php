<?php

declare(strict_types=1);

namespace App\Content\Home;

use App\Content\Graphql\GraphqlClient;
use Statamic\Facades\Site;

final readonly class StatamicHomeRepository implements HomeRepository
{
    private const string HOME_CONTENT = <<<'GRAPHQL'
        query HomeContent($site: String!, $highlightFilter: JsonArgument!, $clientFilter: JsonArgument!) {
            latestInsight: entries(collection: ["insights"], site: $site, limit: 1, sort: ["date desc"]) {
                data {
                    __typename
                    ... on Entry_Insights_Insights {
                        ...ContentCardFields
                    }
                }
            }
            latestKnowledge: entries(collection: ["knowledge"], site: $site, limit: 1, sort: ["date desc"]) {
                data {
                    __typename
                    ... on Entry_Knowledge_Knowledge {
                        ...ContentCardFields
                    }
                }
            }
            highlightedInsight: entries(collection: ["insights"], site: $site, limit: 1, sort: ["date desc"], filter: $highlightFilter) {
                data {
                    __typename
                    ... on Entry_Insights_Insights {
                        ...ContentCardFields
                    }
                }
            }
            partners: entries(collection: ["partners"], site: $site, sort: ["title asc"]) {
                data {
                    ... on Entry_Partners_Partner {
                        id
                        title
                        slug
                        visible
                        logo {
                            ...AssetFields
                        }
                    }
                }
            }
            clients: entries(collection: ["clients"], site: $site, filter: $clientFilter) {
                data {
                    ... on Entry_Clients_Clients {
                        id
                        title
                        slug
                        logo {
                            ...AssetFields
                        }
                    }
                }
            }
        }

        fragment ContentCardFields on EntryInterface {
            id
            title
            slug
            url
            ... on Entry_Insights_Insights {
                category {
                    value
                    label
                }
                introduction
                featured_image {
                    ...AssetFields
                }
            }
            ... on Entry_Knowledge_Knowledge {
                category {
                    value
                    label
                }
                introduction
                featured_image {
                    ...AssetFields
                }
            }
        }

        fragment AssetFields on AssetInterface {
            id
            url
            permalink
            path
            extension
            width
            height
            focus_css
            ... on Asset_Assets {
                alt
            }
            ... on Asset_Clients {
                alt
            }
            ... on Asset_Insights {
                alt
            }
            ... on Asset_Knowledge {
                alt
            }
        }
        GRAPHQL;

    public function __construct(private GraphqlClient $client) {}

    public function get(): array
    {
        return $this->client->query(self::HOME_CONTENT, [
            'site' => Site::default()->handle(),
            'highlightFilter' => [
                'highlight' => ['equals' => true],
            ],
            'clientFilter' => [
                'slug' => ['in' => self::CURATED_CLIENT_SLUGS],
            ],
        ]);
    }
}
