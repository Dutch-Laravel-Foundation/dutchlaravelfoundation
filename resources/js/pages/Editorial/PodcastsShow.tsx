import { SiteLayout } from "@/components/site";
import { Breadcrumb } from "@/components/editorial-react/Breadcrumb";
import { EditorialMeta } from "@/components/editorial-react/EditorialMeta";
import { footerCta } from "@/components/editorial-react/FooterCtaAdapter";
import { PodcastActions, PodcastTabs } from "@/components/editorial-react/PodcastControls";
import { PodcastMedia } from "@/components/editorial-react/PodcastMedia";

type PodcastsShowProps = {
    editorial: App.Data.Editorial.PodcastData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function PodcastsShow({ editorial, site }: PodcastsShowProps) {
    return (
        <SiteLayout
            data={site}
            pageSlug={editorial.slug}
            footerCta={editorial.callToAction ? footerCta(editorial.callToAction) : undefined}
        >

            <div className="editorial-article editorial-article--podcast">
                <div className="editorial-rail editorial-rail--article" data-dlf-footer-cta-stage>
                    <Breadcrumb href="/podcast" label="Podcast" current="Aflevering" />
                    <header className="editorial-article__hero dlf-divider-section dlf-divider-split dlf-divider-split--stacked-reversed">
                        <div className="editorial-article__head">
                            <EditorialMeta
                                article
                                category="Podcast"
                                date={editorial.publishedAt}
                            />
                            <h1>{editorial.title}</h1>
                            {editorial.summary ? (
                                <p className="editorial-article__lead">{editorial.summary}</p>
                            ) : null}
                            <PodcastActions
                                spotifyUrl={editorial.spotifyUrl}
                                videoUrl={editorial.videoUrl}
                            />
                        </div>
                        <PodcastMedia title={editorial.title} videoUrl={editorial.videoUrl} />
                    </header>

                    <div className="editorial-article__body editorial-podcast__body dlf-divider-section">
                        <article
                            className="editorial-article__prose editorial-podcast__content"
                            data-editorial-prose
                        >
                            <PodcastTabs
                                descriptionHtml={editorial.descriptionHtml}
                                spotifyUrl={editorial.spotifyUrl}
                                title={editorial.title}
                                transcriptHtml={editorial.transcriptHtml}
                            />
                        </article>
                    </div>
                </div>
            </div>
        </SiteLayout>
    );
}
