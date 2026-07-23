import { describe, expect, it, mock } from "bun:test";

import {
    TRACKING_CONSENT_STORAGE_KEY,
    TRACKING_CONSENT_VERSION,
    initTrackingConsent,
} from "./tracking-consent";

class FakeElement {
    attributes = new Map();
    hidden = true;
    listeners = {};
    focus = mock(() => {});

    constructor(attributes = {}) {
        Object.entries(attributes).forEach(([name, value]) => {
            this.attributes.set(name, value);
        });
    }

    addEventListener(name, listener) {
        this.listeners[name] = listener;
    }

    click() {
        this.listeners.click?.();
    }

    getAttribute(name) {
        return this.attributes.get(name) ?? null;
    }

    removeAttribute(name) {
        this.attributes.delete(name);
    }

    setAttribute(name, value) {
        this.attributes.set(name, value);
    }
}

function makeStorage(value = null) {
    const values = new Map();

    if (value !== null) {
        values.set(TRACKING_CONSENT_STORAGE_KEY, JSON.stringify(value));
    }

    return {
        getItem: mock((key) => values.get(key) ?? null),
        setItem: mock((key, nextValue) => values.set(key, nextValue)),
    };
}

function makeConsentUi({
    additionalSettings = [],
    embeds = [],
    fallbacks = [],
    placeholders = [],
} = {}) {
    const banner = new FakeElement();
    const settings = new FakeElement();
    const accept = new FakeElement();
    const reject = new FakeElement();
    const elements = {
        "[data-tracking-consent-banner]": banner,
        "[data-tracking-consent-settings]": settings,
        "[data-tracking-consent-accept]": accept,
        "[data-tracking-consent-reject]": reject,
    };

    return {
        accept,
        banner,
        document: {
            cookie: "",
            location: { hostname: "example.test" },
            querySelector: (selector) => elements[selector] ?? null,
            querySelectorAll: (selector) => {
                if (selector === "[data-tracking-consent-settings]") {
                    return [settings, ...additionalSettings];
                }

                if (selector === "[data-consent-src]") {
                    return embeds;
                }

                if (selector === "[data-consent-placeholder]") {
                    return placeholders;
                }

                if (selector === "[data-consent-fallback]") {
                    return fallbacks;
                }

                return [];
            },
        },
        reject,
        settings,
    };
}

