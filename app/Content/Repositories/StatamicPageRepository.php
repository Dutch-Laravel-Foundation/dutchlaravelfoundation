<?php

declare(strict_types=1);

namespace App\Content\Repositories;

use App\Content\Graphql\GraphqlClient;
use Statamic\Facades\Site;

final readonly class StatamicPageRepository implements PageRepository
{
    private const string PAGE_BY_URI = <<<'GRAPHQL'
        query PageByUri($uri: String!, $site: String!) {
            entry(uri: $uri, site: $site) {
                __typename
                id
                title
                slug
                uri
                url
                permalink
                status
                private
                blueprint
                collection {
                    handle
                    title
                }
                site {
                    handle
                    name
                    locale
                    short_locale
                    url
                }

                ... on Entry_Pages_Pages {
                    template
                    menu_color {
                        value
                        label
                    }
                    header_title
                    header_content
                    meta_title
                    meta_description
                    meta_keywords
                    call_to_action {
                        ... on Entry_Cta_Cta {
                            id
                            title
                            description(format: "html")
                            eyebrow
                            benefits
                            link { url title }
                            link_2 { url title }
                            theme { value label }
                            button_text
                            button_style { value label }
                            button_text_2
                            button_style_2 { value label }
                        }
                    }
                }
            }
        }
        GRAPHQL;

    public function __construct(private GraphqlClient $client) {}

    public function findByUri(string $uri): ?array
    {
        $response = $this->client->query(self::PAGE_BY_URI, [
            'uri' => $uri,
            'site' => Site::default()->handle(),
        ]);

        $entry = $response['entry'] ?? null;

        return is_array($entry) ? $entry : null;
    }
}
