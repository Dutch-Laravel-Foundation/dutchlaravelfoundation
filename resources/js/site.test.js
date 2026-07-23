import { beforeAll, describe, expect, it, mock } from "bun:test";

const header = {
    offsetHeight: 142,
};
let headroomOptions;
let resizeCallback;
let resizedElement;

class FakeHeadroom {
    constructor(element, options) {
        expect(element).toBe(header);
        headroomOptions = options;
    }

    init() {}
}

mock.module("headroom.js", () => ({
    default: FakeHeadroom,
}));

mock.module("@alpinejs/csp", () => ({
    default: {
        data: () => {},
        start: () => {},
    },
}));

globalThis.document = {
    documentElement: {
        dataset: {
            environment: "testing",
        },
    },
    documentMode: false,
    querySelector: (selector) => (selector === "header" ? header : null),
    querySelectorAll: () => [],
};

globalThis.window = {
    addEventListener: () => {},
    localStorage: {},
};

globalThis.ResizeObserver = class {
    constructor(callback) {
        resizeCallback = callback;
    }

    observe(element) {
        resizedElement = element;
    }
};

describe("site header visibility", () => {
    beforeAll(async () => {
        await import("./site");
    });

    it("starts hiding only after scrolling beyond the rendered header height", () => {
        expect(headroomOptions.offset).toEqual({
            down: 142,
            up: 142,
        });
    });

    it("keeps the threshold synchronized with responsive header height changes", () => {
        expect(resizedElement).toBe(header);
        expect(resizeCallback).toBeFunction();

        header.offsetHeight = 115;
        resizeCallback();

        expect(headroomOptions.offset).toEqual({
            down: 115,
            up: 115,
        });
    });
});
