import { describe, expect, test } from "bun:test";
import { createElement } from "react";
import { renderToStaticMarkup } from "react-dom/server";

import { resolveSiteLayoutProps } from "../../resources/js/components/site/PersistentSiteLayout";
import {
    PersistentSiteLayoutContext,
    SiteLayout,
} from "../../resources/js/components/site/SiteShell";

const root = new URL("../../", import.meta.url);

async function source(path) {
    return Bun.file(new URL(path, root)).text();
}

describe("the Inertia application shell", () => {
    test("uses the same persistent site layout for client and SSR rendering", async () => {
        const client = await source("resources/js/app.tsx");
        const server = await source("resources/js/ssr.tsx");

        for (const entrypoint of [client, server]) {
            expect(entrypoint).toContain("PersistentSiteLayout");
            expect(entrypoint).toMatch(/layout:\s*\(\)\s*=>\s*PersistentSiteLayout/);
        }
    });

    test("keeps page-level layout wrappers from mounting a second shell", async () => {
        const exports = await source("resources/js/components/site/index.ts");
        const shell = await source("resources/js/components/site/SiteShell.tsx");

        expect(exports).toContain("PersistentSiteLayout");
        expect(exports).not.toContain("SiteShell as SiteLayout");
        expect(shell).toContain("PersistentSiteLayoutContext");
    });

    test("closes desktop submenus after every Inertia navigation", async () => {
        const navigation = await source("resources/js/components/site/DesktopNavigation.tsx");

        expect(navigation).toContain('router.on("navigate", () => setOpen(false))');
    });

    test("renders page wrapper children without a nested shell inside the persistent layout", () => {
        const markup = renderToStaticMarkup(
            createElement(
                PersistentSiteLayoutContext,
                { value: true },
                createElement(
                    SiteLayout,
                    { data: {}, pageSlug: "nieuws" },
                    createElement("p", null, "Nieuwsinhoud"),
                ),
            ),
        );

        expect(markup).toBe("<p>Nieuwsinhoud</p>");
    });

    test("preserves explicit CTA defaults for public, community, and event pages", () => {
        const site = { defaultCta: { id: "default" } };

        expect(
            resolveSiteLayoutProps({
                page: { slug: "over-ons", callToAction: null },
                site,
            }).footerCta,
        ).toBeNull();
        expect(
            resolveSiteLayoutProps({
                community: { page: { slug: "leden", callToAction: null } },
                site,
            }).footerCta,
        ).toBeUndefined();
        expect(
            resolveSiteLayoutProps({
                editorial: { slug: "meetup", timeStart: "19:00" },
                site,
            }).footerCta,
        ).toBeNull();
    });
});
