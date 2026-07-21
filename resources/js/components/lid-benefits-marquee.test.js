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
    it("uses exact item positions when aligning the final visible items", () => {
        const toggleClass = mock(() => {});
        const setProperty = mock(() => {});
        let scrollLeft = 0;
        const scroller = {
            children: [],
            classList: { toggle: toggleClass },
            clientWidth: 400,
            dataset: { lidBenefitsAlignEndItems: "2" },
            scrollWidth: 1200,
            style: { setProperty },
            tabIndex: -1,
            addEventListener: mock(() => {}),
        };
        Object.defineProperty(scroller, "scrollLeft", {
            get: () => scrollLeft,
            set: (value) => {
                scrollLeft = Math.round(value);
            },
        });
        scroller.children = [0, 399.75, 799.5, 1199.25].map((left) => ({
            getBoundingClientRect: () => ({ left: left - scrollLeft }),
        }));

        window.matchMedia = mock(() => ({ matches: false }));

        initializeBenefitsScroller(scroller);

        expect(scroller.scrollLeft).toBe(800);
        expect(setProperty).toHaveBeenCalledWith(
            "--dlf-lid-benefits-alignment-offset",
            "0.5px",
        );
    });

    it("starts at the right edge when benefits use one row", () => {
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

        expect(scroller.scrollLeft).toBe(800);
        expect(toggleClass).toHaveBeenCalledWith("can-scroll-left", true);
        expect(toggleClass).toHaveBeenCalledWith("can-scroll-right", false);
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
