import { describe, expect, it, mock } from "bun:test";

const headerBehavior = await import("./useHeaderBehavior");

describe("header behavior", () => {
    it("uses the visit-intent event so cached prefetches reveal only when clicked", async () => {
        const source = await Bun.file(new URL("./useHeaderBehavior.ts", import.meta.url)).text();

        expect(source).toContain('router.on("before"');
        expect(source).not.toContain('router.on("start"');
    });

    it("ignores Inertia prefetch requests", () => {
        expect(headerBehavior.shouldRevealHeaderForVisit).toBeFunction();
        expect(headerBehavior.shouldRevealHeaderForVisit({ prefetch: true })).toBeFalse();
        expect(headerBehavior.shouldRevealHeaderForVisit({ prefetch: false })).toBeTrue();
    });

    it("reveals a hidden header immediately for an Inertia navigation", () => {
        expect(headerBehavior.revealHeaderForNavigation).toBeFunction();

        const classes = new Set(["slideUp"]);
        const header = {
            classList: {
                add: (...names) => names.forEach((name) => classes.add(name)),
                remove: (...names) => names.forEach((name) => classes.delete(name)),
            },
            getBoundingClientRect: mock(() => ({})),
        };
        const headroom = {
            pin: mock(() => {
                classes.delete("slideUp");
                classes.add("slideDown");
            }),
        };
        let releaseInstantState;
        const scheduleFrame = mock((callback) => {
            releaseInstantState = callback;

            return 42;
        });

        const frame = headerBehavior.revealHeaderForNavigation(header, headroom, scheduleFrame);

        expect(frame).toBe(42);
        expect(headroom.pin).toHaveBeenCalledTimes(1);
        expect(header.getBoundingClientRect).toHaveBeenCalledTimes(1);
        expect(classes).toContain("dlf-header--instant");
        expect(classes).toContain("slideDown");
        expect(classes).not.toContain("slideUp");

        releaseInstantState();

        expect(classes).not.toContain("dlf-header--instant");
    });

    it("does not intercept hard-refresh scroll restoration", async () => {
        const source = await Bun.file(new URL("./useHeaderBehavior.ts", import.meta.url)).text();

        expect(source).not.toContain('performance.getEntriesByType("navigation")');
        expect(source).not.toContain("hideHeaderForRestoredScroll");
        expect(source).not.toContain("monitoringRestoredScroll");
    });

    it("recognizes only same-document anchor destinations", () => {
        expect(headerBehavior.isSameDocumentAnchor).toBeFunction();
        expect(
            headerBehavior.isSameDocumentAnchor(
                "#section-two",
                "https://example.test/article?preview=1",
            ),
        ).toBeTrue();
        expect(
            headerBehavior.isSameDocumentAnchor(
                "https://example.test/article?preview=1#section-two",
                "https://example.test/article?preview=1#section-one",
            ),
        ).toBeTrue();
        expect(
            headerBehavior.isSameDocumentAnchor(
                "/another-article#section-two",
                "https://example.test/article?preview=1",
            ),
        ).toBeFalse();
    });

    it("keeps the current header state frozen until a programmatic scroll jump settles", () => {
        expect(headerBehavior.preserveHeaderDuringScrollJump).toBeFunction();

        let scrollListener;
        let timeoutCallback;
        const frames = [];
        const headroom = {
            freeze: mock(() => {}),
            unfreeze: mock(() => {}),
        };
        const browserWindow = {
            addEventListener: mock((eventName, listener) => {
                if (eventName === "scroll") {
                    scrollListener = listener;
                }
            }),
            removeEventListener: mock(() => {}),
            setTimeout: mock((callback) => {
                timeoutCallback = callback;

                return 7;
            }),
            clearTimeout: mock(() => {}),
        };
        const scheduleFrame = mock((callback) => {
            frames.push(callback);

            return frames.length;
        });
        const cancelFrame = mock(() => {});

        headerBehavior.preserveHeaderDuringScrollJump(headroom, {
            browserWindow,
            scheduleFrame,
            cancelFrame,
        });

        expect(headroom.freeze).toHaveBeenCalledTimes(1);
        expect(headroom.unfreeze).not.toHaveBeenCalled();

        scrollListener();
        frames.shift()();
        expect(headroom.unfreeze).not.toHaveBeenCalled();

        frames.shift()();
        expect(headroom.unfreeze).toHaveBeenCalledTimes(1);
        expect(browserWindow.removeEventListener).toHaveBeenCalledWith("scroll", scrollListener);
        expect(browserWindow.clearTimeout).toHaveBeenCalledWith(7);

        timeoutCallback();
        expect(headroom.unfreeze).toHaveBeenCalledTimes(1);
    });

    it("preserves the current header state during native form validation jumps", async () => {
        const source = await Bun.file(new URL("./useHeaderBehavior.ts", import.meta.url)).text();

        expect(source).toContain('document.addEventListener("invalid", handleInvalid, true)');
        expect(source).toContain('document.removeEventListener("invalid", handleInvalid, true)');
        expect(source).toMatch(
            /const handleInvalid = \(\) => \{\s*preserveCurrentHeaderState\(\);\s*\}/,
        );
    });
});
