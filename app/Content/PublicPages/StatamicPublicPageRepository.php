<?php

declare(strict_types=1);

namespace App\Content\PublicPages;

use App\Content\Graphql\GraphqlClient;
use LogicException;
use Statamic\Facades\Site;
use Statamic\Sites\Site as StatamicSite;

final readonly class StatamicPublicPageRepository implements PublicPageRepository
{
    private const string PUBLIC_PAGE = <<<'GRAPHQL'
        query PublicPage(
            $site: String!
            $uri: String!
            $foundingPartnerFilter: JsonArgument!
            $landingCaseFilter: JsonArgument!
        ) {
            page: entry(site: $site, uri: $uri) {
                __typename
                ... on Entry_Pages_Pages {
                    id
                    title
                    slug
                    url
                    uri
                    template
                    menu_color { value label }
                    header_title
                    header_content
                    meta_title
                    meta_description
                    meta_keywords
                    call_to_action { ...CallToActionFields }
                    content { ...PageContentFields }
                }
            }

            members: entries(collection: ["members"], site: $site, limit: 1) {
                total
            }

            board: entries(collection: ["board"], site: $site, limit: 100, sort: ["title asc"]) {
                data {
                    ... on Entry_Board_Board {
                        id
                        title
                        function
                        photo { ...AssetFields }
                    }
                }
            }

            foundingPartners: entries(
                collection: ["members"]
                site: $site
                limit: 100
                sort: ["title asc"]
                filter: $foundingPartnerFilter
            ) {
                data {
                    ... on Entry_Members_Members {
                        id
                        title
                        slug
                        url
                        city
                        province { value label }
                        logo { ...AssetFields }
                    }
                }
            }

            landingCases: entries(
                collection: ["cases"]
                site: $site
                limit: 10
                filter: $landingCaseFilter
            ) {
                data {
                    ... on Entry_Cases_Cases {
                        id
                        title
                        title_long
                        slug
                        url
                        introduction(format: "html")
                        featured_image { ...AssetFields }
                    }
                }
            }
        }

        fragment PageContentFields on Sets_Content {
            __typename
            ... on BardText {
                type
                text
            }
            ... on Set_Content_DoubleColumn {
                id
                type
                column_heading: heading
                left { ...LeftContentFields }
                right { ...RightContentFields }
            }
            ... on Set_Content_34Column {
                id
                type
                column_heading: heading
                nested_content: content {
                    __typename
                    ... on BardText { type text }
                }
            }
            ... on Set_Content_Image {
                id
                type
                image { ...AssetFields }
            }
            ... on Set_Content_MetaBlock {
                id
                type
                title
                content
            }
            ... on Set_Content_Spacer {
                id
                type
                spacer
            }
            ... on Set_Content_Line {
                id
                type
                line
            }
            ... on Set_Content_Video {
                id
                type
                video
            }
            ... on Set_Content_RedNote {
                id
                type
                content
            }
            ... on Set_Content_DlfHero {
                id
                type
                eyebrow
                heading
                heading_level { value label }
                body
                image { ...AssetFields }
                primary_label
                primary_link { url title }
                secondary_label
                secondary_link { url title }
                image_position { value label }
            }
            ... on Set_Content_DlfMediaText {
                id
                type
                eyebrow
                heading
                body
                image { ...AssetFields }
                image_position { value label }
                tone { value label }
                link_label
                link { url title }
            }
            ... on Set_Content_DlfFeatureGrid {
                id
                type
                eyebrow
                heading
                introduction
                columns { value label }
                features {
                    id
                    icon { ...AssetFields }
                    heading
                    body(format: "html")
                    link_label
                    link { url title }
                }
            }
            ... on Set_Content_DlfCardGrid {
                id
                type
                eyebrow
                heading
                introduction
                cards {
                    id
                    image { ...AssetFields }
                    eyebrow
                    heading
                    body(format: "html")
                    link_label
                    link { url title }
                }
            }
            ... on Set_Content_DlfLogoCloud {
                id
                type
                heading
                logos {
                    id
                    logo { ...AssetFields }
                    name
                    link { url title }
                }
            }
            ... on Set_Content_DlfCtaPanel {
                id
                type
                eyebrow
                heading
                body
                primary_label
                primary_link { url title }
                secondary_label
                secondary_link { url title }
                tone { value label }
            }
            ... on Set_Content_DlfStats {
                id
                type
                eyebrow
                heading
                introduction
                stats {
                    id
                    value
                    label
                    context
                }
            }
            ... on Set_Content_DlfPricing {
                id
                type
                eyebrow
                heading
                introduction
                plans {
                    id
                    name
                    price
                    suffix
                    description(format: "html")
                    features { id feature }
                    button_label
                    button_link { url title }
                    featured
                }
            }
            ... on Set_Content_DlfQuote {
                id
                type
                quote
                name
                role
                image { ...AssetFields }
                tone { value label }
            }
        }

        fragment LeftContentFields on Sets_Content_Left {
            __typename
            ... on BardText { type text }
            ... on Set_Content_Left_MetaBlock { id type title content }
            ... on Set_Content_Left_Image { id type image { ...AssetFields } }
            ... on Set_Content_Left_Spacer { id type spacer }
            ... on Set_Content_Left_Line { id type line }
            ... on Set_Content_Left_Video { id type video }
            ... on Set_Content_Left_CtaButton { id type label link }
        }

        fragment RightContentFields on Sets_Content_Right {
            __typename
            ... on BardText { type text }
            ... on Set_Content_Right_MetaBlock { id type title content }
            ... on Set_Content_Right_Image { id type image { ...AssetFields } }
            ... on Set_Content_Right_Spacer { id type spacer }
            ... on Set_Content_Right_Line { id type line }
            ... on Set_Content_Right_Video { id type video }
        }

        fragment CallToActionFields on EntryInterface {
            __typename
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
            ... on Asset_Assets { alt }
            ... on Asset_Board { alt }
            ... on Asset_Cases { alt }
            ... on Asset_Members { alt }
        }
        GRAPHQL;

    public function __construct(private GraphqlClient $client) {}

    public function findByUri(string $uri): array
    {
        return $this->client->query(self::PUBLIC_PAGE, [
            'site' => $this->siteHandle(),
            'uri' => $uri,
            'foundingPartnerFilter' => [
                'founding_partner' => ['equals' => true],
            ],
            'landingCaseFilter' => [
                'slug' => ['in' => self::LANDING_CASE_SLUGS],
            ],
        ]);
    }

    private function siteHandle(): string
    {
        $site = Site::default();

        if (! $site instanceof StatamicSite) {
            throw new LogicException('Statamic does not have a default site.');
        }

        $handle = $site->handle();

        if (! is_string($handle)) {
            throw new LogicException('The default Statamic site does not have a valid handle.');
        }

        return $handle;
    }
}
