import { describe, expect, it } from "bun:test";

import { initDeferredThirdParties } from "./deferred-third-parties";

describe("deferred third parties", () => {
    it("loads every required tracker after the consent gate opens", () => {
        const scripts = [];
        const document = {
            createElement: () => ({
                setAttribute(name, value) {
                    this[name] = value;
                },
            }),
            head: { append: (script) => scripts.push(script) },
            querySelector: () => null,
        };

        initDeferredThirdParties({ document, window: {} });

        expect(scripts.map((script) => script.src)).toEqual([
            "https://www.googletagmanager.com/gtm.js?id=GTM-N75FRC56",
            "https://cdn.leadinfo.net/ping.js",
            "https://snap.licdn.com/li.lms-analytics/insight.min.js",
        ]);
    });

    it("hands Leadinfo its account id via the tracking namespace", () => {
        const document = {
            createElement: () => ({ setAttribute() {} }),
            head: { append: () => {} },
            querySelector: () => null,
        };
        const window = {};

        initDeferredThirdParties({ document, window });

        expect(window.leadinfo.t).toBe("LI-643558C020FD3");
    });
});
