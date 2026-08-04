import { SiteLayout } from "@/components/site";
import { SmartLink } from "@/components/ui/SmartLink";
import { communityFooterCta } from "@/components/community-react/CommunityFooterCtaAdapter";
import { CommunityImage } from "@/components/community-react/CommunityImage";
import { truncate } from "@/components/editorial-react/format";

type CasesIndexProps = {
    community: App.Data.Community.CaseIndexData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function CasesIndex({ community, site }: CasesIndexProps) {
    const { page, items } = community;

    return (
        <SiteLayout
            data={site}
            pageSlug={page.slug}
            footerCta={page.callToAction ? communityFooterCta(page.callToAction) : undefined}
        >

            <div className="editorial-page editorial-page--cases">
                <div className="editorial-rail" data-dlf-footer-cta-stage>
                    <header className="editorial-page__header dlf-divider-section">
                        <div className="editorial-page__heading">
                            <span className="editorial-eyebrow">Cases</span>
                            <h1 className="editorial-page__title">Cases uit de community</h1>
                            <p className="editorial-page__intro">
                                Ontdek welke digitale oplossingen onze leden met Laravel bouwen voor
                                organisaties in uiteenlopende sectoren.
                            </p>
                        </div>
                    </header>

                    {!items.length ? (
                        <div className="editorial-empty dlf-divider-section">
                            <h2>Geen cases gevonden</h2>
                            <p>Er zijn op dit moment geen cases beschikbaar.</p>
                        </div>
                    ) : (
                        <div className="editorial-feed dlf-divider-section dlf-divider-list">
                            {items.map((item, index) => {
                                const featured = index === 0;
                                const href = item.url ?? item.uri ?? `/cases/${item.slug}`;

                                return (
                                    <article
                                        className={`editorial-entry ${index % 2 === 0 ? "editorial-entry--media-start" : "editorial-entry--media-end"}${featured ? " editorial-entry--featured" : ""}`}
                                        key={item.id}
                                    >
                                        <SmartLink
                                            className="editorial-entry__media"
                                            href={href}
                                            aria-label={`Lees ${item.displayTitle}`}
                                            data-progressive-media-frame
                                        >
                                            <CommunityImage
                                                asset={item.featuredImage}
                                                className="editorial-entry__image"
                                                eager={featured}
                                                title={item.displayTitle}
                                            />
                                        </SmartLink>

                                        <div className="editorial-entry__body">
                                            <div className="editorial-entry__meta">
                                                <span className="editorial-entry__category">
                                                    {item.member?.title ?? "Case"}
                                                </span>
                                            </div>
                                            <h2
                                                className={`editorial-entry__title${featured ? " editorial-entry__title--featured" : ""}`}
                                            >
                                                <SmartLink href={href}>
                                                    {item.displayTitle}
                                                </SmartLink>
                                            </h2>
                                            {item.introductionHtml ? (
                                                <p className="editorial-entry__summary">
                                                    {truncate(item.introductionHtml)}
                                                </p>
                                            ) : null}
                                            <SmartLink className="editorial-text-link" href={href}>
                                                Bekijk case <span aria-hidden="true">→</span>
                                            </SmartLink>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>
        </SiteLayout>
    );
}
