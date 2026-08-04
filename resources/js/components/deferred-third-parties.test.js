import { describe, expect, it } from "bun:test";

import {
    initDeferredThirdParties,
    revokeDeferredThirdParties,
} from "./deferred-third-parties";

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

    it("revokes Google consent and removes tracker-owned elements without reloading", () => {
        const removed = [];
        const trackerElements = [
            { remove: () => removed.push("gtm") },
            { remove: () => removed.push("leadinfo") },
            { remove: () => removed.push("linkedin") },
        ];
        const browserWindow = {
            GlobalLeadinfoNamespace: ["leadinfo"],
            _linkedin_data_partner_ids: ["8379674"],
            dataLayer: [],
            leadinfo: () => {},
            lintrk: () => {},
        };

        revokeDeferredThirdParties({
            document: { querySelectorAll: () => trackerElements },
            window: browserWindow,
        });

        expect(removed).toEqual(["gtm", "leadinfo", "linkedin"]);
        expect(browserWindow.dataLayer.at(-1)).toEqual([
            "consent",
            "update",
            {
                ad_personalization: "denied",
                ad_storage: "denied",
                ad_user_data: "denied",
                analytics_storage: "denied",
            },
        ]);
        expect(browserWindow.GlobalLeadinfoNamespace).toEqual([]);
        expect(browserWindow._linkedin_data_partner_ids).toEqual([]);
        expect(browserWindow.leadinfo.q).toEqual([]);
        expect(browserWindow.lintrk.q).toEqual([]);

        const scripts = [];

        initDeferredThirdParties({
            document: {
                createElement: () => ({ setAttribute() {} }),
                head: { append: (script) => scripts.push(script) },
                querySelector: () => null,
            },
            window: browserWindow,
        });

        expect(scripts).toHaveLength(3);
        expect(browserWindow.leadinfo.dlfRevoked).toBeUndefined();
        expect(browserWindow.lintrk.dlfRevoked).toBeUndefined();
    });
});
