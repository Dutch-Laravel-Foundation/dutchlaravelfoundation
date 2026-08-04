<?php

declare(strict_types=1);

namespace App\Content\Community;

use App\Content\Graphql\GraphqlClient;
use Statamic\Facades\Site;

final readonly class StatamicCommunityRepository implements CommunityRepository
{
    private const string CASE_INDEX = <<<'GRAPHQL'
        query CaseIndex($site: String!, $uri: String!) {
            page: entry(site: $site, uri: $uri) { ...PageFields }
            entries(collection: ["cases"], site: $site, limit: 500, sort: ["date desc"]) {
                data { ...CaseCardFields }
            }
        }
        GRAPHQL;

    private const string CASE_DETAIL = <<<'GRAPHQL'
        query CaseDetail($site: String!, $uri: String!) {
            entry(site: $site, uri: $uri) {
                ... on Entry_Cases_Cases {
                    ...CaseCardFields
                    content { ...ContentFields }
                    meta_title
                    meta_description
                    meta_keywords
                }
            }
        }
        GRAPHQL;

    private const string MEMBER_INDEX = <<<'GRAPHQL'
        query MemberIndex($site: String!, $uri: String!) {
            page: entry(site: $site, uri: $uri) { ...PageFields }
            entries(collection: ["members"], site: $site, limit: 500, sort: ["title asc"]) {
                data { ...MemberFields }
            }
        }
        GRAPHQL;

    private const string MEMBER_DETAIL = <<<'GRAPHQL'
        query MemberDetail($site: String!, $uri: String!) {
            entry(site: $site, uri: $uri) {
                ... on Entry_Members_Members {
                    ...MemberFields
                    description(format: "html")
                    founding_partner
                    email
                    phone
                    recruitment_website
                    video
                    meta_title
                    meta_description
                    meta_keywords
                }
            }
        }
        GRAPHQL;

    private const string MEMBER_RELATED_CONTENT = <<<'GRAPHQL'
        query MemberRelatedContent($site: String!, $memberFilter: JsonArgument!) {
            internships: entries(collection: ["internships"], site: $site, limit: 500, filter: $memberFilter) {
                data { ...InternshipFields }
            }
            cases: entries(collection: ["cases"], site: $site, limit: 3, filter: $memberFilter) {
                data { ...CaseCardFields }
            }
        }
        GRAPHQL;

    private const string INTERNSHIP_INDEX = <<<'GRAPHQL'
        query InternshipIndex($site: String!, $uri: String!) {
            page: entry(site: $site, uri: $uri) { ...PageFields }
            entries(collection: ["internships"], site: $site, limit: 500) {
                data { ...InternshipFields }
            }
        }
        GRAPHQL;

    private const string INTERNSHIP_DETAIL = <<<'GRAPHQL'
        query InternshipDetail($site: String!, $uri: String!) {
            entry(site: $site, uri: $uri) {
                ... on Entry_Internships_Internships {
                    ...InternshipFields
                    meta_title
                    meta_description
                    meta_keywords
                }
            }
        }
        GRAPHQL;

    private const string LARABELLES_PAGE = <<<'GRAPHQL'
        query LarabellesPage($site: String!, $uri: String!) {
            entry(site: $site, uri: $uri) { ...PageFields }
        }
        GRAPHQL;

    private const string PAGE_FIELDS = <<<'GRAPHQL'
        fragment PageFields on EntryInterface {
            ... on Entry_Pages_Pages {
                id
                title
                slug
                url
                uri
                template
                content { ...ContentFields }
                call_to_action { ...CallToActionFields }
                meta_title
                meta_description
                meta_keywords
            }
        }
        GRAPHQL;

    private const string CONTENT_FIELDS = <<<'GRAPHQL'
        fragment ContentFields on Sets_Content {
            __typename
            ... on BardText { type text }
            ... on Set_Content_Image { id type image { ...CommunityAssetFields } }
            ... on Set_Content_Spacer { id type spacer }
            ... on Set_Content_Line { id type line }
            ... on Set_Content_Video { id type video }
            ... on Set_Content_RedNote { id type note: content }
            ... on Set_Content_DoubleColumn {
                id
                type
                heading
                left { __typename ... on BardText { type text } }
                right { __typename ... on BardText { type text } }
            }
        }
        GRAPHQL;

    private const string CALL_TO_ACTION_FIELDS = <<<'GRAPHQL'
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
        GRAPHQL;

    private const string CASE_CARD_FIELDS = <<<'GRAPHQL'
        fragment CaseCardFields on EntryInterface {
            ... on Entry_Cases_Cases {
                id
                title
                title_long
                slug
                url
                uri
                date
                introduction(format: "html")
                featured_image { ...CommunityAssetFields }
                member { ...MemberFields }
                client {
                    ... on Entry_Clients_Clients {
                        id
                        title
                        slug
                        url
                        uri
                        logo { ...CommunityAssetFields }
                    }
                }
            }
        }
        GRAPHQL;

    private const string MEMBER_FIELDS = <<<'GRAPHQL'
        fragment MemberFields on EntryInterface {
            ... on Entry_Members_Members {
                id
                title
                slug
                url
                uri
                logo { ...CommunityAssetFields }
                type { value label }
                employees { value label }
                sbb
                city
                province { value label }
                website
                internship_contact_name
                internship_contact_email
                internship_contact_phone
            }
        }
        GRAPHQL;

    private const string INTERNSHIP_FIELDS = <<<'GRAPHQL'
        fragment InternshipFields on EntryInterface {
            ... on Entry_Internships_Internships {
                id
                title
                slug
                url
                uri
                description(format: "html")
                apply_url { url title }
                member { ...MemberFields }
            }
        }
        GRAPHQL;

    private const string ASSET_FIELDS = <<<'GRAPHQL'
        fragment CommunityAssetFields on AssetInterface {
            id
            url
            permalink
            path
            extension
            width
            height
            focus_css
            ... on Asset_Cases { alt }
            ... on Asset_Members { alt }
            ... on Asset_Clients { alt }
            ... on Asset_Assets { alt }
        }
        GRAPHQL;

    public function __construct(private GraphqlClient $client) {}

    public function caseIndex(): array
    {
        return $this->client->query($this->pageDocument(self::CASE_INDEX, self::CASE_CARD_FIELDS.self::MEMBER_FIELDS), [
            'site' => $this->site(),
            'uri' => '/cases',
        ]);
    }

    public function findCaseByUri(string $uri): ?array
    {
        return $this->findEntry(
            self::CASE_DETAIL.self::CONTENT_FIELDS.self::CASE_CARD_FIELDS.self::MEMBER_FIELDS.self::ASSET_FIELDS,
            $uri,
        );
    }

    public function memberIndex(): array
    {
        return $this->client->query($this->pageDocument(self::MEMBER_INDEX, self::MEMBER_FIELDS), [
            'site' => $this->site(),
            'uri' => '/leden',
        ]);
    }

    public function findMemberByUri(string $uri): array
    {
        $member = $this->findEntry(self::MEMBER_DETAIL.self::MEMBER_FIELDS.self::ASSET_FIELDS, $uri);

        if ($member === null) {
            return ['member' => null, 'internships' => ['data' => []], 'cases' => ['data' => []]];
        }

        $related = $this->client->query(
            self::MEMBER_RELATED_CONTENT
                .self::INTERNSHIP_FIELDS
                .self::CASE_CARD_FIELDS
                .self::MEMBER_FIELDS
                .self::ASSET_FIELDS,
            [
                'site' => $this->site(),
                'memberFilter' => ['member' => ['is' => (string) ($member['id'] ?? '')]],
            ],
        );

        return [
            'member' => $member,
            'internships' => $related['internships'] ?? ['data' => []],
            'cases' => $related['cases'] ?? ['data' => []],
        ];
    }

    public function internshipIndex(): array
    {
        return $this->client->query($this->pageDocument(self::INTERNSHIP_INDEX, self::INTERNSHIP_FIELDS.self::MEMBER_FIELDS), [
            'site' => $this->site(),
            'uri' => '/stagebank',
        ]);
    }

    public function findInternshipByUri(string $uri): ?array
    {
        return $this->findEntry(
            self::INTERNSHIP_DETAIL.self::INTERNSHIP_FIELDS.self::MEMBER_FIELDS.self::ASSET_FIELDS,
            $uri,
        );
    }

    public function findLarabellesByUri(string $uri = '/larabelles'): ?array
    {
        return $this->findEntry($this->pageDocument(self::LARABELLES_PAGE), $uri);
    }

    private function pageDocument(string $query, string $fragments = ''): string
    {
        return $query
            .self::PAGE_FIELDS
            .self::CONTENT_FIELDS
            .self::CALL_TO_ACTION_FIELDS
            .$fragments
            .self::ASSET_FIELDS;
    }

    /** @return array<string, mixed>|null */
    private function findEntry(string $document, string $uri): ?array
    {
        $response = $this->client->query($document, [
            'site' => $this->site(),
            'uri' => $uri,
        ]);
        $entry = $response['entry'] ?? null;

        return is_array($entry) ? $entry : null;
    }

    private function site(): string
    {
        return Site::default()->handle();
    }
}
