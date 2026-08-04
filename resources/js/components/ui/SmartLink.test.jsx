import { describe, expect, it, mock } from "bun:test";
import { createElement } from "react";
import { renderToStaticMarkup } from "react-dom/server";

const inertiaPrefetch = mock(() => {});
const inertiaVisit = mock(() => {});
const renderedLinkProps = [];

mock.module("@inertiajs/react", () => ({
    InfiniteScroll: ({ children }) => children,
    Link: ({ cacheFor, prefetch, viewTransition, ...props }) => {
        renderedLinkProps.push({ cacheFor, prefetch, viewTransition, ...props });

        return createElement("a", {
            ...props,
            "data-cache-for": cacheFor,
            "data-inertia-link": "true",
            "data-prefetch": prefetch?.join(","),
            "data-view-transition": viewTransition ? "true" : undefined,
        });
    },
    router: {
        prefetch: inertiaPrefetch,
        visit: inertiaVisit,
    },
}));

const { createCmsLinkHandlers, shouldUseInertiaLink, SmartLink, SmartLinkEnhancer } =
    await import("./SmartLink");
const { DlfButtonLink } = await import("./DlfButton");
const { CommunityButtonLink } = await import("../community-react/CommunityButton");
const { ArticleIndex, preloadFeaturedImageFromPrefetch } =
    await import("../editorial-react/ArticleIndex");
const { ProgressiveImage } = await import("../editorial-react/ProgressiveImage");

function renderLink(props) {
    return renderToStaticMarkup(<SmartLink {...props}>Ga verder</SmartLink>);
}

function expectInertiaLink(props) {
    expect(renderLink(props)).toContain('data-inertia-link="true"');
}

function expectNativeLink(props) {
    expect(renderLink(props)).not.toContain('data-inertia-link="true"');
}

describe("SmartLink", () => {
    it("uses Inertia for relative app locations", () => {
        expectInertiaLink({ href: "/nieuws" });
        expectInertiaLink({ href: "?page=2" });
        expectInertiaLink({ href: "kennis" });
        expectInertiaLink({ href: "/over-ons#team" });
        expectInertiaLink({ href: "/contact", target: "_self" });
    });

    it("keeps non-app destinations as native anchors", () => {
        expectNativeLink({ href: "https://laravel.com" });
        expectNativeLink({ href: "//laravel.com/docs" });
        expectNativeLink({ href: "mailto:info@example.test" });
        expectNativeLink({ href: "tel:+31101234567" });
        expectNativeLink({ href: "#main-content" });
    });

    it("uses Inertia for absolute links on the current origin", () => {
        expect(
            shouldUseInertiaLink(
                { href: "https://dutchlaravel.nl/agenda" },
                "https://dutchlaravel.nl",
            ),
        ).toBeTrue();
        expect(
            shouldUseInertiaLink({ href: "https://laravel.com/docs" }, "https://dutchlaravel.nl"),
        ).toBeFalse();
    });

    it("keeps control-panel and asset locations as native anchors", () => {
        expectNativeLink({ href: "/cp" });
        expectNativeLink({ href: "/cp/collections/pages" });
        expectNativeLink({ href: "/storage/documents/voorwaarden.pdf" });
        expectNativeLink({ href: "/build/assets/app.js" });
    });

    it("keeps explicit browser navigation as a native anchor", () => {
        expectNativeLink({ href: "/privacy-statement", target: "_blank" });
        expectNativeLink({ download: true, href: "/downloads/leden.csv" });
    });
});

describe("CMS link enhancement", () => {
    it("provides delegated navigation handlers", () => {
        expect(createCmsLinkHandlers).toBeFunction();
        expect(SmartLinkEnhancer).toBeFunction();
    });

    it("visits and prefetches eligible CMS-authored links", () => {
        const anchor = {
            download: "",
            href: "https://dutchlaravel.nl/kennis",
            hasAttribute: () => false,
            target: "",
        };
        const visit = mock(() => {});
        const prefetch = mock(() => {});
        const preventDefault = mock(() => {});
        const handlers = createCmsLinkHandlers({
            findAnchor: () => anchor,
            origin: "https://dutchlaravel.nl",
            prefetch,
            visit,
        });
        const event = {
            altKey: false,
            button: 0,
            ctrlKey: false,
            defaultPrevented: false,
            metaKey: false,
            preventDefault,
            shiftKey: false,
        };

        handlers.mouseover(event);
        handlers.mousedown(event);
        handlers.click(event);

        expect(prefetch).toHaveBeenCalledTimes(2);
        expect(prefetch).toHaveBeenCalledWith(
            "https://dutchlaravel.nl/kennis",
            {},
            { cacheFor: "30s" },
        );
        expect(preventDefault).toHaveBeenCalledTimes(1);
        expect(visit).toHaveBeenCalledWith("https://dutchlaravel.nl/kennis");
    });

    it("preserves modified, non-primary, and already handled clicks", () => {
        const anchor = {
            download: "",
            href: "https://dutchlaravel.nl/kennis",
            hasAttribute: () => false,
            target: "",
        };
        const visit = mock(() => {});
        const preventDefault = mock(() => {});
        const handlers = createCmsLinkHandlers({
            findAnchor: () => anchor,
            origin: "https://dutchlaravel.nl",
            prefetch: () => {},
            visit,
        });
        const event = {
            altKey: false,
            button: 0,
            ctrlKey: false,
            defaultPrevented: false,
            metaKey: false,
            preventDefault,
            shiftKey: false,
        };

        handlers.click({ ...event, metaKey: true });
        handlers.click({ ...event, button: 1 });
        handlers.click({ ...event, defaultPrevented: true });

        expect(preventDefault).not.toHaveBeenCalled();
        expect(visit).not.toHaveBeenCalled();
    });
});

