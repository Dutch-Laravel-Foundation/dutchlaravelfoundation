#!/usr/bin/env node

import { execFileSync } from "node:child_process";
import { readFileSync } from "node:fs";

const defaultRoutes = [
    "/",
    "/wat-is-laravel",
    "/over-ons",
    "/lid-worden",
    "/leden",
    "/leden/pionect",
    "/stagebank",
    "/stagebank/pionect",
    "/cases",
    "/cases/dropday",
    "/kennis",
    "/nieuws",
    "/agenda",
    "/contact",
    "/privacy-statement",
    "/larabelles",
    "/podcast",
    "/aanbestedingen",
    "/een-eigen-systeem-laten-bouwen-is-betaalbaarder-dan-je-denkt",
    "/laravel-het-framework-dat-jouw-systeem-op-maat-tot-een-succes-maakt",
];

const defaultWidths = [390, 639, 640, 768, 1023, 1024, 1440, 2048];
const args = process.argv.slice(2);

function option(name) {
    const index = args.indexOf(name);

    return index === -1 ? null : args[index + 1];
}

const baseUrl = option("--base-url") ?? "https://new-design.dutchlaravelfoundation.test";
const routesFile = option("--routes-file");
const sitemapUrl = option("--sitemap");
const sitemapRoutes = sitemapUrl
    ? [
          ...execFileSync("curl", ["-fsSLk", sitemapUrl], { encoding: "utf8" }).matchAll(
              /<loc>([^<]+)<\/loc>/g,
          ),
      ].map(([, location]) => new URL(location).pathname)
    : null;
const routes = sitemapRoutes
    ? sitemapRoutes
    : routesFile
      ? readFileSync(routesFile, "utf8")
            .split(/\r?\n/)
            .map((route) => route.trim())
            .filter(Boolean)
      : option("--route")
        ? [option("--route")]
        : defaultRoutes;
const widths = option("--width") ? [Number(option("--width"))] : defaultWidths;
const quiet = args.includes("--quiet");
const session = `dlf-divider-audit-${process.pid}`;

