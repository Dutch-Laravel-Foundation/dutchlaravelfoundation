declare namespace App {
    namespace Data {
        export type SeoData = {
            readonly title: string | null;
            readonly description: string | null;
            readonly keywords: string | null;
        };
        namespace Community {
            export type AssetData = {
                readonly id: string;
                readonly url: string | null;
                readonly permalink: string | null;
                readonly path: string;
                readonly extension: string;
                readonly width: number | null;
                readonly height: number | null;
                readonly focusCss: string | null;
                readonly alt: string | null;
            };
            export type CallToActionData = {
                readonly id: string;
                readonly title: string;
                readonly descriptionHtml: string | null;
                readonly eyebrow: string | null;
                readonly benefits: string[];
                readonly primaryLink: App.Data.Community.LinkData | null;
                readonly secondaryLink: App.Data.Community.LinkData | null;
                readonly theme: string | null;
                readonly buttonText: string | null;
                readonly buttonStyle: string | null;
                readonly secondaryButtonText: string | null;
                readonly secondaryButtonStyle: string | null;
            };
            export type CaseCardData = {
                readonly id: string;
                readonly title: string;
                readonly displayTitle: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly date: string | null;
                readonly introductionHtml: string | null;
                readonly featuredImage: App.Data.Community.AssetData | null;
                readonly member: App.Data.Community.MemberSummaryData | null;
                readonly client: App.Data.Community.ClientData | null;
            };
            export type CaseData = {
                readonly id: string;
                readonly title: string;
                readonly displayTitle: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly date: string | null;
                readonly introductionHtml: string | null;
                readonly featuredImage: App.Data.Community.AssetData | null;
                readonly content: App.Data.Community.ContentBlockData[];
                readonly member: App.Data.Community.MemberSummaryData | null;
                readonly client: App.Data.Community.ClientData | null;
                readonly seo: App.Data.SeoData;
            };
            export type CaseIndexData = {
                readonly page: App.Data.Community.PageData;
                readonly items: App.Data.Community.CaseCardData[];
            };
            export type ClientData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly logo: App.Data.Community.AssetData | null;
            };
            export type ContentBlockData = {
                readonly kind: string;
                readonly type: string;
                readonly id: string | null;
                readonly html: string | null;
                readonly value: string | null;
                readonly asset: App.Data.Community.AssetData | null;
                readonly columns: App.Data.Community.ContentColumnsData | null;
            };
            export type ContentColumnsData = {
                readonly headingHtml: string | null;
                readonly left: App.Data.Community.ContentBlockData[];
                readonly right: App.Data.Community.ContentBlockData[];
            };
            export type InternshipCardData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly descriptionHtml: string | null;
                readonly applyUrl: string | null;
                readonly applyLabel: string | null;
                readonly member: App.Data.Community.MemberSummaryData;
            };
            export type InternshipContactData = {
                readonly name: string | null;
                readonly email: string | null;
                readonly phone: string | null;
            };
            export type InternshipData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly descriptionHtml: string | null;
                readonly applyUrl: string | null;
                readonly applyLabel: string | null;
                readonly member: App.Data.Community.MemberSummaryData;
                readonly seo: App.Data.SeoData;
            };
            export type InternshipFiltersData = {
                readonly provinces: string[];
                readonly hasSbb: boolean;
            };
            export type InternshipIndexData = {
                readonly page: App.Data.Community.PageData;
                readonly items: App.Data.Community.InternshipCardData[];
                readonly filters: App.Data.Community.InternshipFiltersData;
            };
            export type LarabellesData = {
                readonly page: App.Data.Community.PageData;
            };
            export type LinkData = {
                readonly url: string;
                readonly title: string | null;
            };
            export type MemberData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly descriptionHtml: string | null;
                readonly logo: App.Data.Community.AssetData | null;
                readonly foundingPartner: boolean;
                readonly type: string | null;
                readonly employeeRange: string | null;
                readonly sbb: boolean;
                readonly city: string | null;
                readonly province: string | null;
                readonly email: string | null;
                readonly phone: string | null;
                readonly website: string | null;
                readonly recruitmentWebsite: string | null;
                readonly video: string | null;
                readonly internshipContact: App.Data.Community.InternshipContactData | null;
                readonly seo: App.Data.SeoData;
                readonly internships: App.Data.Community.InternshipCardData[];
                readonly cases: App.Data.Community.CaseCardData[];
            };
            export type MemberFiltersData = {
                readonly types: string[];
                readonly employeeRanges: string[];
                readonly provinces: string[];
            };
            export type MemberIndexData = {
                readonly page: App.Data.Community.PageData;
                readonly items: App.Data.Community.MemberSummaryData[];
                readonly filters: App.Data.Community.MemberFiltersData;
            };
            export type MemberSummaryData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly logo: App.Data.Community.AssetData | null;
                readonly type: string | null;
                readonly employeeRange: string | null;
                readonly sbb: boolean;
                readonly city: string | null;
                readonly province: string | null;
                readonly website: string | null;
                readonly internshipContact: App.Data.Community.InternshipContactData | null;
            };
            export type PageData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly template: string | null;
                readonly content: App.Data.Community.ContentBlockData[];
                readonly callToAction: App.Data.Community.CallToActionData | null;
                readonly seo: App.Data.SeoData;
            };
        }
        namespace Editorial {
            export type ArticleCardData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly category: string | null;
                readonly date: string | null;
                readonly introduction: string | null;
                readonly featuredImage: App.Data.Editorial.AssetData | null;
            };
            export type ArticleIndexData = {
                readonly items: App.Data.Editorial.ArticleCardData[];
                readonly pagination: App.Data.Editorial.PaginationData;
            };
            export type AssetData = {
                readonly id: string;
                readonly url: string | null;
                readonly permalink: string | null;
                readonly path: string;
                readonly extension: string;
                readonly width: number | null;
                readonly height: number | null;
                readonly focusCss: string | null;
                readonly alt: string | null;
            };
            export type AuthorData = {
                readonly id: string | null;
                readonly name: string;
                readonly role: string | null;
                readonly bio: string | null;
                readonly image: App.Data.Editorial.AssetData | null;
                readonly imageUrl: string | null;
                readonly profileUrl: string | null;
                readonly linkedinUrl: string | null;
                readonly websiteUrl: string | null;
            };
            export type CallToActionData = {
                readonly id: string;
                readonly title: string;
                readonly description: string | null;
                readonly eyebrow: string | null;
                readonly benefits: string[];
                readonly link: App.Data.Editorial.LinkData | null;
                readonly secondaryLink: App.Data.Editorial.LinkData | null;
                readonly theme: string | null;
                readonly buttonText: string | null;
                readonly buttonStyle: string | null;
                readonly secondaryButtonText: string | null;
                readonly secondaryButtonStyle: string | null;
            };
            export type ContentBlockData = {
                readonly kind: string;
                readonly type: string;
                readonly id: string | null;
                readonly html: string | null;
                readonly value: string | null;
                readonly asset: App.Data.Editorial.AssetData | null;
            };
            export type EventCardData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly type: string | null;
                readonly dateStart: string | null;
                readonly introduction: string | null;
                readonly featuredImage: App.Data.Editorial.AssetData | null;
            };
            export type EventData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly type: string | null;
                readonly introduction: string | null;
                readonly featuredImage: App.Data.Editorial.AssetData | null;
                readonly dateStart: string | null;
                readonly timeStart: string | null;
                readonly timeEnd: string | null;
                readonly location: string | null;
                readonly address: string | null;
                readonly signupLink: string | null;
                readonly content: App.Data.Editorial.ContentBlockData[];
                readonly seo: App.Data.SeoData;
            };
            export type EventIndexData = {
                readonly upcoming: App.Data.Editorial.EventCardData[];
                readonly past: App.Data.Editorial.EventCardData[];
                readonly pagination: App.Data.Editorial.PaginationData;
            };
            export type InsightData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly category: string | null;
                readonly date: string | null;
                readonly introduction: string | null;
                readonly featuredImage: App.Data.Editorial.AssetData | null;
                readonly content: App.Data.Editorial.ContentBlockData[];
                readonly author: App.Data.Editorial.AuthorData | null;
                readonly callToAction: App.Data.Editorial.CallToActionData | null;
                readonly seo: App.Data.SeoData;
            };
            export type KnowledgeData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly category: string | null;
                readonly date: string | null;
                readonly introduction: string | null;
                readonly featuredImage: App.Data.Editorial.AssetData | null;
                readonly contentHtml: string | null;
                readonly authors: App.Data.Editorial.AuthorData[];
                readonly callToAction: App.Data.Editorial.CallToActionData | null;
                readonly seo: App.Data.SeoData;
            };
            export type LinkData = {
                readonly url: string | null;
                readonly title: string | null;
            };
            export type PaginationData = {
                readonly total: number;
                readonly perPage: number;
                readonly currentPage: number;
                readonly from: number | null;
                readonly to: number | null;
                readonly lastPage: number;
                readonly hasMorePages: boolean;
            };
            export type PodcastCardData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly summary: string;
                readonly thumbnailUrl: string;
                readonly publishedAt: string;
            };
            export type PodcastData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly summary: string;
                readonly descriptionHtml: string;
                readonly videoUrl: string;
                readonly spotifyUrl: string;
                readonly thumbnailUrl: string;
                readonly transcriptHtml: string;
                readonly publishedAt: string;
                readonly callToAction: App.Data.Editorial.CallToActionData | null;
                readonly seo: App.Data.SeoData;
            };
            export type PodcastIndexData = {
                readonly items: App.Data.Editorial.PodcastCardData[];
                readonly pagination: App.Data.Editorial.PaginationData;
            };
        }
        namespace Forms {
            export type AcquisitionPageData = {
                readonly page: App.Data.PublicPages.PublicPageData;
                readonly form: App.Data.Forms.FormDefinitionData | null;
                readonly submission: App.Data.Forms.FormSubmissionStateData;
            };
            export type FormDefinitionData = {
                readonly handle: string;
                readonly title: string;
                readonly action: string;
                readonly honeypot: string | null;
                readonly rules: Record<string, string[]>;
                readonly fields: App.Data.Forms.FormFieldData[];
            };
            export type FormFieldData = {
                readonly handle: string;
                readonly type: string;
                readonly display: string;
                readonly instructions: string | null;
                readonly width: number | null;
                readonly ifConditions: Record<string, any>;
                readonly unlessConditions: Record<string, any>;
                readonly config: Record<string, any>;
            };
            export type FormSubmissionStateData = {
                readonly success: boolean;
                readonly errors: Record<string, string>;
                readonly old: Record<string, any>;
            };
        }
        namespace Home {
            export type AssetData = {
                readonly id: string;
                readonly url: string;
                readonly permalink: string | null;
                readonly path: string;
                readonly extension: string;
                readonly width: number | null;
                readonly height: number | null;
                readonly focusCss: string | null;
                readonly alt: string | null;
            };
            export type ClientData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly logo: App.Data.Home.AssetData | null;
            };
            export type ContentCardData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly category: string | null;
                readonly introduction: string | null;
                readonly featuredImage: App.Data.Home.AssetData | null;
            };
            export type HomeData = {
                readonly latestInsight: App.Data.Home.ContentCardData | null;
                readonly latestKnowledge: App.Data.Home.ContentCardData | null;
                readonly highlightedInsight: App.Data.Home.ContentCardData | null;
                readonly partners: App.Data.Home.PartnerData[];
                readonly clients: App.Data.Home.ClientData[];
            };
            export type PartnerData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly visible: boolean;
                readonly logo: App.Data.Home.AssetData | null;
            };
        }
        namespace Pages {
            export type HomePageData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly uri: string;
                readonly headerTitle: string | null;
                readonly headerContent: string | null;
                readonly menuTheme: string | null;
                readonly footerCta: App.Data.SiteShell.CtaData | null;
                readonly seo: App.Data.SeoData;
            };
        }
        namespace PublicPages {
            export type ActionData = {
                readonly label: string;
                readonly link: App.Data.PublicPages.LinkData;
            };
            export type AssetData = {
                readonly id: string;
                readonly url: string | null;
                readonly permalink: string | null;
                readonly path: string;
                readonly extension: string;
                readonly width: number | null;
                readonly height: number | null;
                readonly focusCss: string | null;
                readonly alt: string | null;
            };
            export type BoardMemberData = {
                readonly id: string;
                readonly name: string;
                readonly function: string | null;
                readonly photo: App.Data.PublicPages.AssetData | null;
            };
            export type CallToActionData = {
                readonly id: string;
                readonly title: string;
                readonly descriptionHtml: string | null;
                readonly eyebrow: string | null;
                readonly benefits: string[];
                readonly link: App.Data.PublicPages.LinkData | null;
                readonly secondaryLink: App.Data.PublicPages.LinkData | null;
                readonly theme: string | null;
                readonly buttonText: string | null;
                readonly buttonStyle: string | null;
                readonly secondaryButtonText: string | null;
                readonly secondaryButtonStyle: string | null;
            };
            export type CardData = {
                readonly id: string | null;
                readonly eyebrow: string | null;
                readonly heading: string;
                readonly bodyHtml: string | null;
                readonly image: App.Data.PublicPages.AssetData | null;
                readonly action: App.Data.PublicPages.ActionData | null;
            };
            export type ContentBlockData = {
                readonly kind: string;
                readonly type: string;
                readonly id: string | null;
                readonly html: string | null;
                readonly headingHtml: string | null;
                readonly left: App.Data.PublicPages.ContentBlockData[];
                readonly right: App.Data.PublicPages.ContentBlockData[];
                readonly content: App.Data.PublicPages.ContentBlockData[];
                readonly asset: App.Data.PublicPages.AssetData | null;
                readonly title: string | null;
                readonly text: string | null;
                readonly value: string | null;
                readonly label: string | null;
                readonly link: App.Data.PublicPages.LinkData | null;
                readonly eyebrow: string | null;
                readonly heading: string | null;
                readonly bodyHtml: string | null;
                readonly introductionHtml: string | null;
                readonly columns: string | null;
                readonly headingLevel: string | null;
                readonly imagePosition: string | null;
                readonly tone: string | null;
                readonly primaryAction: App.Data.PublicPages.ActionData | null;
                readonly secondaryAction: App.Data.PublicPages.ActionData | null;
                readonly features: App.Data.PublicPages.FeatureData[];
                readonly cards: App.Data.PublicPages.CardData[];
                readonly stats: App.Data.PublicPages.StatData[];
                readonly logos: App.Data.PublicPages.LogoData[];
                readonly plans: App.Data.PublicPages.PricingPlanData[];
                readonly quote: string | null;
                readonly attributionName: string | null;
                readonly attributionRole: string | null;
            };
            export type FeatureData = {
                readonly id: string | null;
                readonly heading: string;
                readonly bodyHtml: string | null;
                readonly icon: App.Data.PublicPages.AssetData | null;
                readonly action: App.Data.PublicPages.ActionData | null;
            };
            export type FoundingPartnerData = {
                readonly id: string;
                readonly name: string;
                readonly slug: string;
                readonly url: string | null;
                readonly city: string | null;
                readonly province: string | null;
                readonly logo: App.Data.PublicPages.AssetData | null;
            };
            export type LandingCaseData = {
                readonly id: string;
                readonly title: string;
                readonly longTitle: string | null;
                readonly slug: string;
                readonly url: string | null;
                readonly introductionHtml: string | null;
                readonly featuredImage: App.Data.PublicPages.AssetData | null;
            };
            export type LinkData = {
                readonly url: string;
                readonly title: string | null;
            };
            export type LogoData = {
                readonly id: string | null;
                readonly name: string;
                readonly asset: App.Data.PublicPages.AssetData | null;
                readonly link: App.Data.PublicPages.LinkData | null;
            };
            export type PricingPlanData = {
                readonly id: string | null;
                readonly name: string;
                readonly price: string | null;
                readonly suffix: string | null;
                readonly descriptionHtml: string | null;
                readonly features: string[];
                readonly action: App.Data.PublicPages.ActionData | null;
                readonly featured: boolean;
            };
            export type PublicPageData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
                readonly uri: string | null;
                readonly template: string;
                readonly menuTheme: string | null;
                readonly headerTitle: string | null;
                readonly headerContentHtml: string | null;
                readonly seo: App.Data.SeoData;
                readonly callToAction: App.Data.PublicPages.CallToActionData | null;
                readonly content: App.Data.PublicPages.ContentBlockData[];
                readonly support: App.Data.PublicPages.PublicPageSupportData;
            };
            export type PublicPageSupportData = {
                readonly memberCount: number;
                readonly board: App.Data.PublicPages.BoardMemberData[];
                readonly foundingPartners: App.Data.PublicPages.FoundingPartnerData[];
                readonly generalLandingCases: App.Data.PublicPages.LandingCaseData[];
                readonly frameworkLandingCases: App.Data.PublicPages.LandingCaseData[];
            };
            export type StatData = {
                readonly id: string | null;
                readonly value: string;
                readonly label: string;
                readonly context: string | null;
            };
        }
        namespace SiteShell {
            export type AssetData = {
                readonly id: string;
                readonly url: string | null;
                readonly permalink: string | null;
                readonly width: number | null;
                readonly height: number | null;
            };
            export type CtaData = {
                readonly id: string;
                readonly title: string;
                readonly description: string | null;
                readonly eyebrow: string | null;
                readonly benefits: string[];
                readonly link: App.Data.SiteShell.LinkData | null;
                readonly secondaryLink: App.Data.SiteShell.LinkData | null;
                readonly theme: App.Data.SiteShell.LabeledValueData | null;
                readonly buttonStyle: App.Data.SiteShell.LabeledValueData | null;
                readonly secondaryButtonStyle: App.Data.SiteShell.LabeledValueData | null;
                readonly buttonText: string | null;
                readonly secondaryButtonText: string | null;
            };
            export type FooterData = {
                readonly members: App.Data.SiteShell.MemberData[];
                readonly socials: App.Data.SiteShell.SocialData[];
            };
            export type LabeledValueData = {
                readonly value: string | null;
                readonly label: string | null;
            };
            export type LinkData = {
                readonly url: string | null;
                readonly title: string | null;
            };
            export type MemberData = {
                readonly id: string;
                readonly title: string;
                readonly slug: string;
                readonly url: string | null;
            };
            export type NavigationData = {
                readonly main: App.Data.SiteShell.NavigationItemData[];
                readonly legal: App.Data.SiteShell.NavigationItemData[];
            };
            export type NavigationItemData = {
                readonly id: string;
                readonly title: string | null;
                readonly slug: string | null;
                readonly url: string | null;
                readonly permalink: string | null;
                readonly isCurrent: boolean;
                readonly isAncestor: boolean;
                readonly children: App.Data.SiteShell.NavigationItemData[];
            };
            export type NewsletterFieldData = {
                readonly handle: string;
                readonly type: string;
                readonly display: string;
                readonly instructions: string | null;
                readonly width: number | null;
                readonly ifConditions: Record<number | string, any>;
                readonly unlessConditions: Record<number | string, any>;
                readonly config: Record<number | string, any>;
            };
            export type NewsletterFormData = {
                readonly handle: string;
                readonly title: string;
                readonly honeypot: string | null;
                readonly rules: Record<number | string, any>;
                readonly fields: App.Data.SiteShell.NewsletterFieldData[];
            };
            export type OpenGraphData = {
                readonly image: App.Data.SiteShell.AssetData | null;
            };
            export type OrganizationData = {
                readonly title: string;
                readonly address: string | null;
                readonly zipcode: string | null;
                readonly city: string | null;
                readonly phone: string | null;
                readonly email: string | null;
                readonly coc: string | null;
                readonly logo: App.Data.SiteShell.AssetData | null;
                readonly site: App.Data.SiteShell.SiteData;
            };
            export type SiteData = {
                readonly handle: string;
                readonly name: string;
                readonly locale: string;
                readonly shortLocale: string;
                readonly url: string;
            };
            export type SiteShellData = {
                readonly organization: App.Data.SiteShell.OrganizationData;
                readonly seo: App.Data.SeoData;
                readonly openGraph: App.Data.SiteShell.OpenGraphData;
                readonly navigation: App.Data.SiteShell.NavigationData;
                readonly footer: App.Data.SiteShell.FooterData;
                readonly defaultCta: App.Data.SiteShell.CtaData | null;
                readonly newsletter: App.Data.SiteShell.NewsletterFormData | null;
            };
            export type SocialData = {
                readonly id: string;
                readonly title: string;
                readonly link: App.Data.SiteShell.LinkData | null;
                readonly icon: App.Data.SiteShell.AssetData | null;
            };
        }
    }
}
declare namespace Illuminate {
    export type CursorPaginator<TKey, TValue> = {
        data: TKey extends string ? Record<TKey, TValue> : TValue[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
        meta: {
            path: string;
            per_page: number;
            next_cursor: string | null;
            next_page_url: string | null;
            prev_cursor: string | null;
            prev_page_url: string | null;
        };
    };
    export type CursorPaginatorInterface<TKey, TValue> = Illuminate.CursorPaginator<TKey, TValue>;
    export type LengthAwarePaginator<TKey, TValue> = {
        data: TKey extends string ? Record<TKey, TValue> : TValue[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
        meta: {
            total: number;
            current_page: number;
            first_page_url: string;
            from: number | null;
            last_page: number;
            last_page_url: string;
            next_page_url: string | null;
            path: string;
            per_page: number;
            prev_page_url: string | null;
            to: number | null;
        };
    };
    export type LengthAwarePaginatorInterface<TKey, TValue> = Illuminate.LengthAwarePaginator<
        TKey,
        TValue
    >;
}
declare namespace Spatie {
    namespace LaravelData {
        export type CursorPaginatedDataCollection<TKey, TValue> = Illuminate.CursorPaginator<
            TKey,
            TValue
        >;
        export type PaginatedDataCollection<TKey, TValue> = Illuminate.LengthAwarePaginator<
            TKey,
            TValue
        >;
    }
}
