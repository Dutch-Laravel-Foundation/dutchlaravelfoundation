<?php

declare(strict_types=1);

namespace App\Content\SiteShell;

use App\Content\Graphql\GraphqlClient;
use LogicException;
use Statamic\Facades\Site;
use Statamic\Sites\Site as StatamicSite;

final readonly class StatamicSiteShellRepository implements SiteShellRepository
{
    private const string DEFAULT_CTA_ID = 'ee5d33de-9a24-4860-92dd-3503740b62af';

    private const string SITE_SHELL_QUERY = <<<'GRAPHQL'
        query SiteShell($site: String!, $defaultCtaId: String!) {
            organization: globalSet(handle: "dlf", site: $site) {
                ... on GlobalSet_Dlf {
                    title
                    address
                    zipcode
                    city
                    phone
                    email
                    coc
                    logo {
                        id
                        url
                        permalink
                        width
                        height
                    }
                    site {
                        handle
                        name
                        locale
                        short_locale
                        url
                    }
                }
            }

            seo: globalSet(handle: "seo", site: $site) {
                ... on GlobalSet_Seo {
                    meta_title
                    meta_description
                    meta_keywords
                }
            }

            openGraph: globalSet(handle: "opengraph", site: $site) {
                ... on GlobalSet_Opengraph {
                    opengraph_image {
                        id
                        url
                        permalink
                        width
                        height
                    }
                }
            }

            mainNavigation: nav(handle: "main") {
                handle
                tree(site: $site) {
                    page {
                        ...MainNavigationPage
                    }
                    children {
                        page {
                            ...MainNavigationPage
                        }
                        children {
                            page {
                                ...MainNavigationPage
                            }
                        }
                    }
                }
            }

            legalNavigation: nav(handle: "legal") {
                handle
                tree(site: $site) {
                    page {
                        id
                        title
                        url
                        permalink
                        ... on NavPage_Legal {
                            page {
                                id
                                title
                                slug
                                url
                                permalink
                            }
                        }
                    }
                    children {
                        page {
                            id
                            title
                            url
                            permalink
                            ... on NavPage_Legal {
                                page {
                                    id
                                    title
                                    slug
                                    url
                                    permalink
                                }
                            }
                        }
                    }
                }
            }

            members: entries(collection: ["members"], sort: ["title"], site: $site, limit: 500) {
                data {
                    ... on Entry_Members_Members {
                        id
                        title
                        slug
                        url
                    }
                }
            }

            socials: entries(collection: ["socials"], sort: ["title"], site: $site, limit: 100) {
                data {
                    ... on Entry_Socials_Socials {
                        id
                        title
                        link {
                            url
                            title
                        }
                        icon {
                            id
                            url
                            permalink
                            width
                            height
                        }
                    }
                }
            }

            defaultCta: entry(id: $defaultCtaId, site: $site) {
                ... on Entry_Cta_Cta {
                    id
                    title
                    description
                    eyebrow
                    benefits
                    link {
                        url
                        title
                    }
                    link_2 {
                        url
                        title
                    }
                    theme {
                        value
                        label
                    }
                    button_style {
                        value
                        label
                    }
                    button_style_2 {
                        value
                        label
                    }
                    button_text
                    button_text_2
                }
            }

            newsletter: form(handle: "newsletter") {
                handle
                title
                honeypot
                rules
                fields {
                    handle
                    type
                    display
                    instructions
                    width
                    if
                    unless
                    config
                }
            }
        }

        fragment MainNavigationPage on PageInterface {
            id
            title
            url
            permalink
            ... on EntryInterface {
                slug
            }
        }
        GRAPHQL;

    public function __construct(private GraphqlClient $client) {}

    public function fetch(): array
    {
        return $this->client->query(self::SITE_SHELL_QUERY, [
            'site' => $this->siteHandle(),
            'defaultCtaId' => self::DEFAULT_CTA_ID,
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
