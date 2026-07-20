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
            "https://cdn.usefathom.com/script.js",
        ]);
        expect(scripts[3]["data-site"]).toBe("XTVPLNKC");
    });
});
