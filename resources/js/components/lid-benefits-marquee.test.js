import { describe, expect, it, mock } from "bun:test";

globalThis.document = {
    querySelectorAll: () => [],
};
globalThis.window = {
    matchMedia: () => ({ matches: false }),
    requestAnimationFrame: mock(() => {}),
};
const { initializeBenefitsScroller } = await import("./lid-benefits-marquee");

describe("member benefits scroller", () => {
    it("starts at the left edge when benefits use one row", () => {
        const toggleClass = mock(() => {});
        const scroller = {
            children: [{}, {}],
            classList: { toggle: toggleClass },
            clientWidth: 400,
            scrollLeft: 0,
            scrollWidth: 1200,
            tabIndex: -1,
            addEventListener: mock(() => {}),
        };

        window.matchMedia = mock((query) => ({
            matches: query === "(max-width: 1023px)",
        }));

        initializeBenefitsScroller(scroller);

        expect(scroller.scrollLeft).toBe(0);
        expect(toggleClass).toHaveBeenCalledWith("can-scroll-left", false);
        expect(toggleClass).toHaveBeenCalledWith("can-scroll-right", true);
    });

    it("keeps one finite set of benefits and supports keyboard scrolling", () => {
        const listeners = new Map();
        const items = [
            { cloneNode: mock(() => ({})) },
            { cloneNode: mock(() => ({})) },
            { cloneNode: mock(() => ({})) },
        ];
        const scrollBy = mock(() => {});
        const toggleClass = mock(() => {});
        const scroller = {
            children: items,
            classList: { toggle: toggleClass },
            clientWidth: 1000,
            scrollLeft: 0,
            scrollWidth: 2000,
            tabIndex: -1,
            addEventListener: (eventName, listener) => listeners.set(eventName, listener),
            scrollBy,
        };

        window.matchMedia = mock(() => ({ matches: false }));

        initializeBenefitsScroller(scroller);

        expect(scroller.children).toHaveLength(3);
        expect(items.every((item) => item.cloneNode.mock.calls.length === 0)).toBeTrue();
        expect(scroller.tabIndex).toBe(0);
        expect(window.requestAnimationFrame).toHaveBeenCalledTimes(0);
        expect(scroller.scrollLeft).toBe(1000);
        expect(toggleClass).toHaveBeenCalledWith("can-scroll-left", true);
        expect(toggleClass).toHaveBeenCalledWith("can-scroll-right", false);

        scroller.scrollLeft = 0;
        listeners.get("scroll")();

        expect(toggleClass).toHaveBeenCalledWith("can-scroll-left", false);
        expect(toggleClass).toHaveBeenCalledWith("can-scroll-right", true);

        const preventDefault = mock(() => {});
        listeners.get("keydown")({ key: "ArrowRight", preventDefault });

        expect(preventDefault).toHaveBeenCalledTimes(1);
        expect(scrollBy).toHaveBeenCalledWith({ left: 320, behavior: "smooth" });
    });
});
