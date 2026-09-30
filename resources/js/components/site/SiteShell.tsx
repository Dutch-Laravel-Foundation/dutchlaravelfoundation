import { createContext, type ReactNode, useContext, useRef } from "react";

import { useFooterCtaStage } from "@/hooks/useFooterCtaStage";
import { useSiteDocument } from "@/hooks/useSiteDocument";
import { useTrackingConsent } from "@/hooks/useTrackingConsent";

import { Footer } from "./Footer";
import { Header } from "./Header";
import { SiteMetadataHead } from "./SiteMetadataHead";
import { TrackingConsent } from "./TrackingConsent";
import { type FooterCta, type SiteShellData } from "./types";

export type SiteShellProps = {
    children: ReactNode;
    data: SiteShellData;
    environment?: string;
    footerCta?: FooterCta | null;
    pageSlug: string;
};

export const PersistentSiteLayoutContext = createContext(false);

export function SiteShell({ children, data, environment, footerCta, pageSlug }: SiteShellProps) {
    const bannerRef = useRef<HTMLElement>(null);
    const consent = useTrackingConsent({ bannerRef });
    const cta = footerCta === undefined ? data.defaultCta : footerCta;

    useSiteDocument(pageSlug, environment);
    useFooterCtaStage(Boolean(cta));

    return (
        <>
            <SiteMetadataHead />

            <a className="dlf-skip-link" href="#main-content">
                Naar de inhoud
            </a>

            <Header navigation={data.navigation.main} siteName={data.organization.site.name} />

            <div id="main-content" className={`frontend relative page-${pageSlug}`} tabIndex={-1}>
                {children}
            </div>

            <Footer
                cta={cta}
                legalNavigation={data.navigation.legal}
                members={data.footer.members}
                newsletterForm={data.newsletter}
                pageSlug={pageSlug}
                settingsHidden={consent.settingsHidden}
                siteName={data.organization.site.name}
                socials={data.footer.socials}
            />

            <TrackingConsent
                bannerRef={bannerRef}
                onAccept={consent.accept}
                onReject={consent.reject}
                rendered={consent.rendered}
                visualState={consent.visualState}
            />
        </>
    );
}

export function SiteLayout(props: SiteShellProps) {
    const persistent = useContext(PersistentSiteLayoutContext);

    if (persistent) {
        return <>{props.children}</>;
    }

    return <SiteShell {...props} />;
}
