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
});
