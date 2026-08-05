import { type ReactNode } from "react";

import { communityFooterCta } from "@/components/community-react/CommunityFooterCtaAdapter";
import { footerCta as editorialFooterCta } from "@/components/editorial-react/FooterCtaAdapter";
import { footerCta as publicFooterCta } from "@/components/forms-react/FooterCtaAdapter";
import { SmartLinkEnhancer } from "@/components/ui/SmartLink";

import { PersistentSiteLayoutContext, SiteShell, type SiteShellProps } from "./SiteShell";

type CommunityPage =
    | App.Data.Community.CaseData
    | App.Data.Community.CaseIndexData
    | App.Data.Community.InternshipData
    | App.Data.Community.InternshipIndexData
    | App.Data.Community.LarabellesData
    | App.Data.Community.MemberData
    | App.Data.Community.MemberIndexData;

type EditorialPage =
    | App.Data.Editorial.EventData
    | App.Data.Editorial.InsightData
    | App.Data.Editorial.KnowledgeData
    | App.Data.Editorial.PodcastData;

type PersistentPageProps = {
    acquisition?: App.Data.Forms.AcquisitionPageData;
    community?: CommunityPage;
    editorial?: EditorialPage;
    error?: { status: number };
    page?: App.Data.Pages.HomePageData | App.Data.PublicPages.PublicPageData;
    site: App.Data.SiteShell.SiteShellData;
};

type PersistentSiteLayoutProps = PersistentPageProps & {
    children: ReactNode;
};

type ResolvedSiteLayoutProps = Pick<SiteShellProps, "data" | "footerCta" | "pageSlug">;

function communityPage(value: CommunityPage): App.Data.Community.PageData | null {
    return "page" in value ? value.page : null;
}

function communityPageSlug(value: CommunityPage): string {
    return "page" in value ? value.page.slug : value.slug;
}

function resolveEditorialFooterCta(editorial: EditorialPage): SiteShellProps["footerCta"] {
    if ("timeStart" in editorial) {
        return null;
    }

    if ("authors" in editorial) {
        return editorialFooterCta(editorial.callToAction);
    }

    return editorial.callToAction ? editorialFooterCta(editorial.callToAction) : undefined;
}

export function resolveSiteLayoutProps(props: PersistentPageProps): ResolvedSiteLayoutProps {
    if (props.error) {
        return {
            data: props.site,
            footerCta: null,
            pageSlug: `error-${props.error.status}`,
        };
    }

    if (props.page) {
        return {
            data: props.site,
            footerCta:
                "footerCta" in props.page
                    ? props.page.footerCta
                    : publicFooterCta(props.page.callToAction),
            pageSlug: props.page.slug,
        };
    }

    if (props.acquisition) {
        return {
            data: props.site,
            footerCta: publicFooterCta(props.acquisition.page.callToAction),
            pageSlug: props.acquisition.page.slug,
        };
    }

    if (props.community) {
        const page = communityPage(props.community);

        return {
            data: props.site,
            footerCta: page?.callToAction ? communityFooterCta(page.callToAction) : undefined,
            pageSlug: communityPageSlug(props.community),
        };
    }

    if (props.editorial) {
        return {
            data: props.site,
            footerCta: resolveEditorialFooterCta(props.editorial),
            pageSlug: props.editorial.slug,
        };
    }

    throw new Error("The Inertia page does not expose persistent site layout data.");
}

export function PersistentSiteLayout({ children, ...props }: PersistentSiteLayoutProps) {
    const layout = resolveSiteLayoutProps(props);

    return (
        <PersistentSiteLayoutContext value>
            <SmartLinkEnhancer />
            <SiteShell {...layout}>{children}</SiteShell>
        </PersistentSiteLayoutContext>
    );
}
