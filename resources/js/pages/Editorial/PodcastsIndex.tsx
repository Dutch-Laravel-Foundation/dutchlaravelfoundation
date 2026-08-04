import { SiteLayout } from "@/components/site";
import { EditorialMeta } from "@/components/editorial-react/EditorialMeta";
import { ExternalImage } from "@/components/editorial-react/Media";
import { PodcastChannelActions } from "@/components/editorial-react/PodcastControls";
import { truncate } from "@/components/editorial-react/format";
import { SmartLink } from "@/components/ui/SmartLink";
import { InfiniteScroll } from "@inertiajs/react";

type PodcastsIndexProps = {
    editorial: App.Data.Editorial.PodcastIndexData;
    page: App.Data.Pages.HomePageData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function PodcastsIndex({ editorial, page, site }: PodcastsIndexProps) {
    return (
        <SiteLayout data={site} pageSlug={page.slug} footerCta={page.footerCta}>
            <div className="editorial-page editorial-page--podcasts">
                <div className="editorial-rail" data-dlf-footer-cta-stage>
                    <header className="editorial-page__header dlf-divider-section">
                        <div className="editorial-page__heading">
                            <span className="editorial-eyebrow">Podcast</span>
                            <h1 className="editorial-page__title">
                                Gesprekken uit de Laravel-community
                            </h1>
                            <p className="editorial-page__intro">
                                Talks, interviews en praktijkverhalen over Laravel,
                                softwareontwikkeling en de mensen achter de Nederlandse community.
                            </p>
                        </div>
                        <PodcastChannelActions />
                    </header>

                    {editorial.pagination.total === 0 ? (
                        <div className="editorial-empty dlf-divider-section">
                            <h2>Binnenkort beschikbaar</h2>
                            <p>Nieuwe afleveringen verschijnen hier zodra ze zijn gepubliceerd.</p>
                        </div>
                    ) : (
                        <InfiniteScroll
                            data="editorial"
                            buffer={1200}
                            className="editorial-feed dlf-divider-section dlf-divider-list"
                            previous={({ loading }) =>
                                loading ? (
                                    <div
                                        className="editorial-pagination dlf-divider-section"
                                        role="status"
                                        aria-live="polite"
                                    >
                                        <span className="editorial-pagination__status">
                                            Nieuwere podcasts laden…
                                        </span>
                                    </div>
                                ) : null
                            }
                            next={({ hasMore, loading }) => {
                                if (loading) {
                                    return (
                                        <div
                                            className="editorial-pagination dlf-divider-section"
                                            role="status"
                                            aria-live="polite"
                                        >
                                            <span className="editorial-pagination__status">
                                                Meer podcasts laden…
                                            </span>
                                        </div>
                                    );
                                }

                                if (hasMore) {
                                    return null;
                                }

                                return (
                                    <div
                                        className="editorial-pagination dlf-divider-section"
                                        role="status"
                                        aria-live="polite"
                                    >
                                        <span className="editorial-pagination__status">
                                            Alle podcasts zijn geladen.
                                        </span>
                                    </div>
                                );
                            }}
                        >
                            {editorial.items.map((episode, index) => {
                                const featured = index === 0;
                                const href =
                                    episode.url ?? episode.uri ?? `/podcast/${episode.slug}`;

                                return (
                                    <article
                                        className={`editorial-entry ${index % 2 === 0 ? "editorial-entry--media-start" : "editorial-entry--media-end"}${featured ? " editorial-entry--featured" : ""}`}
                                        key={episode.id}
                                    >
                                        <SmartLink
                                            className="editorial-entry__media"
                                            href={href}
                                            aria-label={`Beluister ${episode.title}`}
                                            data-progressive-media-frame
                                        >
                                            <ExternalImage
                                                className="editorial-entry__image"
                                                source={episode.thumbnailUrl}
                                                title={episode.title}
                                                eager={featured}
                                            />
                                        </SmartLink>

                                        <div className="editorial-entry__body">
                                            <EditorialMeta
                                                category="Podcast"
                                                date={episode.publishedAt}
                                            />
                                            <h2
                                                className={`editorial-entry__title${featured ? " editorial-entry__title--featured" : ""}`}
                                            >
                                                <SmartLink href={href}>{episode.title}</SmartLink>
                                            </h2>
                                            {episode.summary ? (
                                                <p className="editorial-entry__summary">
                                                    {truncate(episode.summary)}
                                                </p>
                                            ) : null}
                                            <SmartLink className="editorial-text-link" href={href}>
                                                Bekijk aflevering <span aria-hidden="true">→</span>
                                            </SmartLink>
                                        </div>
                                    </article>
                                );
                            })}
                        </InfiniteScroll>
                    )}
                </div>
            </div>
        </SiteLayout>
    );
}