describe("tracking consent", () => {
    it("keeps consent-gated embeds unloaded until consent is granted", () => {
        const embed = new FakeElement({
            "data-consent-src": "https://www.youtube-nocookie.com/embed/example",
        });
        const placeholder = new FakeElement();
        placeholder.hidden = false;
        const fallback = new FakeElement();
        fallback.hidden = false;
        const ui = makeConsentUi({
            embeds: [embed],
            fallbacks: [fallback],
            placeholders: [placeholder],
        });

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage(),
            loadTrackers: () => {},
        });

        expect(embed.getAttribute("src")).toBeNull();
        expect(embed.hidden).toBeTrue();
        expect(fallback.hidden).toBeFalse();
        expect(placeholder.hidden).toBeFalse();

        ui.accept.click();

        expect(embed.getAttribute("src")).toBe("https://www.youtube-nocookie.com/embed/example");
        expect(embed.getAttribute("data-consent-src")).toBeNull();
        expect(embed.hidden).toBeFalse();
        expect(fallback.hidden).toBeTrue();
        expect(placeholder.hidden).toBeTrue();
    });

    it("keeps trackers blocked while consent is undecided", () => {
        const ui = makeConsentUi();
        const loadTrackers = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage(),
            loadTrackers,
        });

        expect(loadTrackers).not.toHaveBeenCalled();
        expect(ui.banner.hidden).toBeFalse();
        expect(ui.settings.hidden).toBeTrue();
    });

    it("keeps trackers blocked when consent was rejected", () => {
        const ui = makeConsentUi();
        const loadTrackers = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage({
                version: TRACKING_CONSENT_VERSION,
                choice: "rejected",
            }),
            loadTrackers,
        });

        expect(loadTrackers).not.toHaveBeenCalled();
        expect(ui.banner.hidden).toBeTrue();
        expect(ui.settings.hidden).toBeFalse();
    });

    it("clears stale tracker cookies when consent is rejected", () => {
        const ui = makeConsentUi();
        const clearTrackerCookies = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage({
                version: TRACKING_CONSENT_VERSION,
                choice: "rejected",
            }),
            clearTrackerCookies,
        });

        expect(clearTrackerCookies).toHaveBeenCalledTimes(1);
    });

    it("clears stale tracker cookies while consent is undecided", () => {
        const ui = makeConsentUi();
        const clearTrackerCookies = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage(),
            clearTrackerCookies,
        });

        expect(clearTrackerCookies).toHaveBeenCalledTimes(1);
    });

    it("loads trackers when current-version consent was accepted", () => {
        const ui = makeConsentUi();
        const loadTrackers = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage({
                version: TRACKING_CONSENT_VERSION,
                choice: "accepted",
            }),
            loadTrackers,
        });

        expect(loadTrackers).toHaveBeenCalledTimes(1);
        expect(ui.banner.hidden).toBeTrue();
        expect(ui.settings.hidden).toBeFalse();
    });

    it("asks again when the stored consent version is stale", () => {
        const ui = makeConsentUi();
        const loadTrackers = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage({ version: 0, choice: "accepted" }),
            loadTrackers,
        });

        expect(loadTrackers).not.toHaveBeenCalled();
        expect(ui.banner.hidden).toBeFalse();
    });

    it("persists acceptance before loading trackers", () => {
        const ui = makeConsentUi();
        const storage = makeStorage();
        const loadTrackers = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage,
            loadTrackers,
        });
        ui.accept.click();

        expect(storage.setItem).toHaveBeenCalledWith(
            TRACKING_CONSENT_STORAGE_KEY,
            JSON.stringify({
                version: TRACKING_CONSENT_VERSION,
                choice: "accepted",
            }),
        );
        expect(loadTrackers).toHaveBeenCalledTimes(1);
        expect(ui.banner.hidden).toBeTrue();
        expect(ui.settings.hidden).toBeFalse();
    });

    it("does not move initial-banner focus to the footer after a choice", () => {
        const ui = makeConsentUi();

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage(),
            loadTrackers: () => {},
        });
        ui.accept.click();

        expect(ui.settings.focus).not.toHaveBeenCalled();
    });

    it("opens consent settings from an embed placeholder", () => {
        const embedSettings = new FakeElement();
        const ui = makeConsentUi({ additionalSettings: [embedSettings] });

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage({
                version: TRACKING_CONSENT_VERSION,
                choice: "rejected",
            }),
        });
        embedSettings.click();

        expect(ui.banner.hidden).toBeFalse();
        expect(ui.settings.hidden).toBeTrue();
        expect(embedSettings.hidden).toBeTrue();
    });

    it("reloads after withdrawing previously accepted consent", () => {
        const ui = makeConsentUi();
        const clearTrackerCookies = mock(() => {});
        const reload = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage({
                version: TRACKING_CONSENT_VERSION,
                choice: "accepted",
            }),
            clearTrackerCookies,
            loadTrackers: () => {},
            reload,
        });
        ui.settings.click();
        ui.reject.click();

        expect(ui.banner.focus).toHaveBeenCalledTimes(1);
        expect(clearTrackerCookies).toHaveBeenCalledTimes(1);
        expect(reload).toHaveBeenCalledTimes(1);
    });

    it("reloads after accepting and withdrawing consent on the same page", () => {
        const ui = makeConsentUi();
        const clearTrackerCookies = mock(() => {});
        const reload = mock(() => {});

        initTrackingConsent({
            document: ui.document,
            storage: makeStorage({
                version: TRACKING_CONSENT_VERSION,
                choice: "rejected",
            }),
            clearTrackerCookies,
            loadTrackers: () => {},
            reload,
        });
        ui.settings.click();
        ui.accept.click();
        ui.settings.click();
        ui.reject.click();

        expect(clearTrackerCookies).toHaveBeenCalledTimes(2);
        expect(reload).toHaveBeenCalledTimes(1);
    });
});
