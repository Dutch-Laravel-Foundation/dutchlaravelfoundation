import { DlfButtonLink } from "@/components/ui/DlfButton";
import { ContentBlocks, PublicPageFrame } from "@/components/public-pages-react";

type Props = { page: App.Data.PublicPages.PublicPageData; site: App.Data.SiteShell.SiteShellData };

export default function Newsletter({ page, site }: Props) {
    return (
        <PublicPageFrame page={page} site={site}>
            <div className="dlf-public-page dlf-public-page--success editorial-article">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <section className="dlf-divider-section">
                        <div className="dlf-public-success">
                            <span className="dlf-public-success__mark" aria-hidden="true">
                                ✓
                            </span>
                            <span className="editorial-eyebrow">Nieuwsbrief</span>
                            <div className="editorial-article__prose dlf-public-page__prose">
                                <ContentBlocks blocks={page.content} />
                            </div>
                            <div className="dlf-public-actions">
                                <DlfButtonLink href="/agenda" face="red" shadow="red">
                                    Bekijk onze agenda
                                </DlfButtonLink>
                                <DlfButtonLink href="/over-ons" face="outline-red" shadow="red">
                                    Lees meer over ons
                                </DlfButtonLink>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </PublicPageFrame>
    );
}
