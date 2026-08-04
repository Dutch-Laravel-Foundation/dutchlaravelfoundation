import type { HttpResponse } from "@inertiajs/core";
import { InfiniteScroll } from "@inertiajs/react";
import { useLayoutEffect, useRef } from "react";

import { SmartLink } from "@/components/ui/SmartLink";

import { EditorialImage } from "./Media";
import { EditorialMeta } from "./EditorialMeta";
import { preloadProgressiveImage } from "./ProgressiveImage";
import { truncate } from "./format";

type PrefetchedArticleIndex = {
    props?: {
        editorial?: {
            items?: Array<{
                featuredImage?: App.Data.Editorial.AssetData | null;
            }>;
        };
    };
};

export function preloadFeaturedImageFromPrefetch(response: {
    data: HttpResponse["data"] | PrefetchedArticleIndex;
}): Promise<void> {
    try {
        const page =
            typeof response.data === "string"
                ? (JSON.parse(response.data) as PrefetchedArticleIndex)
                : response.data;
        const image = page.props?.editorial?.items?.[0]?.featuredImage;
        const source = image?.url ?? image?.permalink;

        return source ? preloadProgressiveImage(source) : Promise.resolve();
    } catch {
        return Promise.resolve();
    }
}

type ArticleIndexProps = {
    activeCategory?: string | null;
    baseUrl: string;
    data: App.Data.Editorial.ArticleIndexData;
    empty: {
        description: string;
        linkLabel: string;
        title: string;
    };
    filters: readonly string[];
    heading: {
        eyebrow: string;
        introduction: string;
        title: string;
    };
    paginationLabel: string;
};

export function ArticleIndex({
    activeCategory,
    baseUrl,
    data,
    empty,
    filters,
    heading,
    paginationLabel,
}: ArticleIndexProps) {
    const filtersRef = useRef<HTMLElement>(null);
    const indicatorRef = useRef<HTMLSpanElement>(null);
    const filterKey = filters.join("\u0000");
    const filterReset = ["editorial", "category"];

    useLayoutEffect(() => {
        const filterRow = filtersRef.current;
        const indicator = indicatorRef.current;

        if (!filterRow || !indicator) {
            return;
        }

        let active = true;
        let animationFrame: number | null = null;

        const positionIndicator = () => {
            if (!active) {
                return;
            }

            const activeFilter =
                filterRow.querySelector<HTMLAnchorElement>('a[aria-current="page"]');

            if (!activeFilter) {
                delete filterRow.dataset.indicatorReady;

                return;
            }

            const filterRowRect = filterRow.getBoundingClientRect();
            const activeFilterRect = activeFilter.getBoundingClientRect();

            indicator.style.setProperty(
                "--editorial-indicator-x",
                `${activeFilterRect.left - filterRowRect.left + filterRow.scrollLeft}px`,
            );
            indicator.style.setProperty(
                "--editorial-indicator-width",
                `${activeFilterRect.width}px`,
            );
            filterRow.dataset.indicatorReady = "true";
        };

        positionIndicator();

        if (filterRow.dataset.indicatorAnimate !== "true") {
            animationFrame = window.requestAnimationFrame(() => {
                if (active) {
                    filterRow.dataset.indicatorAnimate = "true";
                }
            });
        }

        const resizeObserver = new ResizeObserver(positionIndicator);
        const activeFilter = filterRow.querySelector<HTMLAnchorElement>('a[aria-current="page"]');

        resizeObserver.observe(filterRow);

        if (activeFilter) {
            resizeObserver.observe(activeFilter);
        }

        document.fonts?.ready.then(positionIndicator);
        window.addEventListener("resize", positionIndicator);

        return () => {
            active = false;

            if (animationFrame !== null) {
                window.cancelAnimationFrame(animationFrame);
            }

            resizeObserver.disconnect();
            window.removeEventListener("resize", positionIndicator);
        };
    }, [activeCategory, filterKey]);

    return (
        <div className="editorial-page">
            <div className="editorial-rail" data-dlf-footer-cta-stage>
                <header className="editorial-page__header dlf-divider-section">
                    <div className="editorial-page__heading">
                        <span className="editorial-eyebrow">{heading.eyebrow}</span>
                        <h1 className="editorial-page__title">{heading.title}</h1>
                        <p className="editorial-page__intro">{heading.introduction}</p>
                    </div>

                    <nav
                        ref={filtersRef}
                        className="editorial-filters"
                        aria-label={`Filter ${paginationLabel}`}
                    >
                        <SmartLink
                            href={baseUrl}
                            aria-current={!activeCategory ? "page" : undefined}
                            onPrefetched={(response) => {
                                void preloadFeaturedImageFromPrefetch(response);
                            }}
                            reset={filterReset}
                            viewTransition
                        >
                            Alles
                        </SmartLink>
                        {filters.map((filter) => (
                            <SmartLink
                                href={`${baseUrl}?category=${encodeURIComponent(filter)}`}
                                aria-current={activeCategory === filter ? "page" : undefined}
                                key={filter}
                                onPrefetched={(response) => {
                                    void preloadFeaturedImageFromPrefetch(response);
                                }}
                                reset={filterReset}
                                viewTransition
                            >
                                {filter}
                            </SmartLink>
                        ))}
                        <span
                            ref={indicatorRef}
                            className="editorial-filters__indicator"
                            aria-hidden="true"
                        />
                    </nav>
                </header>

                {data.pagination.total === 0 ? (
                    <div className="editorial-empty dlf-divider-section">
                        <h2>{empty.title}</h2>
                        <p>{empty.description}</p>
                        <SmartLink
                            href={baseUrl}
                            className="editorial-text-link"
                            reset={filterReset}
                            viewTransition
                        >
                            {empty.linkLabel}
                        </SmartLink>
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
                                        Nieuwere {paginationLabel} laden…
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
                                            Meer {paginationLabel} laden…
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
                                        Alle {paginationLabel} zijn geladen.
                                    </span>
                                </div>
                            );
                        }}
                    >
                        {data.items.map((item, index) => {
                            const featured = index === 0;
                            const href = item.url ?? item.uri ?? `${baseUrl}/${item.slug}`;

                            return (
                                <article
                                    className={`editorial-entry editorial-entry--linked ${index % 2 === 0 ? "editorial-entry--media-start" : "editorial-entry--media-end"}${featured ? " editorial-entry--featured" : ""}`}
                                    key={item.id}
                                >
                                    <SmartLink
                                        className="editorial-entry__link"
                                        href={href}
                                        aria-label={`Lees ${item.title}`}
                                    >
                                        <span
                                            className="editorial-entry__media"
                                            data-progressive-media-frame
                                        >
                                            <EditorialImage
                                                asset={item.featuredImage}
                                                className="editorial-entry__image"
                                                eager={featured}
                                                title={item.title}
                                            />
                                        </span>

                                        <div className="editorial-entry__body">
                                            <EditorialMeta
                                                category={item.category}
                                                date={item.date}
                                            />
                                            <h2
                                                className={`editorial-entry__title${featured ? " editorial-entry__title--featured" : ""}`}
                                            >
                                                {item.title}
                                            </h2>
                                            {item.introduction ? (
                                                <p className="editorial-entry__summary">
                                                    {truncate(item.introduction)}
                                                </p>
                                            ) : null}
                                            <span className="editorial-text-link">
                                                Lees meer <span aria-hidden="true">→</span>
                                            </span>
                                        </div>
                                    </SmartLink>
                                </article>
                            );
                        })}
                    </InfiniteScroll>
                )}
            </div>
        </div>
    );
}