describe("DlfButtonLink", () => {
    it("uses an Inertia link with hover and click prefetching for app routes", () => {
        const html = renderToStaticMarkup(
            <DlfButtonLink href="/lid-worden" face="red">
                Word lid
            </DlfButtonLink>,
        );

        expect(html).toContain('data-inertia-link="true"');
        expect(html).toContain('data-prefetch="hover,click"');
        expect(html).toContain('data-cache-for="30s"');
    });
});

describe("CommunityButtonLink", () => {
    it("uses an Inertia link for app routes", () => {
        const html = renderToStaticMarkup(
            <CommunityButtonLink href="/leden">Bekijk leden</CommunityButtonLink>,
        );

        expect(html).toContain('data-inertia-link="true"');
    });
});

describe("ArticleIndex category links", () => {
    it("preloads the first featured image from a prefetched category response", async () => {
        const loadedSources = [];
        const OriginalImage = globalThis.Image;

        globalThis.Image = class {
            decode() {
                return Promise.resolve();
            }

            set src(source) {
                loadedSources.push(source);
                queueMicrotask(() => this.onload?.());
            }
        };

        try {
            await preloadFeaturedImageFromPrefetch({
                data: {
                    props: {
                        editorial: {
                            items: [
                                {
                                    featuredImage: {
                                        url: "/assets/uploads/news/bestuur.jpg",
                                    },
                                },
                            ],
                        },
                    },
                },
            });

            expect(loadedSources).toEqual(["/assets/uploads/news/bestuur.jpg"]);
        } finally {
            globalThis.Image = OriginalImage;
        }
    });

    it("renders a prefetched image immediately without the progressive fade", async () => {
        const OriginalImage = globalThis.Image;

        globalThis.Image = class {
            decode() {
                return Promise.resolve();
            }

            set src(_) {
                queueMicrotask(() => this.onload?.());
            }
        };

        try {
            await preloadFeaturedImageFromPrefetch({
                data: JSON.stringify({
                    props: {
                        editorial: {
                            items: [
                                {
                                    featuredImage: {
                                        url: "/assets/uploads/news/inspiration.jpg",
                                    },
                                },
                            ],
                        },
                    },
                }),
            });

            const html = renderToStaticMarkup(
                <ProgressiveImage src="/assets/uploads/news/inspiration.jpg" alt="Inspiratie" />,
            );

            expect(html).toContain('data-media-state="loaded"');
            expect(html).toContain('data-media-cached="true"');
        } finally {
            globalThis.Image = OriginalImage;
        }
    });

    it("renders one shared active indicator for the filter row", () => {
        const html = renderToStaticMarkup(
            <ArticleIndex
                activeCategory="Netwerk"
                baseUrl="/nieuws"
                data={{ items: [], pagination: { total: 0 } }}
                empty={{ description: "Geen nieuws", linkLabel: "Wis filter", title: "Leeg" }}
                filters={["Leden", "Netwerk"]}
                heading={{ eyebrow: "Nieuws", introduction: "Intro", title: "Nieuws" }}
                paginationLabel="nieuwsberichten"
            />,
        );

        expect(html.match(/editorial-filters__indicator/g)).toHaveLength(1);
    });

    it("resets the merged editorial data and category before switching filters", () => {
        inertiaVisit.mockClear();
        inertiaPrefetch.mockClear();
        renderedLinkProps.length = 0;
        renderToStaticMarkup(
            <ArticleIndex
                activeCategory="Netwerk"
                baseUrl="/nieuws"
                data={{ items: [], pagination: { total: 0 } }}
                empty={{ description: "Geen nieuws", linkLabel: "Wis filter", title: "Leeg" }}
                filters={["Netwerk"]}
                heading={{ eyebrow: "Nieuws", introduction: "Intro", title: "Nieuws" }}
                paginationLabel="nieuwsberichten"
            />,
        );
        const categoryLink = renderedLinkProps.find(
            ({ href }) => href === "/nieuws?category=Netwerk",
        );
        const preventDefault = mock(() => {});

        expect(categoryLink?.onClick).toBeFunction();
        expect(categoryLink?.onMouseEnter).toBeFunction();
        expect(categoryLink?.viewTransition).toBeTrue();

        categoryLink.onMouseEnter({ defaultPrevented: false });

        expect(inertiaPrefetch).toHaveBeenCalledTimes(1);
        expect(inertiaPrefetch).toHaveBeenCalledWith(
            "/nieuws?category=Netwerk",
            expect.objectContaining({
                reset: ["editorial", "category"],
            }),
            { cacheFor: "30s" },
        );

        categoryLink.onClick({
            altKey: false,
            button: 0,
            ctrlKey: false,
            currentTarget: { target: "" },
            defaultPrevented: false,
            metaKey: false,
            preventDefault,
            shiftKey: false,
            target: null,
        });

        expect(preventDefault).toHaveBeenCalledTimes(1);
        expect(inertiaVisit).toHaveBeenCalledWith("/nieuws?category=Netwerk", {
            reset: ["editorial", "category"],
            viewTransition: true,
        });
    });
});
