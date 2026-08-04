import { ContentBlocks, PublicArticleBody, PublicPageFrame } from "@/components/public-pages-react";
import { SmartLink } from "@/components/ui/SmartLink";

type Props = { page: App.Data.PublicPages.PublicPageData; site: App.Data.SiteShell.SiteShellData };

export default function PrivacyStatement({ page, site }: Props) {
    const organization = site.organization;

    return (
        <PublicPageFrame page={page} site={site}>
            <div className="dlf-public-page dlf-public-page--legal editorial-article">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <header className="dlf-public-page__hero dlf-divider-section">
                        <span className="editorial-eyebrow">Juridisch</span>
                        <h1>{page.title}</h1>
                    </header>
                    <PublicArticleBody label="In deze verklaring">
                        <ContentBlocks blocks={page.content} />
                        <aside
                            className="dlf-public-contact-card dlf-block"
                            aria-label="Contactgegevens Dutch Laravel Foundation"
                        >
                            <span className="editorial-eyebrow">Contact</span>
                            <h2>{organization.title}</h2>
                            <p>
                                {organization.address}
                                <br />
                                {organization.zipcode} {organization.city}
                            </p>
                            <p>
                                {organization.phone ? (
                                    <>
                                        <SmartLink
                                            href={`tel:${organization.phone.replace(/[^+0-9]/g, "")}`}
                                        >
                                            {organization.phone}
                                        </SmartLink>
                                        <br />
                                    </>
                                ) : null}
                                {organization.email ? (
                                    <SmartLink href={`mailto:${organization.email}`}>
                                        {organization.email}
                                    </SmartLink>
                                ) : null}
                            </p>
                            {organization.coc ? <p>KVK: {organization.coc}</p> : null}
                        </aside>
                    </PublicArticleBody>
                </div>
            </div>
        </PublicPageFrame>
    );
}
