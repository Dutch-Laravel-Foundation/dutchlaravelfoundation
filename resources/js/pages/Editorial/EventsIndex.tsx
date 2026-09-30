import { SiteLayout } from "@/components/site";
import { EventEntry } from "@/components/editorial-react/EventEntry";
import { InfiniteScroll } from "@inertiajs/react";

type EventsIndexProps = {
    editorial: App.Data.Editorial.EventIndexData;
    page: App.Data.Pages.HomePageData;
    site: App.Data.SiteShell.SiteShellData;
};

export default function EventsIndex({ editorial, page, site }: EventsIndexProps) {
    return (
        <SiteLayout data={site} pageSlug={page.slug} footerCta={page.footerCta}>
            <div className="editorial-page editorial-events-page">
                <div className="editorial-rail" data-dlf-footer-cta-stage>
                    <header className="editorial-page__header dlf-divider-section">
                        <div className="editorial-page__heading">
                            <span className="editorial-eyebrow">Dutch Laravel Foundation</span>
                            <h1 className="editorial-page__title">{page.title}</h1>
                            <p className="editorial-page__intro">
                                Ontmoet de Nederlandse Laravel-community tijdens meetups,
                                hackathons, diners en andere evenementen waar kennisdeling en
                                verbinding centraal staan.
                            </p>
                        </div>
                    </header>

                    <section
                        className="editorial-events-section dlf-divider-section"
                        aria-label="Aankomende evenementen"
                    >
                        {!editorial.upcoming.length ? (
                            <div className="editorial-empty">
                                <p>Er staan momenteel geen nieuwe evenementen gepland.</p>
                            </div>
                        ) : (
                            <div className="editorial-feed dlf-divider-list">
                                {editorial.upcoming.map((event, index) => (
                                    <EventEntry
                                        event={event}
                                        featured={index === 0}
                                        index={index}
                                        key={event.id}
                                    />
                                ))}
                            </div>
                        )}
                    </section>

                    <section
                        className="editorial-events-section editorial-events-section--past dlf-divider-section border-t border-[#ececec]"
                        aria-labelledby="past-events-heading"
                    >
                        <header className="editorial-events-section__header border-b border-[#ececec] px-6 pt-20 pb-10 lg:px-10 lg:pt-28">
                            <span className="editorial-eyebrow">Terugblik</span>
                            <h2
                                id="past-events-heading"
                                className="mt-0! mb-3! text-2xl! font-semibold! leading-tight! text-[#090910]!"
                            >
                                Eerdere evenementen
                            </h2>
                            <p className="mb-0! max-w-2xl text-[#525257]">
                                Een overzicht van bijeenkomsten die de foundation eerder
                                organiseerde.
                            </p>
                        </header>

                        {editorial.pagination.total === 0 ? (
                            <div className="editorial-empty">
                                <p>Er zijn nog geen eerdere evenementen om te tonen.</p>
                            </div>
                        ) : (
                            <InfiniteScroll
                                data="editorial"
                                buffer={1200}
                                className="editorial-feed dlf-divider-list"
                                previous={({ loading }) =>
                                    loading ? (
                                        <div
                                            className="editorial-pagination"
                                            role="status"
                                            aria-live="polite"
                                        >
                                            <span className="editorial-pagination__status">
                                                Nieuwere evenementen laden…
                                            </span>
                                        </div>
                                    ) : null
                                }
                                next={({ hasMore, loading }) => {
                                    if (loading) {
                                        return (
                                            <div
                                                className="editorial-pagination"
                                                role="status"
                                                aria-live="polite"
                                            >
                                                <span className="editorial-pagination__status">
                                                    Meer evenementen laden…
                                                </span>
                                            </div>
                                        );
                                    }

                                    if (hasMore) {
                                        return null;
                                    }

                                    return (
                                        <div
                                            className="editorial-pagination"
                                            role="status"
                                            aria-live="polite"
                                        >
                                            <span className="editorial-pagination__status">
                                                Alle eerdere evenementen zijn geladen.
                                            </span>
                                        </div>
                                    );
                                }}
                            >
                                {editorial.past.map((event, index) => (
                                    <EventEntry event={event} index={index} past key={event.id} />
                                ))}
                            </InfiniteScroll>
                        )}
                    </section>
                </div>
            </div>
        </SiteLayout>
    );
}
