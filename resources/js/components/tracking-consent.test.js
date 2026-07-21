import { describe, expect, it, mock } from "bun:test";

import {
    TRACKING_CONSENT_STORAGE_KEY,
    TRACKING_CONSENT_VERSION,
    initTrackingConsent,
} from "./tracking-consent";

class FakeElement {
    hidden = true;
    listeners = {};
    focus = mock(() => {});

    addEventListener(name, listener) {
        this.listeners[name] = listener;
    }

    click() {
        this.listeners.click?.();
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

function makeConsentUi() {
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
            querySelector: (selector) => elements[selector] ?? null,
        },
        reject,
        settings,
    };
}

describe("tracking consent", () => {
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
});
