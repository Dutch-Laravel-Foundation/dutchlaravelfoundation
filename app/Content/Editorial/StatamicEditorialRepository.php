<?php

declare(strict_types=1);

namespace App\Content\Editorial;

use App\Content\Graphql\GraphqlClient;
use Statamic\Facades\Site;

final readonly class StatamicEditorialRepository implements EditorialRepository
{
    private const string ARTICLE_INDEX = <<<'GRAPHQL'
        query ArticleIndex($site: String!, $page: Int!, $filter: JsonArgument!) {
            entries(collection: ["__COLLECTION__"], site: $site, page: $page, limit: 10, filter: $filter, sort: ["date desc"]) {
                data {
                    __typename
                    ...ArticleCardFields
                }
                ...PaginationFields
            }
        }

        fragment ArticleCardFields on EntryInterface {
            id
            title
            slug
            url
            uri
            ... on Entry_Insights_Insights {
                category { value label }
                date
                introduction(format: "html")
                featured_image { ...AssetFields }
            }
            ... on Entry_Knowledge_Knowledge {
                category { value label }
                date
                introduction(format: "html")
                featured_image { ...AssetFields }
            }
        }

        fragment PaginationFields on EntryInterfacePagination {
            total
            per_page
            current_page
            from
            to
            last_page
            has_more_pages
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
            ... on Asset_Insights { alt }
            ... on Asset_Knowledge { alt }
        }
        GRAPHQL;

    private const string INSIGHT_DETAIL = <<<'GRAPHQL'
        query InsightDetail($site: String!, $uri: String!) {
            entry(site: $site, uri: $uri) {
                __typename
                ... on Entry_Insights_Insights {
                    id
                    title
                    slug
                    url
                    uri
                    category { value label }
                    date
                    introduction(format: "html")
                    featured_image { ...AssetFields }
                    content {
                        __typename
                        ... on BardText { type text }
                        ... on Set_Content_Image { id type image { ...AssetFields } }
                        ... on Set_Content_Spacer { id type spacer }
                        ... on Set_Content_Line { id type line }
                        ... on Set_Content_Video { id type video }
                    }
                    author_name
                    author_role
                    author_bio(format: "html")
                    author_image { ...AssetFields }
                    author_link { url title }
                    call_to_action { ...CallToActionFields }
                    ...SeoFields
                }
            }
        }

        fragment SeoFields on Entry_Insights_Insights {
            meta_title
            meta_description
            meta_keywords
        }

        fragment CallToActionFields on EntryInterface {
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

        fragment AssetFields on AssetInterface {
            id
            url
            permalink
            path
            extension
            width
            height
            focus_css
            ... on Asset_Insights { alt }
        }
        GRAPHQL;

    private const string KNOWLEDGE_DETAIL = <<<'GRAPHQL'
        query KnowledgeDetail($site: String!, $uri: String!) {
            entry(site: $site, uri: $uri) {
                __typename
                ... on Entry_Knowledge_Knowledge {
                    id
                    title
                    slug
                    url
                    uri
                    category { value label }
                    date
                    introduction(format: "html")
                    featured_image { ...AssetFields }
                    content(format: "html")
                    authors {
                        ... on Entry_Authors_Author {
                            id
                            title
                            job_title
                            description(format: "html")
                            photo { ...AssetFields }
                            photo_url
                            linkedin_url { url title }
                            website_url { url title }
                        }
                    }
                    call_to_action { ...CallToActionFields }
                    ...SeoFields
                }
            }
        }

        fragment SeoFields on Entry_Knowledge_Knowledge {
            meta_title
            meta_description
            meta_keywords
        }

        fragment CallToActionFields on EntryInterface {
            ... on Entry_Cta_Cta {
                id title description(format: "html") eyebrow benefits
                link { url title }
                link_2 { url title }
                theme { value label }
                button_text
                button_style { value label }
                button_text_2
                button_style_2 { value label }
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
            ... on Asset_Knowledge { alt }
            ... on Asset_Assets { alt }
        }
        GRAPHQL;

    private const string PODCAST_INDEX = <<<'GRAPHQL'
        query PodcastIndex($site: String!, $page: Int!) {
            entries(collection: ["podcasts"], site: $site, page: $page, limit: 10, sort: ["published_at desc"]) {
                data {
                    ... on Entry_Podcasts_Podcasts {
                        id
                        title
                        slug
                        url
                        uri
                        summary
                        thumbnail_url
                        published_at
                    }
                }
                total
                per_page
                current_page
                from
                to
                last_page
                has_more_pages
            }
        }
        GRAPHQL;

    private const string PODCAST_DETAIL = <<<'GRAPHQL'
        query PodcastDetail($site: String!, $uri: String!) {
            entry(site: $site, uri: $uri) {
                __typename
                ... on Entry_Podcasts_Podcasts {
                    id
                    title
                    slug
                    url
                    uri
                    summary
                    description(format: "html")
                    video_url
                    spotify_url
                    thumbnail_url
                    transcript(format: "html")
                    published_at
                    call_to_action { ...CallToActionFields }
                    ...SeoFields
                }
            }
        }

        fragment SeoFields on Entry_Podcasts_Podcasts {
            meta_title
            meta_description
            meta_keywords
        }

        fragment CallToActionFields on EntryInterface {
            ... on Entry_Cta_Cta {
                id title description(format: "html") eyebrow benefits
                link { url title }
                link_2 { url title }
                theme { value label }
                button_text
                button_style { value label }
                button_text_2
                button_style_2 { value label }
            }
        }
        GRAPHQL;

    private const string EVENTS = <<<'GRAPHQL'
        query Events($site: String!, $page: Int!, $upcomingFilter: JsonArgument!, $pastFilter: JsonArgument!) {
            upcoming: entries(collection: ["events"], site: $site, limit: 500, filter: $upcomingFilter, sort: ["date_start asc"]) {
                data { ...EventCardFields }
            }
            past: entries(collection: ["events"], site: $site, page: $page, limit: 10, filter: $pastFilter, sort: ["date_start desc"]) {
                data { ...EventCardFields }
                total
                per_page
                current_page
                from
                to
                last_page
                has_more_pages
            }
        }

        fragment EventCardFields on EntryInterface {
            ... on Entry_Events_Events {
                id
                title
                slug
                url
                uri
                type { value label }
                date_start
                introduction(format: "html")
                featured_image { ...AssetFields }
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
            ... on Asset_Events { alt }
        }
        GRAPHQL;

    private const string EVENT_DETAIL = <<<'GRAPHQL'
        query EventDetail($site: String!, $uri: String!) {
            entry(site: $site, uri: $uri) {
                __typename
                ... on Entry_Events_Events {
                    id
                    title
                    slug
                    url
                    uri
                    type { value label }
                    introduction(format: "html")
                    featured_image { ...AssetFields }
                    date_start
                    time_start
                    time_end
                    location
                    address
                    signup_link
                    content {
                        __typename
                        ... on BardText { type text }
                        ... on Set_Content_Image { id type image { ...AssetFields } }
                        ... on Set_Content_Spacer { id type spacer }
                        ... on Set_Content_Line { id type line }
                        ... on Set_Content_Video { id type video }
                    }
                    ...SeoFields
                }
            }
        }

        fragment SeoFields on Entry_Events_Events {
            meta_title
            meta_description
            meta_keywords
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
            ... on Asset_Events { alt }
        }
        GRAPHQL;

    public function __construct(private GraphqlClient $client) {}

    public function paginateInsights(int $page = 1, ?string $category = null): array
    {
        return $this->paginateArticles('insights', $page, $category);
    }

    public function findInsightByUri(string $uri): ?array
    {
        return $this->findByUri(self::INSIGHT_DETAIL, $uri);
    }

    public function paginateKnowledge(int $page = 1, ?string $category = null): array
    {
        return $this->paginateArticles('knowledge', $page, $category);
    }

    public function findKnowledgeByUri(string $uri): ?array
    {
        return $this->findByUri(self::KNOWLEDGE_DETAIL, $uri);
    }

    public function paginatePodcasts(int $page = 1): array
    {
        return $this->client->query(self::PODCAST_INDEX, [
            'site' => Site::default()->handle(),
            'page' => max(1, $page),
        ]);
    }

    public function findPodcastByUri(string $uri): ?array
    {
        return $this->findByUri(self::PODCAST_DETAIL, $uri);
    }

    public function paginateEvents(int $page = 1): array
    {
        return $this->client->query(self::EVENTS, [
            'site' => Site::default()->handle(),
            'page' => max(1, $page),
            'upcomingFilter' => ['date_start' => ['is_after' => 'today']],
            'pastFilter' => ['date_start' => ['is_before' => 'today']],
        ]);
    }

    public function findEventByUri(string $uri): ?array
    {
        return $this->findByUri(self::EVENT_DETAIL, $uri);
    }

    /** @return array<string, mixed> */
    private function paginateArticles(string $collection, int $page, ?string $category): array
    {
        $filter = $category === null || $category === ''
            ? []
            : ['category' => ['is' => $category]];

        return $this->client->query(str_replace('__COLLECTION__', $collection, self::ARTICLE_INDEX), [
            'site' => Site::default()->handle(),
            'page' => max(1, $page),
            'filter' => $filter,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function findByUri(string $document, string $uri): ?array
    {
        $response = $this->client->query($document, [
            'site' => Site::default()->handle(),
            'uri' => $uri,
        ]);
        $entry = $response['entry'] ?? null;

        return is_array($entry) ? $entry : null;
    }
}