const browserScript = String.raw`(() => {
    const px = value => Number.parseFloat(value) || 0;
    const visible = element => {
        const rect = element.getBoundingClientRect();
        const style = getComputedStyle(element);

        return style.display !== "none" && style.visibility !== "hidden" && rect.width > 0 && rect.height > 0;
    };
    const border = element => {
        const style = getComputedStyle(element);

        return {
            top: px(style.borderTopWidth),
            right: px(style.borderRightWidth),
            bottom: px(style.borderBottomWidth),
            left: px(style.borderLeftWidth),
        };
    };
    const label = (element, index) => {
        const classes = [...element.classList].filter(name => name.startsWith("dlf-")).slice(0, 4).join(".");

        return classes ? "." + classes : element.tagName.toLowerCase() + ":" + index;
    };
    const failures = [];
    const expect = (condition, message) => {
        if (!condition) failures.push(message);
    };
    const exactlyOne = value => Math.abs(value - 1) < 0.1;
    let adjacentBoundaries = 0;
    let ctaStages = 0;
    let editorialTerminalEdges = 0;
    const structuralInsetShadow = value => value !== "none" && value
        .split(/,(?![^()]*\))/)
        .some(shadow => {
            if (!shadow.includes("inset")) return false;

            const values = [...shadow.matchAll(/-?\d+(?:\.\d+)?px/g)].map(match => px(match[0]));

            if (values.length < 4) return false;

            const [offsetX, offsetY, blur, spread] = values.slice(-4);
            const verticalRule = Math.abs(Math.abs(offsetX) - 1) < 0.1 && offsetY === 0;
            const horizontalRule = offsetX === 0 && Math.abs(Math.abs(offsetY) - 1) < 0.1;

            return (verticalRule || horizontalRule) && blur === 0 && spread === 0;
        });
    const structuralPseudoLine = style => {
        const hasContent = style.content !== "none" && style.content !== "normal";
        const hasColor = style.backgroundColor !== "transparent" && style.backgroundColor !== "rgba(0, 0, 0, 0)";
        const thinWidth = px(style.width) > 0 && px(style.width) <= 1.1;
        const thinHeight = px(style.height) > 0 && px(style.height) <= 1.1;

        return hasContent && style.display !== "none" && hasColor && (thinWidth || thinHeight);
    };
    const hasCompositeTail = section => window.innerWidth >= 1024
        && section.classList.contains("dlf-divider-section--composite-tail");

    [...document.querySelectorAll(".dlf-divider-section")].filter(visible).forEach((section, index) => {
        const edges = border(section);
        const sectionRect = section.getBoundingClientRect();
        const contentBottom = sectionRect.bottom - edges.bottom;
        const expectedTop = section.classList.contains("dlf-divider-section--full-bleed-top") ? 1 : 0;
        const expectedBottom = hasCompositeTail(section)
            || section.classList.contains("dlf-divider-section--yield-to-next")
            ? 0
            : 1;

        expect(
            Math.abs(edges.top - expectedTop) < 0.1,
            label(section, index) + " top edge is " + edges.top + "px",
        );
        expect(
            Math.abs(edges.bottom - expectedBottom) < 0.1,
            label(section, index) + " bottom edge is " + edges.bottom + "px",
        );

        const nestedTerminalOwners = [...section.querySelectorAll(
            ".dlf-divider-grid, .dlf-divider-list, .dlf-divider-split, .dlf-divider-region",
        )].filter(visible).filter(owner => {
            const ownerRect = owner.getBoundingClientRect();
            const coversSection = ownerRect.left <= sectionRect.left + 1.1
                && ownerRect.right >= sectionRect.right - 1.1;
            const meetsSectionBottom = Math.abs(ownerRect.bottom - contentBottom) < 1.1;

            return border(owner).bottom > 0 && coversSection && meetsSectionBottom;
        });

        expect(
            edges.bottom === 0 || nestedTerminalOwners.length === 0,
            label(section, index) + " bottom duplicates a full-width nested terminal divider",
        );
    });

    [...document.querySelectorAll(".editorial-rail, #main-content > .dlf-community-page")]
        .filter(visible)
        .forEach((rail, railIndex) => {
            const children = [...rail.children].filter(visible);

            for (let childIndex = 1; childIndex < children.length; childIndex++) {
                const upper = children[childIndex - 1];
                const lower = children[childIndex];
                const upperRect = upper.getBoundingClientRect();
                const lowerRect = lower.getBoundingClientRect();
                const horizontalOverlap = Math.min(upperRect.right, lowerRect.right)
                    - Math.max(upperRect.left, lowerRect.left);

                if (Math.abs(upperRect.bottom - lowerRect.top) >= 1.1 || horizontalOverlap <= 1) {
                    continue;
                }

                adjacentBoundaries++;

                const boundary = border(upper).bottom + border(lower).top;

                expect(
                    boundary <= 1.1,
                    label(rail, railIndex) + " adjacent child boundary " + (childIndex - 1)
                        + "/" + childIndex + " totals " + boundary + "px",
                );
            }
        });

    [...document.querySelectorAll(".editorial-rail")].filter(visible).forEach((rail, index) => {
        const finalChild = [...rail.children].filter(visible).at(-1);

        editorialTerminalEdges++;
        expect(Boolean(finalChild), label(rail, index) + " has no final content block");

        if (!finalChild) return;

        expect(
            exactlyOne(border(finalChild).bottom),
            label(rail, index) + " final page-content edge is " + border(finalChild).bottom + "px",
        );
    });

    const yieldingSections = [...document.querySelectorAll(".dlf-divider-section--yield-to-next")].filter(visible);
    const fullBleedTopSections = [...document.querySelectorAll(".dlf-divider-section--full-bleed-top")].filter(visible);

    yieldingSections.forEach((upper, index) => {
        const lower = upper.nextElementSibling;

        expect(
            lower?.classList.contains("dlf-divider-section--full-bleed-top"),
            label(upper, index) + " does not yield to an adjacent full-bleed section",
        );
    });

    fullBleedTopSections.forEach((lower, index) => {
        const upper = lower.previousElementSibling;

        expect(
            upper?.classList.contains("dlf-divider-section--yield-to-next"),
            label(lower, index) + " has no adjacent yielding section",
        );

        if (!upper) return;

        const upperRect = upper.getBoundingClientRect();
        const lowerRect = lower.getBoundingClientRect();

        expect(
            Math.abs(upperRect.bottom - lowerRect.top) < 1.1,
            label(lower, index) + " does not share the yielding section's boundary",
        );
        expect(
            lowerRect.left <= upperRect.left + 1.1 && lowerRect.right >= upperRect.right - 1.1,
            label(lower, index) + " does not cover the yielding section's horizontal span",
        );
        expect(
            exactlyOne(border(upper).bottom + border(lower).top),
            label(lower, index) + " transferred boundary is not exactly 1px",
        );
    });

    [...document.querySelectorAll("#main-content > .dlf-community-page")].filter(visible).forEach((page, index) => {
        if (window.innerWidth < 1024 || px(getComputedStyle(page).paddingBottom) === 0) return;

        const sections = [...page.children].filter(element => element.classList.contains("dlf-divider-section") && visible(element));
        const finalSection = sections.at(-1);
        const footerBand = document.querySelector("#main-content ~ .dlf-footer .dlf-footer-band");

        expect(Boolean(finalSection), label(page, index) + " has no final divider section before its CTA tail");
        expect(Boolean(footerBand), label(page, index) + " has no footer band after its CTA tail");

        if (!finalSection || !footerBand) return;

        const expectedBottom = hasCompositeTail(finalSection) ? 0 : 1;

        expect(
            Math.abs(border(finalSection).bottom - expectedBottom) < 0.1,
            label(page, index) + " final page-content edge is " + border(finalSection).bottom + "px",
        );
        expect(exactlyOne(border(footerBand).top), label(page, index) + " footer boundary is not exactly 1px");
    });

    const tailSegments = [...document.querySelectorAll(".dlf-divider-tail-segment")].filter(visible);

    tailSegments.forEach((segment, index) => {
        const edges = border(segment);
        const expectedBottom = window.innerWidth >= 1024 ? 1 : 0;

        expect(
            Math.abs(edges.bottom - expectedBottom) < 0.1,
            label(segment, index) + " composite tail bottom is " + edges.bottom + "px",
        );
    });

    if (window.innerWidth >= 1024) {
        const sections = [...new Set(tailSegments.map(segment => segment.closest(".dlf-divider-section")))].filter(Boolean);

        sections.forEach((section, index) => {
            const sectionRect = section.getBoundingClientRect();
            const segments = tailSegments
                .filter(segment => segment.closest(".dlf-divider-section") === section)
                .map(segment => ({ element: segment, rect: segment.getBoundingClientRect() }))
                .sort((first, second) => first.rect.left - second.rect.left);

            expect(
                section.classList.contains("dlf-divider-section--composite-tail"),
                label(section, index) + " has tail segments without the composite-tail state",
            );
            expect(border(section).bottom === 0, label(section, index) + " duplicates its composite tail edge");
            expect(segments.length > 0, label(section, index) + " has no tail segments");

            segments.forEach(({ rect }, segmentIndex) => {
                expect(
                    Math.abs(rect.bottom - sectionRect.bottom) < 1.1,
                    label(section, index) + " tail segment " + segmentIndex + " does not align with the section bottom",
                );
            });

            expect(
                segments[0]?.rect.left <= sectionRect.left + 1.1,
                label(section, index) + " composite tail does not cover the section left edge",
            );
            expect(
                segments.at(-1)?.rect.right >= sectionRect.right - 1.1,
                label(section, index) + " composite tail does not cover the section right edge",
            );

            for (let segmentIndex = 1; segmentIndex < segments.length; segmentIndex++) {
                expect(
                    segments[segmentIndex].rect.left <= segments[segmentIndex - 1].rect.right + 1.1,
                    label(section, index) + " composite tail has a gap before segment " + segmentIndex,
                );
            }
        });

        [...document.querySelectorAll(".dlf-divider-section--composite-tail")].filter(visible).forEach((section, index) => {
            expect(
                tailSegments.some(segment => segment.closest(".dlf-divider-section") === section),
                label(section, index) + " declares a composite tail without tail segments",
            );
        });
    }

    const ctaSections = [...document.querySelectorAll(".dlf-cta-section")].filter(visible);
    const cta = ctaSections[0];
    const card = cta?.querySelector(".dlf-cta-card");
    const stages = [...document.querySelectorAll("[data-dlf-footer-cta-stage]")].filter(visible);

    ctaStages = stages.length;

    expect(ctaSections.length <= 1, "page has " + ctaSections.length + " visible footer CTA sections; expected at most 1");
    ctaSections.forEach((section, index) => {
        expect(Boolean(section.closest(".dlf-footer")), label(section, index) + " is rendered outside the shared footer");
    });

    if (cta && card) {
        expect(stages.length === 1, "page with footer CTA has " + stages.length + " stage owners; expected 1");

        const stage = stages[0];

        if (stage) {
        const sections = [...stage.querySelectorAll(".dlf-divider-section")].filter(visible);
        const finalSection = sections
            .map(section => ({ element: section, rect: section.getBoundingClientRect() }))
            .sort((first, second) => first.rect.bottom - second.rect.bottom)
            .at(-1);

            expect(Boolean(finalSection), label(stage, 0) + " has no final content divider to measure");

            if (finalSection) {
                const ctaRect = cta.getBoundingClientRect();
                const cardRect = card.getBoundingClientRect();
                const verticalGap = cardRect.top - finalSection.rect.bottom;
                const horizontalInset = cardRect.left - ctaRect.left;

                expect(
                    Math.abs(verticalGap - horizontalInset) < 1.1,
                    label(stage, 0) + " CTA gap is " + verticalGap.toFixed(2) + "px but its side inset is " + horizontalInset.toFixed(2) + "px",
                );
            }
        }
    }

    [...document.querySelectorAll(".dlf-divider-region")].filter(visible).forEach((region, index) => {
        const edges = border(region);
        expect(exactlyOne(edges.top), label(region, index) + " top edge is " + edges.top + "px");
    });

    const auditCollection = (container, index, type) => {
        const children = [...container.children].filter(visible);
        const childData = children.map((element, childIndex) => ({
            element,
            childIndex,
            rect: element.getBoundingClientRect(),
            edges: border(element),
        }));
        const visualColumns = new Set(childData.map(({ rect }) => Math.round(rect.left))).size;
        const columnRulesOnly = container.classList.contains("dlf-divider-grid--column-rules-only")
            && visualColumns > 1;
        const leadingOwnsDesktopRule = window.innerWidth >= 1024
            && container.classList.contains("dlf-divider-grid--desktop-leading-owns-rule")
            && visualColumns > 1;
        const expectNoStructuralDecoration = (element, description) => {
            const shadow = getComputedStyle(element).boxShadow;

            expect(
                !structuralInsetShadow(shadow),
                description + " draws a structural inset shadow: " + shadow,
            );

            ["::before", "::after"].forEach(pseudo => {
                expect(
                    !structuralPseudoLine(getComputedStyle(element, pseudo)),
                    description + " draws a structural " + pseudo + " line outside the divider primitive",
                );
            });
        };

        expectNoStructuralDecoration(container, label(container, index));

        childData.forEach(({ element, childIndex, rect, edges }) => {
            expectNoStructuralDecoration(element, label(container, index) + " child " + childIndex);

            const hasRightNeighbor = childData.some(item => {
                const verticalOverlap = Math.min(rect.bottom, item.rect.bottom) - Math.max(rect.top, item.rect.top);

                return Math.abs(rect.right - item.rect.left) < 1.1 && verticalOverlap > 1;
            });
            const expectedRight = leadingOwnsDesktopRule && hasRightNeighbor ? 1 : 0;

            expect(
                Math.abs(edges.right - expectedRight) < 0.1,
                label(container, index) + " child " + childIndex + " owns right " + edges.right + "px; expected " + expectedRight + "px",
            );
            const expectedBottom = window.innerWidth >= 1024
                && element.classList.contains("dlf-divider-tail-segment")
                ? 1
                : 0;
            expect(
                Math.abs(edges.bottom - expectedBottom) < 0.1,
                label(container, index) + " child " + childIndex + " owns bottom " + edges.bottom + "px",
            );

            if (type === "list") {
                const expectedTop = childIndex === 0 ? 0 : 1;
                expect(Math.abs(edges.top - expectedTop) < 0.1, label(container, index) + " list child " + childIndex + " top is " + edges.top + "px");
            }
        });

        if (type !== "list") {
            for (let first = 0; first < childData.length; first++) {
                for (let second = first + 1; second < childData.length; second++) {
                    const a = childData[first];
                    const b = childData[second];
                    const verticalOverlap = Math.min(a.rect.bottom, b.rect.bottom) - Math.max(a.rect.top, b.rect.top);
                    const horizontalOverlap = Math.min(a.rect.right, b.rect.right) - Math.max(a.rect.left, b.rect.left);
                    const touchesVertically = Math.abs(a.rect.right - b.rect.left) < 1.1 || Math.abs(b.rect.right - a.rect.left) < 1.1;
                    const touchesHorizontally = Math.abs(a.rect.bottom - b.rect.top) < 1.1 || Math.abs(b.rect.bottom - a.rect.top) < 1.1;

                    if (touchesVertically && verticalOverlap > 1) {
                        const left = a.rect.left < b.rect.left ? a : b;
                        const right = left === a ? b : a;
                        const edge = left.edges.right + right.edges.left;
                        const owner = left.edges.right > 0 ? left : right.edges.left > 0 ? right : null;
                        const rowTop = Math.min(left.rect.top, right.rect.top);
                        const rowBottom = Math.max(left.rect.bottom, right.rect.bottom);

                        expect(exactlyOne(edge), label(container, index) + " vertical edge " + first + "/" + second + " totals " + edge + "px");
                        expect(
                            owner && owner.rect.top <= rowTop + 1.1 && owner.rect.bottom >= rowBottom - 1.1,
                            label(container, index) + " vertical edge " + first + "/" + second + " owner does not cover the row height",
                        );
                    }

                    if (touchesHorizontally && horizontalOverlap > 1) {
                        const edge = a.rect.top < b.rect.top ? a.edges.bottom + b.edges.top : b.edges.bottom + a.edges.top;
                        const edgeIsCorrect = columnRulesOnly ? edge === 0 : exactlyOne(edge);
                        const expectation = columnRulesOnly ? "0px" : "1px";

                        expect(
                            edgeIsCorrect,
                            label(container, index) + " horizontal edge " + first + "/" + second + " totals " + edge + "px; expected " + expectation,
                        );
                    }
                }
            }
        }

        if (container.classList.contains("dlf-divider-grid--framed")) {
            const edges = border(container);

            ["top", "right", "bottom", "left"].forEach(edge => {
                expect(exactlyOne(edges[edge]), label(container, index) + " frame " + edge + " is " + edges[edge] + "px");
            });
        }

        if (type === "grid" && container.classList.contains("dlf-divider-grid--fill")) {
            const fillers = [...container.children].filter(element => element.classList.contains("dlf-divider-grid__filler"));
            const filler = fillers[0];
            const content = childData.filter(item => !item.element.classList.contains("dlf-divider-grid__filler"));

            expect(fillers.length === 1, label(container, index) + " has " + fillers.length + " filler cells");

            if (!filler || content.length === 0) return;

            const fillerIsVisible = visible(filler);
            const containerRect = container.getBoundingClientRect();
            const finalBottom = Math.max(...content.map(item => item.rect.bottom));
            const finalRow = content.filter(item => Math.abs(item.rect.bottom - finalBottom) < 1.1);
            const finalRight = Math.max(...finalRow.map(item => item.rect.right));
            const needsFiller = finalRight < containerRect.right - 1.1;

            expect(fillerIsVisible === needsFiller, label(container, index) + " filler visibility does not match the incomplete final row");

            if (!fillerIsVisible) return;

            const fillerRect = filler.getBoundingClientRect();
            const fillerEdges = border(filler);
            const finalTop = Math.min(...finalRow.map(item => item.rect.top));

            expect(children.at(-1) === filler, label(container, index) + " filler is not the final visible grid cell");
            expect(Math.abs(fillerRect.left - finalRight) < 1.1, label(container, index) + " filler does not start after the final content cell");
            expect(Math.abs(fillerRect.right - containerRect.right) < 1.1, label(container, index) + " filler does not reach the grid right edge");
            expect(Math.abs(fillerRect.top - finalTop) < 1.1, label(container, index) + " filler does not align with the final row top");
            expect(Math.abs(fillerRect.bottom - finalBottom) < 1.1, label(container, index) + " filler does not align with the final row bottom");
            expect(exactlyOne(fillerEdges.left), label(container, index) + " filler left edge is " + fillerEdges.left + "px");
            expect(
                fillerRect.top > containerRect.top + 1.1 ? exactlyOne(fillerEdges.top) : fillerEdges.top === 0,
                label(container, index) + " filler top edge is " + fillerEdges.top + "px",
            );
        }
    };

    [...document.querySelectorAll(".dlf-fill-grid.dlf-divider-grid")].forEach((grid, index) => {
        expect(grid.classList.contains("dlf-divider-grid--fill"), label(grid, index) + " has patterned empty space without the fill primitive");
        expect(grid.querySelectorAll(":scope > .dlf-divider-grid__filler").length === 1, label(grid, index) + " must contain exactly one filler cell");
    });

    [...document.querySelectorAll(".dlf-divider-grid")].filter(visible).forEach((grid, index) => auditCollection(grid, index, "grid"));
    [...document.querySelectorAll(".dlf-divider-split")].filter(visible).forEach((split, index) => auditCollection(split, index, "split"));
    [...document.querySelectorAll(".dlf-divider-list")].filter(visible).forEach((list, index) => auditCollection(list, index, "list"));

    const publicPageBodies = [...document.querySelectorAll(".dlf-public-page__body")].filter(visible);

    if (window.innerWidth >= 1024) {
        publicPageBodies.forEach((body, index) => {
            const paddingBottom = px(getComputedStyle(body).paddingBottom);

            expect(
                paddingBottom >= 79.9,
                label(body, index) + " desktop terminal content padding is " + paddingBottom + "px; expected at least 80px",
            );
        });
    }

    expect(document.documentElement.scrollWidth <= document.documentElement.clientWidth, "page has horizontal overflow");

    return {
        failures,
        inspected: {
            sections: document.querySelectorAll(".dlf-divider-section").length,
            regions: document.querySelectorAll(".dlf-divider-region").length,
            grids: document.querySelectorAll(".dlf-divider-grid").length,
            splits: document.querySelectorAll(".dlf-divider-split").length,
            lists: document.querySelectorAll(".dlf-divider-list").length,
            adjacentBoundaries,
            ctaStages,
            editorialTerminalEdges,
            publicPageBodies: publicPageBodies.length,
        },
    };
})()`;

