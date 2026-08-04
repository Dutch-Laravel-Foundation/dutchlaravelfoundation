import { type ReactNode } from "react";

import { SiteLayout, type FooterCta } from "@/components/site";

type PublicPageFrameProps = {
    children: ReactNode;
    page: App.Data.PublicPages.PublicPageData;
    site: App.Data.SiteShell.SiteShellData;
};

function footerCta(page: App.Data.PublicPages.PublicPageData): FooterCta | null | undefined {
    const cta = page.callToAction;

    if (!cta) {
        return null;
    }

    return {
        id: cta.id,
        title: cta.title,
        description: cta.descriptionHtml,
        eyebrow: cta.eyebrow,
        benefits: cta.benefits,
        link: cta.link ? { url: cta.link.url, title: cta.link.title } : null,
        secondaryLink: cta.secondaryLink
            ? { url: cta.secondaryLink.url, title: cta.secondaryLink.title }
            : null,
        theme: cta.theme ? { value: cta.theme, label: null } : null,
        buttonStyle: cta.buttonStyle ? { value: cta.buttonStyle, label: null } : null,
        secondaryButtonStyle: cta.secondaryButtonStyle
            ? { value: cta.secondaryButtonStyle, label: null }
            : null,
        buttonText: cta.buttonText,
        secondaryButtonText: cta.secondaryButtonText,
    };
}

export function PublicPageFrame({ children, page, site }: PublicPageFrameProps) {
    return (
        <SiteLayout data={site} footerCta={footerCta(page)} pageSlug={page.slug}>
            {children}
        </SiteLayout>
    );
}