function browser(...command) {
    return execFileSync(
        "agent-browser",
        ["--session", session, "--ignore-https-errors", ...command],
        { encoding: "utf8", stdio: ["ignore", "pipe", "pipe"] },
    ).trim();
}

function parseResult(output) {
    const start = output.indexOf("{");
    const parsed = JSON.parse(output.slice(start));

    return typeof parsed === "string" ? JSON.parse(parsed) : parsed;
}

let failed = false;
let passed = 0;

try {
    for (const route of routes) {
        const url = new URL(route, baseUrl).toString();
        browser("open", url);

        for (const width of widths) {
            browser("set", "viewport", String(width), "1200");
            browser("reload");
            const result = parseResult(browser("eval", browserScript));
            const summary = `${route} @ ${width}px (${Object.values(result.inspected).reduce((sum, count) => sum + count, 0)} owners)`;

            if (result.failures.length === 0) {
                passed++;

                if (!quiet) {
                    console.log(`PASS ${summary}`);
                }

                continue;
            }

            failed = true;
            console.error(`FAIL ${summary}`);
            result.failures.forEach((failure) => console.error(`  - ${failure}`));
        }
    }
} finally {
    try {
        browser("close");
    } catch {
        // The audit result is more useful than a cleanup failure.
    }
}

console.log(
    `Audited ${routes.length} routes at ${widths.length} width${widths.length === 1 ? "" : "s"}: ${passed} passed.`,
);
process.exitCode = failed ? 1 : 0;
